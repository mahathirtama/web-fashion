<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SalesDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sales::with(['kasir', 'details.product'])->get();

        return response()->json([
            'status' => 200,
            'message' => 'OK',
            'data' => $sales
        ]);
    }

   public function store(Request $request)
{
    // 1. Validasi input
    $validated = $request->validate([
        'tanggal' => 'required|date',
        'kasir_id' => 'required|exists:users,id',
        'tax' => 'nullable|numeric|min:0',
        'details' => 'required|array|min:1',
        'details.*.product_id' => 'required|exists:products,id',
        'details.*.quantity' => 'required|integer|min:1',
    ]);

    $taxRate = $validated['tax'] ?? 11;

    // 2. Hitung subtotal & Siapkan Data
    $subtotal = 0;
    $detailsData = [];

    foreach ($validated['details'] as $item) {
        $product = Product::findOrFail($item['product_id']);
        
  
        $harga = $product->selling_price; 
        
        $itemSubtotal = $harga * $item['quantity'];
        $subtotal += $itemSubtotal;

        $detailsData[] = [
            'product_id' => $product->id, // Ganti 'idy' jadi 'id'
            'quantity' => $item['quantity'],
            'subtotal' => $itemSubtotal,
        ];
    }

    // 3. Hitung total
    $total = $subtotal + ($subtotal * $taxRate / 100);

    // 4. Gunakan Database Transaction agar aman
    // (Jika insert detail gagal, header sales juga ikut dibatalkan)
    return \DB::transaction(function () use ($validated, $subtotal, $taxRate, $total, $detailsData) {
        
        // Simpan Header
        $sales = Sales::create([
            'tanggal' => $validated['tanggal'],
            'kasir_id' => $validated['kasir_id'],
            'subtotal' => $subtotal,
            'tax' => $taxRate,
            'total' => $total,
        ]);

        // Simpan Detail
        foreach ($detailsData as $detail) {
            SalesDetail::create([
                'sale_id' => $sales->id,
                'product_id' => $detail['product_id'], // Sekarang ini pasti ada isinya (tidak null)
                'quantity' => $detail['quantity'],
                'subtotal' => $detail['subtotal'],
            ]);
        }

        return response()->json([
            'status' => 201,
            'message' => 'Sale created successfully',
            'data' => $sales->load('details.product') // Pastikan relasi di model Sales bernama 'details'
        ]);
    });
}

    public function show($id)
    {
        $sales = Sales::with(['kasir', 'details.product'])->findOrFail($id);

        return response()->json([
            'status' => 200,
            'message' => 'OK',
            'data' => $sales
        ]);
    }

    public function destroy($id)
    {
        $sales = Sales::findOrFail($id);
        $sales->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Sale deleted successfully'
        ]);
    }
}