# PHASE 5 — PRODUCT, UX & TECHNICAL AUDIT

**Proyek:** KosManager (Laravel 10 — Sistem Manajemen Kos)
**Tujuan:** Audit produk, UX, dan teknis menyeluruh sebagai dasar perencanaan Phase 5.
**Status:** AUDIT SAJA — belum ada implementasi. Menunggu approval.
**Tanggal:** 01 September 2026

---

## 1. Ringkasan Eksekutif

Audit ini membaca langsung seluruh kode (controllers, models, policies, requests, views, routes, commands, tests) dan memverifikasi setiap temuan kunci terhadap file sumber. **Tidak ditemukan CRITICAL** (tidak ada data loss yang dapat dieksploitasi, kerentanan yang bisa dieksploitasi langsung, atau double-booking). Arsitektur keamanan, isolasi tenant/owner, transaksi + `lockForUpdate()`, penyimpanan bukti privat, dan CSRF terbukti kuat.

Namun ditemukan sejumlah **HIGH**: (1) fitur pencarian kos "Cari" di marketplace **rusak** (input tidak ter-submit ke form), (2) metrik super-admin "Perlu Check-in" salah hitung, (3) super admin tidak bisa membuat Kos via UI, (4) tidak ada mekanisme runtime untuk menetapkan Admin ke Kos (fondasi isolasi admin tidak bisa diisi dari UI), (5) super admin bisa mengunci akun sendiri/last super admin, (6) check-in bisa dilakukan setelah periode sewa berakhir, dan (7) potensi Stored XSS pada field `fasilitas.icon`.

Rekomendasi: **Phase 5 difokuskan pada perbaikan fungsional + integritas state + aksesibilitas**, dengan pembersihan maintainability sebagai tahap akhir.

---

## 2. Baseline (Verification Gate Saat Ini)

| Gate | Hasil |
|------|-------|
| `php artisan test` | **567 passed / 1665 assertions** |
| `vendor/bin/pint --test` | **PASS** |
| `npm run build` | **PASS** (4.67s) |
| `php artisan view:cache` | **PASS** |

Baseline ini menjadi acuan: total test **tidak boleh regresi** di bawah 567/1665 setelah implementasi Phase 5 (minimal harus tetap setara atau bertambah).

---

## 3. Fitur Terkonfirmasi Berfungsi (Diverifikasi)

### Super Admin
- Manajemen user (owner/admin/tenant), master fasilitas, laporan global PDF/Excel, audit log.
- Proteksi `destroy()` terhadap self-delete & last-super-admin (tetapi see **OA-4**: guard tidak ada di `update()`).

### Admin
- Manajemen operasional kos yang ditugaskan (`assignedKos`), persetujuan booking, kontrak & penghuni, check-in/out, tagihan, verifikasi pembayaran. Setiap aksi resource ter-authorize via `AdminPolicy`/scope `kos_id`.
- **Catatan:** assignment admin→kos belum punya UI (see **OA-3**).

### Owner
- CRUD kos/kamar (foto, fasilitas, harga harian/bulanan), persetujuan booking, kontrak & penghuni, check-in/out, tagihan (diskon, denda), verifikasi pembayaran, laporan milik sendiri.
- Statistik finansial dashboard (F-3 Phase 4) konsisten: `tagihan_outstanding` = unpaid+overdue, `tagihan_overdue` scoped per owner.
- Isolasi owner terbukti benar: `owner_id` dipaksa ke `auth()->id()` untuk owner, kamar tidak bisa dipindah antar kos pada update, penghapusan kos/kamar diblokir saat ada penghuni/booking/tagihan aktif.

### Tenant
- Browse kos & kamar tersedia, booking instan (harian/bulanan) dengan conflict-check + lock, kontrak aktif, tagihan & status, upload bukti pembayaran (validasi mime+size), status verifikasi beserta alasan penolakan (F-1 Phase 4).

### Cross-domain (diverifikasi kuat)
- Transaksi + `lockForUpdate()` **intact** di seluruh jalur mutasi: booking store/approve/reject/cancel, check-in, check-out approve/reject/request, kurangi pemesanan konflik, pembayaran submit/verify/reject, kedua command expiry. Baris lock menserialisasi race (double check-in, double booking, cancel-vs-checkin).
- Isolasi tenant: `TenantPembayaranController::store` memverifikasi `tagihan->penghuni_id === penghuni->id`; download bukti `authorize('view')` + disk privat `local`; tidak ada IDOR ditemukan.
- Mass assignment terkontrol (tidak ada `->update($request->all())`).
- XSS: seluruh field user-controlled di-escape `{{ }}` (kecuali **SC-1**).
- CSRF global aktif, `except = []`.
- Eager loading disiplin: setiap `index()` me-load relasi yang disentuh view; tidak ada loop N+1 (kecuali **TC-2** yang single-record).

---

## 4. Area Yang Sudah Bagus (Pertahankan)

