@extends('layouts.admin.app')

@section('title', isset($product) ? 'Edit Product' : 'Add New Product')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <span>{{ isset($product) ? 'Edit Product' : 'Add New Product' }}</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <a class="btn btn--secondary" href="{{ route('admin.ppob.master.index') }}">
                        <i class="tio-arrow-backward"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-title">Product Information</h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                        <form action="{{ isset($product) ? route('admin.ppob.master.update', $product->id) : route('admin.ppob.master.store') }}" 
                              method="post" enctype="multipart/form-data">
                            @csrf
                            @if(isset($product))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Category -->
                                    <div class="form-group">
                                        <label class="input-label" for="category_id">Category <span class="input-label-secondary">*</span></label>
                                        <select name="category_id" id="category_id" class="form-control" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                        {{ (isset($product) && $product->category_id == $category->id) || old('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Brand -->
                                    <div class="form-group">
                                        <label class="input-label" for="brand_id">Brand <span class="input-label-secondary">*</span></label>
                                        <select name="brand_id" id="brand_id" class="form-control" required>
                                            <option value="">Select Brand</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" 
                                                        {{ (isset($product) && $product->brand_id == $brand->id) || old('brand_id') == $brand->id ? 'selected' : '' }}>
                                                    {{ $brand->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('brand_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Product Type -->
                                    <div class="form-group">
                                        <label class="input-label" for="type_id">Product Type <span class="input-label-secondary">*</span></label>
                                        <select name="type_id" id="type_id" class="form-control" required>
                                            <option value="">Select Product Type</option>
                                            @foreach($types as $type)
                                                <option value="{{ $type->id }}" 
                                                        {{ (isset($product) && $product->type_id == $type->id) || old('type_id') == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('type_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Seller -->
                                    <div class="form-group">
                                        <label class="input-label" for="seller_id">Seller <span class="input-label-secondary">*</span></label>
                                        <select name="seller_id" id="seller_id" class="form-control" required>
                                            <option value="">Select Seller</option>
                                            @foreach($sellers as $seller)
                                                <option value="{{ $seller->id }}" 
                                                        {{ (isset($product) && $product->seller_id == $seller->id) || old('seller_id') == $seller->id ? 'selected' : '' }}>
                                                    {{ $seller->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('seller_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <!-- SKU Code -->
                                    <div class="form-group">
                                        <label class="input-label" for="buyer_sku_code">SKU Code <span class="input-label-secondary">*</span></label>
                                        <input type="text" name="buyer_sku_code" id="buyer_sku_code" class="form-control" 
                                               placeholder="Enter SKU Code" 
                                               value="{{ isset($product) ? $product->buyer_sku_code : old('buyer_sku_code') }}" required>
                                        @error('buyer_sku_code')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Product Name -->
                                    <div class="form-group">
                                        <label class="input-label" for="product_name">Product Name <span class="input-label-secondary">*</span></label>
                                        <input type="text" name="product_name" id="product_name" class="form-control" 
                                               placeholder="Enter Product Name" 
                                               value="{{ isset($product) ? $product->product_name : old('product_name') }}" required>
                                        @error('product_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Original Price -->
                                    <div class="form-group">
                                        <label class="input-label" for="original_price">Original Price <span class="input-label-secondary">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number" name="original_price" id="original_price" class="form-control" 
                                                   placeholder="Enter Original Price" min="0" step="0.01"
                                                   value="{{ isset($product) ? $product->original_price : old('original_price') }}" required>
                                        </div>
                                        <small class="form-text text-muted">The original price from supplier/vendor</small>
                                        @error('original_price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Selling Price -->
                                    <div class="form-group">
                                        <label class="input-label" for="price">Selling Price <span class="input-label-secondary">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number" name="price" id="price" class="form-control" 
                                                   placeholder="Enter Selling Price" min="0" step="0.01"
                                                   value="{{ isset($product) ? $product->price : old('price') }}" required>
                                        </div>
                                        <small class="form-text text-muted">
                                            The selling price to customers
                                            <span id="profit-margin" class="font-weight-bold"></span>
                                        </small>
                                        @error('price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Status Row -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">Buyer Product Status</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" name="buyer_product_status" id="buyer_product_status" 
                                                   class="custom-control-input" value="1"
                                                   {{ (isset($product) && $product->buyer_product_status) || old('buyer_product_status') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="buyer_product_status">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">Seller Product Status</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" name="seller_product_status" id="seller_product_status" 
                                                   class="custom-control-input" value="1"
                                                   {{ (isset($product) && $product->seller_product_status) || old('seller_product_status') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="seller_product_status">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">Reset</button>
                                <button type="submit" class="btn btn--primary">
                                    {{ isset($product) ? 'Update Product' : 'Save Product' }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        // Format price inputs and calculate profit margin
        function calculateProfitMargin() {
            let originalPrice = parseFloat($('#original_price').val()) || 0;
            let sellingPrice = parseFloat($('#price').val()) || 0;
            
            if (originalPrice > 0 && sellingPrice > 0) {
                let margin = sellingPrice - originalPrice;
                let marginPercent = ((margin / originalPrice) * 100).toFixed(2);
                
                if (margin > 0) {
                    $('#profit-margin').html(`(Profit: Rp ${margin.toLocaleString()} - ${marginPercent}%)`);
                    $('#profit-margin').removeClass('text-danger text-warning').addClass('text-success');
                } else if (margin < 0) {
                    $('#profit-margin').html(`(Loss: Rp ${Math.abs(margin).toLocaleString()} - ${Math.abs(marginPercent)}%)`);
                    $('#profit-margin').removeClass('text-success text-warning').addClass('text-danger');
                } else {
                    $('#profit-margin').html('(Break Even)');
                    $('#profit-margin').removeClass('text-success text-danger').addClass('text-warning');
                }
            } else {
                $('#profit-margin').html('');
            }
        }

        $('#original_price, #price').on('input', function() {
            let value = $(this).val();
            if (value < 0) {
                $(this).val(0);
            }
            calculateProfitMargin();
        });

        // Auto-generate SKU code based on category and brand selection
        $('#category_id, #brand_id').on('change', function() {
            let categoryText = $('#category_id option:selected').text();
            let brandText = $('#brand_id option:selected').text();
            
            if (categoryText && brandText && categoryText !== 'Select Category' && brandText !== 'Select Brand') {
                let sku = categoryText.substring(0, 3).toUpperCase() + '-' + brandText.substring(0, 3).toUpperCase();
                if (!$('#buyer_sku_code').val()) {
                    $('#buyer_sku_code').val(sku + '-');
                }
            }
        });

        // Calculate margin on page load if editing
        $(document).ready(function() {
            calculateProfitMargin();
        });
    </script>
@endpush