<?php

namespace App\Services\DatabaseBackup;

/**
 * Kontrak driver backup database.
 *
 * Setiap driver bertanggung jawab menghasilkan representasi backup (dump)
 * SQL dari koneksi yang ditunjuk, serta memulihkan/verifikasi dump tersebut.
 */
interface DatabaseBackupContract
{
    /**
     * Nama driver (mis. "sqlite", "mysql").
     */
    public function driverName(): string;

    /**
     * Buat dump lengkap (schema + data) dari koneksi database aktif.
     *
     * @throws DatabaseBackupException bila dump gagal.
     */
    public function dump(): string;

    /**
     * Pulihkan database dari string dump SQL.
     *
     * @throws DatabaseBackupException bila restore gagal.
     */
    public function restore(string $content): void;

    /**
     * Verifikasi integritas string dump (dapat dieksekusi ulang).
     */
    public function verify(string $content): bool;

    /**
     * Apakah driver backup tersedia di lingkungan saat ini.
     */
    public function supported(): bool;
}