1. **Konvensi tombol submit** — konsisten `x-bind:disabled` + `disabled:opacity-50` + `disabled:cursor-not-allowed`; **tidak ada** pola `:class="submitting ..."` yang dilarang.
2. **Dark mode** — varian `dark:` menyeluruh, strategi `class` manual.
3. **Semua `<img>` produk** punya `alt` deskriptif.
4. **Status badge** selalu menyertakan teks label, bukan warna saja.
5. **Empty state** `<x-empty-state>` dipakai konsisten.
6. **Pagination query string** dipertahankan via `withQueryString()`.
7. **Eager loading + `withCount`/`withMin`** di marketplace index sangat baik (aggregates via subquery, `FavoritedIds` batch-load).
8. **Label terpusat** `StatusLabels` / `PaymentLabels` (label konsisten, tapi set status masih tersebar — see **TQ-1/2/3**).
9. **`addcslashes`** pada pencarian mencegah wildcard LIKE injection.
10. **Berhasil menjalankan proses booking→checkin→kontrak→tagihan→pembayaran** secara end-to-end dengan konsistensi status.
11. **Komponen revisi penolakan pembayaran (F-1)** — reason wajib di UI owner/admin & backend konsisten.

---

## 5. Temuan — PRIORITAS (sesuai severity)

### 5.1 HIGH

#### TC-1 — Fitur pencarian "Cari" di marketplace rusak (input tidak ter-submit)
- **File:** `resources/views/tenant/kos/index.blade.php:17,27,343-362`
- **Perilaku saat ini:** Input pencarian `name="q"` (L17) berada di dalam `<div>`, **bukan** di dalam `<form>`. Tombol "Cari" (L27) submit ke `#searchForm` (L343) yang **tidak berisi** `<input name="q">` (hanya loc/sort/price/facilities). Mengetik query lalu klik "Cari" → nilai query **hilang**. Enter di input juga tidak berfungsi.
- **Mengapa masalah:** Fitur pencarian utama tenant rusak total dari hero; satu-satunya jalur `q` masuk hanya jika sudah ada di URL.
- **Fix:** Tambahkan `<input type="hidden" name="q">` di dalam `searchForm` dan sinkronkan nilai dari input hero via Alpine (`x-on:input`), atau bungkus hero search dalam `<form>` yang membawa semua hidden fields.
- **Risiko fix:** LOW — frontend-only.
- **Backend required:** Tidak.

#### OA-1 — Metrik super-admin "Perlu Check-in" salah hitung
- **File:** `app/Http/Controllers/DashboardController.php:47` (diklaim "Perlu Check-in" di `dashboard/super-admin.blade.php:99-100`).
- **Perilaku saat ini:** `total_active_bookings = Booking::where('status','approved')->count()` — menghitung *semua* booking approved tanpa filter `withoutActivePenghuni()`, TIDAK konsisten dengan kartu owner (L105-106) dan admin (L75-77) yang menerapkan scope tersebut.
- **Mengapa masalah:** Angka global menggelembung; tidak mengukur label.
- **Fix:** Terapkan `->withoutActivePenghuni()` (+ pertimbangkan date guard) agar sejalan dengan role lain, atau ganti label.
- **Risiko fix:** LOW.
- **Backend required:** Ya.

#### OA-2 — Super admin tidak bisa membuat Kos (tidak ada field `owner_id` di form)
- **File:** `app/Http/Controllers/Owner/KosController.php:60-62` (validasi `owner_id` wajib untuk non-owner) vs `resources/views/owner/kos/create.blade.php` (tidak ada dropdown owner).
- **Perilaku saat ini:** Route `owner.kos.*` mengizinkan `super_admin`, sidebar menampilkan "Tambah Kos" untuk super admin, tapi `store()` mewajibkan `owner_id` yang tidak ada di UI → selalu gagal validasi.
- **Fix:** Tambahkan dropdown "Owner" (daftar `User::where('role','owner')`) hanya untuk non-owner; pertahankan `store()` yang menimpa `owner_id` untuk owner.
- **Risiko fix:** LOW.
- **Backend required:** Ya (pass owner list).

#### OA-3 — Tidak ada mekanisme runtime untuk menetapkan Admin ke Kos
- **File:** `app/Http/Controllers/SuperAdmin/UserController.php` (tidak ada action assignment); pivot `kos_user` hanya diisi seeder `database/seeders/KosSeeder.php:47` dan test.
- **Perilaku saat ini:** Seluruh dashboard/admin list/policy admin bergantung pada `assignedKos()`/pivot `kos_user`, tapi tidak ada UI untuk mengisi pivot. Admin baru selalu punya 0 kos → dashboard & list kosong.
- **Fix:** Tambahkan UI assignment admin di form edit Kos (`$kos->admins()->sync(...)`) dengan guard otorisasi (hanya pemilik kos/super_admin).
- **Risiko fix:** MEDIUM-LOW (perlu guard agar admin tidak bisa self-assign).
- **Backend required:** Ya.

