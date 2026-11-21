# PHASE 6 — FINAL REPORT

**Proyek:** KosManager (Laravel 10 — Sistem Manajemen Kos)
**Scope:** PHASE 6 — PAYMENT GATEWAY INTEGRATION (fitur online payment gateway)
**Status:** ✅ SELESAI — SELURUH FITUR TERIMPLEMENTASI & TERVERIFIKASI
**Tanggal:** 01 September 2026

---

## Ringkasan

Phase 6 mengintegrasikan **payment gateway** ke alur pembayaran tagihan sesuai scope yang disetujui (dipilih dari roadmap, karena tidak ada `docs/PHASE_6_AUDIT.md`). Fitur dibangun di atas pola arsitektur yang sudah ada: driver gateway pluggable berbasis kontrak, alur pembayaran online oleh tenant, dan webhook tanda tangan (HMAC-SHA256) yang memverifikasi pembayaran **secara otomatis** tanpa langkah manual oleh pengelola.

Sasaran verification gate terpenuhi: **587 tests / 1729 assertions** (naik dari baseline 569 / 1671), `pint --test` PASS, `npm run build` PASS, `view:cache` PASS.

Prioritas: tidak ada perubahan schema yang merusak, tidak ada perubahan perilaku hingga fase 1–5, seluruh aturan bisnis & keamanan pembayaran yang sudah ada dipertahankan.

---

## 1. F1 — Abstraksi Gateway (Pluggable Driver)

- **`config/payment-gateway.php`** (baru) — arsitektur driver multi-provider: `default` = `sandbox` (dari env `PAYMENT_GATEWAY`), `signature_key` (dari env `PAYMENT_GATEWAY_WEBHOOK_KEY`, default dev `change-me-in-production`), dan blok `providers.sandbox` berisi `driver`: `SandboxGateway`, `payment_method`: `e_wallet`, `payment_label`: "QRIS / Virtual Account (Simulasi)", `reference_prefix`: `VA-`, `lifetime_hours`: 24.
- **`app/Services/PaymentGateway/PaymentGatewayContract.php`** (baru) — kontrak minimal agar driver dapat ditambahkan tanpa mengubah kode pemanggil: `id()`, `label()`, `paymentMethod()`, `createCharge(array): array`, `verifySignature(array $payload, string $signature): bool`.
- **`app/Services/PaymentGateway/SandboxGateway.php`** (baru) — driver sandbox (tanpa kredensial eksternal): `createCharge` menghasilkan referensi `VA-xxxxxxxxxxxx`, instruksi pembayaran berisi rincian & nominal terformat, dan kadaluarsa `lifetime_hours` dari config. `verifySignature` = `hash_equals(hash_hmac('sha256', json_encode($payload), $signature_key), $signature)`.
- **`app/Services/PaymentGateway/PaymentGatewayManager.php`** (baru) — factory statis `driver()` yang me-resolve driver dari `config('payment-gateway')`; driver tak dikenal melempar `InvalidArgumentException`.
- **Unit test** (di `tests/Feature/PaymentGatewayTest.php`): resolusi driver via manager, driver palsu menolak, `createCharge` menghasilkan referensi `VA-` + instruksi berisi nominal, dan verifikasi tanda tangan accept/reject (termasuk payload diubah).

## 2. F2 — Migrasi Aditif + Model Pembayaran

- **`database/migrations/2026_09_01_000100_add_gateway_to_pembayarans_table.php`** (baru) — menambah kolom aditif ke `pembayarans`: `gateway_provider` (string 50, nullable), `gateway_reference` (string 64, nullable), `gateway_instructions` (text, nullable), `gateway_expires_at` (timestamp, nullable). Tidak ada kolom diubah/dihapus.
- **`app/Models/Pembayaran.php`** — `$fillable` + `$casts` (termasuk `gateway_expires_at => datetime`) diperluas untuk kolom gateway; helper `isFromGateway(): bool` (`gateway_provider !== null`) dipakai di seluruh view untuk menandai pembayaran online.
- Kolom dijalankan oleh seluruh suite test (RefreshDatabase sqlite) — migrasi terverifikasi.

