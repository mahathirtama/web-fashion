<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FrontInvoiceController extends Controller
{
    protected $backendApiUrl;
    protected $backendBaseUrl;

    public function __construct()
    {
        // API: http://127.0.0.1:8000/api
        $this->backendApiUrl = rtrim(env('BACKEND', 'http://127.0.0.1:8000/api'), '/');

        // Base URL: http://127.0.0.1:8000
        $this->backendBaseUrl = preg_replace('#/api$#', '', $this->backendApiUrl);
    }

    // ============================================================
    // ✅ 1. LIST INVOICE
    // ============================================================
    public function index()
    {
        $invoices = [];

        try {
            $response = Http::get($this->backendApiUrl . '/sales');

            if ($response->successful()) {
                $invoices = $response->json('data');
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
            $response = Http::get($this->backendApiUrl . "/sales/{$id}");

            if ($response->successful()) {
                $invoice = $response->json('data');
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
            $response = Http::delete($this->backendApiUrl . "/sales/{$id}");

            if ($response->successful()) {
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