#### OA-4 — Super admin bisa demote/deaktivasi akun sendiri / last super admin
- **File:** `app/Http/Controllers/SuperAdmin/UserController.php:58-63` (`update` via `$request->only(...)` tanpa guard) vs `destroy()` (L65-88) yang memproteksi self-delete & last-super-admin.
- **Perilaku saat ini:** Super admin bisa mengubah role dirinya sendiri atau `is_active`-nya ke 0 (IsActive middleware langsung memutus sesi). Bisa menghilangkan super admin terakhir.
- **Fix:** Di `update()`, larang self-demotion & self-deactivate; larang mengurangi jumlah super admin aktif ke nol (mirror guard `destroy()`).
- **Risiko fix:** LOW.
- **Backend required:** Ya.

#### BK-1 — Check-in bisa dilakukan setelah periode sewa berakhir (no-show window)
- **File:** `app/Http/Controllers/Owner/CheckInController.php:26,39,44-95` (validasi hanya `status==='approved'` (L54) & `kamar.status==='booked'` (L57); tidak ada cek tanggal).
- **Perilaku saat ini:** `readyBookings` menampilkan semua booking approved tanpa filter tanggal; `process()` tidak memvalidasi bahwa hari ini ∈ `[start_date, end_date]`. Command nightly baru expiry jam 00:15 — celah harian.
- **Mengapa masalah:** Owner bisa check-in tenant no-show setelah masa sewa habis → kontrak aktif back-dated & inkonsisten dengan expiry command; juga memungkinkan check-in sebelum `start_date`.
- **Fix:** Di dalam transaksi ber-lock, `abort_unless(now() <= end_date)` (keputusan produk: apakah early check-in di-izinkan); filter `readyBookings` hanya booking yang belum lewat `end_date`.
- **Risiko fix:** LOW (perlu keputusan produk soal early check-in).
- **Backend required:** Ya.

### 5.2 MEDIUM

#### SC-1 — Stored XSS via field `fasilitas.icon` yang tidak di-escape
- **File:** `resources/views/owner/kamar/show.blade.php:95` — `{!! $f->icon !!}`.
- **Perilaku saat ini:** Value DB `icon` dirender sebagai HTML mentah di halaman owner/admin kamar show. Super admin (atau akun super admin yang diretas) bisa menyimpan payload `<img onerror>`.
- **Fix:** Ubah ke `{{ $f->icon }}` (escape), idealnya validasi pola aman (`/^[a-z0-9 _-]+$/`) di `FasilitasController` dan render di atribut class. Di tempat lain kodebase sudah aman (mis. `tenant/kos/index.blade.php:48`).
- **Risiko fix:** LOW.
- **Backend required:** Ya (view + opsional rule).

#### TC-2 — `hasFavorited()` dieksekusi 6x sama di show page (6 query identik)
- **File:** `resources/views/tenant/kos/show.blade.php:41-43,230-232`; `app/Models/User.php:105-108`.
- **Fix:** Hitung sekali di `TenantKosController::show()` lalu kirim sebagai variabel.
- **Risiko fix:** LOW. **Backend required:** Ya.

#### TC-3 — Fasilitas non-aktif masih muncul di filter UI tenant
- **File:** `app/Http/Controllers/Owner/TenantKosController.php:77` — `Fasilitas::orderBy('name')->get()` tanpa `active()`.
- **Fix:** `Fasilitas::active()->orderBy('name')->get();`
- **Risiko fix:** LOW. **Backend required:** Ya.

#### TC-5 — Placeholder janjikan pencarian "fasilitas" tapi backend tidak mendukung
- **File:** `resources/views/tenant/kos/index.blade.php:17` vs `TenantKosController.php:35-38` (hanya name/address/description).
- **Fix:** Tambah `orWhereHas('kamar.fasilitas', ...)` ATAU ubah placeholder.
- **Risiko fix:** LOW-MEDIUM. **Backend required:** Ya (salah satu).

#### TC-4 — Filter lokasi exact-match `WHERE address = ?` tanpa toleransi
- **File:** `app/Http/Controllers/Owner/TenantKosController.php:44`.
- **Fix:** Trim/normalisasi sebelum bandingkan atau `LIKE`; di-minimum `TRIM(address)`.
- **Risiko fix:** LOW. **Backend required:** Ya.

#### PM-2 — Menolak pembayaran tagihan `overdue` menurunkan status ke `unpaid`
- **File:** `app/Http/Controllers/Owner/PembayaranController.php:129-131`.
- **Perilaku saat ini:** Saat reject, tagihan di-set `unpaid` tanpa memperhatikan `due_date`; status `overdue` hilang sampai cron malam berikutnya.
- **Fix:** Saat reject, set `overdue` jika `due_date < today`, selain itu `unpaid`.
- **Risiko fix:** LOW. **Backend required:** Ya.

