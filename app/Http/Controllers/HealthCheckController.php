<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [];
        $healthy = true;

        // Database check
        try {
            DB::select('SELECT 1');
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'fail';
            $healthy = false;
        }

        // Storage check
        try {
            $checks['storage'] = is_writable(storage_path()) ? 'ok' : 'fail';
            if ($checks['storage'] === 'fail') {
                $healthy = false;
            }
        } catch (\Exception $e) {
            $checks['storage'] = 'fail';
            $healthy = false;
        }

        // Cache check
        try {
            cache()->put('health_check', true, 10);
            $checks['cache'] = cache()->get('health_check') ? 'ok' : 'fail';
            if ($checks['cache'] === 'fail') {
                $healthy = false;
            }
        } catch (\Exception $e) {
            $checks['cache'] = 'fail';
            $healthy = false;
        }

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }
}
