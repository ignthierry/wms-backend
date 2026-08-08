<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Batasi akses berdasarkan role.
 *
 * - operator_field: hanya boleh mengakses modul operasional gudang
 *   (manajemen barang masuk: receiving + QC; barang keluar: QC outbound + packing).
 * - forwarding: hanya boleh akses endpoint client.
 * - Role lain (super_admin, warehouse_admin): akses penuh.
 */
class RoleAccess
{
    /** Route prefix yang boleh diakses operator_field */
    private const OPERATOR_ALLOWED = [
        'receivings',
        'deviations',
        'asn-items',
        'outbound/qc',
        'outbound/packing',
        'packings',
    ];

    /** Route prefix yang boleh diakses forwarding */
    private const FORWARDING_ALLOWED = [
        'client',
        'tracking',
        'photos',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $role = strtolower($user->role?->role_name ?? '');

        // Role dengan akses penuh
        if (in_array($role, ['super_admin', 'warehouse_admin'])) {
            return $next($request);
        }

        $path = trim($request->path(), '/');

        if ($role === 'operator_field') {
            foreach (self::OPERATOR_ALLOWED as $prefix) {
                if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                    return $next($request);
                }
            }
            return response()->json(['message' => 'Akses ditolak: operator hanya dapat mengakses receiving, QC, dan pengemasan'], 403);
        }

        if ($role === 'forwarding') {
            foreach (self::FORWARDING_ALLOWED as $prefix) {
                if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                    return $next($request);
                }
            }
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        return $next($request);
    }
}