#### PM-1 — File bukti tersimpan sebelum transaksi; tersisa (orphan) jika transaksi exception
- **File:** `app/Http/Controllers/Owner/TenantPembayaranController.php:37-39,72-80` (cleanup hanya di cabang `return null`).
- **Fix:** Pindahkan penyimpanan file ke dalam transaksi / setelah lock, atau `try/finally` yang menghapus file saat gagal.
- **Risiko fix:** LOW. **Backend required:** Ya.

#### PM-3 — Revenue dashboard memakai `payment_date`, bukan `verified_at`
- **File:** `app/Http/Controllers/DashboardController.php:49,53-58,109,114-121`.
- **Fix (opsional, judgment call):** Group berdasarkan `verified_at` untuk basis pengakuan pendapatan konservatif; atau tampilkan "pending belum diverifikasi" terpisah.
- **Risiko fix:** LOW-MEDIUM (angka berubah). **Backend required:** Ya.

#### BK-2 — Timeline booking `completed` menunjukkan "Check-Out" belum dilakukan setelah tenant check-out
- **File:** `app/Http/Controllers/Owner/TenantBookingController.php:132-139` (`$hasCheckOut` dari `Penghuni::where('status','active')->first()`).
- **Fix:** Ambil penghuni terakhir tanpa filter status (sejalan dengan `$bridgePenghuni`).
- **Risiko fix:** LOW. **Backend required:** Ya.

#### BK-3 — `cancel()` menjalankan audit/notification di luar try/catch (satu-satunya)
- **File:** `app/Http/Controllers/Owner/TenantBookingController.php:189-191`.
- **Fix:** Bungkus dengan `try/catch` log-and-continue seperti controller lain.
- **Risiko fix:** LOW. **Backend required:** Ya.

#### TQ-1 — Set status "tagihan yang menghalangi" duplikat di 4 tempat
- **File:** `KosController.php:128`, `CheckOutController.php:90`, `ExpireOldKontraks.php:92`, `DashboardController.php:140` — semua hardcode `['unpaid','overdue','pending_verification']`.
- **Fix:** Tambah konstanta + scope di model `Tagihan` (`const OUTSTANDING`, `scopeOutstanding`), ganti semua call site (+ unit test set konstan).
- **Risiko fix:** LOW. **Backend required:** Ya.

#### OA-5 — Logika `is_active` diduplikasi di dua middleware
- **File:** `app/Http/Middleware/RoleMiddleware.php:18-25` & `IsActive.php:14-21` (sama-sama logout user non-aktif); `active` hanya ada di dashboard route saja → coverage tidak konsisten.
- **Fix:** Pilih satu mekanisme, terapkan `active` grup-wide (atau hapus `IsActive` dan pertahankan di `RoleMiddleware`).
- **Risiko fix:** LOW. **Backend required:** Ya.

#### OA-7 — Fasilitas `type` bisa diubah padahal sudah terpakai
- **File:** `app/Http/Controllers/SuperAdmin/FasilitasController.php:55-65` (tanpa guard). Berisiko korup relasi `kamar_fasilitas`/`kos_fasilitas`.
- **Fix:** Blokir ganti `type` jika sudah terpakai (mirror guard `destroy()`).
- **Risiko fix:** LOW. **Backend required:** Ya.

#### UX-1 — Modal tanpa focus trap / `aria-modal`
- **File:** `resources/views/components/modal.blade.php`, `confirm-dialog.blade.php`, `owner/pembayaran/show.blade.php:101-120`, `profile/partials/delete-user-form.blade.php`.
- **Fix:** Tambah `role="dialog"` + `aria-modal="true"`, focus trap (Alpine `x-trap` atau manual), fokus ke elemen pertama saat buka & kembali ke trigger saat tutup, `aria-labelledby`.
- **Risiko fix:** MEDIUM. **Backend required:** Tidak.

#### UX-2 — Breadcrumb tanpa list semantik & separator terbaca screen reader
- **File:** `resources/views/components/breadcrumb.blade.php:6-17`.
- **Fix:** Bungkus `<ol>`, `aria-hidden="true"` pada `<i>` separator, `aria-current="page"` pada item aktif.
- **Risiko fix:** LOW. **Backend required:** Tidak.

#### UX-4 — Tidak ada landmark `<main>` / skip-link di layout
- **File:** `resources/views/layouts/app.blade.php:~156`, `guest.blade.php:~58`, `sidebar.blade.php:~88`.
- **Fix:** Bungkus slot dengan `<main id="main-content">` + tambah "Skip to content" link.
- **Risiko fix:** LOW. **Backend required:** Tidak.

#### UX-3 — Alert tidak punya `aria-live`
- **File:** `resources/views/components/alert.blade.php:25`.
- **Fix:** `aria-live="polite"` (success/info) & `aria-live="assertive"`/`role="alert"` (error) + `aria-atomic`.
- **Risiko fix:** LOW. **Backend required:** Tidak.

