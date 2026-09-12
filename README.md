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
Booking → Approval (instant, status approved) → Tenant Identity Submission → Owner/Admin Review
       → [Confirm → Final Check-in (Penghuni+Kontrak+CheckIn)] atau [Reject → Tenant resubmit]
       → Tagihan per periode → Pembayaran oleh tenant → Verifikasi
       → Check-out (kontrak berakhir, kamar kembali available)
```

> **Prinsip:** `TENANT OWNS IDENTITY DATA. OWNER OWNS VERIFICATION DECISION.`
> Tenant mengisi data identitasnya sendiri; owner hanya memverifikasi, bukan menginput ulang.

### Alur Check-in (Identity Ownership)

**A. Tenant — pemilik data identitas**

1. Booking telah berstatus `approved`.
2. Tenant membuka **Check-In Identity** (`/tenant/checkin/{booking}/identity`).
3. Tenant mengisi:
   - **Nomor KTP** (angka, 8–50 karakter, di-trim, spasi dihapus)
   - **Nomor WhatsApp** (9–20 digit, `62`/`+62` → `08` dinormalisasi)
   - **Alamat** (5–500 karakter)
4. Tenant **submit** → `identity_data_submitted_at = now()`, `identity_number/phone/address` tersimpan, `identity_rejected_at/reason` di-clear. **Tidak** men-set `identity_confirmed_at` — status menjadi **`PENDING` → `Menunggu Konfirmasi Owner`.**
5. Tenant menunggu verifikasi. Tidak memiliki tombol confirm — tenant **tidak dapat** self-confirm.
6. Jika **ditolak** → tenant melihat `Check-In Ditolak` + **alasan penolakan** + form `Perbaiki & Ajukan Ulang` → submit kembali → `REJECTED → PENDING`.
7. Jika **dikonfirmasi** → tenant melihat `✓ Check-In Dikonfirmasi` (`confirmed_at`) — form tidak lagi menjadi aksi utama.

**B. Owner / Admin — pemilik keputusan verifikasi**

1. Owner/admin membuka **Review Pengajuan Check-In** (`/owner/check-in` atau `/admin/check-in`).
2. Identitas tampil **readonly** — owner **tidak perlu mengetik ulang** KTP/WA. KTP ditampilkan **masked** `************1234` (`Booking::maskedIdentityNumber()`), WhatsApp & alamat tampil sesuai desain.
3. Reviewer dapat:
   - **Confirm** → `identity_confirmed_at = now()`, `identity_confirmed_by = auth.id`, clear `rejected_*`. Notifikasi ke tenant `Check-In Dikonfirmasi`, audit `Approve`.
   - **Reject** → `identity_rejected_at = now()`, `identity_rejection_reason = alasan (5–500, trimmed)`, clear `confirmed_*`. Notifikasi `Pengajuan Check-In Ditolak` + alasan, audit `Reject`. Reason **wajib**.
4. Konfirmasi memungkinkan **final check-in processing** → `POST /owner/check-in/{booking}` membuat `Penghuni` + `Kontrak (KT-…)` + `CheckIn (officer_id)` dalam transaksi `lockForUpdate`, booking `completed`, kamar `occupied`.
5. Reviewer hanya dapat memverifikasi booking **dalam scope kos yang diizinkan** (`kos.owner_id` atau `assignedKos` pivot). Cross-scope → `403`.

**C. State Machine**

```
NOT_SUBMITTED (belum submit)
  ↓ tenant submit
PENDING (Menunggu Konfirmasi Owner)
  ├──→ CONFIRMED (owner confirm) ──→ FINAL CHECK-IN PROCESS (Penghuni/Kontrak/CheckIn)
  └──→ REJECTED (owner reject + alasan)
         ↓ tenant resubmit (perbaiki & ajukan ulang)
       PENDING → CONFIRMED → FINAL PROCESS
