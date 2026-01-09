<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Tambahkan ini

class FrontAuthController extends Controller
{
    // Constructor tidak lagi dibutuhkan karena kita tidak pakai URL eksternal
    // public function __construct() {}

    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. BUAT REQUEST BOHONGAN (Internal Proxy)
        // Kita membuat request seolah-olah ada user menembak ke '/api/login'
        // Pastikan route '/api/login' benar-benar ada di routes/api.php
        $proxyRequest = Request::create('/api/login', 'POST', $credentials);
        
        // PENTING: Paksa header agar API merespon JSON, bukan redirect HTML
        $proxyRequest->headers->set('Accept', 'application/json');

        // 3. JALANKAN REQUEST DI DALAM MEMORI
        // app()->handle() akan memproses request tanpa melewati Nginx/Internet
        $response = app()->handle($proxyRequest);

        // 4. AMBIL HASILNYA
        // Karena response-nya raw, kita harus decode JSON-nya manual
        $result = json_decode($response->getContent(), true);
        $statusCode = $response->getStatusCode();

        // 5. CEK STATUS
        // Jika status bukan 200 (misal 401 Unauthorized atau 500 Error)
        if ($statusCode != 200) {
            return back()->with('error', $result['message'] ?? 'Login gagal, periksa email/password.');
        }

        // 6. PROSES LOGIN BERHASIL
        $user = $result['data']; // Pastikan struktur JSON API kamu ada key 'data'

        session([
            'user_id' => $user['id'],
            'username' => $user['username'] ?? $user['name'], // Jaga-jaga beda nama kolom
            'email' => $user['email'],
            'role' => $user['role'],
            'logged_in' => true,
        ]);

        $request->session()->regenerate();

        return redirect()->intended(route('reports.index'));
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerate();
        return redirect(route('login'));
    }
}