#### UX-5 — Dropdown tanpa navigasi keyboard & role menu
- **File:** `resources/views/components/dropdown.blade.php`.
- **Fix:** `role="menu"/"menuitem"`, arrow-key navigation, fokus pertama + return-to-trigger.
- **Risiko fix:** MEDIUM. **Backend required:** Tidak.

#### UX-6 — Pagination tanpa `aria-current="page"`
- **File:** `resources/views/vendor/pagination/custom.blade.php:32-42`.
- **Fix:** `aria-current="page"` + `aria-label="Paginasi"` di `<nav>`.
- **Risiko fix:** LOW. **Backend required:** Tidak.

#### UX-8 — Input auth tidak punya `autocomplete`
- **File:** `resources/views/auth/*`, `profile/partials/update-*`.
- **Fix:** `autocomplete="email"/"name"/"current-password"/"new-password"` (WCAG 1.3.5).
- **Risiko fix:** LOW. **Backend required:** Tidak.

#### UX-9 — Baris tabel yang clickable tidak punya fokus terlihat
- **File:** `owner/kamar/index.blade.php:86`, `owner/penghuni/index.blade.php:45`, `owner/pembayaran/index.blade.php:64`, `owner/checkin/index.blade.php:82`, `owner/tagihan/show.blade.php:62`.
- **Fix:** `focus-visible:ring-2 ring-primary-500` dkk. (WCAG 2.4.7).
- **Risiko fix:** LOW. **Backend required:** Tidak.

### 5.3 LOW

- **BK-4/SC-3** — `ExpireOldKontraks` under-report kontrak expired yang terblokir tagihan (counter hanya naik saat auto-checkout sukses). `app/Console/Commands/ExpireOldKontraks.php:29-50,83-126`.
- **BK-5** — Booking `pending` legacy tidak lagi di-sweep (ExpireOldBookings drop `expireStalePendingBookings`). Data lama bisa menggantung.
- **BK-6** — Command `ExpireOldBookings`, `MarkOverdueTagihans`, `RemindDueTagihans` tidak try/catch per-record → satu gagal notification membatalkan sisa batch.
- **PM-4** — Tagihan total 0 tidak pernah bisa dibayar (`StorePembayaranRequest` `min:1` vs `TagihanController` merekonsiliasi `max(0, ...)`).
- **PM-5** — Form upload tenant mati (dead code) di `owner/tagihan/show.blade.php:124-162` (tenant tidak pernah render view ini).
- **PM-6** — Inkonsistensi kecil: dashboard tenant `nearest_due` include `pending_verification`, `total_belum_dibayar` tidak.
- **PM-7** — Urutan lock store (tagihan dulu) vs verify/reject (pembayaran dulu) berbeda; dokumentasikan (deadlock tidak praktis saat ini).
- **OA-6** — Bar "Status Kamar" admin mengabaikan kamar maintenance (denominator salah / tidak ada segmen).
- **OA-8** — Copy konfirmasi hapus fasilitas ("dilepas dari semua kos/kamar") kontradiksi dengan controller yang memblokir hapus saat terpakai.
- **OA-9** — Variabel `$kosList` tak terpakai di `KamarController::edit` (secara positif memastikan kamar tidak bisa pindah kos).
- **OA-10** — `KosController::index` tanpa `authorize('viewAny')` (Kamar menggunakannya) — inkonsistensi defensif.
- **OA-11/OA-12** — Nama `total_pending_bookings` menyesatkan (sebenarnya "needs check-in"); query "needs check-in" diduplikasi antara dashboard & badge sidebar (`AppServiceProvider.php:38-52`).
- **OA-13** — Denominator okupansi owner include kamar maintenance.
- **SC-2** — Admin yang membuat kos di-set `owner_id` ke owner lain & tidak masuk `assignedKos` (bikin kos tak terlihat oleh pembuatnya); putuskan kebijakan kepemilikan.
- **SC-4** — `.env.example` `APP_DEBUG=true` (hygiene deploy).
- **TC-6/7/8/9/10** — `HAVING COUNT(*)>=1` redundan; URL booking modal dibangun via concatenation; `(int)` cast null price; correlated subquery di ORDER BY; favorite toggle reload halaman penuh (bukan AJAX) — UX.
- **TQ-2/3/4** — Set status `['unpaid','overdue']`, `['pending','approved']`, dan transisi approve/reject hardcoded literal di banyak tempat (lihat juga TQ-10 literal `'active'`).
- **TQ-5** — Indeks komposit hilang pada `tagihans(penghuni_id,status)`, `check_outs(penghuni_id,status)`, `pembayarans(penghuni_id,verification_status)`.
- **TQ-6** — `TagihanController::show` tidak eager-load `kamar.kos` (lazy pada show page).
- **TQ-7** — Duplikasi rute owner/admin/tenant besar; contoh nyata drift: `check-out/{penghuni}/request` ada di admin:156 & tenant:180 tapi **tidak** di grup owner (owner tak punya route requestCheckout padahal controller mendukungnya).
- **TQ-8** — Controller bengkak: `DashboardController` (397 baris), `TenantKosController` (361), `LaporanController` (354), `TagihanController` math billing, `CheckInController` orchestration — ekstraksi service bertahap.
- **TQ-11** — Test smoke/`assertOk` tanpa assert bisnis (`SuperAdminSmokeTest`, `OwnerLaporanTest`, banyak `PhaseXX*MarketplaceTest`).
- **TQ-12** — Route closure inline untuk audit-log (`routes/web.php:81-85`) daripada controller.
- **UX-7** — Favorite toggle `disabled:opacity-60 cursor-wait` tidak konsisten dengan konvensi (`opacity-50 cursor-not-allowed`).
- **UX-10** — Placeholder search dark mode kontras rendah (`owner/kos/show.blade.php:116`).
- **UX-11** — Gambar dekoratif guest layout punya alt deskriptif (harus `alt=""` + `aria-hidden`).
- **UX-12/13/14/15/16/17** — API `$trigger` slot confirm-dialog ambigu; delete account pakai `confirm()` browser; `<nav>` sidebar/topbar tanpa `aria-label`; bottom-nav & mobile-menu toggle tanpa `aria-current`/`aria-expanded`; `<img>` booking create `src=""` memicu broken-image sebelum JS.

