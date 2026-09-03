# P7-C: Notification System

Dokumen ini merangkum audit sistem notifikasi yang sudah ada, perubahan yang dilakukan untuk
menstandarkan **fondasi, state, event, UI/UX, dan arsitektur pengiriman** notifikasi, hasil verifikasi,
serta rekomendasi lanjutan.

## Ringkasan

- **Status:** Selesai (implementasi + dokumentasi).
- **Hasil audit:** Fondasi notifikasi **sudah sehat** dan banyak yang tidak perlu diubah:
  1. **Struktur:** model `App\Models\Notification` + tabel `notifications` (user_id, type, title, message,
     is_read, data json) sudah cukup; kolom `data` dipakai minim dan **tidak menyimpan credential/secret apa pun**.
  2. **Fondasi:** semua event tersentralisasi di `App\Services\NotificationService` (17 helper) yang
     membuat record + antrekan email (`KosManagerMail` via `Mail::queue`). Kegagalan email tidak membuat
     notifikasi gagal (wrapped try/catch).
  3. **Recipient:** pemetaan dibaca ulang — booking baru/batal → owner; booking disetujui/ditolak/expired
     → tenant; tagihan → tenant; pembayaran masuk → owner; pembayaran diverifikasi/ditolak → tenant;
     check-in → tenant; check-out request → owner; check-out approve/reject → tenant. **Tidak ada yang salah arah.**
  4. **Multi-tenancy:** notifikasi adalah per-baris per `user_id`; UI selalu membatasi ke `auth()->user()`.
  5. **Pembayaran gateway:** webhook sudah **idempotent** (guard `verification_status !== PENDING`);
     verifikasi manual tidak bisa menimpa pembayaran gateway yang sudah approved, sehingga tidak ada
     notifikasi dobel dari jalur manual + webhook.

**Temuan yang diperbaiki di tahap ini:**

1. **Belum ada halaman inbox** dengan pagination — notifikasi hanya muncul di dropdown 8 terbaru.
2. **Belum ada mark satu-per-satu** — hanya "tandai semua dibaca".
3. **Belum ada otorisasi server-side** (policy) untuk membaca/menandai notifikasi user lain.
4. **Perhitungan unread count terduplikasi** di dua tempat (composer sidebar + query inline header).
5. **Risk notifikasi dobel** dari command terjadwal (contoh: pengingat tagihan dijalankan ulang di hari sama
   akan mengirim lagi karena tagihan belum lunas) — belum ada mekanisme dedupe.
6. **Tautan sidebar "Notifikasi"** mengarah ke anchor `#notifikasi` yang tidak ada targetnya.
7. **Kosakata `type`** masih string literal tersebar tanpa konstanta/label terpusat.

## Perubahan yang Dilakukan

### 1. Fondasi: konstanta type & dedupe (tidak ada migrasi)

- **`app/Support/NotificationType.php` (baru)** — konstanta `BOOKING/PAYMENT/BILLING/...` + `label()` dan `all()`,
  mengikuti pola `PaymentLabels`/`StatusLabels`. Semua helper `NotificationService` kini memakai konstanta ini
  (nilai string tidak berubah, test lama tetap hijau).
- **`NotificationService::create()`** menambah parameter opsional `?string $uniqueKey = null` (**backward compatible**;
  pemanggil lama tanpa argumen tambahan tetap sama). Bila `uniqueKey` diberikan, ia disimpan di
  `data['unique_key']` dan **cek duplikat** dengan window 24 jam: bila ada record (user + type + unique_key yang sama)
  maka `create()` mengembalikan record lama — **tidak membuat notifikasi ganda**. Implementasi dedupe dilakukan
  di PHP (bounded terhadap 24 jam terakhir) sehingga driver-agnostik (tidak bergantung dukungan JSON sqlite).
- **Command terjadwal kini mengirim unique key:**
  - `tagihan:remind-due-soon` → `bill-due:{tagihan_id}`
  - `tagihan:mark-overdue` → `bill-overdue:{tagihan_id}`
  - `booking:expire-old` → `booking-expired:{booking_id}`
  - `kontrak:expire-old` → `checkout-auto:{user_id}`
- Tidak ada migrasi dan tidak ada dependency baru.

### 2. State & model

- **`App\Models\Notification`** menambah scope `unread()` dan `forUser(User|int)` untuk query yang konsisten
  (digunakan unread count, inbox, dan test).
- **`database/factories/NotificationFactory.php` (baru)** — factory model (model sudah pakai `HasFactory`),
  menambah state `read()`.

### 3. Otorisasi (policy) & controller

- **`app/Policies/NotificationPolicy.php` (baru)** — `view` dan `update` hanya untuk pemilik (`user_id === auth id`),
  didaftarkan di `AuthServiceProvider`.
- **`NotificationController`**:
  - `index()` — inbox **paginated 15/halaman**, hanya milik user aktif (`latest()`).
  - `markAsRead(Notification)` — tandai satu notifikasi, **dengan `authorize('update')`** (403 jika bukan miliknya).
  - `markAllRead()` — tetap ada, hanya memengaruhi notifikasi user yang login.
