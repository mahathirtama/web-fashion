<?php

namespace App\Http\Controllers\Front;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
// Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FrontPosController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

     protected $backendApiUrl;
    public function __construct()
    {
        $this->backendApiUrl = rtrim(env('BACKEND', 'http://127.0.0.1:8000/api'), '/');

    }

 public function index()
{
  
    $products = []; 
    $kasir_id = session('user_id');
    $apiError = null;

    try {

        $response = Http::get($this->backendApiUrl . '/products/sell');
        // dd($response->json());

        if ($response->successful()) {

            // ✅ Ambil data dari JSON field "data"
            $products = $response->json('data') ?? [];
    

            // ✅ Perbaiki data produk
            foreach ($products as &$product) {

                // Jika product adalah object, convert ke array
                if (is_object($product)) {
                    $product = (array) $product;
                }
                // dd($product);
            

                // ✅ Atur image URL
                if (!empty($product['image'])) {
                    $product['image'] = $product['image'];
                } else {
                    $product['image'] = asset('images/no-image.png');
                }
            }
            unset($product);

        } else {
            $apiError = "API error: " . ($response->json()['message'] ?? 'Unknown');
        }

    } catch (\Exception $e) {
        $apiError = "Error: " . $e->getMessage();
    }

    return view("pos.index", [
        'products' => $products,
        'kasir_id' => $kasir_id,
        'apiError' => $apiError
    ]);
}



    // Fungsi baru untuk process payment
    public function processPayment(Request $request)
{
    try {
        // 1. Validasi input dari Frontend dulu agar tidak error
        // Pastikan cart dikirim dan formatnya array
        $request->validate([
            'cart' => 'required|array',
            'cart.*.product_id' => 'required', // Pastikan setiap item punya product_id
            'cart.*.quantity' => 'required|numeric',
        ]);

        $cart = $request->input('cart', []);
        
        // Logika Pajak: Jika dikirim 0.11 jadi 11, jika dikirim 11 tetap 11
        $rawTax = $request->input('tax_rate', 0.11);
        $taxRate = ($rawTax <= 1) ? $rawTax * 100 : $rawTax;

        // 2. Siapkan data (FIX LOOPING DI SINI)
        $details = [];
        foreach ($cart as $item) {
            $details[] = [
                // AMBIL DARI VALUE ITEM, JANGAN DARI KEY
                'product_id' => $item['product_id'], 
                'quantity'   => $item['quantity'],
            ];
        }

        // Cek Session (Pastikan user login atau kirim ID via request)
        $kasirId = session('user_id'); 
        if (!$kasirId) {
             return response()->json(['status' => 401, 'message' => 'Unauthorized: Session user tidak ditemukan'], 401);
        }

        $payload = [
            'tanggal'  => now()->toDateString(),
            'kasir_id' => $kasirId,
            'tax'      => $taxRate,
            'details'  => $details,
        ];

        // 3. Panggil API Backend
        $response = Http::withHeaders(['Accept' => 'application/json'])
                        ->post($this->backendApiUrl . '/sales', $payload);

        // 4. Return response
        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json([
                'status'  => $response->status(),
                'message' => 'Backend Error: ' . ($response->json()['message'] ?? 'Unknown Error'),
                'errors'  => $response->json()['errors'] ?? null
            ], $response->status());
        }

    } catch (\Exception $e) {
        Log::error('Process Payment Error: ' . $e->getMessage());
        return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
    }
}
}

