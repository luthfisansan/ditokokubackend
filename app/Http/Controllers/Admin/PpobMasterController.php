<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PpobMasterController extends Controller
{
    /**
     * Display a listing of PPOB products
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('pricelist_ppob as p')
                ->join('categories_ppob as c', 'p.category_id', '=', 'c.id')
                ->join('brands_ppob as b', 'p.brand_id', '=', 'b.id')
                ->join('product_types_ppob as pt', 'p.type_id', '=', 'pt.id')
                ->join('sellers_ppob as s', 'p.seller_id', '=', 's.id')
                ->select(
                    'p.*',
                    'c.name as category_name',
                    'b.name as brand_name',
                    'pt.name as type_name',
                    's.name as seller_name'
                );

            // Apply filters
            if ($request->filled('category')) {
                $query->where('c.name', $request->category);
            }

            if ($request->filled('brand')) {
                $query->where('b.name', $request->brand);
            }

            if ($request->filled('active') && $request->active === 'true') {
                $query->where('p.buyer_product_status', 1)
                      ->where('p.seller_product_status', 1);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('p.buyer_sku_code', 'like', "%{$search}%")
                      ->orWhere('p.product_name', 'like', "%{$search}%")
                      ->orWhere('c.name', 'like', "%{$search}%")
                      ->orWhere('b.name', 'like', "%{$search}%");
                });
            }

            $products = $query->orderBy('c.name')
                             ->orderBy('b.name')
                             ->orderBy('p.price')
                             ->paginate(15);

            // Get filter options
            $categories = DB::table('categories_ppob')->select('name')->distinct()->get();
            $brands = DB::table('brands_ppob')->select('name')->distinct()->get();

            return view('admin-views.ppob.master.index', compact('products', 'categories', 'brands'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to fetch products: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $categories = DB::table('categories_ppob')->get();
        $brands = DB::table('brands_ppob')->get();
        $types = DB::table('product_types_ppob')->get();
        $sellers = DB::table('sellers_ppob')->get();

        return view('admin-views.ppob.master.create', compact('categories', 'brands', 'types', 'sellers'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories_ppob,id',
            'brand_id' => 'required|exists:brands_ppob,id',
            'type_id' => 'required|exists:product_types_ppob,id',
            'seller_id' => 'required|exists:sellers_ppob,id',
            'buyer_sku_code' => 'required|string|unique:pricelist_ppob,buyer_sku_code',
            'product_name' => 'required|string|max:255',
            'original_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'buyer_product_status' => 'nullable|boolean',
            'seller_product_status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::table('pricelist_ppob')->insert([
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'type_id' => $request->type_id,
                'seller_id' => $request->seller_id,
                'buyer_sku_code' => $request->buyer_sku_code,
                'product_name' => $request->product_name,
                'original_price' => $request->original_price,
                'price' => $request->price,
                'buyer_product_status' => $request->has('buyer_product_status') ? 1 : 0,
                'seller_product_status' => $request->has('seller_product_status') ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('admin.ppob.master.index')
                           ->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified product
     */
    public function show($id)
    {
        try {
            $product = DB::table('pricelist_ppob as p')
                ->join('categories_ppob as c', 'p.category_id', '=', 'c.id')
                ->join('brands_ppob as b', 'p.brand_id', '=', 'b.id')
                ->join('product_types_ppob as pt', 'p.type_id', '=', 'pt.id')
                ->join('sellers_ppob as s', 'p.seller_id', '=', 's.id')
                ->select(
                    'p.*',
                    'c.name as category_name',
                    'b.name as brand_name',
                    'pt.name as type_name',
                    's.name as seller_name'
                )
                ->where('p.id', $id)
                ->first();

            if (!$product) {
                return redirect()->route('admin.ppob.master.index')
                               ->withErrors(['error' => 'Product not found']);
            }

            return view('admin-views.ppob.master.show', compact('product'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to fetch product: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit($id)
    {
        try {
            $product = DB::table('pricelist_ppob')->where('id', $id)->first();
            
            if (!$product) {
                return redirect()->route('admin.ppob.master.index')
                               ->withErrors(['error' => 'Product not found']);
            }

            $categories = DB::table('categories_ppob')->get();
            $brands = DB::table('brands_ppob')->get();
            $types = DB::table('product_types_ppob')->get();
            $sellers = DB::table('sellers_ppob')->get();

            return view('admin-views.ppob.master.edit', compact('product', 'categories', 'brands', 'types', 'sellers'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to fetch product: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories_ppob,id',
            'brand_id' => 'required|exists:brands_ppob,id',
            'type_id' => 'required|exists:product_types_ppob,id',
            'seller_id' => 'required|exists:sellers_ppob,id',
            'buyer_sku_code' => 'required|string|unique:pricelist_ppob,buyer_sku_code,' . $id,
            'product_name' => 'required|string|max:255',
            'original_price' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'buyer_product_status' => 'nullable|boolean',
            'seller_product_status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $updated = DB::table('pricelist_ppob')
                ->where('id', $id)
                ->update([
                    'category_id' => $request->category_id,
                    'brand_id' => $request->brand_id,
                    'type_id' => $request->type_id,
                    'seller_id' => $request->seller_id,
                    'buyer_sku_code' => $request->buyer_sku_code,
                    'product_name' => $request->product_name,
                    'original_price' => $request->original_price,
                    'price' => $request->price,
                    'buyer_product_status' => $request->has('buyer_product_status') ? 1 : 0,
                    'seller_product_status' => $request->has('seller_product_status') ? 1 : 0,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                return redirect()->route('admin.ppob.master.index')
                               ->with('success', 'Product updated successfully');
            } else {
                return back()->withErrors(['error' => 'Product not found or no changes made'])->withInput();
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        try {
            $deleted = DB::table('pricelist_ppob')->where('id', $id)->delete();
            
            if ($deleted) {
                return redirect()->route('admin.ppob.master.index')
                               ->with('success', 'Product deleted successfully');
            } else {
                return back()->withErrors(['error' => 'Product not found']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'field' => 'required|in:buyer_product_status,seller_product_status',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $updated = DB::table('pricelist_ppob')
                ->where('id', $id)
                ->update([
                    $request->field => $request->status,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                return response()->json(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'Product not found']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status']);
        }
    }
}