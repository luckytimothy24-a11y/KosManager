<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    public static function log(
        string $action,
        string $module,
        string $description,
        ?int $userId = null,
        ?array $data = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip(),
            'timestamp_data' => $data,
        ]);
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
}
