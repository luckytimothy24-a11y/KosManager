<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disk Penyimpanan Backup
    |--------------------------------------------------------------------------
    |
    | Disk filesystem (lihat config/filesystems.php) tempat file backup
    | database disimpan. Default "backups" adalah disk lokal privat di
    | storage/app/backups.
    |
    */

    'disk' => env('BACKUP_DISK', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Retention (hari)
    |--------------------------------------------------------------------------
    |
    | Berapa hari file backup dipertahankan. Backup yang lebih tua akan
    | dihapus otomatis oleh backup:run / backup:prune.
    |
    */

    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Prefix Nama File
    |--------------------------------------------------------------------------
    |
    | Prefix nama file backup. Hanya file dengan prefix ini yang dianggap
    | file backup (dipakai untuk list & prune).
    |
    */

    'filename_prefix' => env('BACKUP_FILENAME_PREFIX', 'backup-'),

    /*
    |--------------------------------------------------------------------------
    | Penjadwalan
    |--------------------------------------------------------------------------
    |
    | Menentukan apakah backup:run dijadwalkan otomatis via scheduler
    | dan jam eksekusinya.
    |
    */

    'schedule_enabled' => (bool) env('BACKUP_SCHEDULE_ENABLED', true),
    'schedule_at' => env('BACKUP_SCHEDULE_AT', '00:35'),

    /*
    |--------------------------------------------------------------------------
    | Driver MySQL
    |--------------------------------------------------------------------------
    |
    | Path biner mysqldump & mysql client, serta flag default. Biner harus
    | tersedia di server. Privilege read database diperlukan.
    |
    */

    'mysql' => [
        'dump_binary' => env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'),
        'client_binary' => env('BACKUP_MYSQL_CLIENT_PATH', 'mysql'),
        'dump_flags' => env('BACKUP_MYSQLDUMP_FLAGS', '--single-transaction --routines --triggers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver PostgreSQL
    |--------------------------------------------------------------------------
    |
    | Path biner pg_dump & psql client.
    |
    */

    'pgsql' => [
        'dump_binary' => env('BACKUP_PG_DUMP_PATH', 'pg_dump'),
        'client_binary' => env('BACKUP_PSQL_PATH', 'psql'),
    ],

];
