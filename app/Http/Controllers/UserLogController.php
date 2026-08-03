<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    /**
     * Daftar log user dengan filter (user, aktivitas, tanggal, pencarian).
     */
    public function index(Request $request)
    {
        $query = UserLog::with('user:id,username,name')
            ->latest('created_at');

        // Filter: per user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter: jenis aktivitas
        if ($request->filled('activity')) {
            $query->where('activity', $request->activity);
        }

        // Filter: rentang tanggal (YYYY-MM-DD)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pencarian bebas (username / IP / endpoint / deskripsi)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('username', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhere('endpoint', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $perPage = $request->integer('per_page', 20);
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 20;
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Statistik ringkas untuk header report.
     */
    public function stats(Request $request)
    {
        $total = UserLog::count();
        $today = UserLog::whereDate('created_at', today())->count();
        $uniqueUsers = UserLog::distinct('user_id')->count('user_id');
        $logins = UserLog::where('activity', 'LOGIN')->count();

        return response()->json([
            'total_logs'     => $total,
            'today_logs'     => $today,
            'unique_users'   => $uniqueUsers,
            'total_logins'   => $logins,
        ]);
    }

    /**
     * Hapus log (misal pembersihan berkala).
     */
    public function destroy(string $id)
    {
        $item = UserLog::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
