<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyembunyikan fitur/route dari demo dan penggunaan normal.
 * Route tetap terdaftar agar test suite fitur terkait tetap berjalan.
 */
class HideForDemo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        abort(404);
    }
}
