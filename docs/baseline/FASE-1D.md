# Fase 1D — Hardening Data (Hasil)

**Branch:** `chore/fase-0-baseline` @ `634df99`
**Status:** Selesai (4 slice). Menunggu persetujuan eksplisit sebelum Fase 2.

## Ringkasan

Fase 1D terdiri dari tiga slice additive yang tidak menghapus data legacy:

1. **Audit log** untuk perubahan status di modul legacy dan Work Order.
2. **File privat + temporary signed URL** untuk bukti pembayaran, dengan audit akses.
3. **Cutover integer rupiah** untuk lima model inti: `Kamar`, `Tagihan`, `Pembayaran`,
   `Kontrak`, `Booking`.

## Slice 1 — Audit Log

**Commit:** `b40a346` (awal) + `b6cae21` (pelengkapan)

- `DamageReportController::update` mencatat perubahan status damage report.
- `WorkOrderController::update` mencatat perubahan status untuk jalur teknisi dan
  admin/owner.
- `KamarController::update` mencatat perubahan status kamar.
- `KosController` mencatat create/update/delete kos.
- `PembayaranController` (verify/reject/bulk) dan webhook mencatat perubahan
  status Tagihan (paid / dikembalikan).
- `CheckOutController` mencatat perubahan status Kontrak (terminated/expired).
- `MarkOverdueTagihans` mencatat Tagihan yang ditandai overdue (denda).
- Menggunakan `AuditLogService` dengan rantai HMAC yang sudah ada.

**File terkait:** `app/Http/Controllers/Owner/DamageReportController.php`,
`app/Http/Controllers/Owner/WorkOrderController.php`,
`app/Http/Controllers/Owner/KamarController.php`,
`app/Http/Controllers/Owner/KosController.php`,
`app/Http/Controllers/Owner/PembayaranController.php`,
`app/Http/Controllers/Owner/CheckOutController.php`,
`app/Http/Controllers/Webhook/PaymentGatewayWebhookController.php`,
`app/Console/Commands/MarkOverdueTagihans.php`.

## Slice 2 — File Privat + Signed URL

**Commit:** `8aa0104`

- `downloadProof` mencatat audit akses file sensitif.
- `signedProofUrl` membuat temporary signed URL berlaku 15 menit.
- `downloadProofSigned` mengalirkan file melalui route bertanda tangan.
- Route `pembayaran.proof.signed` dilindungi middleware `signed`.

## Slice 4 — Production Hardening

**Commit:** `ae4396e`

- Production guard ditambahkan pada seeder demo: `DemoDataSeeder`, `MarketplaceDemoSeeder`,
  `PartnerSeeder`, `KosPhotoBackfillSeeder` (early return di `app()->environment('production')`).
- `DatabaseSeeder` sudah meng-conditional-kan demo seeders.
- Queue: `QUEUE_CONNECTION=database` (queued mail via `Mail::queue`).
- Notifikasi: in-app (`Notification` model) + email queued + push. Channel abstraction
  (WhatsApp/QRIS-VA) ditunda ke Fase 2.
- `storage/app/private` tersedia untuk bukti pembayaran.
- `AUDIT_LOG_HMAC_SECRET` documented di `.env.example` (fail-closed jika kosong).
- Cross-driver: migrasi inti (termasuk integer money) berhasil dijalankan di MySQL/MariaDB
  10.4 (fresh instance) — `WorkOrderTest`, `MoneyIntegerCutoverTest`, `TechnicianRoleTest`,
  `RbacPhase1bTest` hijau.

## Slice 3 — Cutover Integer Rupiah

**Commit:** `634df99`

### Perubahan utama

