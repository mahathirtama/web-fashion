<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FrontUserManagementController extends Controller
{
    protected $backendApiUrl;

    public function __construct()
    {
        $this->backendApiUrl = rtrim(env('BACKEND', 'http://127.0.0.1:8000/api'), '/');
    }

    /** LIST USER */
    public function index()
    {
        $response = Http::get($this->backendApiUrl . '/users');

        if (!$response->successful()) {
            return view('users.index', [
                'users' => [],
                'error' => 'Failed to load users'
            ]);
        }

        return view('users.index', [
            'users' => $response->json()['data'] ?? []
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

        $response = Http::post($this->backendApiUrl . '/users', $payload);

        if ($response->failed()) {
            return back()->with('error', $response->json()['message'] ?? 'Failed to create');
        }

        return redirect()->route('users.index')->with('success', 'User created');
    }

    /** FORM EDIT */
    public function edit($id)
    {
        $response = Http::get($this->backendApiUrl . "/users/$id");

        if ($response->failed()) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        return view('users.edit', [
            'user' => $response->json()['data']
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
        $response = Http::put($this->backendApiUrl . "/users/{$id}", $payload);

        if ($response->failed()) {
            // Jika backend mengembalikan error validasi, ambil pesan & errors bila ada
            $body = $response->json();
            $msg = $body['message'] ?? 'Failed to update user';
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
        $response = Http::delete($this->backendApiUrl . "/users/$id");

        if ($response->failed()) {
            return back()->with('error', 'Failed to delete user');
        }

        return redirect()->route('users.index')->with('success', 'User deleted');
    }
}
