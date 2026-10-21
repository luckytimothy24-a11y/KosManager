<?php

namespace App\Http\Middleware;

use App\Models\Penghuni;
use App\Models\Tagihan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BillingGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user->isTenant()) {
            return $next($request);
        }

        // Izinkan akses ke halaman yang tidak terkait billing
        $allowedPrefixes = ['/tenant/tagihan', '/tenant/pembayaran', '/tenant/messages', '/tenant/damage-reports', '/tenant/inquiries', '/tenant/invitations', '/tenant/kontrak', '/messages'];
        $path = $request->path();

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, ltrim($prefix, '/'))) {
                return $next($request);
            }
        }

        // Izinkan akses ke dashboard (untuk menampilkan peringatan)
        if ($path === 'dashboard' || $path === 'dashboard/') {
            return $next($request);
        }

        // Cek apakah ada tagihan overdue
        $penghunis = Penghuni::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('id');

        if ($penghunis->isEmpty()) {
            return $next($request);
        }

        $hasOverdue = Tagihan::whereIn('penghuni_id', $penghunis)
            ->where('status', Tagihan::STATUS_OVERDUE)
            ->exists();

        if ($hasOverdue) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda memiliki tagihan yang belum dibayar. Silakan lunasi terlebih dahulu.',
                ], 403);
            }

            return redirect()->route('tenant.tagihan.index')->with('error', 'Anda memiliki tagihan yang belum dibayar. Silakan lunasi terlebih dahulu.');
        }

        return $next($request);
    }
}
