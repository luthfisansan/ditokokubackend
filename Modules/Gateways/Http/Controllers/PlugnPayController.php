<?php

namespace Modules\Gateways\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Entities\PaymentRequest;
use Modules\Gateways\Traits\Processor;

class PlugnPayController extends Controller
{
    use Processor;

    private $configValues;

    private PaymentRequest $payment;
    private User $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('plugnpay', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->configValues = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->configValues = json_decode($config->test_values);
        }

        $this->payment = $payment;
        $this->user = $user;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return \response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return \response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $callbackUrl = \route('plugnpay.callback', ['payment_id' => $data->id]);

        $payer = json_decode($data->payer_information);

        $postData = [
            'pt_gateway_account' => $this->configValues->pt_gateway_account ?? null,
            'pt_transaction_amount' => $data->payment_amount,
            'pt_currency' => $data->currency_code ?? 'USD',
            'pt_billing_name'          => $payer->name,
            'pt_billing_phone_number'  => $payer->phone,
            'pt_billing_email_address' => $payer->email,
            'pb_transition_type'       => 'hidden',
            'pb_response_url' => $callbackUrl,
            'pb_bad_card_url' => $callbackUrl,
            'pb_problem_url' => $callbackUrl,
            'pb_success_url' => $callbackUrl,
        ];

        $formHtml = '<form id="redirectForm" method="POST" action="https://pay1.plugnpay.com/pay/">';
        foreach ($postData as $key => $value) {
            if ($value === null) continue;
            $formHtml .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars((string)$value) . '">';
        }
        $formHtml .= '</form>';
        $formHtml .= '<script>document.getElementById("redirectForm").submit();</script>';

        return \response($formHtml);
    }

    public function callback(Request $request)
    {
        $validator = Validator::make(['payment_id' => $request->query('payment_id')], [
            'payment_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return \response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $paymentId = $request->query('payment_id');
        $paymentData = $this->payment::where(['id' => $paymentId])->first();
        if (!isset($paymentData)) {
            return \response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        $status = strtolower((string)$request->input('pi_response_status', ''));
        $responseCode = (string)$request->input('pi_response_code', '');

        if ($status === 'success' || $responseCode === '00') {
            $this->payment::where(['id' => $paymentId])->update([
                'payment_method' => 'plugnpay',
                'is_paid' => 1,
                'transaction_id' => $request->input('pt_order_id') ?: $request->input('pt_authorization_code') ?: $request->input('pt_payment_session'),
            ]);

            $updated = $this->payment::where(['id' => $paymentId])->first();
            if (isset($updated) && function_exists($updated->success_hook)) {
                call_user_func($updated->success_hook, $updated);
            }
            // IMPORTANT: PlugnPay "hidden POST" proxies our HTML, so Location headers won't change the browser URL.
            // Return a minimal HTML page with <base> and a JS/meta refresh redirect to force the browser to our domain.
            $finalUrl = $this->buildFinalRedirectUrl($updated, 'success');
            return $this->htmlRedirectResponse($finalUrl);
        }

        if (isset($paymentData) && function_exists($paymentData->failure_hook)) {
            call_user_func($paymentData->failure_hook, $paymentData);
        }
        $finalUrl = $this->buildFinalRedirectUrl($paymentData, 'fail');
        return $this->htmlRedirectResponse($finalUrl);
    }

    /**
     * Build the final URL exactly like Processor::payment_response would, but as a string.
     */
    private function buildFinalRedirectUrl(PaymentRequest $payment_info, string $flag): string
    {
        // Mirror Processor::payment_response logic, returning a string URL instead of a RedirectResponse
        $payment_info = PaymentRequest::find($payment_info->id);
        $token_string = 'payment_method=' . $payment_info->payment_method . '&&attribute_id=' . $payment_info->attribute_id . '&&transaction_reference=' . $payment_info->transaction_id;
        if (in_array($payment_info->payment_platform, ['web', 'app']) && $payment_info['external_redirect_link'] != null) {
            return $payment_info['external_redirect_link'] . '?flag=' . $flag . '&&token=' . base64_encode($token_string);
        }
        return \route('payment-' . $flag, ['token' => base64_encode($token_string)]);
    }

    /**
     * Return a tiny HTML page that sets a <base> and client-redirects to the provided absolute URL.
     */
    private function htmlRedirectResponse(string $absoluteUrl)
    {
    // Derive base href from the absolute URL to ensure relative assets resolve to our domain.
    $parts = parse_url($absoluteUrl);
    $scheme = $parts['scheme'] ?? 'https';
    $host = $parts['host'] ?? '';
    $port = isset($parts['port']) ? (":" . $parts['port']) : '';
    $baseHref = ($host !== '') ? ($scheme . '://' . $host . $port . '/') : '/';
        $html = '<!doctype html>'
            . '<html lang="en">'
            . '<head>'
            . '<meta charset="utf-8">'
            . '<meta http-equiv="x-ua-compatible" content="ie=edge">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<base href="' . htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') . '/">'
            . '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($absoluteUrl, ENT_QUOTES, 'UTF-8') . '">'
            . '<title>Redirecting…</title>'
            . '</head>'
            . '<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; padding:24px; text-align:center;">'
            . '<p>Redirecting you securely… <a href="' . htmlspecialchars($absoluteUrl, ENT_QUOTES, 'UTF-8') . '">Continue</a></p>'
            . '<script>'
            . 'try { window.top.location.replace(' . json_encode($absoluteUrl) . '); } catch (e) { window.location.href = ' . json_encode($absoluteUrl) . '; }'
            . '</script>'
            . '</body>'
            . '</html>';

    return new HttpResponse($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}


