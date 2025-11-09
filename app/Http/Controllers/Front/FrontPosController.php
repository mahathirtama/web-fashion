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

        $response = Http::get($this->backendApiUrl . '/products');
        // dd($response->json());

        if ($response->successful()) {

            // ✅ Ambil data dari JSON field "data"
            $products = $response->json() ?? [];
            // dd($products);

            // ✅ Base URL backend
            $backendBaseUrl = rtrim(env('BACKEND', 'http://127.0.0.1:8000'), '/');

            // ✅ Perbaiki data produk
            foreach ($products as &$product) {

                // Jika product adalah object, convert ke array
                if (is_object($product)) {
                    $product = (array) $product;
                }

                // ✅ Atur image URL
                if (!empty($product['image'])) {
                    $product['image_url'] = $backendBaseUrl . '/storage/' . $product['image'];
                } else {
                    $product['image_url'] = asset('images/no-image.png');
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
            // 1. Dapatkan data dari frontend (cart, tax_rate)
            $cart = $request->input('cart', []);
            $taxRate = $request->input('tax_rate', 0.11) * 100; // Ubah 0.11 jadi 11

            // 2. Siapkan data untuk dikirim ke API Backend
            $details = [];
            foreach ($cart as $productId => $item) {
                $details[] = [
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                ];
            }

            $payload = [
                'tanggal' => now()->toDateString(), // Tanggal hari ini
                'kasir_id' => session('user_id'),
                'tax' => $taxRate,
                'details' => $details,
            ];

            // 3. Panggil API Backend (SalesController)
            $response = Http::post($this->backendApiUrl . '/sales', $payload);

            // 4. Teruskan respon dari API ke frontend
            if ($response->successful()) {
                return $response->json();
            } else {
                // Jika API gagal, kirim error
                return response()->json([
                    'status' => $response->status(),
                    'message' => 'API Error: ' . ($response->json()['message'] ?? 'Failed to process sale'),
                    'errors' => $response->json()['errors'] ?? null
                ], $response->status());
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Tangani error koneksi (seperti yang Anda alami)
            Log::error('Koneksi ke API sales gagal: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Error connecting to API service: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            // Tangani error umum lainnya
            Log::error('Gagal memproses pembayaran: ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}

