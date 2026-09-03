<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [];
        $healthy = true;

        try {
            DB::connection()->select('SELECT 1');
            $checks['database'] = 'connected';
        } catch (\Throwable $e) {
            $healthy = false;
            $checks['database'] = 'unavailable';
            report($e);
        }

        return response()->json([
            'status' => $healthy ? 'ok' : 'error',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }
}
