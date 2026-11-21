# P8 Batch 1: Webhook & Payment Integrity Hardening

Dokumen ini merangkum implementasi **P8 Batch 1** — hardening keamanan payment webhook dan
penguatan integritas data pembayaran level database, tanpa mengubah business flow, API contract,
maupun lifecycle pembayaran yang sudah berjalan.

## 1. Objective

1. Menghapus secret webhook default yang dapat dipakai (`change-me-in-production`) → **fail-closed**.
2. Menambahkan rate limiting pada webhook `/webhooks/payment-gateway`.
3. Menjaga integritas pembayaran level database:
   - satu pembayaran aktif (`pending`/`approved`) per tagihan;
   - `gateway_reference` unik per provider.
4. Deduplikasi logika pembuatan pembayaran gateway ke `PaymentService`.
5. Password MySQL backup tidak lagi dikirim lewat process argv (gunakan `MYSQL_PWD`).
6. Kompatibilitas lintas DB (MySQL / PostgreSQL / SQLite-test).

## 2. Audit Findings

- **Kritis**: `config/payment-gateway.php:33` memakai default `change-me-in-production` — non-kosong
  sehingga lolos guard; `.env` tidak men-set key → secret default **aktif**. Webhook publik tanpa
  rate limiting (`routes/web.php:37`) → forgeable jika dideploy tanpa set env.
- `gateway_reference` di `migrations/2026_09_01_000100` tanpa index & tanpa unique.
- Tidak ada constraint database "satu active payment per tagihan" — hanya guard aplikasi
  (`exists()` + `lockForUpdate`).
- Logika pembuatan gateway payment duplikat di `Owner\TenantPembayaranController::gatewayStore`
  (di luar `PaymentService`).
- `MySqlBackupDriver` menyisipkan `--password=...` sebagai argument proses (`=>`argv) — bocor di
  process list. (Driver PostgreSQL sudah memakai env `PGPASSWORD`.)

## 3. Security Issues Addressed

| # | Issue | Fix |
|---|-------|-----|
| 3.1 | Secret webhook default usable | Config tanpa default + **fail-closed** di `PaymentGatewayManager::driver()` & `SandboxGateway::verifySignature` |
| 3.2 | Webhook tanpa rate limit | `throttle:20,1` pada route |
| 1.1 | Duplicate active payment satu tagihan (app-only guard) | Unique index pada kolom `active_payment_key` (APP-level dipertahankan) |
| 7.1 | `gateway_reference` tanpa index/unique | Unique `(gateway_reference, gateway_provider)` |
| 1.2 | Duplikasi logika gateway | `PaymentService::createGateway` |

## 4. Implementation

### 4.1 Fail-closed secret
- `config/payment-gateway.php`: `signature_key => env('PAYMENT_GATEWAY_WEBHOOK_KEY')` (tanpa default);
  tambah daftar `placeholder_secrets` (`change-me-in-production`).
- `PaymentGatewayManager::driver()`: menolak (throw `InvalidArgumentException`) bila secret kosong
  atau = placeholder → memproteksi **createCharge dan verifySignature** (single choke-point).
- `PaymentGatewayManager::isValidSecret()`: helper untuk lapisan verifikasi.
- `SandboxGateway::verifySignature()`: defense-in-depth menolak secret kosong/placeholder.
- `PaymentGatewayWebhookController::handle()`: menangkap fail-closed dan me-return **403**
  (bukan 500) ketika secret tidak terkonfigurasi.

### 4.2 Rate limiting
- `routes/web.php`: `POST /webhooks/payment-gateway` → `middleware('throttle:20,1')`
  (maks 20 request / 1 menit; overflow → 429). Contract & algoritma signature tidak berubah.

### 4.3 Database integrity (portabel)
Migration aditif `2026_09_02_000001_add_payment_integrity_to_pembayarans_table.php`:
1. Kolom `active_payment_key` (unsignedBigInteger, nullable) DIPELIHARA APP:
   - `= tagihan_id` saat pembayaran aktif (`pending`/`approved`);
   - `NULL` saat non-aktif (mis. `rejected`).
   Backfill data lama aktif; lalu **unique index** pada kolom nullable → satu non-NULL per tagihan,
   banyak NULL (retry/reject) diperbolehkan.
