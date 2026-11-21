# PHASE 7 — P7-A: DATABASE BACKUP & RECOVERY

**Proyek:** KosManager (Laravel 10 — Sistem Manajemen Kos)
**Scope:** P7-A — Database Backup & Recovery
**Status:** ✅ SELESAI
**Tanggal:** 01 September 2026

---

## 1. Audit Awal

Sebelum implementasi, tidak ditemukan mekanisme backup database apa pun pada aplikasi:

- Tidak ada Artisan command backup.
- Tidak ada konfigurasi backup di `config/`.
- Tidak ada scheduling backup di `app/Console/Kernel.php` (hanya task bisnis: mark-overdue, expire-booking, expire-kontrak, remind tagihan).
- Tidak ada disk khusus backup di `config/filesystems.php` (hanya `local`, `public`, `s3`).
- Tidak ada variabel `.env` terkait backup.

Implementasi dibangun dari nol mengikuti pola arsitektur yang sudah ada (`app/Services/PaymentGateway/*`).

## 2. Yang Diimplementasikan

### A. Abstraksi driver backup (`app/Services/DatabaseBackup/`)

- **`DatabaseBackupContract`** — kontrak setiap driver: `driverName()`, `dump()`, `restore()`, `verify()`, `supported()`. Mirip pola `PaymentGatewayContract`.
- **`SqliteBackupDriver`** — dump portabel SQL (schema dari `sqlite_master` + data `INSERT` dalam transaksi). Restore mengeksekusi ulang dump dengan rollback penuh bila gagal. `verify()` memuat dump ke SQLite `:memory:` sementara dan memeriksa tabel tercipta. **Didukung penuh & diuji** di lingkungan test SQLite.
- **`MySqlBackupDriver`** — dump via `mysqldump` (`--single-transaction --routines --triggers`), restore via `mysql` client, `verify()` memeriksa penanda `CREATE TABLE`/header MySQL dump. Kegagalan proses → `DatabaseBackupException`.
- **`PostgreSqlBackupDriver`** — dump via `pg_dump` (`--no-owner --no-acl`), restore via `psql` (`ON_ERROR_STOP=1`), `verify()` memeriksa penanda dump. Kegagalan proses → `DatabaseBackupException`.
- **`DatabaseBackupManager`** — memilih driver berdasarkan `driver` koneksi database aktif (`sqlite|mysql|pgsql`); driver lain → `DatabaseBackupException`.
- **`DatabaseBackupException`** — menyatukan kegagalan; dipakai agar command **tidak pernah menganggap backup sukses bila gagal**.

### B. Konfigurasi (`config/backup.php` + `.env.example`)

| Variabel | Default | Fungsi |
|---|---|---|
| `BACKUP_DISK` | `backups` | Disk penyimpanan (disk `backups` privat baru di `config/filesystems.php`) |
| `BACKUP_RETENTION_DAYS` | `30` | Retention (hari) — backup lebih tua dihapus otomatis |
| `BACKUP_FILENAME_PREFIX` | `backup-` | Prefix nama file backup |
| `BACKUP_SCHEDULE_ENABLED` | `true` | Aktifkan backup terjadwal |
| `BACKUP_SCHEDULE_AT` | `00:35` | Jam backup harian |
| `BACKUP_MYSQLDUMP_PATH` / `BACKUP_MYSQL_CLIENT_PATH` | `mysqldump` / `mysql` | Biner MySQL |
| `BACKUP_PG_DUMP_PATH` / `BACKUP_PSQL_PATH` | `pg_dump` / `psql` | Biner PostgreSQL |

### C. Artisan Commands

| Command | Fungsi |
|---|---|
| `backup:run [--disk=] [--keep=] [--connection=]` | Buat backup + bersihkan backup lama sesuai retention |
| `backup:list [--disk=]` | Daftar backup tersedia |
| `backup:verify {file} [--disk=] [--connection=]` | Verifikasi integritas file backup |
| `backup:restore {file} [--disk=] [--connection=] [--force]` | Pulihkan database (DESTRUKTIF — konfirmasi wajib tanpa `--force`) |

**Penanganan kegagalan (tidak ada sukses palsu):**
- `dump()` kosong → gagal.
- File tidak dapat ditulis / tidak eksis → gagal.
- `mysqldump`/`pg_dump`/`mysql`/`psql` exit non-zero → gagal + pesan stderr terakhir ditampilkan.
- Restore: dump gagal verifikasi → **dibatalkan** (tidak mengeksekusi); eksekusi gagal → rollback penuh (SQLite).
- Semua kegagalan → exit code FAILURE, tidak pernah SUCCESS.