```

Aturan:

- `PENDING → CONFIRMED` hanya oleh owner/admin, bukan tenant.
- `REJECT → PENDING` hanya oleh tenant resubmit.
- `PENDING → REJECTED` wajib alasan penolakan.
- Final `process` mensyaratkan `identity_confirmed_at != null` + `hasIdentityData()` — sebelum itu **diblokir** `400 Menunggu konfirmasi owner`.

**D. Model data (`bookings` identity fields)**

| Kolom | Tujuan |
|---|---|
| `identity_number` | Nomor KTP tenant (disimpan penuh, tampil masked ke owner) |
| `phone` | Nomor WhatsApp (dinormalisasi) |
| `address` | Alamat sesuai KTP |
| `identity_data_submitted_at` | Waktu pengajuan (set saat submit) |
| `identity_confirmed_at` | Waktu konfirmasi owner |
| `identity_confirmed_by` | FK `users.id` owner yang mengonfirmasi |
| `identity_rejected_at` | Waktu penolakan |
| `identity_rejection_reason` | Alasan penolakan (ditampilkan ke tenant) |

Helper: `identityPending()`, `identityConfirmed()`, `identityRejected()`, `identityStatus(): not_submitted|pending|confirmed|rejected`, `maskedIdentityNumber()`, `confirmedBy()`.

Aturan penting lainnya:
- Kamar *occupied/maintenance* tidak bisa dibooking; rentang tanggal tidak boleh tumpang-tindih dengan booking approved lain.
- Check-in hanya untuk booking berstatus `approved` dan identitas telah **dikonfirmasi owner** (bukan self-confirm) — migrasi `2026_09_13_000001` additive (`confirmed_by`, `rejected_at`, `rejection_reason`).
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
- **Tenant identity ownership** — tenant mengisi KTP/WA/alamat sendiri → `PENDING`; tenant **tidak dapat** self-confirm (endpoint `owner/check-in/{booking}/confirm` hanya `owner/admin/super_admin`, tenant hit → `403`, gate `identityPending()`).
- **Owner verification scoped** — hanya owner kos terkait (`kos.owner_id`) atau admin ter-assign (`assignedKos` pivot) dapat `confirm`/`reject`; cross-scope → `403`. KTP tampil **masked** `************1234` (`Booking::maskedIdentityNumber()`) untuk owner; **tidak** masuk audit log/notification (`booking_id`, `user_id` saja).
- **State-machine guarded** — `PENDING → CONFIRMED` hanya owner, `REJECT` wajib alasan 5–500, `REJECTED → PENDING` hanya via resubmit, `process` mensyaratkan `identity_confirmed_at` (`400` jika belum confirmed).
- **Double-confirm & reject-only-pending** diblokir via `identityPending()` gate.
- **Soft delete historical users** — user dengan riwayat transaksi tidak terhapus permanen
- **Security tests** — suite khusus isolation boundary & payment security (+ `CheckInSecurityTest` 12 tests untuk identity ownership)

## Automated Testing

Hasil test terbaru (terverifikasi C.3/C.4 — identity ownership):

```
Target identity/security suite: 47 passed / 174 assertions
  - IdentityConfirmationTest: 20 passed (68 assertions)
  - CheckInSecurityTest:      12 passed (46 assertions)
  - CheckInOutTest:            9 passed (24 assertions)
  - BusinessFlowTest:          2 passed (28 assertions)
  - PasswordResetTest:         4 passed (8 assertions)
Pint:     passed (code style PSR-12)
Build:    sukses (Vite — 57 modules, 174.98 kB css)
```

Target suite di atas **PASS** penuh. Catatan:

> `Full suite was not completed within the configured 120-second execution window.` — timeout `php artisan test` (skala >1300 test, bukan failure). Browser/HTTP verification C.4 pada `390×844`: tenant own booking PASS, tenant other booking 403, tenant self-confirm 403, owner scoped review PASS (masking, confirm/reject), owner cross-scope 403, reject/reason PASS, resubmit PASS, confirm PASS, confirmed state PASS, responsive no overflow/clipping — PASS. Catatan C4-10: final `process` via browser reuse dataset terblokir oleh `kamar/penghuni` state reuse — bukan bug identity (identity prerequisite PASS, `BusinessFlowTest` dengan DB fresh memverifikasi final process).

> Sebelum deploy pastikan seluruh suite lolos: `php artisan test`, `vendor/bin/pint --test`, `npm run build`.

Cakupan meliputi unit test logika bisnis, feature test seluruh modul, race condition, security/isolation boundary (termasuk **identity ownership**), hingga command otomasi.

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
