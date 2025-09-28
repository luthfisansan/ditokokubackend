<?php

namespace Modules\Gateways\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Gateways\Entities\PaymentRequest;
use Modules\Gateways\Traits\Processor;

class OrangeMoneyController extends Controller
{
    use Processor;

    private mixed $config_values;
    private string $config_mode = 'test';
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('orange_money', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }

        if ($config) {
            $this->config_mode = ($config->mode == 'test') ? 'test' : 'live';
        }

        $this->payment = $payment;
    }

    public function payment(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $payer = json_decode($data['payer_information']);
        $mobile_number = isset($payer->phone) ? (string)$payer->phone : null;
        $mobile_number = $mobile_number ? (string)str_replace([' ', '-', '+'], '', $mobile_number) : null;

        if (!$mobile_number) {
            $payment_data = $data;
            if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
            return $this->payment_response($payment_data, 'fail');
        }

        $response = $this->makePayment($data->id, $mobile_number);

        if ($response->successful()) {
            $body = $response->json();
            $transactionId = $body['transactionId'] ?? $body['transaction_id'] ?? $body['financialTransactionId'] ?? null;
            $status = $body['status'] ?? $body['status_code'] ?? null;
            $isSuccessful = in_array(strtoupper((string)$status), ['SUCCESS', 'SUCCESSFUL', '200', '201']);

            if ($isSuccessful) {
                $this->payment::where(['id' => $data->id])->update([
                    'payment_method' => 'orange_money',
                    'is_paid' => 1,
                    'transaction_id' => $transactionId ?? Str::uuid(),
                ]);

                $fresh = $this->payment::where(['id' => $data->id])->first();
                if (isset($fresh) && function_exists($fresh->success_hook)) {
                    call_user_func($fresh->success_hook, $fresh);
                }
                return $this->payment_response($fresh, 'success');
            }
        }

        $payment_data = $this->payment::where(['id' => $data->id])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }

    public function callback(Request $request): Application|JsonResponse|Redirector|\Illuminate\Contracts\Foundation\Application|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'paymentID' => 'required|uuid',
            'mobile_number' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $payment_id = $request->paymentID;
        $data = $this->payment::where(['id' => $payment_id])->first();
        $payer = $data ? json_decode($data['payer_information']) : null;
        $mobile_number = $request->mobile_number ?? ($payer->phone ?? null);
        $mobile_number = $mobile_number ? (string)str_replace([' ', '-', '+'], '', $mobile_number) : null;
        if (!$mobile_number) {
            if (isset($data) && function_exists($data->failure_hook)) {
                call_user_func($data->failure_hook, $data);
            }
            return $this->payment_response($data, 'fail');
        }
        $response = $this->makePayment($payment_id, $mobile_number);

        if ($response->successful()) {
            $body = $response->json();

            $isSuccessful = false;
            $transactionId = null;
            if (is_array($body)) {
                $transactionId = $body['transactionId'] ?? $body['transaction_id'] ?? $body['financialTransactionId'] ?? null;
                $status = $body['status'] ?? $body['status_code'] ?? null;
                $isSuccessful = in_array(strtoupper((string)$status), ['SUCCESS', 'SUCCESSFUL', '200', '201']);
            }

            if ($isSuccessful) {
                $this->payment::where(['id' => $payment_id])->update([
                    'payment_method' => 'orange_money',
                    'is_paid' => 1,
                    'transaction_id' => $transactionId ?? Str::uuid(),
                ]);

                $data = $this->payment::where(['id' => $payment_id])->first();
                if (isset($data) && function_exists($data->success_hook)) {
                    call_user_func($data->success_hook, $data);
                }
                return $this->payment_response($data, 'success');
            }
        }

        $payment_data = $this->payment::where(['id' => $payment_id])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }

    public function makePayment(string $payment_id, string $mobile_number)
    {
        $payment_data = $this->payment::where(['id' => $payment_id])->first();
        $amount = (int)$payment_data->payment_amount;

        $baseUrl = $this->config_mode == 'test' ? 'https://api.sandbox.orange-sonatel.com' : 'https://api.orange-sonatel.com';
        $clientId = (string)($this->config_values->client_id ?? '');
        $clientSecret = (string)($this->config_values->client_secret ?? '');

        $tokenResponse = Http::asForm()->post($baseUrl . '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (!$tokenResponse->successful()) {
            return $tokenResponse; // bubble up error
        }

        $accessToken = (string)$tokenResponse->json('access_token');

        // Orange Money eWallet Cashin (subject to product enablement). Adjust endpoint per product subscription.
        $payload = [
            'msisdn' => $mobile_number,
            'amount' => $amount,
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($baseUrl . '/api/eWallet/v1/cashin', $payload);
    }
}