---

## 6. Ringkasan Temuan per Severity

| Severity | Jumlah | Fokus |
|----------|--------|-------|
| CRITICAL | 0 | — (tidak ditemukan data-loss / exploit langsung) |
| HIGH | 6 | TC-1, OA-1, OA-2, OA-3, OA-4, BK-1 |
| MEDIUM | 21 | SC-1, TC-2/3/4/5, PM-1/2/3, BK-2/3, TQ-1, OA-5/7, UX-1..6, UX-8/9 |
| LOW | ~35 | sisanya (cleanup, maintainability, a11y minor) |

---

## 7. Temuan Keamanan (dikonsolidasi)

- **SC-1 (MEDIUM):** Stored XSS via `fasilitas.icon` di `owner/kamar/show`. Fix escape + whitelist pattern.
- **SC-2 (LOW):** Kepemilikan kos saat admin membuat kos ambigu (tidak di-assign ke dirinya).
- **SC-3 (LOW):** Undercount command `ExpireOldKontraks` + state "penghuni aktif di kontrak expired".
- **SC-4 (LOW):** `.env.example` `APP_DEBUG=true` (hygiene).

**Diverifikasi bersih:** otorisasi per-role (tidak ada IDOR), mass assignment, upload (mime/size/traversal), XSS selain SC-1, CSRF, sekret (tidak ada Cloudinary; `.env` ter-gitignore), isolasi tenant, transaksi+lock.

---

## 8. Temuan Business-Logic (dikonsolidasi)

- **BK-1** check-in lewat masa sewa (HIGH).
- **PM-2** reject menurunkan `overdue` → `unpaid` (MEDIUM).
- **BK-2** timeline booking completed salah saat sudah check-out (MEDIUM).
- **BK-4/SC-3** counter & state `ExpireOldKontraks` (LOW).
- **BK-5** booking pending legacy tidak pernah berakhir (LOW).
- **TQ-1/2/3/4/10** vocabulary status tersebar → risiko drift logika (HIGH/MEDIUM/LOW).

---

## 9. Temuan Mobile (dikonsolidasi)

- Positif: bottom-nav `z-50 h-16`, sticky bar "Pilih Kamar" `bottom-16 z-40`, spacer `h-32`, grid responsif, tap-target memadai.
- Yang perlu diperhatikan: **TC-1** (search rusak di semua ukuran, paling terasa di mobile), **TC-10** (favorite toggle reload penuh — jank di mobile), **UX-10** (placeholder kontras dark), **UX-15/16** (aria pada bottom-nav & menu toggle).
- Tidak ada overflow horizontal/blokir konten yang ditemukan.

---

## 10. Temuan Aksesibilitas (dikonsolidasi)

Prioritas: **UX-1** (focus trap modal) → **UX-2/3/4** (breadcrumb list, `aria-live`, `<main>`/skip-link) → **UX-5/6** (dropdown keyboard, pagination) → **UX-8/9** (autocomplete, fokus tabel) → sisanya LOW. Tidak ada pelanggaran kontras/misi alt/yang bersifat murni estetik yang fatal; fondasi sudah baik.

---

## 11. Temuan Teknis / Maintainability (dikonsolidasi)

