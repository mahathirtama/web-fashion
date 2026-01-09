<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http; // Tidak dipakai lagi

class FrontReportController extends Controller
{
    // Helper function untuk Internal Request
    private function internalApiCall($method, $uri, $data = [])
    {
        // 1. Buat Request Bohongan
        // Parameter ke-3 ($data) di Request::create akan otomatis masuk ke query string (untuk GET) atau body (untuk POST)
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

    public function index(Request $request)
    {
        // Ambil tanggal dari query string browser
        $startDate = $request->query('start_date', now()->subDays(30)->toDateString());
        $endDate   = $request->query('end_date', now()->toDateString());

        // Siapkan parameter untuk dikirim ke API
        $queryParams = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ];

        // ✅ Ubah Http::get menjadi Internal Call ke /api/reports
        // Query params dikirim sebagai argumen ke-3
        $response = $this->internalApiCall('GET', '/api/reports', $queryParams);

        if (!$response->successful) {
            return view('reports.index', [
                'error' => 'Failed to fetch report from backend API: ' . ($response->body['message'] ?? 'Unknown Error'),
                'report' => null,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }

        // Ambil data dari key 'data' (sesuai standar JSON Response kamu)
        $data = $response->body['data'] ?? [];

        return view('reports.index', [
            'report' => $data,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}