# DOKUMENTASI LENGKAP & AUDIT — KOSMANAGER

> **Jenis dokumen:** Dokumentasi teknis + audit fitur-fungsional (read-only audit).
> **Tanggal:** 8 September 2026
> **Metode:** Inspeksi kode (routes, controllers, services, models, policies, middleware, requests, migrations, seeders, views, tests). Verifikasi dilakukan per beban bukti langsung dari file & nomor baris.
> **Konvensi label temuan:**
> - `[TERSEDIA]` — fitur ada dan berfungsi sesuai kode.
> - `[NOT IMPLEMENTED]` — fitur diklaim/dijanjikan UI/narasi, tapi tidak ada implementasi di backend.
> - `[PARTIALLY IMPLEMENTED]` — sebagian alur ada, sebagian tidak/belum lengkap.
> - `[UI/BACKEND MISMATCH]` — tampilan yang ditawarkan berbeda dari kapabilitas backend.

---

## RINGKASAN EKSEKUTIF

KosManager adalah aplikasi web manajemen indekos berbasis **Laravel 10 (PHP 8.1)** dengan
komponen frontend **Blade + Tailwind CSS 3 + Alpine.js + Flowbite**, dilengkapi **API V1 (Sanctum)**,
mesin **booking instant-approve yang aman terhadap race-condition**, siklus hidup
**kontrak → tagihan → pembayaran → verifikasi**, **modul advertising multi-peran**, **audit log
berrantai HMAC**, dan **suite pengujian ±1133 test / ±3875 assertion** (97 file).

Arsitektur memakai **4 peran** (`super_admin`, `admin`, `owner`, `tenant`) yang diberlakukan
melalui middleware `role:...` + `active`; **admin dibatasi pada kos yang ditugaskan** (`kos_user`).
Dokumentasi ini memetakan seluruh area tersebut dan menandai temuan **yang belum diimplementasikan
atau parsial** (Lapor Kerusakan belum ada di backend; kategori fasilitas belum dapat diisi dari UI;
verifikasi chain audit log tidak ditampilkan di UI; payment gateway masih simulasi sandbox;
registrasi tidak menangkap kontak dan tidak ada alur penugasan peran lanjutan).

Status proyek: **fitur inti lengkap & teruji**, layak untuk produksi dengan catatan (Bagian 25).

---

## DAFTAR ISI (26 BAGIAN + RINGKASAN)