- **TQ-1 (HIGH):** sentralisasi set status blocking-tagihan.
- **TQ-2/3/4/10 (MEDIUM/LOW):** sentralisasi set status aktif/payable + transisi.
- **TQ-5 (MEDIUM):** indeks komposit hot-path.
- **TQ-7 (MEDIUM):** dedup rute + tambah `owner.checkout.request` yang hilang.
- **TQ-8 (MEDIUM):** ekstraksi service (DashboardStats/Marketplace/Billing/CheckIn).
- **TQ-11 (LOW):** perkuat test smoke dengan assert bisnis.
- **TQ-6/9/12 (LOW):** eager-load `kamar.kos`, status-set di label layer, pindah route closure ke controller.
- **TC-2/3/4/5/6/9 (MEDIUM/LOW):** optimasi & konsistensi marketplace.

---

## 12. Rekomendasi SCOPE PHASE 5 (diusulkan untuk approval)

### Batch A — Fungsional correct (bug nyata, dampak user langsung)
1. **TC-1** — perbaiki search "Cari" (frontend).
2. **OA-1** — perbaiki metrik super-admin "Perlu Check-in".
3. **SC-1** — escape/whitelist `fasilitas.icon`.
4. **BK-1** — guard tanggal check-in (+ keputusan produk early check-in).
5. **PM-2** — reject kembalikan `overdue` bila perlu.

### Batch B — Kelengkapan produk admin/super-admin
6. **OA-2** — dropdown owner di form create Kos (super admin).
7. **OA-3** — UI assignment admin→kos di form edit Kos.
8. **OA-4** — guard self-demotion/deaktivasi super admin.

### Batch C — Integritas state & robustness
9. **PM-1** — cleanup file orphan pada exception.
10. **BK-2** — perbaiki timeline completed.
11. **BK-3** — fencing notification cancel.
12. **OA-7** — blokir ganti type fasilitas terpakai.
13. **OA-5** — satukan middleware `is_active`.

### Batch D — Aksesibilitas & UX (low risk, high compliance)
14. **UX-1** focus trap modal; **UX-2** breadcrumb; **UX-3** aria-live; **UX-4** main/skip-link; **UX-6** pagination; **UX-8** autocomplete; **UX-15/16** aria nav.
15. **UX-7** konsistensi disabled styling; **TC-10** (opsional) favorite toggle AJAX.

### Batch E — Maintainability (tahap akhir)
16. **TQ-1/2/3/10** — sentralisasi set status + scope model.
17. **TQ-7** — dedup rute + tambah rute yang hilang.
18. **TQ-5** — indeks komposit (migrasi aditif, opsional).
19. **TQ-6/9/12, TC-2/3/5, OA-9/10/11/12** — cleanup ringan.

### Tidak termasuk dalam scope rekomendasi (perlu keputusan eksplisit bila mau)
- **TQ-8** (ekstraksi service besar) — baru disarankan sebagai follow-up terpisah.
- **PM-3** (perubahan basis pengakuan revenue) — judgment call, tunda.
- **TQ-11** (hardening semua test smoke) — dorong bertahap.

---

## 13. File yang Kemungkinan Berubah

**Backend:**
- `app/Http/Controllers/Owner/TenantKosController.php` (TC-1 view, TC-2/3/4/5)
- `app/Http/Controllers/DashboardController.php` (OA-1, OA-6 optional)
- `app/Http/Controllers/Owner/KosController.php` (OA-2/OA-3 passthrough)
- `app/Http/Controllers/SuperAdmin/UserController.php` (OA-4)
- `app/Http/Controllers/Owner/CheckInController.php` (BK-1)
- `app/Http/Controllers/Owner/PembayaranController.php` (PM-2)
- `app/Http/Controllers/Owner/TenantPembayaranController.php` (PM-1)
- `app/Http/Controllers/Owner/TenantBookingController.php` (BK-2/BK-3)
- `app/Http/Controllers/SuperAdmin/FasilitasController.php` (OA-7, SC-1 whitelist)
- `app/Models/Tagihan.php`, `app/Models/Booking.php` (TQ-1/2/3/10 konstanta+scope)
- `app/Http/Middleware/RoleMiddleware.php` / `IsActive.php` (OA-5)
- `routes/web.php` (TQ-7 optional)
- migrasi indeks komposit (TQ-5, optional, aditif)

**Frontend / View:**
- `resources/views/tenant/kos/index.blade.php` (TC-1/TC-5/TC-3)
- `resources/views/owner/kamar/show.blade.php` (SC-1)
- `resources/views/owner/kos/create.blade.php`, `edit.blade.php` (OA-2/OA-3)
- `resources/views/super-admin/users/edit.blade.php` (OA-4)
- `resources/views/components/*` (modal, confirm-dialog, breadcrumb, alert, dropdown) — a11y
- `resources/views/layouts/*` (main/skip-link, aria nav)
- `resources/views/vendor/pagination/custom.blade.php` (UX-6)
- `resources/views/auth/*`, `resources/views/profile/*` (UX-8, UX-13)

