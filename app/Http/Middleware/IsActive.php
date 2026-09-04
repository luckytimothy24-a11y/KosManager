<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsActive
{
    public static function guardActive(Request $request): ?Response
    {
        if (Auth::check() && ! Auth::user()->is_active) {
            // API stateless: jangan akses session web, kembalikan JSON 403
            // sesuai kontrak API (terautentikasi tetapi akun nonaktif/diblokir).
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator.');
        }

        return null;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (($response = self::guardActive($request)) !== null) {
            return $response;
        }

        return $next($request);
    }
}