1. [Ringkasan Proyek & Tujuan](#1-ringkasan-proyek--tujuan)
2. [Tech Stack & Environment](#2-tech-stack--environment)
3. [Struktur Repo](#3-struktur-repo)
4. [Arsitektur & Alur Utama](#4-arsitektur--alur-utama)
5. [Peran & Izin](#5-peran--izin)
6. [Marketplace & Katalog Kos](#6-marketplace--katalog-kos)
7. [Booking & Pemilihan Kamar](#7-booking--pemilihan-kamar)
8. [Pembayaran & Verifikasi](#8-pembayaran--verifikasi)
9. [Kontrak & Check-in](#9-kontrak--check-in)
10. [Tagihan & Monitoring Sisa Sewa](#10-tagihan--monitoring-sisa-sewa)
11. [Check-out](#11-check-out)
12. [Lapor Kerusakan](#12-lapor-kerusakan)
13. [Laporan Keuangan & Statistik](#13-laporan-keuangan--statistik)
14. [Fasilitas & Ikon](#14-fasilitas--ikon)
15. [Keamanan & Sertifikat](#15-keamanan--sertifikat)
16. [Audit Log & Integritas](#16-audit-log--integritas)
17. [Basis Data & Skema](#17-basis-data--skema)
18. [Endpoint/Route API](#18-endpointroute-api)
19. [Controllers & Services](#19-controllers--services)
20. [Business Rules](#20-business-rules)
21. [Skema Alur E2E](#21-skema-alur-e2e)
22. [Feature Matrix (per Peran)](#22-feature-matrix-per-peran)
23. [Uji (Testing)](#23-uji-testing)
24. [Masalah Ditemukan & Inkonsistensi](#24-masalah-ditemukan--inkonsistensi)
25. [Status Proyek & Rekomendasi](#25-status-proyek--rekomendasi)
26. [Lampiran](#26-lampiran)

---

## 1. Ringkasan Proyek & Tujuan

Aplikasi manajemen kos menyeluruh dengan dua sisi:

- **Penyewa (tenant):** melihat katalog kos, memesan kamar (booking), melakukan check-in,
  memantau kontrak & tagihan, membayar tagihan dengan unggah bukti, dan mengajukan check-out.
- **Pengelola (owner/super admin/admin):** mengelola kos & kamar, memantau booking (instant approved),
  verifikasi pembayaran, check-in/check-out penghuni, membuat tagihan, menyusun laporan (PDF/CSV), dan
  mengelola bisnis **advertising** (partner, paket, kampanye, analitik).

Tujuan utama sesuai kode: memberikan marketplace + operasional bantu yang **transparan**
(instant booking, bukti bayar, audit trail), **integritas data** (transaction & row locking,
HMAC audit chain, hash kolom billing), dan **jalur monetisasi** (advertising CPM/CPC/deal/contract).

## 2. Tech Stack & Environment

### Backend
- **PHP `^8.1`** — `composer.json:8`
- **Laravel `^10.0`** (terpasang 10.50.x) — `composer.json:11`
- **barryvdh/laravel-dompdf `^3.1`** — ekspor PDF laporan — `composer.json:9`
- **maatwebsite/excel `^1.1`** — (terdaftar; ekspor Excel aktual memakai CSV `fputcsv`) — `composer.json:14`
- **guzzlehttp/guzzle `^7.2`** — `composer.json:10`
- **laravel/sanctum `^3.2`** — API token — `composer.json:12`
- **laravel/tinker `^2.8`** — `composer.json:13`
- Dev: laravel/breeze ^1.29, pint ^1.0, sail, mockery, collision ^7, phpunit ^10, spatie/laravel-ignition ^2

### Frontend (`package.json`)
- **tailwindcss `^3.1.0`** (package.json:15), **alpinejs `^3.4.2`** (:9), **flowbite `^4.0.2`** (:19),
  **vite `^4.0.0`** (:16), axios ^1.1.2, laravel-vite-plugin ^0.7.2, **puppeteer-core `^25.10.0`** (:14 — QA E2E).

### Konfigurasi inti
- Timezone `Asia/Jakarta`, locale `id` — `config/app.php`
- Database default MySQL `127.0.0.1:3306` — `config/database.php` (`DB_CONNECTION=mysql`)
- Tests memakai **SQLite `:memory:`** — `phpunit.xml`
- Filesystem: **default disk `local`** — `config/filesystems.php:16`; disk `public` rooted di
  `storage/app/public` (:39–43); symlink `public/storage` (:79)
- **207 route terdaftar** (web + api + auth + console helpers)

## 3. Struktur Repo

```
app/
  Console/Commands/        # scheduler: ExpireOldBookings, ExpireOldKontraks, MarkOverdueTagihans,
                           # RemindDueTagihans, CleanupNotifications, BackupDatabase, RestoreDatabaseBackup,
                           # VerifyDatabaseBackup, ListDatabaseBackups, AdvertisingProcessCampaigns
  Exceptions/              # Handler
  Http/Controllers/
    root: DashboardController, NotificationController, ProfileController, WelcomeController,
          HealthController, AdvertisingClickController
    Admin/                 # AdvertisingController (moderasi admin)
    Api/V1/                # BillingController, BookingController, (+resources)
    Auth/                  # Breeze scaffold (register/login/password/verifikasi)
    Owner/                 # KosController, KamarController, BookingController, CheckInController,
                           # CheckOutController, KontrakController, PenghuniController, TagihanController,
                           # PembayaranController, FavoriteController, Tenant*Controller (booking/kos/
                           # pembayaran/kontrak untuk sisi tenant), AdvertisingController
    SuperAdmin/            # UserController, FasilitasController, LaporanController, AuditLogController,
                           # PartnerController, AdvertisingPackageController, AdvertisingCampaignController,
                           # AdvertisingDashboardController
    Webhook/               # PaymentGatewayWebhookController
  Http/Middleware/         # RoleMiddleware, IsActive, dll
  Http/Policies/           # (policy per model booking/kos/kamar/dll)
  Http/Requests/           # validation (StorePembayaranRequest, StoreCheckInRequest, StoreKamarRequest, dll)
  Models/                  # User, Kos, Kamar, Fasilitas, Booking, Penghuni, CheckIn, CheckOut, Kontrak,
                           # Tagihan, Pembayaran, AuditLog, Notification, Favorite, Partner, Advertising*
  Services/                # BookingService, PaymentService, AuditLogService, NotificationService,
                           # AdvertisingService, AdvertisingAnalytics
  Traits/HasRole.php
database/
  factories/  migrations/ (~51 file, era 2024 + batch perbaikan 2026)  seeders/
routes/       web.php, api.php, auth.php, console.php
resources/views/   layouts/, components/, dashboard/{super-admin,admin,owner,tenant}.blade.php,
                   welcome/, auth/, owner/, super-admin/, tenant/, exports/pdf/
tests/        Unit/ (5), Feature/ (92)
docs/         ERD.md, DOKUMENTASI.md, laporan fase P4–P8, NASKAH_PRESENTASI.md
```

Catatan arsitektur penting: **tidak ada subfolder `Controllers/Tenant/`** — routing tenant
mengarah ke controller milik `Owner\` (mis. `TenantKosController`, `TagihanController`,
`PembayaranController`) yang melakukan scoping berdasar peran user di dalam method
(contoh: `CheckInController::index` `isOwner()`/`isAdmin()` — `app/Http/Controllers/Owner/CheckInController.php:30-37`).

## 4. Arsitektur & Alur Utama

- **MVC klasik Laravel**, routing terpusat di `routes/web.php` (242 baris) +
  `routes/api.php` (V1) — bukan SPA; interaktivitas memakai Alpine di sisi Blade.
- **Alur utama bisnis** (bab 21) menghubungkan: Marketplace → Booking → Check-in → Kontrak →
  Tagihan → Pembayaran → Verifikasi → Check-out, plus jalur Advertising.
- **Pengaman transaksi:** operasi kritis dibungkus `DB::transaction()` + `lockForUpdate()`
  (BookingService.php:24-25 & 96-97; CheckInController.php:53-54).
- **Scheduler:** beberapa command CLI untuk sweeps (booking/kontrak kadaluwarsa, tagihan overdue,
  pengingat, backup DB, proses kampanye iklan) — `app/Console/Commands/*` (jadwal di
  `App\Console\Kernel::schedule`).
- **Webhook payment gateway** publik `POST /webhooks/payment-gateway` dengan `throttle:20,1`
  (routes/web.php:45-47) dan verifikasi tanda tangan HMAC **fail-closed**.

## 5. Peran & Izin

- Kolom `users.role` bertipe enum `['super_admin','admin','owner','tenant']` **default `'tenant'`**
  — `database/migrations/2014_10_12_000000_create_users_table.php:21`.
- Cek peran via trait `HasRole` (`isSuperAdmin/isAdmin/isOwner/isTenant/hasRole`) —
  `app/Traits/HasRole.php:7-30`.
- **Middleware** `role:...` memeriksa `in_array(auth()->user()->role, $roles)` dan menyisipkan
  penjagaan `IsActive::guardActive()` — `app/Http/Middleware/RoleMiddleware.php:21-23`.
- Route groups (routes/web.php, sudah diverifikasi):
  - `super_admin`: grup `prefix('super-admin')` + `role:super_admin` (:68)
  - `owner`: grup `prefix('owner')` + `role:super_admin,owner` (:111)
  - `admin`: grup `prefix('admin')` + `role:admin` (:159)
  - `tenant`: grup `prefix('tenant')` + `role:tenant` (:210)
  - Umum login: `auth` + `active` (profile/notifications/proof) — :49–65; dashboard `auth+active+verified` — :41-43.
- **Scoping data admin:** `User::assignedKos()` (pivot `kos_user`) — `app/Models/User.php:58-61`;
  `canAccessKos()` memutuskan akses owner (via `ownedKos`) / admin (via `assignedKos`) / super admin (selalu) — :63-78.
- Kontrol data laporan juga disaring `accessibleKosIds()` dengan `abort(403)` — `app/Http/Controllers/SuperAdmin/LaporanController.php:98-120`.

### Matriks akses ringkas (detail di Bagian 22)
| Area | Super Admin | Admin | Owner | Tenant |
|---|---|---|---|---|
| Marketplace/Katalog | – | – | – | ✔ |
| Kelola Kos & Kamar | ✔ (via owner prefix) | Lihat saja (`admin.kamar`) | ✔ | – |
| Booking (lihat/check-in) | ✔ (via owner prefix) | ✔ | ✔ | Buat/batalkan (instant approved) |
| Check-in / Check-out | ✔ (via owner prefix) | ✔ | ✔ | Ajukan check-out |
| Tagihan (buat/lihat) | ✔ (via owner prefix) | ✔ (termasuk create) | ✔ | Lihat + bayar |
| Pembayaran (verifikasi) | ✔ (via owner prefix) | ✔ | ✔ | Buat bukti bayar |
| Laporan PDF/CSV | ✔ (semua kos) | ✔ (kos ditugaskan) | ✔ (kos milik) | – |
| Advertising (kelola) | ✔ (paket/kampanye/partner/revenue) | ✔ (moderasi) | ✔ (kampanye owner) | – |
| User & Fasilitas global | ✔ | – | – | – |

## 6. Marketplace & Katalog Kos

- **Landing**: `GET /` → `WelcomeController` → `welcome.blade.php` menampilkan daftar kos aktif
  (foto via `asset('storage/'.$kos->photo)` bila file ada — welcome.blade.php:310-311).
- **Katalog tenant**: `tenant.kos` index/show (`TenantKosController`) — `resources/views/tenant/kos/show.blade.php`
  merinci foto, fasilitas (`sk`), harga kamar; partial `tenant/partials/kos-card.blade.php` dan
  **`promoted-kos.blade.php`** (slot iklan) — :19-21.
- **Favorit**: toggle `tenant.favorites` (`FavoriteController::toggle`/`index`) —
  routes/web.php:226-227; relasi `User::favorites` + `hasFavorited()` — User.php:100-123.
- **Iklan di marketplace**: klik iklan `ad/click/{campaign}` mencatat event
  (tabel `advertising_events`, atribusi via session `ad_click`) lalu redirect ke detail kos —
  routes/web.php:214-217 → `AdvertisingClickController`.
- **Peta/lokasi**: kolom `kos.location` (migrasi `add_location_to_kos`) — koordinat vs teks tergantung data; UI menampilkan lokasi dalam kartu kos.

`[TERSEDIA]` marketplace inti + promosi ter-upgrade.

## 7. Booking & Pemilihan Kamar

- **Pembuatan booking (tenant):** `TenantBookingController::store` → `BookingService::create`
  (`app/Services/BookingService.php:22-83`):
  1. `DB::transaction` + `Kamar::lockForUpdate()` (:24-25);
  2. kamar harus `status === 'available'` dan kos `status === 'active'` → error `kamar_tidak_tersedia` (:27-29);
  3. cek konflik: booking `approved` lain yang periodenya overlap → error `overlap` (:31-39);
  4. harga diambil dari `daily_price`/`monthly_price` **kamar** (:41), error `no_price` bila kosong;
  5. status di-set **`approved` (instant booking)** (:57) — konsisten migrasi
     `change_default_booking_status_to_approved` (default MySQL `'approved'` — :11);
  6. kamar → `booked` (:61); notifikasi (otomatis ke tenant & owner, `NotificationService`),
     audit log, dan atribusi **conversion advertising** bila datang dari klik iklan (:70-76).
- **Status booking**: `pending|approved|rejected|cancelled|completed|expired`
  (enum di `bookings` — migrasi `2026_08_21_000001` :29; konstanta `Booking.php:14-24`).
  Booking instan approved; `cancellableStatuses = pending|approved` (Booking.php:81-84);
  terminal = cancelled|rejected|expired (:107-110); `scopeNeedsCheckin` = approved tanpa penghuni aktif (:76-79).
- **Pembatalan:** `BookingService::cancel` — transaction + lock booking; hanya status cancellable;
  kamar kembali `available` bila tak ada booking approved lain dengan `end_date >= hari ini` (:94-121).
  Tenant dapat membatalkan (`tenant.booking.cancel` — routes/web.php:224).
- **Sisi pengelola:** pengelola melihat daftar booking dan memproses check-in booking yang sudah
  terkonfirmasi (`approved`) — owner & admin memakai `owner.booking.index`/`admin.booking.index`
  (routes/web.php:124-125, 172-173) dan mengarah ke check-in. Workflow approve/reject manual booking
  telah dihapus (instant booking).
- **Kadaluwarsa:** command `ExpireOldBookings` memindahkan booking approved tanpa check-in ke `expired`.
- **Overlap prevention** diuji (Bagian 23).

`[TERSEDIA]` — flow booking lengkap, termasuk proteksi race & overlap.

## 8. Pembayaran & Verifikasi

### Alur
- Tenant membayar tagihan via `tenant.pembayaran.store` (`TenantPembayaranController`, routes/web.php:239)
  → `PaymentService` (app/Services/PaymentService.php) memproses [penghuni, tagihan, amount, payment_method, proof_file?].
- **Bukti bayar opsional** validasi `mimes:jpg,jpeg,png,pdf|max:5120` — `app/Http/Requests/StorePembayaranRequest.php:20`.
- **Fungsi verifikasi:** `Owner\PembayaranController::verify/reject` (owner & admin) —
  routes/web.php:142-143, 193-194; verifikasi meng-update tagihan → `paid`, pembayaran `approved`,
  dan memulihkan status tagihan yang sempat `overdue` kembali ke status semula (restore logic —
  `app/Http/Controllers/Owner/PembayaranController.php:132-135`).
- **Status pembayaran:** `pending|approved|rejected` (default pending) — migrasi pembayarans :23;
  **status tagihan:** `unpaid|pending_verification|paid|overdue|cancelled` — migrasi tagihans :28.
- **Gateway (simulasi):** kolom `gateway` + `PaymentGateway` (sandbox/simulasi di `app/Services`) dan webhook
  `PaymentGatewayWebhookController::handle` (HMAC; tercakup pengujian fail-closed). `[PARTIALLY IMPLEMENTED]`
  — belum integrasi PSP sungguhan (midtrans/dll).
- **Integritas:**
  - `pembayarans.*` & `tagihans.*` + kolom integrity (migrasi `2026_09_02_000001`/`...000002 add_*_integrity`);
  - API `PembayaranResource` **tidak mengekspos path filesystem** — hanya `proof_available` boolean
    (`app/Http/Resources/PembayaranResource.php:13,31`);
  - proof disimpan ke disk **`local`** (`->store('bukti-pembayaran')` — BillingController.php:75,
    TenantPembayaranController.php:35) dan **tidak pernah via `asset('storage/...')`**; migrasi
    `2026_08_22_000001_move_payment_proofs_to_private_disk` memindahkan file legacy dari `public` ke `local`.
- **Serve proof:** route `pembayaran.proof` + middleware `auth,active`
  (`Owner\PembayaranController::downloadProof` — `Storage::exists` → `Storage::download`; :69-71);
  ditampilkan sebagai `<img>`/`<iframe>` di `owner/pembayaran/show.blade.php:71-76` dan `tenant/pembayaran/show.blade.php:123-133`.

`[TERSEDIA]` end-to-end; catatan: gateway masih sandbox/simulasi.

## 9. Kontrak & Check-in — Tenant Owns Identity, Owner Owns Verification

> **Prinsip produk:** `TENANT OWNS IDENTITY DATA. OWNER OWNS VERIFICATION DECISION.`
> Tenant mengisi data identitasnya sendiri; owner/admin hanya melakukan verifikasi (confirm/reject), bukan menginput ulang data tenant. Tenant **tidak dapat** melakukan self-confirm.

### A. Tenant — pemilik data identitas

1. Booking telah berstatus `approved` (instant booking).
2. Tenant membuka **Pengajuan Check-In** (`GET /tenant/checkin/{booking}/identity` → `Tenant\TenantCheckInController@show`).
3. Tenant mengisi:
   - **Nomor KTP** (`identity_number`) — angka 8–50 karakter, di-trim, spasi dihapus, regex `^[0-9 ]+$`
   - **Nomor WhatsApp** (`phone`) — 9–20 digit, `62`/`+62` dinormalisasi → `08`, regex `^[0-9+\-\s\(\)]+$`
   - **Alamat** (`address`) — 5–500 karakter
4. Tenant **submit** (`POST /tenant/checkin/{booking}/identity` → `Tenant\TenantCheckInController@submit`) → `identity_data_submitted_at = now()`, `identity_number/phone/address` tersimpan, `identity_rejected_at/reason` di-clear. **Tidak** men-set `identity_confirmed_at/by` — status menjadi **`PENDING` (`Menunggu Konfirmasi Owner` link: https://github.com/).**
5. Tenant menunggu verifikasi. Tidak ada tombol confirm — **tenant tidak dapat self-confirm** (endpoint `owner/check-in/{booking}/confirm` hanya untuk role `owner/admin/super_admin`, tenant hit → `403`).
6. Jika **ditolak** → tenant melihat `Check-In Ditolak` + **alasan penolakan** (`identity_rejection_reason`) + form `Perbaiki & Ajukan Ulang` → submit kembali → `REJECTED → PENDING` (`rejected_at/reason` di-clear, `submitted_at` baru, tetap `confirmed = null`).
7. Jika **dikonfirmasi** → tenant melihat `✓ Check-In Dikonfirmasi` (`identity_confirmed_at` + `confirmed_by`), form tidak lagi menjadi aksi utama. Data KTP milik tenant sendiri ditampilkan penuh (miliknya), owner melihat masking.

### B. Owner / Admin — pemilik keputusan verifikasi

1. Owner/admin membuka **Review Pengajuan Check-In** (`GET /owner/check-in` atau `/admin/check-in` → `Owner\CheckInController@index`, scope `kos.owner_id` atau `assignedKos` pivot).
2. Identitas tampil **readonly** — owner **tidak perlu mengetik ulang** KTP/WA. KTP ditampilkan **masked** `************1234` (`Booking::maskedIdentityNumber()`), WhatsApp & alamat tampil sesuai desain. Tidak ada `<input name="identity_number">` untuk editing.
3. Reviewer dapat:
   - **Confirm** (`POST /owner/check-in/{booking}/confirm` → `confirmIdentity`): guard `view` + role `owner/admin/super_admin` + `status approved` + `identityPending()` + `hasIdentityData()` + **strict scope** (`kos.owner_id` / `canAccessKos`); lalu `identity_confirmed_at = now()`, `identity_confirmed_by = auth.id`, clear `rejected_*`; notifikasi tenant `Check-In Dikonfirmasi`, audit `Approve` (`booking_id`, `user_id` tanpa KTP plaintext).
   - **Reject** (`POST /owner/check-in/{booking}/reject` → `rejectIdentity`): guard sama + `reason required 5–500 trimmed`; lalu `identity_rejected_at = now()`, `identity_rejection_reason`, clear `confirmed_*`; notifikasi `Pengajuan Check-In Ditolak` + alasan, audit `Reject`. Reason **wajib**, empty → validasi gagal.
4. Konfirmasi memungkinkan **final check-in processing** (`POST /owner/check-in/{booking}` → `process`): guard `view` + `status approved` + `identity_confirmed_at != null` + `hasIdentityData()`; transaksi `lockForUpdate`; buat `Penghuni (active, identity_number/phone/address dari booking)` + `Kontrak (KT-…, billing_mode snapshot)` + `CheckIn (officer_id)`; booking `completed`, kamar `occupied`. Sebelum confirmed → **diblokir** `400 Menunggu konfirmasi owner`.
5. Reviewer hanya dapat memverifikasi booking **dalam scope yang diizinkan** — cross-scope `Owner A → Booking Kos B` → `403` (uji `CheckInSecurityTest t4`).

### C. State Machine

```
NOT_SUBMITTED (belum submit, belum ada submitted_at)
  ↓ tenant submit (KTP/WA/Alamat)
PENDING (Menunggu Konfirmasi Owner, submitted_at != null, confirmed = null, rejected = null)
  ├──→ CONFIRMED (owner confirm, confirmed_at = now, confirmed_by, rejected cleared)
  │      ↓ final process → Penghuni/Kontrak/CheckIn
  └──→ REJECTED (owner reject + alasan 5–500, rejected_at = now, confirmed cleared)
         ↓ tenant resubmit (perbaiki & ajukan ulang → submitted_at baru, rejected cleared)
       PENDING → CONFIRMED → FINAL PROCESS
```

Aturan:

- `PENDING → CONFIRMED` hanya oleh owner/admin — tenant **tidak dapat** langsung `PENDING → CONFIRMED`.
- `PENDING → REJECTED` wajib alasan.
- `REJECTED → PENDING` hanya oleh tenant resubmit.
- Final `process` mensyaratkan `identity_confirmed_at != null` + `hasIdentityData()`.

Helper `Booking::identityStatus()` → `not_submitted | pending | confirmed | rejected` (precedence `confirmed > rejected > pending`). `identityPending()` = `submitted_at != null && confirmed == null && rejected == null`.

### D. Model data (`bookings` identity fields)

| Kolom | Tujuan |
|---|---|
| `identity_number` | Nomor KTP (disimpan penuh, tampil masked `************1234` ke owner) |
| `phone` | Nomor WhatsApp (dinormalisasi `62/+62 → 08`) |
| `address` | Alamat sesuai KTP |
| `identity_data_submitted_at` | Waktu pengajuan (set saat tenant submit) |
| `identity_confirmed_at` | Waktu konfirmasi owner |
| `identity_confirmed_by` | FK `users.id` owner yang mengonfirmasi (`nullOnDelete`, nullable) |
| `identity_rejected_at` | Waktu penolakan |
| `identity_rejection_reason` | Alasan penolakan (ditampilkan ke tenant) |

Migrasi: existing `2026_09_10_000003` (`confirmed_at`) + `2026_09_11_000001` (`identity_number/phone/address/submitted_at`) tetap; baru `2026_09_13_000001` additive (`confirmed_by`, `rejected_at`, `rejection_reason`) — tidak drop/rename, tidak tabel baru.

### E. Keamanan & masking

- KTP **masked** di owner view (`maskedIdentityNumber()`), tidak masuk audit log/notification (`booking_id`, `user_id` saja), `Log::warning` generik.
- Tenant hanya akses booking miliknya (`BookingPolicy@view: booking.user_id == user.id`); owner cross-scope → `403`.
- Mass-assignment aman: tenant `submit` pakai `validate()` whitelist explicit, `update([6 fields])` — bukan `$request->all()`.
- Double-confirm & reject-only-pending diblokir via `identityPending()` gate (`400`).

- **Kontrak tenant:** `tenant.kontrak` index/show (`TenantKontrakController`) — routes/web.php:244-245; **owner/admin**: `owner.kontrak` / `admin.kontrak` (:134-135, 190-191).
- **Kadaluwarsa kontrak:** command `ExpireOldKontraks` (status aktif → berakhir).

`[TERSEDIA]` — alur identity ownership lengkap, C.3/C.4 terverifikasi.

### F. Verifikasi C.4 (ringkas, detail lihat laporan C.4)

Target suite: **47 passed / 174 assertions** (`IdentityConfirmationTest 20`, `CheckInSecurityTest 12`, `CheckInOutTest 9`, `BusinessFlowTest 2`, `PasswordResetTest 4`). `npm run build` PASS, `pint --test` PASS (C.3). Full suite tidak diselesaikan dalam window 120 detik (timeout, bukan failure). Browser/HTTP C.4 pada `390×844`: tenant own PASS, tenant other 403, tenant self-confirm 403, owner scoped review PASS (masking, confirm/reject), owner cross-scope 403, reject/resubmit/confirm semua PASS, responsive no overflow/clipping. Catatan C4-10: final `process` via browser reuse dataset terblokir oleh `kamar/penghuni` state reuse — bukan bug identity (identity prerequisite PASS, `BusinessFlowTest` dengan DB fresh memverifikasi final process).

## 10. Tagihan & Monitoring Sisa Sewa

- **Pembuatan tagihan:** `owner.tagihan.create/store` & `admin.tagihan.create/store`
  (`Owner\TagihanController`) — routes/web.php:135-138, 186-189.
  Skema: `bill_number` unik, `bill_type`, `period_start/end`, `subtotal`, `discount`, `penalty`, `total`,
  `due_date`, status — migrasi `2024_01_01_000011_create_tagihans_table.php:14-31`.
- **Monitoring tenant:** dashboard tenant menampilkan **sisa sewa** & tagihan terdekat
  (stat dari DashboardController; `tenant.blade.php` FAQ + kartu tagihan). Summary & KPI di
  dashboard owner/admin (pending payments, occupancy, dst).
- **Sweeps:** `MarkOverdueTagihans` (unpaid & lewat due → `overdue`), `RemindDueTagihans` (notifikasi),
  `CleanupNotifications`. Webhook/API billing V1 (`Api\V1\BillingController`).

`[TERSEDIA]`.

## 11. Check-out

- `Owner\CheckOutController` (routes/web.php:148-151, 199-202): listing, **request**
  (admin/owner atas nama penghuni) dan tenant sendiri (`tenant.checkout.request` :229),
  **approve/reject** oleh pengelola.
- Alur selesai di lini operasional mengembalikan kamar ke `available` (menutup kontrak & penghuni aktif).

`[TERSEDIA]`; detail transisi kamar pada check-out mengikuti implementasi controller (audit lengkap di Bagian 20).

## 12. Lapor Kerusakan

`[NOT IMPLEMENTED]`

- **Diklaim di UI:** FAQ dashboard tenant — "Pilih menu 'Lapor Kerusakan' ... pengelola akan segera
  meninjau" (`resources/views/dashboard/tenant.blade.php:796`); panduan di `tenant/kontrak/index.blade.php:11`;
  kartu/quick-action di tenant dashboard mengarah ke anchor `#bantuan` (`tenant.blade.php`).
- **Faktual:** **tidak ada route, controller, model, migrasi, ataupun view khusus** untuk laporan
  kerusakan. Pencarian ekstensif pada `routes/*`, `app/Http/Controllers/*`, `app/Models/*`,
  `database/migrations/*` tidak menemukan entitas "laporan kerusakan/maintenance-request/report-damage".
- Link menu = anchor #bantuan (bantuan/hubungi pengelola), bukan alur kerja.

**Status: promise UI tanpa backend.**

## 13. Laporan Keuangan & Statistik

- **Controller tunggal lintas peran:** `SuperAdmin\LaporanController` dipakai di tiga grup
  route (super-admin:72-74; owner:153-155; admin:204-206) dengan scoping per-role:
  - `index`: super admin → semua kos; admin → `assignedKos`; owner → `ownedKos` — `LaporanController.php:19-22`;
  - proteksi `kos_id` param: `abort(403, 'Anda tidak memiliki akses ke kos ini.')` bila di luar scope — :100-102;
  - `accessibleKosIds()` null = semua kos (hanya super admin) — :118+.
- **Jenis laporan** (`match $type` — :104-111): `pendapatan`, `penghuni`, `booking`, `kamar`, `tagihan`.
- **Ekspor:**
  - **PDF**: `Barryvdh\DomPDF\Facade\Pdf::loadView('exports.pdf.'.$type)` — :36-44;
  - **Excel/CSV**: stream CSV dengan **BOM UTF-8** (`chr(0xEF)...`) + header summary —
    `exportExcel` :47-91 (menggunakan `fputcsv`, bukan library maatwebsite — meski paket terpasang).
- **Statistik dashboard:** `DashboardController` (root; `::class` single action — routes/web.php:41)
  menghitung KPI per peran (mis. occupancy kamar incl. `booked`, pending payments) —
  lihat `resources/views/dashboard/owner.blade.php`, `super-admin.blade.php`, `admin.blade.php`, `tenant.blade.php`.

`[TERSEDIA]`; catatan: "Ekspor Excel" adalah CSV (BOM UTF-8) yang dapat dibuka Excel.

## 14. Fasilitas & Ikon

- **Master fasilitas (Super Admin):** `SuperAdmin\FasilitasController` resource (routes/web.php:70);
  kolom ikon + `scope` (kos/kamar) + `category` — migrasi `add_scope_to_fasilitas` (2026_08_28),
  `add_category_to_fasilitas` (2026_08_31), `backfill_fasilitas_icons` (2026_08_31).
- **Relasi:** fasilitas ↔ kos via `kos_fasilitas`, kamar via `kamar_fasilitas` (migrasi `..._000004_create_kamar_fasilitas`,
  `..._000002_create_kos_fasilitas`); tampilan kos/kamar menampilkan fasilitas sebagai chips ikon.
- **`[UI/BACKEND MISMATCH] / PARTIALLY IMPLEMENTED`:** kolom `category` tersedia di DB dan model,
  tetapi **form create/edit fasilitas Super Admin tidak mengekspos input kategori**
  (field category tidak tampil pada form; hanya nama + ikon + scope yang diisi).

## 15. Keamanan & Sertifikat

- **Autentikasi**: Breeze (email+password, `Rules\Password::defaults()` —
  `RegisteredUserController.php:34-38`), throttle login, verifikasi email opsional (middleware
  `verified` hanya pada `/dashboard` — routes/web.php:42).
- **Otorisasi**: middleware `role`, `active` (IsActive), Policy per model (`authorize('view', ...)` —
  CheckInController.php:47), scoping kos (User::canAccessKos, LaporanController::accessibleKosIds).
- **Input validation**: form request terpusat (`app/Http/Requests/`); keamanan file (mime+size);
  **PII sanitasi pada API resources** (OwnerBookingResource, OwnerKontrakResource, OwnerTagihanResource,
  PembayaranResource menyatakan tidak mengekspos data sensitif).
- **Integritas & anti-tamper**: audit log HMAC (Bagian 16), kolom integrity billing,
  webhook payment HMAC fail-closed.
- **Privacy**: bukti pembayaran di disk `local` (bukan public), disajikan lewat route ter-autentikasi.
- **`[TERSEDIA]`** untuk lapisan aplikasi. **Catatan `[PARTIALLY IMPLEMENTED]`:** "sertifikat" di sini
  tak ada konfigurasi SSL **in-app** — deployment HTTPS bergantung server (dokumen `docs/PHASE_7_P7B_DEPLOYMENT.md`);
  payment gateway & integrasi pembayaran sungguhan masih simulasi sandbox.

## 16. Audit Log & Integritas

- Migrasi: `create_audit_logs_table` (2024_01_01_000014) + `add_integrity_to_audit_logs` (000015),
  menambah kolom `previous_hash`, `integrity_hash`.
- `AuditLogService` (`app/Services/AuditLogService.php`):
  - `log()` memakai `previous_hash` = hash log terbaru (:17), menyimpan, lalu `computeHash` SHA-256 HMAC
    memakai secret `config('audit.hmac_secret')` (:104-127) mencakup id, created_at, user_id, action,
    module, description, ip, `json_encode(timestamp_data)`, + previous_hash;
  - **fail-closed**: secret wajib 64-hex; bila tidak valid hash = NULL — tak pernah fallback key (:133-137);
  - helper: `login`, `create`, `update`, `delete`, `approve`, `reject` (:36-64);
  - **`verifyChain()`** — memverifikasi seluruh rantai, melaporkan `{total, verified, failed_id, valid}` (:66-97).
- Setiap tindakan penting memanggil audit (booking :73, check-in :105, login :36-39).
- `SuperAdmin\AuditLogController::index` menampilkan log.
- **`[PARTIALLY IMPLEMENTED]`:** hasil `verifyChain()` **tidak tampil di UI** audit-log
  (tidak ada tombol/indikator verifikasi rantai pada view); verifikasi hanya tersedia via kode/panggilan service.

## 17. Basis Data & Skema

Tabel utama (migrasi di `database/migrations/`; ERD referensi: `docs/ERD.md`):

| Tabel | Isi kunci | Migration |
|---|---|---|
| users | role enum default 'tenant', is_active, softDeletes | 2014_10_12_000000 |
| kos | owner_id, nama, alamat, lokasi, payment info, status | 000001 + add_location + add_payment_info |
| kamar | status enum `available/booked/occupied/maintenance` default available, harga | 000002(:26) |
| fasilitas / kos_fasilitas / kamar_fasilitas | master + pivot | 000003/000004/2026_08_28 |
| kos_user | pivot penugasan admin↔kos | 000005 |
| bookings | status enum + expired, start/end, rental_type | 000006 + 2 migrasi 2026_08 |
| penghunis / check_ins / check_outs | data penghuni aktif, riwayat | 000007/000009/000010 |
| kontraks | KT-, rental, status | 000008 |
| tagihans | bill_number, total, penalty, due_date, status enum | 000011 (:28) |
| pembayarans | payment_number, method enum, proof_file, verification_status | 000012 (:21-23) + gateway + integrity |
| audit_logs | + previous_hash/integrity_hash | 000014/000015 |
| notifications | + indeks user-read | 000013 + 2026_09_02 |
| favorites | favorit tenant | 2026_08_27 |
| partners / advertising_* | partner, paket, kampanye, order, event, analytics | 2026_09_0x |
| pekerjaan/queue | jobs, failed_jobs | 000002/2019 |
| auth/api | password_reset_tokens, personal_access_tokens | 2014/2019_12_14 |

Detail enum terkini sudah tercatat di masing-masing bab; ada **indeks komposit status**
(migrasi `2026_09_01_000001_add_composite_status_indexes_table`) untuk query dashboard.

## 18. Endpoint/Route API

- **159+ s/d 207 route total** (web + api + auth + console). API publik internal:
  `routes/api.php` V1 + Sanctum (`personal_access_tokens`, `HasApiTokens` di User.php:15).
- Contoh endpoint V1 (dari controller `Api/V1`): **Billing** (index/pay dengan `proof_file`) dan
  **Booking** (store) — dengan resource terpilah yang menyembunyikan PII/lokasi file
  (OwnerBookingResource, PembayaranResource, dll.).
- Webhook publik: `POST /webhooks/payment-gateway` (throttle 20/1 menit) — routes/web.php:45-47.
- Health: `GET /health`, `GET /up` (HealthController) — :38-39.

`[TERSEDIA]` subset API billing/booking dengan sanitasi; **tidak ada dokumentasi OpenAPI/Swagger**
terdeteksi di repo. `[PARTIALLY IMPLEMENTED]` terkait cakupan API (belum semua fitur web diekspos).

## 19. Controllers & Services

| Komponen | Lokasi | Peran |
|---|---|---|
| DashboardController | root | single-action KPI per role (termasuk `total_kamar_booked`) |
| BookingService | app/Services | transaksi, lock, overlap, instant-approved, cancel |
| PaymentService | app/Services | proses pembayaran + opsional gateway |
| AuditLogService | app/Services | rantai HMAC |
| AdvertisingService / AdvertisingAnalytics | app/Services | tracking, atribusi session, konsolidasi revenue |
| NotificationService | app/Services | notifikasi DB (login, booking, checkin, kontrak, tagihan, dst.) |
| Tenant*Controller | app/Http/Controllers/Owner | sisi tenant pada booking/kos/pembayaran/kontrak |
| CheckInController / CheckOutController | Owner | alur masuk/keluar penghuni |
| PembayaranController (Owner) | Owner | listing, verify/reject, download proof (pojok tenant & owner) |
| LaporanController (SuperAdmin) | SuperAdmin | laporan lintas peran + ekspor PDF/CSV |
| FasilitasController, UserController, PartnerController, Advertising*Controller | SuperAdmin | master global |
| AdvertisingController (Admin) | Admin | moderasi kampanye |
| PaymentGatewayWebhookController | Webhook | verifikasi HMAC webhook |
| Api\V1\* | Api\V1 | API ber-Sanctum |
| Controllers root | Profile, Notification, Welcome, Health, AdvertisingClick | dukungan |

## 20. Business Rules

1. **Booking instan**: status langsung `approved` (BookingService.php:57), kamar → `booked` (:61).
2. **Availability**: khusus kamar `available` dan kos `active` (:27-29).
3. **No double-booking**: cek overlap booking approved pada kamar, protected oleh transaksi+lock (:24-39).
4. **Harga kaku**: memakai harga kamar yang ada, tanpa input tenant (:41).
5. **Batalkan**: hanya pending/approved; kamar dikembalikan bila tak ada pemesan lain (approved, end_date ≥ hari ini) (:94-121).
6. **Check-in**: booking wajib approved → menghasilkan Penghuni+Kontrak+CheckIn → booking `completed`, kamar `occupied` (CheckInController.php:53-96).
7. **Kamar status lifecycle**: `available → booked (booking) → occupied (check-in) → available (check-out / expire)`; `available ⇄ maintenance`.
8. **Tagihan**: `unpaid → pending_verification (pembayaran diajukan) → paid (verifikasi)`; `unpaid lewat due → overdue` (MarkOverdueTagihans); verifikasi memulihkan status overdue (PembayaranController.php:132-135).
9. **Bukti bayar**: opsional; hanya jpg/png/pdf ≤ 5MB; tersimpan disk privat; disajikan via route auth; resource API hanya expose boolean (PembayaranResource.php:31).
10. **Audit chain**: setiap log terhubung previous_hash; hash NULL apabila secret tak valid (fail-closed).
11. **Advertising gating**: kampanye aktif hanya bila status `active` (proses oleh AdvertisingProcessCampaigns); pembayaran kampanye diblock di sandbox; atribusi conversion dari session `ad_click` → bonus di BookingService.php:76.
12. **Webhook**: throttle + verifikasi tanda tangan fail-closed; order mark-paid hanya oleh super admin/admin terotorisasi.
13. **Registrasi**: `[CATATAN]` store() hanya name/email/password; `role` memakai default DB `'tenant'`; tidak menangkap phone/address; dashboard butuh email terverifikasi (verified middleware). Tidak ada alur upgrade peran dari dalam aplikasi — peran diubah oleh Super Admin/administrator lain (UserController) atau seeder.

## 21. Skema Alur E2E

### A. Penyewa baru
1. Registrasi (`auth/register`) → role default `tenant`, email verifikasi wajib sebelum `/dashboard`.
2. Marketplace: cari kos/kamar → favorit/klik iklan (tracking) → `booking.create`.
3. Booking → **langsung approved** → kamar `booked` → notifikasi ke owner & tenant.
4. Datang ke kos → pengelola **check-in** (data identitas) → Penghuni active + Kontrak (`KT-…`) → kamar `occupied`.
5. Tagihan dibuat (otomatis/manual) → tenant lihat tagihan → bayar (gesek/cash/e-wallet, opsional bukti) → status `pending_verification`.
6. Owner/Admin **verifikasi** → `paid`, kamar/statistik ter-update (termasuk pemulihan overdue bila perlu).
7. Sewa berakhir → tenant/pengelola **request check-out** → approve → kamar `available`, kontrak berakhir.

### B. Mitra pengiklan (super admin / partner / owner)
1. Super Admin kelola **Partner** (DANA/Shopee/GoPay/metric) + **Paket** (harga, durasi, penempatan).
2. Owner buat **Kampanye** (kos-promotion) → bayar (sandbox) → review admin/super admin → **approved** → **active** (mulai tayang) → **completed/expired**; super admin bisa **suspend/reject**.
3. Iklan pihak ketiga: **order** → **mark-paid** (super/admin) → tayang.
4. Event `impress/click/conversion` tercatat → `AdvertisingAnalytics` → dasbor revenue + ekspor CSV.

### C. Pengelola operasional (owner/admin)
Adopt satu kos (admin via `kos_user`) → pantau dashboard (occupancy incl. `booked`, pending payment) →
kelola kamar/kos (owner) → pantau booking (instant approved) & verifikasi pembayaran → check-in/out → buat tagihan →
ekspor laporan PDF/CSV.

## 22. Feature Matrix (per Peran)

Legenda: `Y` penuh · `V` lihat saja · `~` parsial · `-` tidak ada · `N` jalan alternatif/dummy

| Fitur | Super Admin | Admin | Owner | Tenant |
|---|---|---|---|---|
| Dashboard KPI | Y | Y | Y (incl. status `booked`) | Y (sisa sewa, tagihan, quick actions) |
| Kelola Kos (CRUD+foto) | Y (via owner route) | V (`admin.kamar` saja, tdk ada kos route admin) | Y | - |
| Kelola Kamar (CRUD+foto,status) | Y | V (index/show saja) | Y | - |
| Marketplace/katalog | - | - | - | Y |
| Favorit | - | - | - | Y |
| Booking (buat/batal) | - | - | - | Y (instant approved) |
| Booking (lihat) | Y | Y | Y | V (riwayat) |
| Check-in | Y | Y | Y | - |
| Check-out (request/approve/reject) | Y | Y | Y | request |
| Kontrak (lihat) | Y | Y | Y | Y |
| Tagihan (buat) | Y | Y | Y | - |
| Tagihan (lihat/bayar) | Y | Y | Y | Y |
| Verifikasi pembayaran | Y | Y | Y | - |
| Bukti bayar (upload/lihat) | lihat | lihat | lihat/verif | upload+lihat |
| Laporan PDF/CSV | Y (semua kos) | Y (kos ditugaskan) | Y (kos milik) | - |
| Kelola User | Y | - | - | - |
| Kelola Fasilitas global | Y | - | - | - |
| Advertising: Partner/Paket | Y | - | - | - |
| Advertising: Kampanye owner | Y | - | Y (buat/pay/cancel) | - |
| Advertising: Moderasi/approve | Y | Y | - | - |
| Advertising: Revenue/analytics | Y | - | dasbor owner | - |
| Audit Log | Y | - | - | - |
| Lapor Kerusakan | - | - | - | `N` (dummy → #bantuan) |

Catatan `~`: fasilitas category (Bagian 14), audit-chain verify di UI (Bagian 16), gateway sungguhan (Bagian 8).

## 23. Uji (Testing)

- **97 file test**: **5 Unit**, **92 Feature**; total **±1.133 test / ±3.875 assertion**.
- Runner: PHPUnit 10 di **SQLite :memory:** (`phpunit.xml`) — fitur yang butuh MySQL path di-mock/skip.
- Cakupan teruji (dari nama/isi test):
  - **Autentikasi & otorisasi** (login/logout, role isolation 403);
  - **Booking**: pembuatan instan, **overlap prevention**, **race/concurrency** (lock), pembatalan,
    status expire;
  - **Check-in / check-out**: transisi status kamar & booking, validasi identitas, otorisasi;
  - **Tagihan**: penjadwalan, overdue sweep, pengingat, manipulasi status;
  - **Pembayaran**: verifikasi/reject, **integritas bukti**, restore status overdue;
  - **Webhook**: **verifikasi HMAC fail-closed**, throttle;
  - **Advertising**: gating pembayaran & aktivasi kampanye;
  - **API V1**: auth Sanctum, sanitasi resource.
- `composer test` hijau, `pint` bersih di area perubahan terakhir (per pekerjaan QA sebelumnya).

## 24. Masalah Ditemukan & Inkonsistensi

1. **`[NOT IMPLEMENTED]` Lapor Kerusakan** — hanya anchor `#bantuan` + teks janji; tanpa route/controller/model/view. (Bab 12)
2. **`[UI/BACKEND MISMATCH] [PARTIALLY IMPLEMENTED]` Kategori Fasilitas** — kolom `category` ada di DB tapi form create/edit Super Admin tidak memintanya. (Bab 14)
3. **`[PARTIALLY IMPLEMENTED]` Audit-log verification chain** — `AuditLogService::verifyChain()` tidak disajikan/ekspos di view audit log. (Bab 16)
4. **`[PARTIALLY IMPLEMENTED]` Payment gateway** — pipeline simulasi/sandbox; belum integrasi PSP riil; webhook disimulasikan. (Bab 8)
5. **`[PARTIALLY IMPLEMENTED]` API cakupan** — hanya billing & booking yang diekspos V1; banyak fitur web tanpa endpoint resmi. (Bab 18)
6. **`[UI/BACKEND MISMATCH]` "Ekspor Excel"** — teknisnya menghasilkan **CSV** (BOM UTF-8), bukan `.xlsx`; meski library maatwebsite/excel terpasang. (Bab 13)
7. **`[CATATAN]` Registrasi** — role default `tenant` dari DB, tanpa capture phone/address; wajib verifikasi email untuk dashboard; tidak ada alur penugasan peran dari UI tenant. (Bab 20.13)
8. **`[CATATAN]` Admin exposure tagihan/bayar** — route admin menyediakan create tagihan & verify/reject pembayaran (read-mostly claim bertabrakan dgn kapabilitas write). (Bab 22)
9. **`[PARTIALLY IMPLEMENTED]` Sertifikat/HTTPS & backup** — backup DB via command tersedia; belum ada otomatisasi cron default selain scheduler Laravel; SSL adalah concern deployment. (Bab 15)
10. **`[CATATAN]` Dokumen bertumpuk** — banyak laporan fase (P4–P8) di `docs/`; disarankan konsolidasi agar satu sumber kebenaran.

## 25. Status Proyek & Rekomendasi

**Status:** `feature-complete` untuk domain inti; diuji secara luas; siap staging/production
dengan catatan berikut.

Rekomendasi prioritas:
1. **Tinggi:** Implementasi **Lapor Kerusakan** asli (tambah table + controller + notifikasi) atau hapus teks/aksi dummy dari UI.
2. **Tinggi:** Ekspos **verifikasi chain audit log** di halaman audit (tombstone + indicator) agar nilai integritas terlihat.
3. **Sedang:** Selesaikan pengisian **kategori fasilitas** di form Super Admin (pakai kolom yang sudah ada).
4. **Sedang:** Integrasi **payment gateway riil** (atau tandai jelas "mode demo/sandbox" di UI) + dokumentasi OpenAPI untuk V1.
5. **Sedang:** Timpa label "Ekspor Excel" menjadi XLSX via maatwebsite atau ubah UI menjadi CSV.
6. **Rendah:** Perbaiki UX registrasi (phone/address, alur penugasan role) dan rapikan `docs/`.

## 26. Lampiran

### Akun demo (seeder) — `database/seeders/UserSeeder.php:16-58`
| Nama | Email | Password | Peran |
|---|---|---|---|
| Super Admin | superadmin@example.com | password | super_admin |
| Admin | admin@example.com | password | admin |
| Owner | owner@example.com | password | owner |
| Tenant | tenant@example.com | password | tenant |
(+ factory: 2 owner, 5 tenant — :60-68)

### Data demo
- Kamar demo owner: total 15 kamar (12 available, 1 booked, 2 occupied, 0 maintenance) —
  basis audit dashboard owner (KPI occupancy menyertakan status `booked`).
- Fasilitas, kos, booking, penghuni, kontrak, tagihan, demo photo, partner &
  advertising data ter-seed saat `php artisan db:seed` (**AdvertisingSeeder dikecualikan di production**
  — `DatabaseSeeder.php:14-48`).

### Menjalankan
```
composer install && npm install
php artisan key:generate
cp .env.example .env   # set DB_MYSQL + AUDIT_HMAC_SECRET (64-hex) + APP_URL
php artisan migrate --seed
php artisan storage:link
composer run dev       # vite dev (tailwind+alpine+flowbite)
php artisan serve
php artisan schedule:work   # sweeps: overdue, expired, backup, ads
```
QA E2E: puppeteer-core (dev dependency) terhadap `http://127.0.0.1:8000` dengan Chrome lokal.

### Tolok ukur integritas
- Audit secret: `config('audit.hmac_secret')`, wajib 64-hex, fail-closed.
- Webhook: HMAC fail-closed + throttle.
- Proof payment: disk `local`, hanya route ter-autentikasi.

---
*Dokumen dihasilkan dari audit kode read-only; setiap klaim didukung sitasi file:baris. Perbarui saat kode berubah.*