<?php

namespace App\Http\Controllers\Front;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontPosController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // Helper untuk Internal Request
    private function internalApiCall($method, $uri, $data = [])
    {
        $request = Request::create($uri, $method, $data);
        $request->headers->set('Accept', 'application/json');
        $response = app()->handle($request);

        return (object) [
            'status' => $response->getStatusCode(),
            'body' => json_decode($response->getContent(), true),
            'successful' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];
    }

    public function index()
    {
        $products = []; 
        $kasir_id = session('user_id');
        $apiError = null;

        try {
            $response = $this->internalApiCall('GET', '/api/products/sell');

            if ($response->successful) {
                $products = $response->body['data'] ?? [];

                // Logic Gambar (Pastikan ini sesuai dengan fix Image URL sebelumnya)
                foreach ($products as &$product) {
                    if (is_object($product)) {
                        $product = (array) $product;
                    }

                    $img = $product['image'] ?? null;
                    if (!empty($img)) {
                        // Cek apakah link eksternal atau lokal
                        if (str_contains($img, 'http')) {
                            $product['image'] = $img;
                        } else {
                            $product['image'] = asset('storage/' . $img);
                        }
                    } else {
                        $product['image'] = asset('images/no-image.png');
                    }
                }
                unset($product);

            } else {
                $msg = $response->body['message'] ?? 'Unknown Error';
                $apiError = "API error: " . $msg;
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

    public function processPayment(Request $request)
    {
        try {
            // 1. Validasi input
            $request->validate([
                'cart' => 'required|array',
                'cart.*.product_id' => 'required',
                'cart.*.quantity' => 'required|numeric',
            ]);

            $cart = $request->input('cart', []);
            $rawTax = $request->input('tax_rate', 0.11);
            $taxRate = ($rawTax <= 1) ? $rawTax * 100 : $rawTax;

            $details = [];
            foreach ($cart as $item) {
                $details[] = [
                    'product_id' => $item['product_id'], 
                    'quantity'   => $item['quantity'],
                ];
            }

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

            // 2. Panggil API Backend (INTERNAL CALL)
            $response = $this->internalApiCall('POST', '/api/sales', $payload);

            // 3. Return response ke Frontend (AJAX)
            if ($response->successful) {
                return response()->json($response->body);
            } else {
                // [UPDATE DI SINI] 
                // Hapus tulisan "Backend Error: " agar pesan dari backend (soal Stok) langsung tampil bersih
                return response()->json([
                    'status'  => $response->status,
                    'message' => $response->body['message'] ?? 'Unknown Error', // <--- Bersih
                    'errors'  => $response->body['errors'] ?? null
                ], $response->status);
            }

        } catch (\Exception $e) {
            Log::error('Process Payment Error: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }
}