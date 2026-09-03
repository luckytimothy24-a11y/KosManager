<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Carbon;

class AuditLogService
{
    public static function log(
        string $action,
        string $module,
        string $description,
        ?int $userId = null,
        ?array $data = null
    ): AuditLog {
        $previousHash = static::latestHash();
        $resolvedUserId = $userId ?? auth()->id();

        $log = AuditLog::create([
            'user_id' => $resolvedUserId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip(),
            'timestamp_data' => $data,
            'previous_hash' => $previousHash,
            'integrity_hash' => null,
        ]);

        $log->update(['integrity_hash' => static::computeHash($log, $previousHash)]);

        return $log;
    }

    public static function login($user): void
    {
        self::log('Login', 'Auth', "User {$user->name} logged in", $user->id);
    }

    public static function create(string $module, string $description, ?array $data = null): void
    {
        self::log('Create', $module, $description, data: $data);
    }

    public static function update(string $module, string $description, ?array $data = null): void
    {
        self::log('Update', $module, $description, data: $data);
    }

    public static function delete(string $module, string $description, ?array $data = null): void
    {
        self::log('Delete', $module, $description, data: $data);
    }

    public static function approve(string $module, string $description, ?array $data = null): void
    {
        self::log('Approve', $module, $description, data: $data);
    }

    public static function reject(string $module, string $description, ?array $data = null): void
    {
        self::log('Reject', $module, $description, data: $data);
    }

    public static function verifyChain(): array
    {
        $previousHash = null;

        $logs = AuditLog::orderBy('id', 'asc')->get();

        $total = $logs->count();
        $verified = 0;
        $failed = null;

        foreach ($logs as $log) {
            $expectedHash = static::computeHash($log, $previousHash);

            if ($log->integrity_hash !== null && $log->integrity_hash !== $expectedHash) {
                $failed = $log->id;
                break;
            }

            if ($log->integrity_hash !== null) {
                $verified++;
            }

            $previousHash = $log->integrity_hash;
        }

        return [
            'total' => $total,
            'verified' => $verified,
            'failed_id' => $failed,
            'valid' => $failed === null,
        ];
    }

    private static function latestHash(): ?string
    {
        return AuditLog::whereNotNull('integrity_hash')->latest('id')->value('integrity_hash');
    }

    private static function computeHash(AuditLog $log, ?string $previousHash): string
    {
        $secret = config('audit.hmac_secret');

        $payload = implode(':', [
            $log->id ?? '',
            $log->created_at instanceof Carbon
                ? $log->created_at->timestamp
                : '',
            $log->user_id ?? '',
            $log->action,
            $log->module,
            $log->description ?? '',
            $log->ip_address ?? '',
            json_encode($log->timestamp_data),
            $previousHash ?? '',
        ]);

        if ($secret === null || strlen($secret) < 64) {
            return hash_hmac('sha256', $payload, str_repeat('0', 32));
        }

        return hash_hmac('sha256', $payload, $secret);
    }
}