- Trait baru `app/Traits/HasIntegerRupiah.php`:
  - Accessor membaca kolom `*_rp` (integer) sebagai source of truth.
  - Fallback ke kolom decimal lama bila `*_rp` masih `null` (baris legacy).
  - Mutator menulis **kedua** kolom: integer `*_rp` dan decimal lama, sehingga
    rollback dan query agregat legacy tetap aman.
  - Hook `saving` menyinkronkan bila salah satu kolom diisi langsung
    (`forceFill`, dsb.).
  - `null` dipertahankan sebagai `null` — penting karena aturan bisnis membedakan
    "tanpa DP" (`null`) dari "DP nol" (`0`).
- Model yang memakai trait:
  - `Kamar`: `daily_price`, `weekly_price`, `monthly_price`
  - `Tagihan`: `subtotal`, `discount`, `down_payment_discount`, `penalty`,
    `total`, `total_room_bill`
  - `Pembayaran`: `amount`, `rent_amount`, `penalty_amount`, `refund_amount`
  - `Kontrak`: `rental_price`, `down_payment_amount`, `custom_share_amount`
  - `Booking`: `price`, `down_payment_amount`
- Migrasi `2026_11_28_000001_add_integer_money_columns.php`:
  - Menambah `bookings.down_payment_amount_rp`.
  - Backfill driver-aware: `CAST(... AS INTEGER)` untuk SQLite,
    `CAST(... AS SIGNED)` untuk MySQL/MariaDB.
- `SharedBillingService`:
  - Pembagian biaya bersama dihitung dalam satuan rupiah penuh (bukan sen),
    sehingga sisa pembagian dibagikan 1 rupiah per penghuni dan jumlahnya tetap
    sama dengan total tagihan kamar.
  - `validateCustomShares` memakai toleransi 1 rupiah.
  - Total tagihan dibulatkan ke satuan rupiah.

### Kompatibilitas

- Kolom decimal lama **tidak dihapus**; cutover final (drop kolom decimal) belum
  dilakukan.
- API resource tetap mengembalikan angka (`float`) sehingga kontrak JSON tidak
  berubah.
- Baris legacy tanpa `*_rp` tetap terbaca lewat fallback decimal.

## Verifikasi

- SQLite full suite: **2435 passed**, **8411 assertions**, exit 0.
- Full suite dijalankan **3x** pada kondisi final, semuanya hijau.
- Pint: passed.
- `npm run build`: passed.
- CI run #29 (`634df99`): **success**.

## File berubah

### Baru

- `app/Traits/HasIntegerRupiah.php`
- `tests/Feature/MoneyIntegerCutoverTest.php` (7 test)

### Dimodifikasi

- `app/Models/Kamar.php`
- `app/Models/Tagihan.php`
- `app/Models/Pembayaran.php`
- `app/Models/Kontrak.php`
- `app/Models/Booking.php`
- `app/Services/SharedBillingService.php`
- `database/migrations/2026_11_28_000001_add_integer_money_columns.php`
- `tests/Feature/Characterization/MoneyMathCharacterizationTest.php`
  (karakterisasi decimal → integer, perubahan disengaja)

## Catatan / risiko tersisa

1. **Tabel uang di luar lima model inti** masih memakai decimal:
   `payouts`, `payout_items`, `payout_disbursements`, `payout_adjustments`,
   `payment_distributions`, `advertising_*`, `marketplace_*`,
   `management_agreements`, `wallet_transactions`. Cutover-nya perlu slice
   tambahan (A2) bila disetujui.
2. **Kolom decimal belum di-drop.** Ini disengaja agar rollback aman.
3. **MySQL/MariaDB** belum diverifikasi end-to-end karena data dir server lokal
   corrupt (`innodb_force_recovery=2`). Migrasi memakai Schema Builder standar
   dan backfill driver-aware, tetapi verifikasi cross-driver tetap diperlukan
   sebelum production.
4. **Komisi/payout** masih memakai pembulatan 2 desimal di `SettlementService`
   dan `PayoutItem`; belum termasuk cutover ini.

## Gate berikutnya

Fase 2 — WhatsApp notification, QRIS/VA payment, cutover tabel uang lainnya
(payout/ads/marketplace/wallet), aktifkan global role scope. Butuh persetujuan eksplisit.
