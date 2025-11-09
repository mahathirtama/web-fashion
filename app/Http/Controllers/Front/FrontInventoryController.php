<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FrontInventoryController extends Controller
{
    protected $backendApiUrl;
    protected $backendBaseUrl;

    public function __construct()
    {
        // http://127.0.0.1:8000/api
        $this->backendApiUrl = rtrim(env('BACKEND', 'http://127.0.0.1:8000/api'), '/');

        // http://127.0.0.1:8000
        $this->backendBaseUrl = preg_replace('#/api$#', '', $this->backendApiUrl);
    }

    public function index()
    {
        $products = [];
        $apiError = null;

        try {
            // ✅ GET API /products
            $response = Http::get($this->backendApiUrl . '/products');

            if ($response->successful()) {

                // Backend kamu: return langsung array produk
                // BUKAN: {status, data: [...]}
                // Jadi langsung ambil response json
                $products = $response->json() ?? [];

                // ✅ Convert object → array + generate image_url
                foreach ($products as &$product) {

                    // Jaga-jaga API return object
                    if (is_object($product)) {
                        $product = (array) $product;
                    }

                    // ✅ Buat URL gambar
                    if (!empty($product['image'])) {
                        $product['image_url'] = $this->backendBaseUrl . '/storage/' . $product['image'];
                    } else {
                        $product['image_url'] = asset('images/no-image.png');
                    }
                }
                unset($product);

            } else {
                $apiError = "API Error: " . ($response->json()['message'] ?? $response->status());
            }

        } catch (\Exception $e) {
            $apiError = "Gagal memuat data: " . $e->getMessage();
        }

        return view('inventory.index', [
            'products' => $products,
            'apiError' => $apiError
        ]);
    }
    public function create()
{
    return view('inventory.create');
}

    public function store(Request $request)
{

    try {
        $validated = $request->validate([
            'kode_product' => 'required|string|max:50',
            'name'         => 'required|string|max:255',
            'category'     => 'nullable|string|max:100',
            'deskripsi'    => 'nullable|string',
            'status'       => 'nullable|in:active,inactive',
            'stock'        => 'required|integer|min:0',
            'price'        => 'required|numeric|min:0',
            'supplier_id'  => 'required|integer',
            'image'        => 'nullable|string|max:255', // ✅ string, no upload
        ]);

        // ✅ Kirim data langsung ke API backend
        $response = Http::post($this->backendApiUrl . '/products', $validated);

        if (!$response->successful()) {
            return back()->with('error', 'API Error: ' . $response->body());
        }

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Product created successfully');

    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}

 public function edit($id)
    {
        $product = null;

        try {
            $response = Http::get($this->backendApiUrl . "/products/$id");

            if ($response->successful()) {
                $product = $response->json();
            } else {
                return back()->with('error', 'Product not found');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'API Error: ' . $e->getMessage());
        }

        return view('inventory.edit', compact('product'));
    }

    // ==========================
    // UPDATE PRODUCT
    // ==========================
    public function update(Request $request, $id)
    {
        $payload = [
            'kode_product' => $request->kode_product,
            'name' => $request->name,
            'deskripsi' => $request->deskripsi,
            'price' => $request->price,
            'category' => $request->category,
            'stock' => $request->stock,
            'image' => $request->image,
            'supplier_id' => $request->supplier_id,
            'status' => $request->status,
        ];

        try {
            $response = Http::put($this->backendApiUrl . "/products/$id", $payload);

            if ($response->successful()) {
                return redirect()
                    ->route('inventory.index')
                    ->with('success', 'Product updated successfully');
            } else {
                return back()->with('error', 'Failed to update product');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'API Error: ' . $e->getMessage());
        }
    }

}
