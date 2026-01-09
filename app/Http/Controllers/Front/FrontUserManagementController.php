<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http; // Tidak dipakai lagi

class FrontUserManagementController extends Controller
{
    // Helper function untuk Internal Request
    private function internalApiCall($method, $uri, $data = [])
    {
        // 1. Buat Request Bohongan
        // Parameter ke-3 ($data) masuk ke Body (POST/PUT) atau Query (GET) otomatis
        $request = Request::create($uri, $method, $data);
        
        // 2. Set Header agar dianggap JSON Request
        $request->headers->set('Accept', 'application/json');

        // 3. Eksekusi di dalam memori server
        $response = app()->handle($request);

        // 4. Return object sederhana
        return (object) [
            'status' => $response->getStatusCode(),
            'body' => json_decode($response->getContent(), true),
            'successful' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];
    }

    /** LIST USER */
    public function index()
    {
        // ✅ Ubah Http::get jadi Internal Call ke /api/users
        $response = $this->internalApiCall('GET', '/api/users');

        if (!$response->successful) {
            return view('users.index', [
                'users' => [],
                'error' => 'Failed to load users: ' . ($response->body['message'] ?? 'Unknown error')
            ]);
        }

        return view('users.index', [
            // Ambil data dari key 'data' (sesuai standar API Resource Laravel)
            'users' => $response->body['data'] ?? []
        ]);
    }

    /** FORM CREATE */
    public function create()
    {
        return view('users.create');
    }

    /** STORE USER */
    public function store(Request $request)
    {
        $payload = $request->only(['username', 'email', 'password', 'role']);

        // ✅ Ubah Http::post jadi Internal Call
        $response = $this->internalApiCall('POST', '/api/users', $payload);

        if (!$response->successful) {
            return back()->with('error', $response->body['message'] ?? 'Failed to create');
        }

        return redirect()->route('users.index')->with('success', 'User created');
    }

    /** FORM EDIT */
    public function edit($id)
    {
        // ✅ Ubah Http::get detail user
        $response = $this->internalApiCall('GET', "/api/users/$id");

        if (!$response->successful) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        return view('users.edit', [
            'user' => $response->body['data']
        ]);
    }

    /** UPDATE USER */
    public function update(Request $request, $id)
    {
        // Validasi ringan
        $request->validate([
            'username' => 'required|string',
            'email'    => 'required|email',
            'role'     => 'required|in:admin,kasir',
            // password optional, tidak wajib
        ]);

        // Ambil field yang pasti dikirim
        $payload = $request->only(['username', 'email', 'role']);

        // Tambahkan password hanya jika user mengisi (tidak kosong)
        if ($request->filled('password')) {
            $payload['password'] = $request->password;
        }

        try {
            // ✅ Ubah Http::put jadi Internal Call
            $response = $this->internalApiCall('PUT', "/api/users/{$id}", $payload);

            if (!$response->successful) {
                // Ambil pesan error dari body response
                $msg = $response->body['message'] ?? 'Failed to update user';
                return back()->with('error', $msg)->withInput();
            }

            return redirect()->route('users.index')->with('success', 'User updated');

        } catch (\Exception $e) {
            return back()->with('error', 'API Error: ' . $e->getMessage());
        }
    }

    /** DELETE USER */
    public function destroy($id)
    {
        // ✅ Ubah Http::delete jadi Internal Call
        $response = $this->internalApiCall('DELETE', "/api/users/$id");

        if (!$response->successful) {
            return back()->with('error', 'Failed to delete user');
        }

        return redirect()->route('users.index')->with('success', 'User deleted');
    }
}