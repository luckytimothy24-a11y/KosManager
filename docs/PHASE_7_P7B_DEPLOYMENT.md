# P7-B: Deployment & Production Readiness

Dokumen ini merangkum hasil audit kesiapan produksi (**Deployment & Production Readiness**),
perubahan yang dilakukan, cara deploy ke produksi, serta rekomendasi lanjutan.

## Ringkasan

- **Status:** Selesai (implementasi + dokumentasi).
- **Hasil audit:** Aplikasi sudah memiliki fondasi produksi yang sehat: `APP_DEBUG` default `false`,
  `APP_ENV` default `production`, tabel antrean (`jobs`, `job_batches`, `failed_jobs`) sudah dimigrasi,
  skeduler sudah mencakup maintenance + backup, CI sudah ada, dan berkas sensitif tidak terekspos di `public/`.
- **Temuan yang diperbaiki di tahap ini:**
  1. Belum ada **endpoint health/readiness** (`GET /health` dan `GET /up`) untuk load balancer / docker healthcheck.
  2. Route `/` masih berbentuk **closure** sehingga `php artisan route:cache` gagal (tidak bisa dioptimasi di produksi).
  3. `.env.example` belum mendokumentasikan variabel **payment gateway** (Phase 6) dan **trusted proxies**.
  4. `TrustProxies` tidak dapat dikonfigurasi via env (ketika berada di belakang reverse proxy / load balancer,
     deteksi HTTPS & `schema` bisa salah).

## Perubahan yang Dilakukan

### 1. Health / readiness check (baru)

- `app/Http/Controllers/HealthController.php` — mengecek koneksi database dengan `SELECT 1`.
  Mengembalikan `200 {"status":"ok","checks":{"database":"connected"}}` bila sehat, atau
  `503 {"status":"error","checks":{"database":"unavailable"}}` bila gagal.
- Tidak membocorkan detail konfigurasi/stack trace: hanya status generik + nama check.
- Publik tanpa autentikasi (diperuntukkan load balancer / healthcheck), tetap hanya berisi JSON dan tidak
  mengekspos data aplikasi.
- Route: `GET /health` dan `GET /up` (alias) di `routes/web.php`.

### 2. Konversi route `/` dari closure ke controller

- `app/Http/Controllers/WelcomeController.php` — memindahkan logika route `/` dari closure
  (query kos aktif + pencarian) ke invokable controller.
- `routes/web.php` — route `/` kini `Route::get('/', WelcomeController::class);`.
- **Efek:** `php artisan route:cache` kini berhasil (di-bootstrap sebelum perubahan akan gagal karena closure),
  sehingga command optimasi produksi `php artisan optimize` dapat digunakan utuh.

### 3. Konfigurasi env & Trusted Proxies

- `config/app.php` — menambah `trusted_proxies` (diisi dari `TRUSTED_PROXIES`).
- `app/Http/Middleware/TrustProxies.php` — membaca `config('app.trusted_proxies')`; menerima `"*"`
  (percaya semua proxy) atau daftar IP/CIDR dipisah koma. Default kosong = tidak ada proxy yang dipercaya
  (perilaku lama dipertahankan).
- `.env.example` — menambah:
  - `PAYMENT_GATEWAY=sandbox`
  - `PAYMENT_GATEWAY_WEBHOOK_KEY=` (kosong; **WAJIB** diisi nilai acak panjang di produksi)
  - `TRUSTED_PROXIES=` (kosongkan bila tidak di belakang proxy)

> **Keamanan (P8 Batch 1):** `PAYMENT_GATEWAY_WEBHOOK_KEY` kini **mandatory** dan **fail-closed**.
> Jika env kosong atau masih memakai nilai placeholder legacy (`change-me-in-production`),
> seluruh operasi payment gateway (buat charge online + verifikasi webhook) akan **ditolak (fail-closed)**.
> Tidak ada secret default yang aman — jangan commit secret ke repository.

### 4. Test baru

- `tests/Feature/HealthCheckTest.php` (6 test / 15 assertion):
  - `/health` dan `/up` mengembalikan 200 saat database sehat (json lengkap).
  - Endpoint publik & hanya JSON.
  - Saat koneksi DB gagal → `503`, isi response tidak memuat `SQLSTATE`, path, atau `APP_KEY`.
  - Pencarian landing tetap berfungsi setelah konversi controller (match dan non-match).

## Checklist Produksi

### Prasyarat

- PHP 8.2+ (ektensi: ctype, curl, dom, fileinfo, filter, gd, hash, iconv, intl, mbstring, openssl, pcre,
  pdo, pdo_mysql/pdo_sqlite, session, tokenizer, xml, zip).
- Composer, Node.js 20+, MySQL 8 (atau SQLite bila kebutuhan kecil).
- Pastikan direktori `storage/`, `storage/framework/*`, dan `bootstrap/cache` writable oleh user web server.

### 1. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Wajib diubah di produksi:

| Variabel              | Nilai produksi yang disarankan |
|-----------------------|--------------------------------|
| `APP_ENV`             | `production`                   |
| `APP_DEBUG`           | `false`                        |
| `APP_URL`             | `https://domain-anda`          |
| `LOG_CHANNEL`         | `daily` (rotasi 14 hari)       |
| `LOG_LEVEL`           | `warning` (atau `error`)       |
| `SESSION_SECURE_COOKIE` | `true` (wajib bila HTTPS)    |
| `PAYMENT_GATEWAY_WEBHOOK_KEY` | **WAJIB** — string acak panjang (fail-closed bila kosong) |
| `TRUSTED_PROXIES`     | IP/CIDR reverse proxy atau `*` |
| `MAIL_HOST` / `MAIL_PORT` | SMTP penyedia email produksi |

