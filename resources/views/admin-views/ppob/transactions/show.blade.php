@extends('layouts.admin.app')

@section('title', translate('messages.ppob_transaction_details'))

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
                        <span>
                            {{ translate('messages.ppob_transaction_details') }}
                        </span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a class="btn-icon btn-sm btn-soft-secondary rounded-circle mr-1"
                       href="{{ route('admin.ppob.transactions.show', [$transaction['id'] - 1]) }}" 
                       data-toggle="tooltip" data-placement="top" title="{{ translate('messages.previous_transaction') }}">
                        <i class="tio-chevron-left"></i>
                    </a>
                    <a class="btn-icon btn-sm btn-soft-secondary rounded-circle"
                       href="{{ route('admin.ppob.transactions.show', [$transaction['id'] + 1]) }}" 
                       data-toggle="tooltip" data-placement="top" title="{{ translate('messages.next_transaction') }}">
                        <i class="tio-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row flex-xl-nowrap" id="printableArea">
            <div class="col-lg-8 order-print-area-left">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header border-0 align-items-start flex-wrap">
                        <div class="order-invoice-left d-flex d-sm-block justify-content-between">
                            <div>
                                <h1 class="page-header-title d-flex align-items-center __gap-5px">
                                    {{ translate('messages.transaction') }} #{{ $transaction['id'] }}
                                </h1>
                                <span class="mt-2 d-block d-flex align-items-center __gap-5px">
                                    <i class="tio-date-range"></i>
                                    {{ date('d M Y ' . config('timeformat'), strtotime($transaction['created_at'])) }}
                                </span>
                                <h6 class="mt-2 pt-1 mb-2 d-flex align-items-center __gap-5px">
                                    <i class="tio-confirmation"></i>
                                    <span>{{ translate('messages.ref_id') }}</span> <span>:</span> 
                                    <span class="badge badge-soft-primary">{{ $transaction->ref_id }}</span>
                                </h6>
                            </div>
                        </div>
                        <div class="order-invoice-right mt-3 mt-sm-0">
                            <div class="btn--container ml-auto align-items-center justify-content-end">
                                <a class="btn btn--success btn-sm" 
                                   href="{{ route('admin.ppob.transactions.edit', $transaction->id) }}">
                                    <i class="tio-edit mr-1"></i> {{ translate('messages.edit') }}
                                </a>
                            </div>
                            <div class="text-right mt-3 order-invoice-right-contents text-capitalize">
                                <h6>
                                    <span>{{ translate('messages.status') }}</span> <span>:</span>
                                    <span class="badge {{ $transaction->status_badge_class }} ml-2 ml-sm-3 text-capitalize">
                                        {{ translate('messages.' . strtolower($transaction->status)) }}
                                    </span>
                                </h6>
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.customer_no') }}</span>
                                    </div>
                                    <span class="text-dark">{{ $transaction->customer_no }}</span>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.buyer_sku_code') }}</span>
                                    </div>
                                    <span class="badge badge-soft-dark">{{ $transaction->buyer_sku_code }}</span>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.price') }}</span>
                                    </div>
                                    <span class="text-dark font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($transaction->price) }}</span>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.response_code') }}</span>
                                    </div>
                                    <span class="text-dark">{{ $transaction->rc ?: '-' }}</span>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.buyer_last_saldo') }}</span>
                                    </div>
                                    <span class="text-dark">{{ \App\CentralLogics\Helpers::format_currency($transaction->buyer_last_saldo) }}</span>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.serial_number') }}</span>
                                    </div>
                                    <span class="text-dark">{{ $transaction->sn ?: '-' }}</span>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.telephone') }}</span>
                                    </div>
                                    <span class="text-dark">{{ $transaction->tele ?: '-' }}</span>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.whatsapp') }}</span>
                                    </div>
                                    <span class="text-dark">{{ $transaction->wa ?: '-' }}</span>
                                </div>
                                <hr>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="text-body text-hover-primary">{{ translate('messages.last_updated') }}</span>
                                    </div>
                                    <span class="text-dark">{{ $transaction->updated_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <hr>
                            </div>
                            
                            @if($transaction->message)
                            <div class="col-12">
                                <h5 class="card-header-title">{{ translate('messages.message') }}</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        {{ $transaction->message }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">{{ translate('messages.transaction_summary') }}</h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                        <div class="media">
                            <div class="media-body">
                                <span class="text-body">{{ translate('messages.transaction_id') }}</span>
                            </div>
                            <span class="text-dark">#{{ $transaction->id }}</span>
                        </div>
                        <hr>
                        
                        <div class="media">
                            <div class="media-body">
                                <span class="text-body">{{ translate('messages.ref_id') }}</span>
                            </div>
                            <span class="text-dark">{{ $transaction->ref_id }}</span>
                        </div>
                        <hr>

                        <div class="media">
                            <div class="media-body">
                                <span class="text-body">{{ translate('messages.status') }}</span>
                            </div>
                            <span class="badge {{ $transaction->status_badge_class }}">
                                {{ translate('messages.' . strtolower($transaction->status)) }}
                            </span>
                        </div>
                        <hr>

                        <div class="media">
                            <div class="media-body">
                                <span class="text-body">{{ translate('messages.created_at') }}</span>
                            </div>
                            <span class="text-dark">{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <hr>

                        <div class="media">
                            <div class="media-body">
                                <span class="text-body h5">{{ translate('messages.total_amount') }}</span>
                            </div>
                            <span class="text-dark h5">{{ \App\CentralLogics\Helpers::format_currency($transaction->price) }}</span>
                        </div>
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
@endsection