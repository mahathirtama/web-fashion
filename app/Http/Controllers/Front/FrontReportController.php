<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FrontReportController extends Controller
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

    public function index(Request $request)
    {
        // Ambil tanggal dari query
        $startDate = $request->query('start_date', now()->subDays(30)->toDateString());
        $endDate   = $request->query('end_date', now()->toDateString());

        // Panggil API Report
        $response = Http::get($this->backendApiUrl . '/reports', [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        if (!$response->successful()) {
            return view('reports.index', [
                'error' => 'Failed to fetch report from backend API',
                'report' => null,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }

        $data = $response->json()['data'];

        return view('reports.index', [
            'report' => $data,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