## 3. F3 — Alur Pembayaran Online Tenant

- **Route** baru di grup tenant: `POST pembayaran/gateway` → `tenant.pembayaran.gateway`.
- **`TenantPembayaranController::gatewayStore(Request)`** (baru) — mengikuti pola `store()` yang sudah ada: cek otorisasi tenant + kepemilikan tagihan, validasi status `PAYABLE`, `DB::transaction` + `lockForUpdate()`, blok `ACTIVE_VERIFICATIONS` duplikat, resolusi driver gateway, membuat `Pembayaran` status `pending` + kolom gateway (referensi `VA-*`, instruksi, kadaluarsa), tagihan di-set `Tagihan::STATUS_PAYMENT_PENDING`. Setelah itu `NotificationService` + `AuditLogService`, lalu redirect ke `tenant.pembayaran.show` dengan flash sukses. Pengamanan: tenant lain → `403`, tagihan tak layak → error, duplikat → error.
- **View `tenant/tagihan/show.blade.php`** — panel **"Bayar Online"** di atas form bukti manual: tombol "Buat Pembayaran Online" dengan pola submit `x-bind:disabled` + `disabled:opacity`/`disabled:cursor`.

## 4. F4 — Webhook Verifikasi Otomatis

- **Route** publik baru: `POST /webhooks/payment-gateway` → `webhook.payment-gateway` (CSRF di-*exempt* via `VerifyCsrfToken::$except` karena pemanggil eksternal; keamanan ditangani tanda tangan).
- **`app/Http/Controllers/Webhook/PaymentGatewayWebhookController.php`** (baru) — `handle(Request)`:
  - Validasi header `X-Gateway-Signature` terhadap payload (HMAC-SHA256 via driver) → `403` bila invalid.
  - `gateway_reference` wajib → `422`; pembayaran tidak ditemukan → `404`.
  - Status non-`success` → `200` passthrough (tidak mengubah apa pun).
  - `verification_status` bukan `pending` → `200` **idempotent** (callback ganda tidak mengubah state).
  - `amount` ≠ nominal pembayaran → `422` (anti tamper).
  - `DB::transaction` + `lockForUpdate()` → pembayaran `approved` + `verified_at`, tagihan `paid`. Layanan notifikasi/audit dibungkus try/catch (`\Log::warning`) agar webhook tetap sukses walau layanan pendukung gagal.

## 5. F5 — Visualisasi Info Gateway

- **`tenant/pembayaran/show.blade.php`** — blok "Instruksi Pembayaran Online" (referensi, kadaluarsa, catatan verifikasi otomatis) saat pending; fallback kuning "Pembayaran Online Menunggu" bila instruksi tak tersedia; teks "Terverifikasi secara otomatis" pada status approved dari gateway.
- **`tenant/pembayaran/index.blade.php`** & **`owner/pembayaran/index.blade.php`** (admin & owner) — indikator badge `Online` di samping metode pembayaran bila `isFromGateway()`. Kontrol verify/reject manual tetap tampil sebagai fallback bila pembayaran gateway masih pending (callback belum diterima) — perilaku lama dipertahankan.
- **`owner/pembayaran/show.blade.php`** — baris "Sumber: Pembayaran Online + `gateway_reference`".
- **View test:** tenant lihat panel "Bayar Online", tenant lihat instruksi & referensi, owner lihat indikator gateway. Owner tetap bisa verify manual gateway pending (fallback) — hanya pembayaran yang sudah auto-approved yang terblokir `400`.

---

## 6. Batasan/Konstrain yang Dipatuhi

