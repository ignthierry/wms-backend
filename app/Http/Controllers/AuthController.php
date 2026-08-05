<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
                    ->orWhere('email', $request->username)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Kredensial tidak valid'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Catat login ke user_logs (login berlangsung sebelum token ada)
        try {
            $ua = $request->userAgent() ?? '';
            $device = app(\App\Http\Middleware\LogActivity::class)->detectDevice($ua);
            \App\Models\UserLog::create([
                'user_id'     => $user->id,
                'username'    => $user->username,
                'ip_address'  => $request->ip(),
                'user_agent'  => substr($ua, 0, 500),
                'device_type' => $device['type'],
                'browser'     => $device['browser'],
                'platform'    => $device['platform'],
                'activity'    => 'LOGIN',
                'method'      => 'POST',
                'endpoint'    => 'auth/login',
                'description' => 'User login ke sistem',
                'payload'     => ['username' => $user->username],
            ]);
        } catch (\Throwable $e) {
            // Logging gagal tidak boleh menggagalkan login
            \Illuminate\Support\Facades\Log::warning('Gagal catat login: ' . $e->getMessage());
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('role', 'consignee'),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 2, // Assuming 2 is a standard user/customer role. Adjust as needed.
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }
}
