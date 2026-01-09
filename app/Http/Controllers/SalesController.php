<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SalesDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // 2. Hitung subtotal & Cek Stok
        $subtotal = 0;
        $detailsData = [];

        foreach ($validated['details'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            // --- [LOGIKA BARU] Validasi Stok ---
            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'status' => 422, // Unprocessable Entity
                    'message' => "Stok tidak mencukupi untuk produk '{$product->name}'. Sisa stok: {$product->stock}, Diminta: {$item['quantity']}",
                ], 422);
            }
            // -----------------------------------

            $harga = $product->selling_price;
            
            $itemSubtotal = $harga * $item['quantity'];
            $subtotal += $itemSubtotal;

            $detailsData[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'subtotal' => $itemSubtotal,
            ];
        }

        // 3. Hitung total akhir
        $total = $subtotal + ($subtotal * $taxRate / 100);

        // 4. Gunakan Database Transaction
        return DB::transaction(function () use ($validated, $subtotal, $taxRate, $total, $detailsData) {
            
            // Simpan Header Penjualan
            $sales = Sales::create([
                'tanggal' => $validated['tanggal'],
                'kasir_id' => $validated['kasir_id'],
                'subtotal' => $subtotal,
                'tax' => $taxRate,
                'total' => $total,
            ]);

            // Simpan Detail & Kurangi Stok
            foreach ($detailsData as $detail) {
                // Simpan ke tabel sales_details
                SalesDetail::create([
                    'sale_id' => $sales->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'subtotal' => $detail['subtotal'],
                ]);

                // --- [LOGIKA BARU] Kurangi Stok ---
                // Menggunakan decrement agar lebih atomic dan aman
                Product::where('id', $detail['product_id'])
                        ->decrement('stock', $detail['quantity']);
                // ----------------------------------
            }

            return response()->json([
                'status' => 201,
                'message' => 'Sale created successfully',
                'data' => $sales->load('details.product')
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
        // Opsional: Jika sales dihapus, apakah stok mau dikembalikan? 
        // Logic default delete biasanya tidak mengembalikan stok kecuali diminta.
        
        $sales = Sales::findOrFail($id);
        $sales->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Sale deleted successfully'
        ]);
    }
}