# Fase 0 — Baseline Karakterisasi

**Branch:** `chore/fase-0-baseline` (dari `develop` @ `3c03dd2`)
**Status:** Selesai. Menunggu persetujuan eksplisit sebelum Fase 1.

## 1. Ringkasan

Fase 0 hanya menambah **test karakterisasi + dokumentasi baseline**. Tidak ada perubahan
pada `app/`, `routes/`, `config/`, `database/migrations/`, maupun view. Test lama tidak
diubah (perbaikan flaky `3c03dd2` sudah ada di base).

Hasil:
- Test terdaftar: **2368 → 2410** (+42 test karakterisasi, +261 assertion).
- Full suite (SQLite): **3 run berturut-turut hijau** (2410 passed, 8347 assertion, exit 0).
- Pint: **passed**.
- Vite build: **passed** (62 modul, 7.86s).
- MySQL/MariaDB: **diblokir** oleh migrasi pre-existing (lihat §5).

## 2. Cakupan test karakterisasi

Lokasi: `tests/Feature/Characterization/` (6 file, 42 test).

| File | Jumlah test | Domain |
|---|---:|---|
| `RoleAccessCharacterizationTest.php` | 8 | Matriks akses role termasuk `super_admin`, admin scope, owner isolation |
| `DomainValuesCharacterizationTest.php` | 10 | Nilai role/status saat ini, constraint DB, kolom uang decimal |
| `BookingLifecycleCharacterizationTest.php` | 6 | Booking → check-in → billing → checkout, deposit & revisi tagihan belum ada |
| `PaymentFlowCharacterizationTest.php` | 8 | Pembayaran penuh, partial ditolak, duplikat, verifikasi/tolak, receipt |
| `DamageDisputeCharacterizationTest.php` | 8 | `DamageReport` & `Dispute` create/status/visibility |
| `MoneyMathCharacterizationTest.php` | 4 | DP, penalti, rounding komisi, cast decimal |
| `FundsFlowCharacterizationTest.php` | 2 | Alur dana 2 tahap (`PaymentDistribution`, `Payout`/`PayoutDisbursement`) |

### 2.1 Matriks akses role (perilaku saat ini)

- `super_admin`: boleh masuk area `super-admin`, `admin`, dan `owner`; **tidak boleh** masuk area `tenant`.
- `admin`: hanya kos yang di-assign (`assignedKos`); ditolak pada kos lain.
- `owner`: hanya kos miliknya (`owner_id`).
- `pencari`: hanya area tenant miliknya.
- Endpoint verifikasi pembayaran (`owner.pembayaran.verify`) menolak `pencari`.
- Guest diarahkan ke `/login` untuk route terproteksi.

### 2.2 Nilai role & status (perilaku saat ini)

- `users.role`: `super_admin`, `admin`, `owner`, `pencari` (belum ada `tenant`/`technician`).
- `UserRole` enum: 4 nilai, label: Super Admin, Admin Kos, Owner, Pencari Kos.
- `kamar.status`: `available`, `booked`, `occupied`, `maintenance` (belum `needs_cleaning`).
- `bookings.status`: `pending`, `approved`, `rejected`, `cancelled`, `completed`, `expired` (model: `STATUS_CONFIRMED = approved`).
- `tagihans.status`: `unpaid`, `pending_verification`, `paid`, `overdue`, `cancelled` (belum `draft`/`partial`).
- `pembayarans.verification_status`: `pending`, `approved`, `rejected`.
- `kontraks.status`: `active`, `expired`, `terminated` (tidak ada konstanta di model).
- `check_outs.status`: `pending`, `approved`, `rejected`, `completed`, `cancelled` (DB mengizinkan `completed`, model tidak punya konstantanya).
- `damage_reports.status`: `pending`, `reviewing`, `approved`, `rejected`, `resolved`.
- `disputes.status`: `open`, `in_progress`, `resolved`, `closed`.
- `pembayarans.payment_method`: `transfer_bank`, `qris`, `e_wallet`.

### 2.3 Money (perilaku saat ini)

- Kolom uang masih `decimal`/`numeric` 2 angka, bukan integer rupiah.
- DP booking: **Rp 100.000** (dari config), dipotong sekali pada tagihan pertama, sisa hangus.
- Penalti keterlambatan: **Rp 50.000** tetap, idempoten, ditambahkan sekali.
- Komisi persen dibulatkan 2 desimal (contoh 33.33% dari 999.999 = 333.299,67).
- Tidak ada model deposit/deduksi/refund.

## 3. Alur yang terkarakterisasi

- Booking → DP payment → verifikasi → approve → check-in (kontak tenant, konfirmasi owner, process) → kontrak aktif → kamar occupied.
- Checkout diblokir jika ada tagihan outstanding; jika disetujui sebelum end_date → `terminated`, kamar available.
- Tagihan: hanya pembayaran penuh diterima; partial ditolak; duplikat pembayaran aktif diblokir; bukti ditolak mengembalikan status ke `unpaid`/`overdue` dan mencatat `admin_notes`.
- Receipt hanya tersedia setelah pembayaran `approved`.
- DamageReport: tenant buat `pending`, owner triage `reviewing → resolved`, visibility per owner/admin/tenant, tenant hanya hapus laporan `pending` miliknya.
- Dispute: tenant buat `open`, hanya tenant pembuat yang bisa lihat; route owner/admin untuk dispute **belum terdaftar**.
- Alur dana 2 tahap:
  - `PaymentDistribution` dibuat saat pembayaran booking diverifikasi, status `pending`; baru `ready` setelah booking approved; `processed` hanya lewat `markProcessed`.
  - `Payout` boleh dicicil (`PayoutDisbursement` parsial), status `pending` sampai semua cicilan terverifikasi, lalu `verified`.

