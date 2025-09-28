@extends('layouts.admin.app')

@section('title', translate('messages.ppob_transactions'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Custom styling untuk filter buttons */
        .btn--success.active {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
            box-shadow: 0 0 0 0.125rem rgba(40, 167, 69, 0.25);
        }
        
        .btn--warning.active {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #212529 !important;
            box-shadow: 0 0 0 0.125rem rgba(255, 193, 7, 0.25);
        }
        
        .btn--danger.active {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
            box-shadow: 0 0 0 0.125rem rgba(220, 53, 69, 0.25);
        }
        
        .btn--secondary.active {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: white !important;
            box-shadow: 0 0 0 0.125rem rgba(108, 117, 125, 0.25);
        }
        
        /* Hover effects */
        .btn--success:hover {
            background-color: #218838 !important;
            border-color: #1e7e34 !important;
            color: white !important;
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
                        
                        <span>
                            {{ translate('messages.ppob_transactions') }} 
                            <span class="badge badge-soft-dark rounded-circle ml-1">{{ $transactions->total() }}</span>
                        </span>
                    </h1>
                </div>
                <!-- <div class="col-sm-auto">
                    <a class="btn btn--primary" href="{{ route('admin.ppob.transactions.create') }}">
                        <i class="tio-add-circle"></i>
                        {{ translate('messages.add_new_transactions') }}
                    </a>
                </div> -->
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{ translate('messages.transaction_list') }}</h5>
                            <form class="search-form min--250">
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" class="form-control h--40px"
                                           placeholder="{{ translate('messages.search_by_ref_id_customer_no_sku') }}"
                                           aria-label="Search" value="{{ request()->get('search') }}">
                                    <button type="submit" class="btn btn--secondary h--40px">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                                <!-- End Search -->
                            </form>

                            <!-- Status Filter Buttons -->
                            <div class="btn-group ml-2" role="group">
                                <a href="{{ route('admin.ppob.transactions.index') }}" 
                                   class="btn {{ !request('status') || request('status') == 'all' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                    {{ translate('messages.all') }}
                                </a>
                                <a href="{{ route('admin.ppob.transactions.index') }}?status=Pending" 
                                   class="btn {{ request('status') == 'Pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                                    {{ translate('messages.pending') }}
                                </a>
                                <a href="{{ route('admin.ppob.transactions.index') }}?status=Sukses" 
                                   class="btn {{ request('status') == 'Sukses' ? 'btn-success' : 'btn-outline-success' }}">
                                    {{ translate('messages.success') }}
                                </a>
                                <a href="{{ route('admin.ppob.transactions.index') }}?status=Failed" 
                                   class="btn {{ request('status') == 'Failed' ? 'btn-danger' : 'btn-outline-danger' }}">
                                    {{ translate('messages.failed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('messages.sl') }}</th>
                                    <th>{{ translate('messages.ref_id') }}</th>
                                    <th>{{ translate('messages.customer_no') }}</th>
                                    <th>{{ translate('messages.sku_code') }}</th>
                                    <th>{{ translate('messages.price') }}</th>
                                    <th>{{ translate('messages.status') }}</th>
                                    <th>{{ translate('messages.created_at') }}</th>
                                    <th class="text-center">{{ translate('messages.action') }}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                                @foreach($transactions as $key => $transaction)
                                    <tr>
                                        <td>{{ $key + $transactions->firstItem() }}</td>
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $transaction->ref_id }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $transaction->customer_no }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-dark">
                                                {{ $transaction->buyer_sku_code }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-right">
                                                {{ \App\CentralLogics\Helpers::format_currency($transaction->price) }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $transaction->status_badge_class }} text-capitalize">
                                                {{ $transaction->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $transaction->created_at->format('d M Y, h:i A') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--primary btn-outline-primary"
                                                   href="{{ route('admin.ppob.transactions.show', [$transaction['id']]) }}"
                                                   title="{{ translate('messages.view') }}">
                                                    <i class="tio-visible"></i>
                                                </a>
                                                <a class="btn action-btn btn--success btn-outline-success"
                                                   href="{{ route('admin.ppob.transactions.edit', [$transaction['id']]) }}"
                                                   title="{{ translate('messages.edit') }}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <a class="btn action-btn btn--danger btn-outline-danger"
                                                   href="javascript:" 
                                                   onclick="form_alert('transaction-{{ $transaction['id'] }}','{{ translate('messages.want_to_delete_this_transaction') }}')"
                                                   title="{{ translate('messages.delete') }}">
                                                    <i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{ route('admin.ppob.transactions.destroy', [$transaction['id']]) }}"
                                                      method="post" id="transaction-{{ $transaction['id'] }}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($transactions) === 0)
                            <div class="empty--data">
                                <img src="{{ asset('/public/public/assets/admin/img/empty.png') }}" alt="public">
                                <h5>
                                    {{ translate('no_data_found') }}
                                </h5>
                            </div>
                        @endif
                    </div>
                    <!-- End Table -->

                    <!-- Footer -->
                    <div class="card-footer">
                        <!-- Pagination -->
                        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                            <div class="col-sm-auto">
                                <div class="d-flex justify-content-center justify-content-sm-end">
                                    <!-- Pagination -->
                                    {!! $transactions->links() !!}
                                </div>
                            </div>
                        </div>
                        <!-- End Pagination -->
                    </div>
                    <!-- End Footer -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        function form_alert(id, message) {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: 'No',
                confirmButtonText: 'Yes',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $('#' + id).submit()
                }
            })
        }
    </script>
@endpush