2. Unique index `(gateway_reference, gateway_provider)` → cegah duplikat referensi per provider;
   NULL distinct sehingga pembayaran manual (kedua NULL) tidak terpengaruh.

**Kompatibilitas DB**:
- Kolom & index berupa **plain nullable column + plain unique index** — tidak memakai
  generated-column/partial index yang tidak didukung SQLite. Diuji pada SQLite in-memory (test);
  bekerja identik di MySQL & PostgreSQL (unique index memperlakukan NULL sebagai distinct).

APP tetap memakai guard existing (`lockForUpdate` + cek `ACTIVE_VERIFICATIONS`) sebagai lapisan
pertama; unique index menjadi **backstop DB-level**.

### 4.4 PaymentService dedup
- `PaymentService::createGateway()`: mentransfer logika pembuatan gateway dari controller
  (transaction + lockForUpdate + cek PAYABLE + cek active payment + set `gateway_*` + set
  `active_payment_key`).
- `Owner\TenantPembayaranController::gatewayStore()` kini memakai `PaymentService`.
- `PaymentService::createManual()` set `active_payment_key = tagihan_id` saat create.
- `Owner\PembayaranController::reject()` set `active_payment_key = null` (non-aktif saat ditolak).
- Verify (manual & webhook) mempertahankan status `approved` (tetap aktif, key tidak berubah).

### 4.5 MySQL backup password
- `MySqlBackupDriver`: password dikeluarkan dari argv; disalurkan via env `MYSQL_PWD` pada proses
  (`new Process($command, null, env)`). Pemisahan `arguments()` (tanpa `--password`) & `environment()`
  (memuat `MYSQL_PWD`) dapat diuji. Perilaku backup tidak berubah selain keamanan kredensial.

## 5. Database Compatibility

- Migration memakai **plain nullable column + unique index** — portabel MySQL (5.7+) / PostgreSQL /
  SQLite. Tidak ada generated column atau partial index.
- Test DB = SQLite in-memory; seluruh migration + backfill ter-eksekusi oleh `RefreshDatabase` di
  suite. Dibuktikan test `test_integrity_indexes_exist`, `test_duplicate_active_payment...`,
  `test_duplicate_gateway_reference...`, `test_rejected_payment_can_be_retried...`.
- `gateway_reference` NULL ganda diizinkan (manual); non-NULL ganda ditolak (gateway).

## 6. Files Changed

| File | Change |
| ---- | ------ |
| `database/migrations/2026_09_02_000001_add_payment_integrity_to_pembayarans_table.php` | Baru — kolom `active_payment_key` + backfill + unique indexes |
| `config/payment-gateway.php` | Hapus default secret; tambah `placeholder_secrets` |
| `app/Services/PaymentGateway/PaymentGatewayManager.php` | Fail-closed + `isValidSecret()` |
| `app/Services/PaymentGateway/SandboxGateway.php` | Verify fail-closed (empty/placeholder) |
| `app/Http/Controllers/Webhook/PaymentGatewayWebhookController.php` | 403 saat secret invalid |
| `routes/web.php` | Throttle pada webhook |
| `app/Services/PaymentService.php` | `createGateway()`; `createManual()` set active key |
| `app/Http/Controllers/Owner/TenantPembayaranController.php` | Dedup ke `PaymentService::createGateway` |
| `app/Http/Controllers/Owner/PembayaranController.php` | Reject → `active_payment_key = null` |
| `app/Models/Pembayaran.php` | `active_payment_key` masuk `$fillable` |
| `app/Services/DatabaseBackup/MySqlBackupDriver.php` | `MYSQL_PWD` via env, tanpa `--password` di argv |
| `phpunit.xml` | Test secret untuk webhook |
| `.env.example` | Hapus placeholder; instruksi mandatory |
| `docs/PHASE_7_P7B_DEPLOYMENT.md` | Dokumentasi key mandatory + fail-closed |
| `tests/Feature/P8Batch1PaymentIntegrityTest.php` | Baru — 13 test / 28 assertion |

