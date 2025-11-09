<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class FrontAuthController extends Controller
{
    protected $backendApiUrl;
    public function __construct()
    {
        $this->backendApiUrl = rtrim(env('BACKEND', 'http://127.0.0.1:8000/api'), '/');

    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Call Backend API
        $response = Http::post($this->backendApiUrl . "/login", $credentials);

        // Decode JSON
        $result = $response->json();

        // Jika API gagal
        if (!$response->successful() || $result['status'] != 200) {
            return back()->with('error', $result['message'] ?? 'Login gagal.');
        }

        // Data user dari API
        $user = $result['data'];

        // Simpan ke session
        session([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'logged_in' => true,
        ]);

        // Regenerate session
        $request->session()->regenerate();



        return redirect()->intended(route('dashboard.index'));
    }


    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerate();
        return redirect(route('login'));
    }

}
