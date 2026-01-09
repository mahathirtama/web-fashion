<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http; // Tidak dipakai lagi

class FrontInvoiceController extends Controller
{
    // Helper function untuk Internal Request (Sama seperti di Inventory)
    private function internalApiCall($method, $uri, $data = [])
    {
        // 1. Buat Request Bohongan
        $request = Request::create($uri, $method, $data);
        
        // 2. Set Header agar dianggap JSON Request
        $request->headers->set('Accept', 'application/json');

        // 3. Eksekusi di dalam memori
        $response = app()->handle($request);

        // 4. Return object sederhana
        return (object) [
            'status' => $response->getStatusCode(),
            'body' => json_decode($response->getContent(), true),
            'successful' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];
    }

    // ============================================================
    // ✅ 1. LIST INVOICE
    // ============================================================
    public function index()
    {
        $invoices = [];

        try {
            // Ubah Http::get menjadi internal call ke /api/sales
            $response = $this->internalApiCall('GET', '/api/sales');

            if ($response->successful) {
                // Ambil key 'data' dari response body
                $invoices = $response->body['data'] ?? [];
            }

        } catch (\Exception $e) {
            return view('invoices.index')->with('error', 'API Error: '.$e->getMessage());
        }

        return view('invoices.index', compact('invoices'));
    }

    // ============================================================
    // ✅ 2. DETAIL INVOICE
    // ============================================================
    public function show($id)
    {
        $invoice = null;

        try {
            // Ubah Http::get menjadi internal call ke /api/sales/{id}
            $response = $this->internalApiCall('GET', "/api/sales/{$id}");

            if ($response->successful) {
                $invoice = $response->body['data'] ?? null;
            } else {
                return back()->with('error', 'Invoice not found');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'API Error: '.$e->getMessage());
        }

        return view('invoices.show', compact('invoice'));
    }

    // ============================================================
    // ✅ 3. DELETE INVOICE
    // ============================================================
    public function destroy($id)
    {
        try {
            // Ubah Http::delete menjadi internal call DELETE
            $response = $this->internalApiCall('DELETE', "/api/sales/{$id}");

            if ($response->successful) {
                return redirect()
                    ->route('invoices.index')
                    ->with('success', 'Invoice deleted successfully');
            } else {
                return back()->with('error', 'Failed to delete invoice');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'API Error: '.$e->getMessage());
        }
    }
}