# KosManager

[![CI](https://github.com/luckytimothy24-a11y/KosManager/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/luckytimothy24-a11y/KosManager/actions/workflows/ci.yml)

Sistem manajemen kos (boarding house) berbasis web yang dibangun dengan **Laravel 10**. KosManager mengelola seluruh siklus bisnis kos dalam satu aplikasi: dari pencarian kamar, booking, check-in, tagihan bulanan, verifikasi pembayaran, hingga check-out — dengan pemisahan peran yang jelas antara pemilik kos, admin operasional, dan penyewa.

## Tentang Project

KosManager dirancang untuk pemilik satu hingga banyak properti kos. Setiap kos dapat memiliki banyak kamar, banyak admin yang ditugaskan, dan penghuni dengan kontrak sewa harian maupun bulanan. Seluruh alur keuangan (tagihan dan pembayaran) terikat pada kontrak, sehingga status kamar dan kondisi keuangan selalu konsisten di setiap langkah proses.

## Fitur Utama

### Super Admin
- Manajemen user (owner, admin, tenant)
- Manajemen master fasilitas
- Laporan global (pendapatan & okupansi, export PDF/Excel)
- Audit log aktivitas seluruh sistem

### Admin
- Manajemen operasional kos yang ditugaskan
- Persetujuan booking
- Manajemen kontrak & penghuni
- Proses check-in / check-out
- Pembuatan tagihan
- Verifikasi pembayaran

### Owner
- Manajemen kos & kamar (CRUD, foto, fasilitas, harga harian/bulanan)
- Persetujuan booking
- Manajemen kontrak & penghuni
- Proses check-in / check-out
- Manajemen tagihan (diskon, denda)
- Verifikasi pembayaran
- Laporan milik sendiri (export PDF/Excel)

### Tenant
- Browse kos & kamar tersedia
- Booking kamar (harian/bulanan)
- Melihat kontrak sewa aktif
- Melihat tagihan & statusnya
- Upload bukti pembayaran
- Melihat status verifikasi pembayaran beserta alasan penolakan

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 10, PHP ≥ 8.1 |
| Database | MySQL (testing: SQLite in-memory) |
| Frontend | Blade, Tailwind CSS, Flowbite, Alpine.js |
| Build tool | Vite |
| Authentication | Laravel Breeze |
| Authorization | Role middleware + Policy |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/laravel-excel |
| Queue | Database driver |

## Architecture

Alur request mengikuti pola Laravel standar:

```
HTTP Request
  → Controller
  → Request Validation (FormRequest)
  → Policy / Authorization (middleware role + policy per record)
  → Service (AuditLogService, NotificationService)
  → Eloquent Model
  → Database
```

**Security boundary:** setiap route dikelompokkan berdasarkan role melalui `RoleMiddleware` (`role:a,b`). Akses level record divalidasi oleh Policy — misalnya owner tidak dapat membaca/mengubah data kos milik owner lain, admin hanya dapat mengakses kos yang ditugaskan padanya (pivot `kos_user`), dan tenant hanya dapat melihat data miliknya sendiri. Isolasi multi-owner ini diuji langsung oleh test suite.

## Database / ERD

Diagram relasi lengkap tersedia di [`docs/ERD.md`](docs/ERD.md).

Tabel utama:

- `users` — akun dengan kolom `role` (super_admin / owner / admin / tenant), soft delete
- `kos` → `kamar` — properti dan kamar (status: available/booked/occupied/maintenance)
- `fasilitas` + pivot `kamar_fasilitas` — fasilitas per kamar
- `kos_user` — penugasan admin ke kos tertentu
- `bookings` — pengajuan sewa dengan deteksi overlap periode
- `penghunis`, `kontraks` — data penghuni dan kontrak sewa (harian/bulanan)
- `check_ins`, `check_outs` — transisi masuk/keluar penghuni
- `tagihans`, `pembayarans` — tagihan per periode dan bukti pembayarannya
- `notifications`, `audit_logs` — notifikasi in-app/email dan jejak audit

Relasi inti: **Kos 1–N Kamar 1–N Booking/Kontrak/Tagihan → Tagihan 1–N Pembayaran**.

## Business Flow

```
Booking → Approval Owner/Admin → Check-in (buat Penghuni + Kontrak otomatis)
       → Tagihan per periode → Pembayaran oleh tenant → Verifikasi
       → Check-out (kontrak berakhir, kamar kembali available)
```

Aturan penting:
- Kamar *occupied/maintenance* tidak bisa dibooking; rentang tanggal tidak boleh tumpang-tindih dengan booking approved lain.
- Check-in hanya untuk booking berstatus approved.
- Check-out diblokir bila masih ada tagihan belum lunas atau pembayaran menunggu verifikasi.
- Command terjadwal: `tagihan:mark-overdue`, `tagihan:remind-due`, `booking:expire-old`, `kontrak:expire-old`.

## Payment Flow

```
Unpaid → Tenant upload bukti → Menunggu Verifikasi
       → Terverifikasi → Lunas
```

Jika ditolak:

```
Menunggu Verifikasi → Ditolak (+ alasan penolakan) → Tenant bayar kembali
```

Nominal pembayaran wajib sama dengan total tagihan dan divalidasi ulang saat verifikasi.

## Security

Fitur keamanan yang sudah diimplementasikan dan diuji:

- **Policy authorization** pada akses level record
- **Role middleware** untuk pengelompokan route
- **Multi-owner isolation** — owner/admin/tenant hanya melihat datanya sendiri
- **Private payment proof storage** — bukti bayar disimpan di disk privat, tidak dapat diakses publik
- **Authorized payment proof download** — unduhan bukti divalidasi kepemilikannya
- **Payment amount validation** — nominal diverifikasi ulang terhadap total tagihan
- **Double-payment protection** — satu submission aktif per tagihan
- **Transaction + lockForUpdate** pada aksi approve/reject untuk mencegah race condition
- **Booking overlap protection** — penolakan otomatis periode bentrok
- **Audit log** — login, CRUD, approve/reject tercatat termasuk IP address
- **Audit log anti-tamper** — tiap entri ditandatangani HMAC-SHA256 (rantai hash antarsatu dengan secret 64-hex; fail-closed jika secret tidak valid)
- **Secure image upload** — validasi konten file asli (MIME via finfo + decoding image), HTML/SVG/malformed ditolak
- **Soft delete historical users** — user dengan riwayat transaksi tidak terhapus permanen
- **Security tests** — suite khusus isolation boundary & payment security

## Automated Testing

Hasil test terbaru (baseline terverifikasi batch 9):

```
Last verified baseline: 1031 passed (3521 assertions)
Pint:     passed (code style PSR-12)
Build:    sukses (Vite)
```

> Sebelum deploy pastikan seluruh suite lolos: `php artisan test`, `vendor/bin/pint --test`, `npm run build`.

Cakupan meliputi unit test logika bisnis, feature test seluruh modul, race condition, security/isolation boundary, hingga command otomasi.

## Installation

```bash
git clone https://github.com/luckytimothy24-a11y/KosManager.git
cd KosManager

composer install
npm install

copy .env.example .env        # Windows (cp .env.example .env di Linux/macOS)
php artisan key:generate
```

Konfigurasi database MySQL pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kosmanager
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan dengan:

```bash
php artisan migrate --seed
npm run build
php artisan storage:link
php artisan serve
```

Aplikasi berjalan di `http://127.0.0.1:8000`. Untuk fitur email queue dan scheduler:

```bash
php artisan queue:work
php artisan schedule:work
```

## Demo Accounts

Akun demo berikut dibuat otomatis oleh seeder (`php artisan migrate --seed`) dan hanya untuk lingkungan development:

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@example.com | password |
| Owner | owner@example.com | password |
| Admin | admin@example.com | password |
| Tenant | tenant@example.com | password |

> Jangan gunakan kredensial ini di production.

## Testing

```bash
php artisan test              # PHPUnit (SQLite in-memory)
vendor/bin/pint --test        # Code style check
npm run build                 # Frontend production build
```

## CI/CD

Project menggunakan **GitHub Actions** (`.github/workflows/ci.yml`) yang berjalan otomatis pada setiap push dan pull request menuju branch `develop`. Pipeline menjalankan:

1. Instalasi dependency Composer & NPM
2. Build aset frontend (Vite)
3. Migration pada SQLite in-memory
4. PHPUnit test suite
5. Validasi code style (Pint)

Pipeline gagal jika salah satu tahap gagal, sehingga regresi terdeteksi sebelum merge.

## Future Development

Rencana pengembangan selanjutnya (belum diimplementasikan):

- Notifikasi realtime
- Aktivasi payment gateway produksi (sandbox sudah terpasang; mekanisme webhook & verifikasi pembayaran tersedia)
- Deployment automation (Docker/CI deploy) — runbook deployment manual sudah terdokumentasi
- Master data untuk tipe kamar dan tipe tagihan
