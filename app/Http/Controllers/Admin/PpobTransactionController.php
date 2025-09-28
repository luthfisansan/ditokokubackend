<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpobTransaction;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class PpobTransactionController extends Controller
{
    public function index(Request $request)
    {
        $key = explode(' ', $request['search']);
        
        $transactions = PpobTransaction::when($request->has('search'), function ($query) use ($key) {
                foreach ($key as $value) {
                    $query->where('ref_id', 'like', "%{$value}%")
                          ->orWhere('customer_no', 'like', "%{$value}%")
                          ->orWhere('buyer_sku_code', 'like', "%{$value}%");
                }
            })
            ->when($request->has('status') && $request->status != 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('id', 'desc')
            ->paginate(config('default_pagination'));

        return view('admin-views.ppob.transactions.index', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = PpobTransaction::findOrFail($id);
        return view('admin-views.ppob.transactions.show', compact('transaction'));
    }

    public function create()
    {
        return view('admin-views.ppob.transactions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ref_id' => 'required|string|unique:ppob_transactions',
            'customer_no' => 'required|string',
            'buyer_sku_code' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:Pending,Success,Failed',
        ]);

        try {
            PpobTransaction::create([
                'ref_id' => $request->ref_id,
                'customer_no' => $request->customer_no,
                'buyer_sku_code' => $request->buyer_sku_code,
                'message' => $request->message ?? '',
                'status' => $request->status ?? 'Pending',
                'rc' => $request->rc ?? '',
                'buyer_last_saldo' => $request->buyer_last_saldo ?? 0,
                'sn' => $request->sn ?? '',
                'price' => $request->price,
                'tele' => $request->tele ?? '',
                'wa' => $request->wa ?? '',
            ]);

            Toastr::success(translate('messages.transaction_created_successfully'));
            return redirect()->route('admin.ppob.transactions.index');
        } catch (\Exception $e) {
            Toastr::error(translate('messages.failed_to_create_transaction'));
            return back()->withInput();
        }
    }

    public function edit($id)
    {
        $transaction = PpobTransaction::findOrFail($id);
        return view('admin-views.ppob.transactions.edit', compact('transaction'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_no' => 'required|string',
            'buyer_sku_code' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:Pending,Success,Failed',
        ]);

        try {
            $transaction = PpobTransaction::findOrFail($id);
            
            $transaction->update([
                'customer_no' => $request->customer_no,
                'buyer_sku_code' => $request->buyer_sku_code,
                'message' => $request->message,
                'status' => $request->status,
                'rc' => $request->rc,
                'buyer_last_saldo' => $request->buyer_last_saldo ?? 0,
                'sn' => $request->sn,
                'price' => $request->price,
                'tele' => $request->tele,
                'wa' => $request->wa,
            ]);

            Toastr::success(translate('messages.transaction_updated_successfully'));
            return redirect()->route('admin.ppob.transactions.index');
        } catch (\Exception $e) {
            Toastr::error(translate('messages.failed_to_update_transaction'));
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $transaction = PpobTransaction::findOrFail($id);
            $transaction->delete();

            Toastr::success(translate('messages.transaction_deleted_successfully'));
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => translate('messages.failed_to_delete_transaction')]);
        }
    }

    // Status-based methods for sidebar
    public function all()
    {
        return $this->index(request()->merge(['status' => 'all']));
    }

    public function pending()
    {
        return $this->index(request()->merge(['status' => 'Pending']));
    }

    public function success()
    {
        return $this->index(request()->merge(['status' => 'Success']));
    }

    public function failed()
    {
        return $this->index(request()->merge(['status' => 'Failed']));
    }
}