## 4. Hasil verifikasi

### 4.1 Full suite SQLite (3 run berturut-turut)

| Run | Tests | Assertions | Exit | Durasi |
|---:|---:|---:|---:|---:|
| 1 | 2410 passed | 8347 | 0 | 1046.73s |
| 2 | 2410 passed | 8347 | 0 | 1054.39s |
| 3 | 2410 passed | 8347 | 0 | 1053.70s |

Baseline sebelum penambahan test (commit `3c03dd2`): 2368 passed, 8086 assertion, 1044.81s.

### 4.2 Pint

```
{"tool":"pint","result":"passed"}
```

### 4.3 Vite build

```
vite v4.5.14 building for production...
✓ 62 modules transformed.
✓ built in 7.86s
```

## 5. Percobaan MySQL/MariaDB

- Server tersedia: MariaDB 10.11.16 (XAMPP), port 3306, root tanpa password.
- Database `kosmanager_test` berhasil dibuat.
- **Hasil: gagal** pada migrasi `database/migrations/2026_09_11_000003_add_weekly_to_rental_type_enum.php:18`
  (`CREATE TABLE "bookings_weekly_upgrade" ("id" integer primary key autoincrement, ...)`).
- Penyebab: SQL mentah spesifik SQLite (tanpa cabang driver) dipakai untuk rebuild tabel.
- Dampak: `RefreshDatabase` tidak bisa menyiapkan skema di MySQL/MariaDB, jadi suite tidak dapat dijalankan penuh di driver tersebut.
- Perbedaan driver yang tercatat:
  - SQLite: beberapa tabel (`bookings`, `kontraks`) kehilangan CHECK constraint karena rebuild migrasi; `damage_reports`/`disputes` memang string.
  - MySQL/MariaDB (bila migrasi bisa jalan): `bookings`/`kontraks`/`check_outs` tetap ENUM; perbedaan enforcement skema.
  - `users.role`, `kamar.status`, `tagihans.status`, `pembayarans.verification_status`, `check_outs.status` tetap ENUM di kedua driver.

Tidak ada perbaikan migrasi di Fase 0 (di luar scope).

## 6. Bug / perilaku aneh terkunci (bukan diperbaiki)

1. Pembayaran partial ditolak (amount harus sama dengan `tagihan.total`); tidak ada alokasi parsial. `// karakterisasi, bukan perilaku yang diinginkan`.
2. Tidak ada fitur revisi tagihan (route `*.tagihan.update` dan tabel `bill_revisions` tidak ada). `// karakterisasi, bukan perilaku yang diinginkan`.
3. Tidak ada model deposit (tabel `deposits`, kolom deposit pada `check_outs`/`kontraks` tidak ada). `// karakterisasi, bukan perilaku yang diinginkan`.
4. `super_admin` boleh masuk area owner/admin tetapi tidak boleh area tenant.
5. Kolom status `bookings`/`kontraks` di SQLite kehilangan CHECK constraint akibat rebuild migrasi (menerima nilai apa pun). `// karakterisasi, bukan perilaku yang diinginkan`.
6. `damage_reports.status` dan `disputes.status` adalah string tanpa CHECK/ENUM (validasi hanya di aplikasi). `// karakterisasi, bukan perilaku yang diinginkan`.
7. `check_outs` DB mengizinkan `completed`, tetapi model tidak punya `STATUS_COMPLETED`. `// karakterisasi, bukan perilaku yang diinginkan`.
8. `payment_method` hanya 3 kanal (`transfer_bank`, `qris`, `e_wallet`); WhatsApp/QRIS-VA dedicated belum ada (Fase 2).
9. Uang disimpan sebagai decimal, bukan integer rupiah.
10. Controller owner/admin untuk `Dispute` ada, tetapi route-nya **belum terdaftar** (hanya tenant). `// karakterisasi, bukan perilaku yang diinginkan`.
11. Migrasi `2026_09_11_000003` memakai SQL mentah SQLite sehingga MySQL/MariaDB gagal.
12. DP Rp 100.000 tetap, sisa hangus (non-refundable).
13. Penalti keterlambatan Rp 50.000 tetap, idempoten.
14. Receipt hanya tersedia setelah pembayaran `approved`.
15. Alur dana 2 tahap: `PaymentDistribution` tidak `processed` sampai booking approved; `Payout` partial disbursement perlu verifikasi.

## 7. Overlap dengan `feature/alur-dana-2-tahap`

Test berikut ditandai `#[Group('alur-dana-2-tahap')]`:
- `FundsFlowCharacterizationTest` (seluruh kelas).
- `MoneyMathCharacterizationTest::test_percent_commission_rounds_to_two_decimals`.

Saat branch `feature/alur-dana-2-tahap` di-merge, jalankan:

```bash
php artisan test --group=alur-dana-2-tahap
```

dan rekonsiliasi jika logika settlement/disbursement berubah. Jangan menghapus test karakterisasi tanpa review.

## 8. Cakupan yang belum diuji (di luar scope Fase 0)

Controller/modul lain (messaging, advertising, marketplace, CS conversation, notifikasi queue, file signed URL, audit log, assignment admin/owner, payout penuh) belum dikarakterisasi. Fase 0 hanya mencakup daftar yang disetujui.

## 9. Batas Fase 0 (dipatuhi)

- Tidak ada perubahan pada `app/`, `routes/`, `config/`, `database/migrations/`, view.
- Tidak ada perubahan pada test lama.
- Hanya penambahan `tests/Feature/Characterization/` dan `docs/baseline/FASE-0.md`.
- Tidak ada perubahan skema/role.

## 10. Langkah berikutnya

**Berhenti.** Menunggu persetujuan eksplisit sebelum memulai Fase 1 (penambahan role `technician`, RBAC, work order, dll.).
