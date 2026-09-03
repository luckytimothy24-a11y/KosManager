<?php

namespace App\Services\DatabaseBackup;

/**
 * Resolver driver backup berdasarkan driver koneksi database yang aktif.
 */
class DatabaseBackupManager
{
    public static function driver(?string $connection = null): DatabaseBackupContract
    {
        $connection = $connection ?? config('database.default', 'mysql');
        $driverName = config("database.connections.{$connection}.driver", $connection);

        return match ($driverName) {
            'sqlite' => new SqliteBackupDriver($connection),
            'mysql' => new MySqlBackupDriver($connection),
            'pgsql' => new PostgreSqlBackupDriver($connection),
            default => throw new DatabaseBackupException("Driver database [{$driverName}] tidak didukung untuk backup."),
        };
    }
}