- **Routes (group `auth,active`):**
  - `GET /notifications` → `notifications.index`
  - `POST /notifications/{notification}/read` → `notifications.read`

### 4. UI/UX

- **`resources/views/notifications/index.blade.php` (baru)** — halaman inbox lengkap: daftar semua notifikasi
  (latest), penanda "Baru" + highlight untuk yang belum dibaca, tombol **"Tandai Dibaca"** per item,
  tombol **"Tandai Semua Dibaca"**, empty-state, dan pagination (`vendor.pagination.custom`).
  Responsive/mobile di atas layout `layouts.app` yang sudah memakai `tenant-bottom-nav`.
- **`layouts/app.blade.php`**:
  - Unread count header kini memakai nilai terpusat `$sidebarBadges['unreadNotif']` (tidak query inline ganda).
  - Dropdown notifikasi diberi link footer **"Lihat Semua Notifikasi"** → inbox.
  - `titleMap` + entri `notifications.index => 'Notifikasi'`.
- **`layouts/sidebar.blade.php`** — tautan "Notifikasi" diarahkan ke `route('notifications.index')`
  (sebelumnya anchor `#notifikasi` yang tidak memiliki target).
- **`AppServiceProvider`** — composer unread count kini juga mewakili `layouts.app` (satu sumber kebenaran;
  sebelumnya hanya `layouts.sidebar` + `tenant-bottom-nav`).

### 5. Arsitektur pengiriman (tetap, tanpa realtime)

- **Tidak ada WebSocket/Pusher/Reverb di P7-C.** Persistensi (DB) + email (antrean) tetap jalur utama;
  `NotificationService` adalah satu-satunya pintu masuk → mudah ditambahkan broadcasting di tahap masa depan
  bila terbukti dibutuhkan.
- Karakteristik queue dijaga: failure email **tidak** menyebabkan "false success" di sisi bisnis
  (transaksi booking/pembayaran terpisah dari notifikasi), dan semua kegagalan antrean ditangkap/log di service.
- Kelas `NotificationType` juga menjadi referensi jenis notifikasi untuk UI dan masa depan.

### 6. Keamanan

- `markAsRead` diverifikasi via policy server-side (bukan sekadar menyembunyikan tombol di UI).
- Inbox selalu dibatasi ke `user_id` user aktif — tidak ada kebocoran antar-tenant/user.
- Payload `data` tetap minimal dan tidak pernah berisi password/secret/payment key/webhook key.

## Test Baru

- `tests/Feature/NotificationSystemTest.php` (15 test / 44 assertion):
  - **Recipient & authorization:** inbox hanya menampilkan milik sendiri; `markAsRead` notifikasi user lain → 403.
  - **State:** mark single read (unread count turun), mark all read hanya milik user.
  - **Duplicate prevention:** `create()` dengan unique key sama → 1 baris; key berbeda → baris terpisah;
    key sama antar user tidak bocor; command `tagihan:remind-due-soon` dijalankan 2× → tetap 1 pengingat.
  - **Booking event:** `tenant.booking.store` → tenant dapat "Booking Berhasil" dan owner mendapat "Booking Baru".
  - **Payment event:** inisiasi pembayaran gateway → owner mendapat "Pembayaran Baru";
    webhook verifikasi → tenant mendapat "Pembayaran Diverifikasi".
  - **UI/route:** inbox dapat diakses, pagination 15 (page 2 total benar), empty-state.
  - **Ekstra:** `NotificationType::label()`/`all()`.

## Hasil Verifikasi (Regression Gate)

- `php artisan test` → **619 passed** (1829 assertions); baseline P7-B 604 (1785) + 15 test baru (44 assertion) — **PASS**.
- `vendor/bin/pint --test` → **PASS**.
- `npm run build` → **PASS**.
- `php artisan view:cache` → **PASS**.
- `php artisan route:cache` + `php artisan config:cache` → **PASS**.

## Risiko / Rekomendasi Lanjutan (di luar scope P7-C)

1. **Realtime:** belum ada push realtime. Bila kebutuhan muncul (mis. dashboard owner), tambahkan broadcasting
   di `NotificationService` — pintu masuknya sudah tunggal, migrasi datanya tidak perlu diubah.
2. **Worker:** email tetap dikirim via antrean; pastikan `queue:work` berjalan (lihat P7-B §4).
3. **Pagination efisiensi:** inbox memakai pagination biasa (per-page). Bila volume per user sangat besar,
   pertimbangkan cursor pagination atau pembatasan umur notifikasi (retensi) di tahap mendatang.
4. **Geo/tenant multi-server:** tidak ada perubahan terkait ini; lihat rekomendasi P7-B untuk Redis.
5. **Dedupe window 24 jam** dipilih untuk menghindari notifikasi dobel dari scheduler/misfire tanpa memblokir
   pengingat yang valid pada hari berbeda. Bila interval antar-run lebih pendek, sesuaikan window sesuai kebutuhan.