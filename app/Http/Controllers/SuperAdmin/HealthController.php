<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

/**
 * Ringkasan kesehatan sistem (read-only, observasi saja).
 * Tanpa detail sensitif (secret, env, path absolut, kredensial).
 */
class HealthController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', AuditLog::class);
        $dbOk = false;
        $dbDriver = config('database.default');
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Exception $e) {
            $dbOk = false;
        }

        $storagePath = storage_path('app');
        $storageWritable = is_writable($storagePath);
        $storageUsedMb = $this->directorySizeMb($storagePath);

        $chain = ['total' => 0, 'verified' => 0, 'failed' => null];
        try {
            $result = AuditLogService::verifyChain();
            $chain = [
                'total' => (int) ($result['total'] ?? 0),
                'verified' => (int) ($result['verified'] ?? 0),
                'failed' => $result['failed'] ?? null,
            ];
        } catch (\Exception $e) {
            $chain['failed'] = 'check-error';
        }

        $checks = [
            'database' => $dbOk,
            'storage_writable' => $storageWritable,
            'audit_chain' => $chain['failed'] === null,
        ];

        return view('super-admin.system-health.index', [
            'dbOk' => $dbOk,
            'dbDriver' => $dbDriver,
            'cacheDriver' => config('cache.default'),
            'queueDriver' => config('queue.default'),
            'storageWritable' => $storageWritable,
            'storageUsedMb' => $storageUsedMb,
            'appVersion' => config('app.version', app()->version()),
            'phpVersion' => PHP_VERSION,
            'auditChain' => $chain,
            'allOk' => ! in_array(false, $checks, true),
        ]);
    }

    private function directorySizeMb(string $path): ?float
    {
        if (! is_dir($path)) {
            return null;
        }

        $bytes = 0;
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            $count = 0;
            foreach ($iterator as $file) {
                $bytes += $file->getSize();
                if (++$count > 20000) {
                    break;
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return round($bytes / 1048576, 1);
    }
}
