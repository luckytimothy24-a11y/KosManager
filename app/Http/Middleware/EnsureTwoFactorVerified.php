<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan user yang mengaktifkan 2FA telah melewati tantangan kode
 * pada sesi ini. Juga mendorong (atau mewajibkan, jika config enforce=true)
 * role admin/super_admin untuk mengaktifkan 2FA.
 *
 * Didaftarkan pada grup `web`; no-op untuk tamu dan rute 2FA/logout.
 */
class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        // 1) User dengan 2FA aktif wajib melewati tantangan pada sesi ini.
        if ($user->hasTwoFactorEnabled() && ! $this->sessionVerified($request)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Verifikasi 2FA diperlukan.'], 423);
            }

            return redirect()->route('two-factor.challenge');
        }

        // 2) Enforcement opsional untuk role tertentu yang belum mengaktifkan 2FA.
        if ($user->twoFactorRequired() && ! $user->hasTwoFactorEnabled()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Aktifkan 2FA terlebih dahulu.'], 423);
            }

            return redirect()->route('two-factor.index')
                ->with('warning', 'Role Anda diwajibkan mengaktifkan 2FA sebelum melanjutkan.');
        }

        return $next($request);
    }

    private function shouldSkip(Request $request): bool
    {
        return $request->routeIs(
            'two-factor.*',
            'logout',
            'login',
            'password.*',
            'verification.*',
            'profile.password.confirm'
        );
    }

    private function sessionVerified(Request $request): bool
    {
        $verifiedAt = (int) $request->session()->get('two_factor_verified_at', 0);
        $lifetime = (int) config('two-factor.session_minutes', 720) * 60;

        return $verifiedAt > 0 && (now()->timestamp - $verifiedAt) < $lifetime;
    }
}