**Tests:**
- Test baru per fix (mis. `Phase5FunctionalAuditTest.php`, `Phase5AccessibilitySmokeTest.php`, unit test status-set), perbaikan smoke test (TQ-11).

---

## 14. File yang TIDAK BOLEH Diubah (Constraint)

- **Migrasi/schema** kecuali aditif indeks (TQ-5) bila disetujui terpisah. Jangan ubah schema yang ada.
- **Rute** kecuali batch E (TQ-7) disetujui & terverifikasi nama route tetap stabil.
- **Policies** (`Kos/Kamar/Tagihan/Pembayaran/Kontrak/Penghuni/Booking/CheckIn/CheckOut/User`).
- **Lifecycle booking/check-in/check-out** yang sudah benar (transaksi + `lockForUpdate` + alur status) — BK-1 hanyalah penambahan guard, tidak menyentuh inti transaksi.
- **`TenantPembayaranController` storage proof** (disk privat `local` + autorisasi download) — PM-1 hanya cleanup failure-path.
- **Isolasi tenant** (`penghuni_id`, `user_id`, scope `assignedKos`).
- **CSRF**, konvensi tombol submit (`x-bind:disabled` + opacity/cursor utilities).
- Konvensi label `StatusLabels`/`PaymentLabels` (hanya aditif konstanta/scope).

---

## 15. Strategi Testing

- **Gate wajib (harus PASS):** `php artisan test` (min. 567 passed / 1665 assertions — target bertambah), `vendor/bin/pint --test`, `npm run build`, `php artisan view:cache`.
- **Test baru yang direkomendasikan:**
  - TC-1: GET search dengan `q` → hasil berubah; test `assertSee` item.
  - BK-1: booking dengan `end_date` kemarin → check-in ditolak (400); booking dalam window → sukses.
  - PM-2: reject pembayaran tagihan overdue → status tagihan kembali `overdue`.
  - OA-1: booking approved ber-penghuni aktif → tidak terhitung di "Perlu Check-in" super admin.
  - OA-4: super admin tidak bisa self-demote/deactivate.
  - SC-1: `{{ }}` escape icon (test render).
  - TQ-1: unit test bahwa `Tagihan::OUTSTANDING` set lengkap & konsisten.
  - UX (false positive guard): assertion view component render dengan role/aria yang benar bila perlu.
- **Dilarang:** merombak test existing yang passing tanpa alasan; menurunkan total assertion.

---

## 16. Risk Assessment

| Risiko | Peringkat | Mitigasi |
|--------|-----------|----------|
| Menyentuh transaksi/lock lifecycle | TINGGI (harus dihindari) | Batch C hanya failure-path cleanup; jaga `DB::transaction`+`lockForUpdate` utuh |
| Perubahan angka dashboard (PM-3) | MEDIUM | Ditunda / keputusan eksplisit |
| Refactor route (TQ-7) | MEDIUM | Verifikasi semua named route via `route:list`; commit terpisah |
| Ekstraksi service (TQ-8) | MEDIUM | Bertahap, back dengan unit test yang ada |
| Migrasi indeks | LOW | Aditif; jalankan pada staging dulu |
| A11y refactor modal/dropdown | MEDIUM | Test manual keyboard + screen reader; jaga flash/dark-mode |

---

## 17. Urutan Implementasi yang Diusulkan

1. **Batch A** (fungsional benar): TC-1, OA-1, SC-1, BK-1, PM-2.
2. **Batch B** (kelengkapan produk): OA-2, OA-3, OA-4.
3. **Batch C** (integrity/robustness): PM-1, BK-2, BK-3, OA-7, OA-5.
4. **Batch D** (a11y/UX): UX-1, UX-2, UX-3, UX-4, UX-6, UX-8, UX-15/16.
5. **Batch E** (maintainability, opsional): TQ-1/2/3/10, TQ-7, TQ-5, TQ-6/9/12, TC-2/3/5, OA-9/10/11/12, TQ-11.
6. **Gate verifikasi penuh** + laporan akhir Phase 5.

Setiap batch diakhiri dengan menjalankan gate penuh (`test`, `pint --test`, `npm run build`, `view:cache`).

---

## 18. Kesimpulan

Tidak ada bug CRITICAL; fondasi keamanan & integritas transaksional sudah kuat. Namun 6 temuan HIGH (search rusak, metrik super-admin salah, gap create kos & assignment admin, self-lockout super admin, check-in lewat masa sewa) dan 1 masalah keamanan MEDIUM (XSS fasilitas icon) layak menjadi inti Phase 5, diikuti hardening aksesibilitas/UX dan pembersihan maintainability. Semua perubahan diusulkan tanpa melanggar constraint lifecycle, isolasi, dan konvensi yang telah distabilkan.

---

PHASE 5 AUDIT COMPLETE — STOPPING FOR USER APPROVAL.