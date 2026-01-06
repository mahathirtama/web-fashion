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
        // ✅ 1. Validasi input dasar (tanpa subtotal)
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kasir_id' => 'required|exists:users,id',
            'tax' => 'nullable|numeric|min:0',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
        ]);

        // Default pajak 11% kalau tidak dikirim
        $taxRate = $validated['tax'] ?? 11;

        // ✅ 2. Hitung subtotal otomatis
        $subtotal = 0;
        $detailsData = [];

        foreach ($validated['details'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $itemSubtotal = $product->selling_y * $item['quantity']; // 💡 hitung otomatis
            $subtotal += $itemSubtotal;

            $detailsData[] = [
                'product_id' => $product->idy,
                'quantity' => $item['quantity'],
                'subtotal' => $itemSubtotal,
            ];
        }

        // ✅ 3. Hitung total setelah pajak
        $total = $subtotal + ($subtotal * $taxRate / 100);

        // ✅ 4. Simpan ke tabel `sales`
        $sales = Sales::create([
            'tanggal' => $validated['tanggal'],
            'kasir_id' => $validated['kasir_id'],
            'subtotal' => $subtotal,
            'tax' => $taxRate,
            'total' => $total,
        ]);

        // ✅ 5. Simpan detail transaksi
        foreach ($detailsData as $detail) {
            SalesDetail::create([
                'sale_id' => $sales->id,
                'product_id' => $detail['product_id'],
                'quantity' => $detail['quantity'],
                'subtotal' => $detail['subtotal'],
            ]);
        }

        // ✅ 6. Kembalikan response JSON
        return response()->json([
            'status' => 201,
            'message' => 'Sale created successfully',
            'data' => $sales->load('details.product')
        ]);
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