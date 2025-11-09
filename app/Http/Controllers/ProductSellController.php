<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductSellController extends Controller
{
    public function index()
    {
        // Ambil semua produk yang statusnya 'active' beserta data supplier-nya
        $products = Product::with('supplier')
            ->where('status', 'active')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $products->count(),
            'data' => $products
        ]);
    }
}