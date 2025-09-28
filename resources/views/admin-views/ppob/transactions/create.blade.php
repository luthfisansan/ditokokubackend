@extends('layouts.admin.app')

@section('title', translate('messages.add_new_ppob_transaction'))

@push('css_or_js')
    <style>
        .profit-display {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 10px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon">
                            <img src="{{ asset('/public/public/assets/admin/img/receipt.png') }}" class="w--20" alt="">
                        </span>
                        <span>{{ translate('messages.add_new_ppob_transaction') }}</span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{ route('admin.ppob.transactions.store') }}" method="post" enctype="multipart/form-data" id="transactionForm">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="ref_id">{{ translate('messages.ref_id') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="ref_id" class="form-control" placeholder="{{ translate('messages.ref_id') }}" 
                                               value="{{ old('ref_id') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="customer_no">{{ translate('messages.customer_no') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_no" class="form-control" 
                                               placeholder="{{ translate('messages.customer_no') }}" 
                                               value="{{ old('customer_no') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="buyer_sku_code">{{ translate('messages.buyer_sku_code') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="buyer_sku_code" class="form-control" 
                                               placeholder="{{ translate('messages.buyer_sku_code') }}" 
                                               value="{{ old('buyer_sku_code') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="price">{{ translate('messages.price') }} (Selling Price) <span class="text-danger">*</span></label>
                                        <input type="number" name="price" id="price" class="form-control" 
                                               placeholder="{{ translate('messages.price') }}" 
                                               value="{{ old('price') }}" step="0.01" min="0" required onchange="calculateProfit()">
                                        <small class="text-muted">Harga jual kepada customer</small>
                                    </div>
                                </div>
                                <!-- NEW: Original Price Field -->
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="original_price">{{ translate('messages.original_price') }} (Cost Price) <span class="text-danger">*</span></label>
                                        <input type="number" name="original_price" id="original_price" class="form-control" 
                                               placeholder="Harga modal/cost price" 
                                               value="{{ old('original_price') }}" step="0.01" min="0" required onchange="calculateProfit()">
                                        <small class="text-muted">Harga modal dari supplier</small>
                                    </div>
                                </div>
                                <!-- Profit Display -->
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label">Profit Calculation</label>
                                        <div class="profit-display" id="profitDisplay">
                                            <span id="profitAmount">Rp 0</span>
                                            <br>
                                            <small id="profitPercentage">0% margin</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="status">{{ translate('messages.status') }} <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control" required>
                                            <option value="">{{ translate('messages.select_status') }}</option>
                                            <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>{{ translate('messages.pending') }}</option>
                                            <option value="Success" {{ old('status') == 'Success' ? 'selected' : '' }}>{{ translate('messages.success') }}</option>
                                            <option value="Failed" {{ old('status') == 'Failed' ? 'selected' : '' }}>{{ translate('messages.failed') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="rc">{{ translate('messages.response_code') }}</label>
                                        <input type="text" name="rc" class="form-control" 
                                               placeholder="{{ translate('messages.response_code') }}" 
                                               value="{{ old('rc') }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="buyer_last_saldo">{{ translate('messages.buyer_last_saldo') }}</label>
                                        <input type="number" name="buyer_last_saldo" class="form-control" 
                                               placeholder="{{ translate('messages.buyer_last_saldo') }}" 
                                               value="{{ old('buyer_last_saldo', 0) }}" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="sn">{{ translate('messages.serial_number') }}</label>
                                        <input type="text" name="sn" class="form-control" 
                                               placeholder="{{ translate('messages.serial_number') }}" 
                                               value="{{ old('sn') }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="tele">{{ translate('messages.telephone') }}</label>
                                        <input type="text" name="tele" class="form-control" 
                                               placeholder="{{ translate('messages.telephone') }}" 
                                               value="{{ old('tele') }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label" for="wa">{{ translate('messages.whatsapp') }}</label>
                                        <input type="text" name="wa" class="form-control" 
                                               placeholder="{{ translate('messages.whatsapp') }}" 
                                               value="{{ old('wa') }}">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="input-label" for="message">{{ translate('messages.message') }}</label>
                                        <textarea name="message" class="form-control" rows="3" 
                                                  placeholder="{{ translate('messages.message') }}">{{ old('message') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="btn--container justify-content-end">
                                <a class="btn btn--reset" href="{{ route('admin.ppob.transactions.index') }}">
                                    {{ translate('messages.reset') }}
                                </a>
                                <button type="submit" class="btn btn--primary">{{ translate('messages.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        function calculateProfit() {
            const price = parseFloat(document.getElementById('price').value) || 0;
            const originalPrice = parseFloat(document.getElementById('original_price').value) || 0;
            
            if (price > 0 && originalPrice > 0) {
                const profit = price - originalPrice;
                const profitPercentage = ((profit / originalPrice) * 100).toFixed(2);
                
                // Format currency
                const formattedProfit = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(profit);
                
                document.getElementById('profitAmount').textContent = formattedProfit;
                document.getElementById('profitPercentage').textContent = profitPercentage + '% margin';
                
                // Change color based on profit
                const profitDisplay = document.getElementById('profitDisplay');
                if (profit > 0) {
                    profitDisplay.style.background = 'linear-gradient(45deg, #28a745, #20c997)';
                } else if (profit < 0) {
                    profitDisplay.style.background = 'linear-gradient(45deg, #dc3545, #fd7e14)';
                } else {
                    profitDisplay.style.background = 'linear-gradient(45deg, #6c757d, #adb5bd)';
                }
            } else {
                document.getElementById('profitAmount').textContent = 'Rp 0';
                document.getElementById('profitPercentage').textContent = '0% margin';
            }
        }

        // Calculate profit on page load if values exist
        document.addEventListener('DOMContentLoaded', function() {
            calculateProfit();
        });
    </script>
@endpush