Tidak ada perubahan: P7-D API contract, Tenant/Owner API, notification system, booking/billing
lifecycle, payment gateway contract, webhook payload contract, UI.

## 7. Tests

Targeted (P8 Batch 1): `P8Batch1PaymentIntegrityTest` → **13 passed / 28 assertions**:
- fail-closed secret missing & placeholder (driver + webhook + sandbox verify)
- webhook rate limit (429 saat melewati batas)
- integrity indexes exist
- duplicate active payment ditolak DB-level; rejected dpt di-retry; null gateway ref diizinkan
- duplicate gateway_reference ditolak DB-level
- gateway & manual payment via service set active key + reference
- MySQL backup password tidak di argv (`MYSQL_PWD` di env)

Existing payment/security regressions (PaymentGateway, Pembayaran, ApiV1Billing, ApiV1OwnerPayment,
PaymentSecurity): semua tetap hijau.

Full suite (final, aktual):
```
Tests:  835 passed (2861 assertions)
```

## 8. Security Verification

- `[x]` Auth webhook (HMAC) — fail-closed bila secret default/placeholder
- `[x]` Rate limiting aktif (`ThrottleRequests:20,1` per `route:list -v`)
- `[x]` Integrity DB-level: unique `active_payment_key` + unique `(gateway_reference, provider)`
- `[x]` Race-condition resistance — app lock dipertahankan; DB mencegah duplikat
- `[x]` Sensitive credential — password MySQL via env (bukan argv)
- `[x]` Mass assignment — `active_payment_key` di `$fillable` (nilai APP-controlled)
- `[x]` Tidak ada secret di repo (`.env` gitignored; `.env.example` tanpa placeholder usable)

## 9. Pint

```
vendor/bin/pint --test  → PASS (style otomatis diperbaiki terlebih dahulu)
```

## 10. Routes

```
POST webhooks/payment-gateway → webhook.payment-gateway  (middleware: web, ThrottleRequests:20,1)
```
`route:cache` OK lalu `route:clear` (dev). Tidak ada route baru selain modifikasi middleware.

## 11. Build

Tidak ada perubahan frontend. `npm run build` → PASS (kontrol baseline).

## 12. Documentation

- `docs/PHASE_8_P8_BATCH1_API_PAYMENT_HARDENING.md` (ini)
- `docs/PHASE_7_P7B_DEPLOYMENT.md` — `PAYMENT_GATEWAY_WEBHOOK_KEY` **mandatory** & fail-closed
- `.env.example` — placeholder dihapus, instruksi generate nilai acak

## 13. Known Limitations

- Constraint `active_payment_key` menunggu aplikasi men-set kolom. Seluruh jalur produksi kreasi
  pembayaran (manual API/web + gateway web) sudah melalui `PaymentService` yang men-set key,
  dan reject membersihkan key. Test factory yang menyisipkan baris tanpa key tetap lolos (NULL)
  — konvensi data test.
- Rate limit berbasis cache; bila `CACHE_DRIVER` database/redis di multi-server, throttle global
  bekerja lintas node; dengan cache array (dev) limit per-request.

## 14. Out of Scope

Admin/SuperAdmin API, UI redesign, Tenant/Owner API, gateway/payment method baru, WebSocket,
backup redesign, audit-log redesign, billing calculation, auto first-tagihan, scheduler redesign,
dashboard, report redesign.

## 15. Final Assessment

**COMPLETE.** Seluruh security issue teratas di address, fail-closed berperilaku benar, integritas
level database dijamin dengan desain portabel (tanpa merusak SQLite), deduplikasi selesai, password
MySQL tidak lagi lewat argv. Regresi penuh hijau (835/2861). Tidak ada critical/high unresolved.