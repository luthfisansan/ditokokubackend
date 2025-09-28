@extends('layouts.admin.app')

@section('title', 'Product Details')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .detail-card {
            border: 1px solid #e7eaf3;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #677788;
            min-width: 150px;
        }
        
        .detail-value {
            font-weight: 500;
            color: #334257;
            text-align: right;
        }
        
        .status-active {
            background-color: #e8f5e8;
            color: #28a745;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-inactive {
            background-color: #fdeaea;
            color: #dc3545;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .price-display {
            font-size: 1.25rem;
            font-weight: 700;
            color: #28a745;
        }
        
        .sku-badge {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
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
                            <img src="{{ asset('/public/public/assets/admin/img/product.png') }}" class="w--20" alt="">
                        </span>
                        <span>Product Details</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <div class="btn-group">
                        <a class="btn btn--secondary" href="{{ route('admin.ppob.master.index') }}">
                            <i class="tio-arrow-backward"></i>
                            Back to List
                        </a>
                        <a class="btn btn--primary" href="{{ route('admin.ppob.master.edit', $product->id) }}">
                            <i class="tio-edit"></i>
                            Edit Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <!-- Product Information -->
            <div class="col-lg-8">
                <div class="card detail-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-0">Product Information</h4>
                    </div>
                    <div class="card-body pt-2">
                        <div class="detail-item">
                            <span class="detail-label">SKU Code:</span>
                            <span class="detail-value">
                                <span class="sku-badge">{{ $product->buyer_sku_code }}</span>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Product Name:</span>
                            <span class="detail-value">{{ $product->product_name }}</span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Price:</span>
                            <span class="detail-value price-display">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">
                                <span class="badge badge-soft-info">{{ $product->category_name }}</span>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Brand:</span>
                            <span class="detail-value">
                                <span class="badge badge-soft-primary">{{ $product->brand_name }}</span>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Product Type:</span>
                            <span class="detail-value">{{ $product->type_name }}</span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Seller:</span>
                            <span class="detail-value">{{ $product->seller_name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status & Timestamps -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card detail-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-0">Product Status</h4>
                    </div>
                    <div class="card-body pt-2">
                        <div class="detail-item">
                            <span class="detail-label">Buyer Status:</span>
                            <span class="detail-value">
                                <span class="{{ $product->buyer_product_status ? 'status-active' : 'status-inactive' }}">
                                    {{ $product->buyer_product_status ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Seller Status:</span>
                            <span class="detail-value">
                                <span class="{{ $product->seller_product_status ? 'status-active' : 'status-inactive' }}">
                                    {{ $product->seller_product_status ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Overall Status:</span>
                            <span class="detail-value">
                                <span class="{{ ($product->buyer_product_status && $product->seller_product_status) ? 'status-active' : 'status-inactive' }}">
                                    {{ ($product->buyer_product_status && $product->seller_product_status) ? 'Available' : 'Not Available' }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Timestamps Card -->
                <div class="card detail-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-0">Timestamps</h4>
                    </div>
                    <div class="card-body pt-2">
                        <div class="detail-item">
                            <span class="detail-label">Created At:</span>
                            <span class="detail-value">
                                <div class="text-right">
                                    <div>{{ \Carbon\Carbon::parse($product->created_at)->format('d M Y') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($product->created_at)->format('h:i A') }}</small>
                                </div>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Updated At:</span>
                            <span class="detail-value">
                                <div class="text-right">
                                    <div>{{ \Carbon\Carbon::parse($product->updated_at)->format('d M Y') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($product->updated_at)->format('h:i A') }}</small>
                                </div>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="detail-label">Last Modified:</span>
                            <span class="detail-value">
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($product->updated_at)->diffForHumans() }}
                                </small>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card detail-card">
                    <div class="card-header border-0 pb-0">
                        <h4 class="card-title mb-0">Quick Actions</h4>
                    </div>
                    <div class="card-body pt-2">
                        <div class="btn-group-vertical w-100">
                            <button type="button" class="btn btn-outline-success mb-2" 
                                    onclick="toggleStatus({{ $product->id }}, 'buyer_product_status', {{ $product->buyer_product_status ? 0 : 1 }})">
                                <i class="tio-toggle-{{ $product->buyer_product_status ? 'off' : 'on' }}"></i>
                                {{ $product->buyer_product_status ? 'Deactivate' : 'Activate' }} Buyer Status
                            </button>
                            
                            <button type="button" class="btn btn-outline-success mb-2" 
                                    onclick="toggleStatus({{ $product->id }}, 'seller_product_status', {{ $product->seller_product_status ? 0 : 1 }})">
                                <i class="tio-toggle-{{ $product->seller_product_status ? 'off' : 'on' }}"></i>
                                {{ $product->seller_product_status ? 'Deactivate' : 'Activate' }} Seller Status
                            </button>
                            
                            <button type="button" class="btn btn-outline-danger" 
                                    onclick="confirmDelete('product-{{ $product->id }}', 'Are you sure you want to delete this product?')">
                                <i class="tio-delete"></i>
                                Delete Product
                            </button>
                            
                            <form action="{{ route('admin.ppob.master.destroy', $product->id) }}"
                                  method="post" id="product-{{ $product->id }}">
                                @csrf @method('delete')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        function confirmDelete(formId, message) {
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
                    $('#' + formId).submit()
                }
            })
        }

        function toggleStatus(id, field, status) {
            $.ajax({
                url: `/admin/ppob/master/${id}/toggle-status`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    field: field,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to update status');
                }
            });
        }
    </script>
@endpush