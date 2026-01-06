<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Ambil semua produk beserta supplier-nya
        return response()->json(Product::with('supplier')->get());
    }

    public function store(Request $request){
    $validated = $request->validate([
        'code_product' => 'required|string|max:50|unique:products',
        'name' => 'required|string|max:255',
        'category' => 'nullable|string|max:100',
        'status' => 'nullable|in:active,inactive',
        'description' => 'nullable|string',
        'stock' => 'required|integer|min:0',
        'selling_price' => 'required|numeric|min:0',
        'purchase_price' => 'required|numeric|min:0',
        'image' => 'nullable|string|max:255',
        'supplier_id' => 'required|exists:suppliers,id',
    ]);

    $product = Product::create($validated);

    return response()->json([
        'status' => 201,
        'message' => 'Product created successfully',
        'data' => $product
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with('supplier')->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id){
    $product = Product::findOrFail($id);

    $validated = $request->validate([
        'code_product' => 'sometimes|string|max:50|unique:products,code_product,' . $id,
        'name' => 'sometimes|string|max:255',
        'category' => 'sometimes|string|max:100',
        'status' => 'sometimes|in:active,inactive',
        'description' => 'sometimes|string',
        'stock' => 'sometimes|integer|min:0',
        'selling_price' => 'sometimes|numeric|min:0',
        'purchase_price' => 'sometimes|numeric|min:0',
        'image' => 'sometimes|string|max:255',
        'supplier_id' => 'sometimes|exists:suppliers,id',
    ]);

    $product->update($validated);

    return response()->json([
        'status' => 200,
        'message' => 'Product updated successfully',
        'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}