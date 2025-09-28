@extends('layouts.admin.app')

@section('title', 'PPOB Master Data')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .btn--success.active {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
            box-shadow: 0 0 0 0.125rem rgba(40, 167, 69, 0.25);
        }
        
        .btn--danger.active {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
            box-shadow: 0 0 0 0.125rem rgba(220, 53, 69, 0.25);
        }
        
        .status-toggle {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Fixed styling for action buttons */
        .btn--container {
            display: flex;
            gap: 5px;
            justify-content: center;
            align-items: center;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Ensure buttons are always visible regardless of status */
        .action-btn.btn--primary {
            border: 1px solid #007bff;
            color: #007bff;
            background: transparent;
        }
        
        .action-btn.btn--primary:hover {
            background: #007bff;
            color: white;
        }
        
        .action-btn.btn--success {
            border: 1px solid #28a745;
            color: #28a745;
            background: transparent;
        }
        
        .action-btn.btn--success:hover {
            background: #28a745;
            color: white;
        }
        
        .action-btn.btn--danger {
            border: 1px solid #dc3545;
            color: #dc3545;
            background: transparent;
        }
        
        .action-btn.btn--danger:hover {
            background: #dc3545;
            color: white;
        }

        /* Sync button styles */
        .sync-btn {
            position: relative;
            overflow: hidden;
            min-width: 120px;
        }

        .sync-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .sync-btn .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .sync-btn.syncing .spinner {
            display: inline-block;
        }

        .sync-btn.syncing .btn-text {
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .profit-info {
            font-size: 0.75rem;
            margin-top: 2px;
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
                        <span>
                            PPOB Master Data
                            <span class="badge badge-soft-dark rounded-circle ml-1">{{ $products->total() }}</span>
                        </span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <div class="d-flex gap-2">
                        <!-- Sync Buttons -->
                        <button class="btn btn-warning sync-btn" onclick="syncDigiflazz('prabayar')" id="sync-prabayar">
                            <div class="spinner"></div>
                            <span class="btn-text">
                                <i class="tio-sync"></i> Sync Prabayar
                            </span>
                        </button>
                        <button class="btn btn-warning sync-btn" onclick="syncDigiflazz('pascabayar')" id="sync-pascabayar">
                            <div class="spinner"></div>
                            <span class="btn-text">
                                <i class="tio-sync"></i> Sync Pascabayar
                            </span>
                        </button>
                        <a class="btn btn--primary" href="{{ route('admin.ppob.master.create') }}">
                            <i class="tio-add-circle"></i>
                            Add New Product
                        </a>
                    </div>
                </div>
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
                            <h5 class="card-title">Product List</h5>
                            <form class="search-form min--250" method="GET">
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" class="form-control h--40px"
                                           placeholder="Search by SKU, product name, category, brand..."
                                           aria-label="Search" value="{{ request()->get('search') }}">
                                    <button type="submit" class="btn btn--secondary h--40px">
                                        <i class="tio-search"></i>
                                    </button>
                                </div>
                                <!-- End Search -->
                            </form>

                            <!-- Filter Buttons -->
                            <div class="btn-group ml-2" role="group">
                                <a href="{{ route('admin.ppob.master.index') }}" 
                                   class="btn {{ !request('active') ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                    All
                                </a>
                                <a href="{{ route('admin.ppob.master.index') }}?active=true" 
                                   class="btn {{ request('active') == 'true' ? 'btn-success active' : 'btn-outline-success' }}">
                                    Active Only
                                </a>
                            </div>

                            <!-- Reports button -->
                            <a href="{{ route('admin.ppob.transactions.report') }}" 
                               class="btn btn-info ml-2 reports-btn" title="Transaction Report">
                                <i class="tio-chart-bar-1"></i>
                                Reports
                            </a>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Filters -->
                    <div class="card-body border-top">
                        <form method="GET" class="row">
                            <div class="col-md-3">
                                <select name="category" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->name }}" {{ request('category') == $category->name ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="brand" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->name }}" {{ request('brand') == $brand->name ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="active" value="{{ request('active') }}">
                            </div>
                        </form>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>SL</th>
                                    <th>SKU Code</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Type</th>
                                    <th>Seller</th>
                                    <th>Original Price</th>
                                    <th>Selling Price</th>
                                    <th>Buyer Status</th>
                                    <th>Seller Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                                @foreach($products as $key => $product)
                                    <tr>
                                        <td>{{ $key + $products->firstItem() }}</td>
                                        <td>
                                            <span class="badge badge-soft-dark">
                                                {{ $product->buyer_sku_code }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block font-weight-semibold">
                                                {{ $product->product_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-info">
                                                {{ $product->category_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft-primary">
                                                {{ $product->brand_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $product->type_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="d-block font-size-sm text-body">
                                                {{ $product->seller_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-right">
                                                <div class="font-weight-bold text-muted">
                                                    Rp {{ number_format($product->original_price ?? 0, 0, ',', '.') }}
                                                </div>
                                                <small class="text-muted">Original</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-right">
                                                <div class="font-weight-bold">
                                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                                </div>
                                                @if($product->original_price && $product->original_price > 0)
                                                    @php
                                                        $profit = $product->price - $product->original_price;
                                                        $profitPercent = (($profit / $product->original_price) * 100);
                                                    @endphp
                                                    <small class="profit-info {{ $profit > 0 ? 'text-success' : ($profit < 0 ? 'text-danger' : 'text-warning') }}">
                                                        {{ $profit > 0 ? '+' : '' }}{{ number_format($profitPercent, 1) }}%
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-toggle badge status-badge {{ $product->buyer_product_status ? 'badge-success' : 'badge-danger' }}"
                                                  onclick="toggleStatus({{ $product->id }}, 'buyer_product_status', {{ $product->buyer_product_status ? 0 : 1 }})">
                                                {{ $product->buyer_product_status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-toggle badge status-badge {{ $product->seller_product_status ? 'badge-success' : 'badge-danger' }}"
                                                  onclick="toggleStatus({{ $product->id }}, 'seller_product_status', {{ $product->seller_product_status ? 0 : 1 }})">
                                                {{ $product->seller_product_status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Fixed Action Buttons - Always visible regardless of status -->
                                            <div class="btn--container">
                                                <a class="btn action-btn btn--primary"
                                                   href="{{ route('admin.ppob.master.show', $product->id) }}"
                                                   title="View">
                                                    <i class="tio-visible"></i>
                                                </a>
                                                <a class="btn action-btn btn--success"
                                                   href="{{ route('admin.ppob.master.edit', $product->id) }}"
                                                   title="Edit">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <a class="btn action-btn btn--danger"
                                                   href="javascript:" 
                                                   onclick="confirmDelete('product-{{ $product->id }}', 'Are you sure you want to delete this product?')"
                                                   title="Delete">
                                                    <i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{ route('admin.ppob.master.destroy', $product->id) }}"
                                                      method="post" id="product-{{ $product->id }}" style="display: none;">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($products) === 0)
                            <div class="empty--data">
                                <img src="{{ asset('/public/public/assets/admin/img/empty.png') }}" alt="No data">
                                <h5>No products found</h5>
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
                                    {!! $products->appends(request()->query())->links() !!}
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
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to update status');
                }
            });
        }

        function syncDigiflazz(type) {
            const button = $(`#sync-${type}`);
            const apiUrl = type === 'prabayar' ? 
                'http://103.196.155.202:8787/api/sync/digiflazzprabayar' : 
                'http://103.196.155.202:8787/api/sync/digiflazzpasca';

            // Disable button and show loading
            button.prop('disabled', true);
            button.addClass('syncing');
            
            // Show loading toast
            toastr.info(`Syncing ${type} data from Digiflazz...`, 'Sync in Progress', {
                timeOut: 0,
                extendedTimeOut: 0,
                closeButton: true
            });

            $.ajax({
                url: apiUrl,
                method: 'POST',
                timeout: 120000, // 2 minutes timeout
                success: function(response) {
                    // Clear loading toast
                    toastr.clear();
                    
                    // Show success message
                    toastr.success(`${type.charAt(0).toUpperCase() + type.slice(1)} data synced successfully!`, 'Sync Complete');
                    
                    // Reload page after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr, status, error) {
                    // Clear loading toast
                    toastr.clear();
                    
                    let errorMessage = 'Failed to sync data';
                    if (status === 'timeout') {
                        errorMessage = 'Sync request timed out. Please try again.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    toastr.error(errorMessage, 'Sync Failed');
                    console.error(`Sync ${type} error:`, error);
                },
                complete: function() {
                    // Re-enable button and hide loading
                    button.prop('disabled', false);
                    button.removeClass('syncing');
                }
            });
        }

        // Keyboard shortcut for sync
        $(document).keydown(function(e) {
            if (e.ctrlKey && e.shiftKey) {
                if (e.key === 'P' || e.key === 'p') {
                    e.preventDefault();
                    syncDigiflazz('prabayar');
                } else if (e.key === 'S' || e.key === 's') {
                    e.preventDefault();
                    syncDigiflazz('pascabayar');
                }
            }
        });
    </script>
@endpush