### D. Scheduling (`app/Console/Kernel.php`)

```
00:35 harian: backup:run (bila BACKUP_SCHEDULE_ENABLED=true)
```
Dengan `withoutOverlapping()` + `onOneServer()` sehingga tidak dobel eksekusi.

### E. Disk & Keamanan

- Disk `backups` (privat) — `storage/app/backups`, tidak terekspos publik (di bawah `storage/app/.gitignore`).
- Nama file unik per detik: `backup-YYYY-MM-DD_HHMMSS-xxxx.sql` (suffix acak) sehingga dua backup di detik yang sama tidak saling menimpa.
- `backup:run` memeriksa `allFiles()` dengan filter prefix `backup-` + `.sql` sebelum delete — tidak akan menghapus file lain.

## 3. Verifikasi (Test — `tests/Feature/DatabaseBackupTest.php`)

**11 test / 41 assertions** — semua lulus:

1. Resolusi driver SQLite untuk koneksi default.
2. `backup:run` membuat file di disk + berisi `CREATE TABLE` dan `INSERT INTO "users"`.
3. `backup:run` menghapus backup lama (> retention 30 hari), menyisakan hanya backup baru.
4. `backup:list` menampilkan tabel file/ukuran/waktu.
5. `backup:verify` menolak konten invalid.
6. **`backup:restore` memulihkan database**: data dihapus → restore → data kembali utuh.
7. `backup:restore` meminta konfirmasi bila tanpa `--force`.
8. `backup:restore` menolak file yang tidak ada.
9. `backup:restore` menolak backup yang gagal verifikasi.
10. Driver tidak didukung (`sqlsrv`) → command gagal dengan pesan jelas.
11. Dua `backup:run` di detik yang sama menghasilkan nama file berbeda (tidak menimpa).

## 4. Cara Menggunakan

### Backup manual

```
php artisan backup:run
```

### Daftar backup

```
php artisan backup:list
```

### Verifikasi file backup

```
php artisan backup:verify backup-2026-09-01_000100-abcd.sql
```

### Restore (dari posisi proyek)

```
php artisan backup:restore backup-2026-09-01_000100-abcd.sql
# dimintai konfirmasi; atau tambahkan --force untuk melewati konfirmasi
```

> ⚠️ **PENTING:** `backup:restore` MENGGANTI seluruh data database saat ini. Selalu jalankan `backup:run` (atau verifikasi dengan `--force`) sebelum restore pada production.

### Backup terjadwal

Jalankan scheduler sistem pada kernel harian (biasanya sudah aktif di Laravel):

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Maka `backup:run` berjalan otomatis setiap hari pukul 00:35.

## 5. Batasan & Catatan

- Lingkungan test memakai SQLite `:memory:` — driver MySQL/PgSQL **tidak diuji end-to-end** di CI (butuh server DB + biner). Keduanya mengikuti struktur yang sama dan menegakkan kegagalan eksplisit; verifikasi di lingkungan production dengan biner yang tersedia disarankan.
- `backup:run` tidak streaming; untuk DB sangat besar, sesuaikan batas `setTimeout` di driver MySQL/PgSQL bila perlu.
- Tidak ada perubahan schema, fitur bisnis, routes, atau logic aplikasi selain yang terkait P7-A.

## 6. Files Changed

| Kategori | File |
|---|---|
| Config (baru) | `config/backup.php` |
| Services (baru) | `app/Services/DatabaseBackup/DatabaseBackupContract.php`, `SqliteBackupDriver.php`, `MySqlBackupDriver.php`, `PostgreSqlBackupDriver.php`, `DatabaseBackupManager.php`, `DatabaseBackupException.php` |
| Command (baru) | `app/Console/Commands/BackupDatabase.php`, `ListDatabaseBackups.php`, `VerifyDatabaseBackup.php`, `RestoreDatabaseBackup.php` |
| Config | `config/filesystems.php` (disk `backups`), `.env.example` (BACKUP_*) |
| Kernel | `app/Console/Kernel.php` (jadwal `backup:run`) |
| Test (baru) | `tests/Feature/DatabaseBackupTest.php` |