<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LogActivity
{
    /**
     * Catat aktivitas user (login, logout, dan request ber-auth) ke tabel user_logs.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $user = $request->user();
            if ($user) {
                $this->record($request, $user);
            }
        } catch (\Throwable $e) {
            // Jangan sampai logging merusak request utama
            Log::warning('UserLog gagal dicatat: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * Tulis log ke database.
     */
    protected function record(Request $request, User $user): void
    {
        $method = strtoupper($request->method());
        $endpoint = $request->path();

        // Abaikan endpoint user-log itu sendiri (hindari infinite loop)
        if (str_contains($endpoint, 'user-logs')) {
            return;
        }

        $activity = match (true) {
            $method === 'POST' && str_contains($endpoint, 'auth/login') => 'LOGIN',
            $method === 'POST' && str_contains($endpoint, 'auth/logout') => 'LOGOUT',
            $method === 'POST'   => 'CREATE',
            $method === 'PUT', $method === 'PATCH' => 'UPDATE',
            $method === 'DELETE' => 'DELETE',
            $method === 'GET'    => 'VIEW',
            default => 'OTHER',
        };

        // Deskripsi ringkas dari payload (buang field sensitif)
        $payload = $request->except(['password', 'password_confirmation', 'token', 'access_token']);
        $description = $this->buildDescription($activity, $endpoint, $payload);

        // Deteksi perangkat dari User-Agent
        $ua = $request->userAgent() ?? '';
        $device = $this->detectDevice($ua);

        UserLog::create([
            'user_id'     => $user->id,
            'username'    => $user->username,
            'ip_address'  => $request->ip(),
            'user_agent'  => substr($ua, 0, 500),
            'device_type' => $device['type'],
            'browser'     => $device['browser'],
            'platform'    => $device['platform'],
            'activity'    => $activity,
            'method'      => $method,
            'endpoint'    => substr($endpoint, 0, 255),
            'description' => $description,
            'payload'     => $this->compactPayload($payload),
        ]);
    }

    protected function buildDescription(string $activity, string $endpoint, array $payload): string
    {
        $map = [
            'LOGIN'  => 'User login ke sistem',
            'LOGOUT' => 'User logout dari sistem',
        ];
        if (isset($map[$activity])) {
            return $map[$activity];
        }

        // Ambil identifier umum dari payload untuk deskripsi yang lebih jelas
        $identifiers = ['asn_number', 'dr_number', 'invoice_number', 'item_code', 'item_name',
                        'name', 'username', 'code', 'warehouse_name', 'surat_jalan_number'];
        foreach ($identifiers as $key) {
            if (isset($payload[$key]) && !empty($payload[$key])) {
                return ucfirst(strtolower($activity)) . ' data ' . $key . ': ' . $payload[$key];
            }
        }
        return ucfirst(strtolower($activity)) . ' pada endpoint ' . $endpoint;
    }

    protected function compactPayload(array $payload): array
    {
        // Batasi isi payload agar tabel tidak membengkak
        foreach ($payload as $k => $v) {
            if (is_string($v) && strlen($v) > 200) {
                $payload[$k] = substr($v, 0, 200) . '...';
            }
        }
        return array_slice($payload, 0, 10, true);
    }

    public function detectDevice(string $ua): array
    {
        $uaLower = strtolower($ua);

        // Platform
        $platform = 'Unknown';
        if (str_contains($uaLower, 'windows')) $platform = 'Windows';
        elseif (str_contains($uaLower, 'android')) $platform = 'Android';
        elseif (str_contains($uaLower, 'iphone') || str_contains($uaLower, 'ipad')) $platform = 'iOS';
        elseif (str_contains($uaLower, 'mac os')) $platform = 'macOS';
        elseif (str_contains($uaLower, 'linux')) $platform = 'Linux';

        // Tipe perangkat
        $type = 'desktop';
        if (preg_match('/mobile|android|iphone|ipod/i', $ua)) $type = 'mobile';
        elseif (preg_match('/ipad|tablet/i', $ua)) $type = 'tablet';

        // Browser
        $browser = 'Unknown';
        if (str_contains($uaLower, 'edg/')) $browser = 'Edge';
        elseif (str_contains($uaLower, 'opr/') || str_contains($uaLower, 'opera')) $browser = 'Opera';
        elseif (str_contains($uaLower, 'chrome')) $browser = 'Chrome';
        elseif (str_contains($uaLower, 'firefox')) $browser = 'Firefox';
        elseif (str_contains($uaLower, 'safari')) $browser = 'Safari';

        return compact('type', 'browser', 'platform');
    }
}