> Catatan: permission file log `storage/logs/` dan session `storage/framework/sessions/` harus writable.
> Untuk multi-server, pertimbangkan `CACHE_DRIVER=redis`, `SESSION_DRIVER=redis`, dan `QUEUE_CONNECTION=redis`.

### 2. Migrasi & seed (jika pertama kali)

```bash
php artisan migrate --force
php artisan db:seed --force   # jika diperlukan data awal/demo
```

### 3. Optimasi produksi

```bash
php artisan optimize          # config:cache + route:cache + view:cache + event:cache
php artisan storage:link      # bila memakai disk public (mis. upload bukti di masa depan)
```

- Pastikan sudah tidak ada `.env` off-by-one: `php artisan env` menunjukkan nilai aktif.
- `route:cache` kini didukung (route `/` sudah bukan closure).
- Gunakan `php artisan optimize:clear` saat deploy update untuk membersihkan cache lama.

### 4. Queue worker

Aplikasi menggunakan antrean email (`NotificationsService` → `Mail::queue`), `QUEUE_CONNECTION=database`.

```bash
php artisan queue:work database --tries=3 --timeout=90
```

- Untuk produksi gunakan supervisor/systemd agar worker selalu berjalan:
  - perintah: `php {path}/artisan queue:work database --sleep=3 --tries=3 --max-time=3600`
  - `numprocs`: sesuaikan dengan load.
- Pantau failed jobs: `artisan queue:failed` / `queue:retry` / `queue:flush`.

### 5. Scheduler (cron)

Semua tugas terjadwal (tandai tagihan overdue 00:05, expire booking 00:15, expire kontrak 00:25,
pengingat jatuh tempo 07:00, dan **backup database** 00:35 — lihat P7-A) dijalankan lewat scheduler.

```bash
crontab -e
* * * * * php /path/ke/project/artisan schedule:run >> /dev/null 2>&1
```

- Jika ada >1 server, aktifkan `onOneServer` (sudah dipasang pada `backup:run`) dan gunakan cache store yang sama.

### 6. Backup (rekap P7-A)

- `php artisan backup:run` — dump database ke `storage/app/backups` dengan nama unik + retensi otomatis (`BACKUP_RETENTION_DAYS`).
- `php artisan backup:list`, `backup:verify`, `backup:restore --force`.
- Amankan hasil backup: upload salinan ke remote (S3/rsync/gcloud) karena disk `backups` privat di dalam server.
- Detail lengkap: `docs/PHASE_7_P7A_DATABASE_BACKUP.md`.

### 7. Health check

- `GET /health` dan `GET /up` → `200` bila database terhubung, `503` bila tidak.
- Daftarkan pada load balancer / orchestrator (mis. Nginx `proxy_pass`, Docker healthcheck, UptimeRobot).

### 8. Keamanan

- `APP_DEBUG` selalu `false` di produksi.
- Gunakan HTTPS + set `SESSION_SECURE_COOKIE=true`. Atur `TRUSTED_PROXIES` bila di belakang proxy.
  Default (kosong) tidak mempercayai proxy manapun — aman bagi deployment langsung.
- `.env` tidak boleh masuk VCS (sudah ada di `.gitignore` beserta `.env.backup`, `.env.production`).
- `public/.htaccess` memakai `Options -Indexes` (tidak ada listing direktori). Pastikan `storage/` tidak docroot.
- Perbarui `PAYMENT_GATEWAY_WEBHOOK_KEY` dengan nilai acak panjang agar verifikasi signature webhook aman.
- Terapkan rotasi log (`LOG_CHANNEL=daily`) agar `storage/logs/laravel.log` tidak membengkak.

### 9. Deploy update

```bash
php artisan down            # maintenance mode
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build     # bila ada perubahan frontend
php artisan migrate --force
php artisan optimize
php artisan up
```

## Hasil Verifikasi (Regression Gate)

- `php artisan test` → **604 passed** (1785 assertions); baseline 598 (1770) + 6 test baru (15 assertion) — **PASS**.
- `vendor/bin/pint --test` → **PASS**.
- `npm run build` → **PASS**.
- `php artisan view:cache` → **PASS**.
- `php artisan route:cache` + `php artisan config:cache` → **PASS** (diverifikasi, lalu `route:clear`/`config:clear`).

## Risiko / Rekomendasi Lanjutan (di luar scope P7-B)

1. **Queue:** karena email dikirim via antrean, tanpa worker berjalan email tidak terkirim. Sudah terdokumentasi di atas.
2. **Multi-server:** file cache/session (default: `file`) tidak shared antar server. Untuk horizon scaling
   pertimbangkan Redis untuk `CACHE_DRIVER`, `SESSION_DRIVER`, dan `QUEUE_CONNECTION`.
3. **CI:** workflow `.github/workflows/ci.yml` hanya menjalankan PHPUnit, build, dan pint. Bisa diperluas
   (mis. `php -l`, cache build check, dependency audit) saat requirement CI lebih ketat.
4. **Containerisasi:** tidak ada `Dockerfile`/`docker-compose` — deployment didukung via Nginx/Apache + PHP-FPM
   standar. Docker dapat ditambahkan di tahap berikutnya bila infrastruktur target membutuhkannya.
5. **Monitoring:** belum ada tracing/APM terintegrasi (di luar cakupan P7-B; kandidat P7-E Observability).