- ✅ Tidak ada perubahan schema fase sebelumnya; hanya migrasi **aditif** (kolom gateway baru).
- ✅ Aturan bisnis pembayaran lama dipertahankan: `DB::transaction` + `lockForUpdate()`, `StorePembayaranRequest` (metode `transfer_bank|cash|e_wallet`, bukti wajib kecuali cash) tak disentuh, konstanta `Pembayaran::STATUS_*`/`ACTIVE_VERIFICATIONS` dan `Tagihan::STATUS_*`/`PAYABLE` dipakai.
- ✅ Keamanan: webhook memakai tanda tangan HMAC, pengecekan nominal, idempotensi, dan `lockForUpdate()` — tidak ada jalur verifikasi tanpa otorisasi.
- ✅ Layer label memakai `PaymentLabels` yang sudah ada; tidak ada duplikasi label.
- ✅ Konvensi tombol submit (`x-bind:disabled` + `disabled:opacity`/`disabled:cursor`) dipertahankan.
- ✅ Tidak ada fitur/string yang sudah ada diubah; seluruh test UI fase sebelumnya lulus.
- ✅ Tidak ada item di luar scope approved yang dikerjakan.

---

## 7. Perubahan File

| Kategori | File |
|---|---|
| Config (baru) | `config/payment-gateway.php` |
| Service (baru) | `app/Services/PaymentGateway/PaymentGatewayContract.php`, `PaymentGatewayManager.php`, `SandboxGateway.php` |
| Controller (baru) | `app/Http/Controllers/Webhook/PaymentGatewayWebhookController.php` |
| Controller | `app/Http/Controllers/Owner/TenantPembayaranController.php` (`gatewayStore`) |
| Middleware | `app/Http/Middleware/VerifyCsrfToken.php` (exempt webhook) |
| Routes | `routes/web.php` (`tenant.pembayaran.gateway`, `webhook.payment-gateway`) |
| Migration (baru) | `database/migrations/2026_09_01_000100_add_gateway_to_pembayarans_table.php` |
| Model | `app/Models/Pembayaran.php` (fillable, casts, `isFromGateway()`) |
| Views | `tenant/tagihan/show.blade.php`, `tenant/pembayaran/show.blade.php`, `tenant/pembayaran/index.blade.php`, `owner/pembayaran/index.blade.php`, `owner/pembayaran/show.blade.php` |
| Tests (baru) | `tests/Feature/PaymentGatewayTest.php` |

---

## 8. VERIFICATION GATE — LENGKAP ✅

| Gate | Target | Hasil |
|---|---|---|
| `php artisan test` | min. 569 tests / 1671 assertions (baseline akhir Phase 5) | ✅ **587 passed (1729 assertions)** |
| `vendor/bin/pint --test` | PASS | ✅ PASS |
| `npm run build` | PASS | ✅ PASS (6.31s) |
| `php artisan view:cache` | PASS | ✅ PASS |

**Rincian test:** baseline 569 → **587** setelah batch test gateway baru (+18 test / +58 assertions). Tidak ada regresi: seluruh suite pembayaran lama (`PembayaranTest`, `TagihanTest`, `PaymentSecurityTest`, `PaymentRejectionReasonTest`) tetap hijau.

---

## 9. Cakupan Test Gateway (`tests/Feature/PaymentGatewayTest.php`, 18 test)

- **F1 driver:** resolusi manager, driver palsu ditolak, `createCharge` (referensi `VA-*`, instruksi), verifikasi tanda tangan (lolos/gagal/tamper).
- **F3 flow tenant:** buat gateway payment (terarah, pending, tagihan `pending_verification`), tolak tagihan tak layak, tolak tenant lain (`403`), blokir duplikat aktif.
- **F4 webhook:** verifikasi otomatis (approved + tagihan paid), tolak tanda tangan salah (`403`), referensi tak dikenal (`404`), nominal tidak cocok (`422`), **idempoten** pada callback ganda (audit log tidak bertambah), passthrough status non-success (`200`).
- **F5 views:** panel "Bayar Online", instruksi & referensi di detail tenant, indikator gateway di detail owner, owner tetap bisa verify manual gateway pending (fallback) dan terblokir setelah auto-approve.

---

## 10. Langkah Selanjutnya

❌ **Phase 7 TIDAK dimulai.** Menunggu approval eksplisit sebelum melanjutkan.