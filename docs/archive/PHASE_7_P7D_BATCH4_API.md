# P7-D Batch 4: API Resource Contract (Tenant Billing & Payment Flow)

Dokumen ini merangkum implementasi **P7-D Batch 4** — empat endpoint resource untuk alur
**tagihan & pembayaran tenant** yang mengikuti kontrak JSON LOCKED, di atas fondasi Batch 1–3
(auth Sanctum + resource + pagination + error).

## 1. Objective

Menyediakan API agar tenant dapat: melihat daftar tagihan miliknya, melihat detail tagihan,
mengunggah bukti pembayaran, dan melihat status pembayaran/tagihan — seluruhnya memakai
**business logic domain existing** (diadaptasikan, tidak mengarang aturan baru).

## 2. Locked Scope

Batch 4 hanya berisi **4 endpoint** tenant billing & payment (disetujui). Tidak ada endpoint lain.

| Method | Endpoint                          | Auth                  | Fungsi                                  |
| ------ | --------------------------------- | --------------------- | --------------------------------------- |
| `GET`  | `/api/v1/tagihan`                 | `auth:sanctum` + role tenant | List tagihan tenant terautentikasi |
| `GET`  | `/api/v1/tagihan/{tagihan}`       | `auth:sanctum` + role tenant | Detail tagihan tenant            |
| `POST` | `/api/v1/tagihan/{tagihan}/pembayaran` | `auth:sanctum` + role tenant | Upload/submit bukti pembayaran   |
| `GET`  | `/api/v1/tagihan/{tagihan}/pembayaran` | `auth:sanctum` + role tenant | Status pembayaran tagihan        |

Semua respons `Content-Type: application/json`. Koleksi kosong tetap `200 OK`, bukan `404`.

## 3. Authentication

- `auth:sanctum` → wajib token Sanctum valid; tanpa/token rusak → `401 {"message":"Unauthenticated."}`.
- Dibuat di constructor controller: `middleware('auth:sanctum')` + `middleware('role:tenant')`.

## 4. Authorization

- `role:tenant` → hanya role `tenant` (owner/admin/super_admin ditolak `403`).
- Kepemilikan tagihan tenant ditentukan lewat relasi `Tagihan → penghuni → user_id`.
- `show`, `paymentStore`, `paymentShow` memakai **policy existing** `TagihanPolicy::view`
  (cabang tenant: `penghuni->user_id === user->id`). Akses tagihan tenant lain → `403`.
- Pembayaran dibuat hanya untuk tagihan milik tenant aktif (`PaymentService::resolveOwnTagihan` +
  `authorize('view', $tagihan)`); tenant lain → `403`.
- **Tidak ada authorization baru** — reuse `TagihanPolicy`/`PembayaranPolicy` yang sudah ada.

## 5. Endpoint Table

Lihat tabel di bagian **2. Locked Scope**. Route names: `api.v1.tagihan.index/show/.*pembayaran.store/show`.

## 6. Request Contracts

### `POST /api/v1/tagihan/{tagihan}/pembayaran`

Body (field sama dengan `StorePembayaranRequest` existing, minus `tagihan_id` karena terikat route):

```json
{
  "amount": 1500000,
  "payment_method": "transfer_bank",
  "proof_file": "<file upload>"
}
```

| Field          | Required | Rule                                             |
| -------------- | -------- | ------------------------------------------------ |
| `amount`       | ya       | numeric, `min:1`                                 |
| `payment_method` | ya     | `in: transfer_bank, cash, e_wallet`              |
| `proof_file`   | kondisional | wajib bila `payment_method !== cash`; `file`; `mimes: jpg,jpeg,png,pdf`; `max:5120` (KB) |

Aturan bisnis tambahan (reuse web):
- `amount` harus sama dengan `tagihan.total` (dibulatkan ke 2 desimal) → selain itu `422 errors.amount`.
- Tagihan harus berstatus **PAYABLE** (`unpaid`/`overdue`); paid/terminal/menunggu verifikasi → `422`.
- Melarang **pembayaran ganda aktif** (ada pembayaran `pending`/`approved` lain) → `422`.

## 7. Success Response Contracts

### List tagihan — `{"data": [...], "meta": {...}}`

Pagination konsisten Batch 2: default `per_page=15`, cap maks `50`.

`TagihanResource` field:
| Field          | Sumber existing                |
| -------------- | ------------------------------ |
| `id`           | `tagihan.id`                   |
| `bill_number`  | `tagihan.bill_number`          |
| `bill_type`    | `tagihan.bill_type`            |
| `kos_id`       | `tagihan.kamar.kos_id`         |
| `kamar_id`     | `tagihan.kamar_id`             |
| `period_start` | `tagihan.period_start` (date)  |
| `period_end`   | `tagihan.period_end` (date)    |
| `subtotal`     | `tagihan.subtotal` (float)     |
| `discount`     | `tagihan.discount` (float)     |
| `penalty`      | `tagihan.penalty` (float)      |
| `total`        | `tagihan.total` (float)        |
| `due_date`     | `tagihan.due_date` (date)      |
| `status`       | `tagihan.status` enum existing |
| `pembayaran`   | koleksi `PembayaranResource` (when loaded) |

### Detail tagihan — `{"data": {...}}` (sama shape, `pembayarans` diload)

### Submit pembayaran — `201 {"data": {tagihan}, "message": "Bukti pembayaran berhasil diupload. Menunggu verifikasi."}`

Status tagihan berubah ke `pending_verification`.

### Status pembayaran — `{"data": {...}}`

`PembayaranResource` field:
| Field                | Sumber existing                    |
| -------------------- | ---------------------------------- |
| `id`                 | `pembayaran.id`                    |
| `payment_number`     | `pembayaran.payment_number`        |
| `tagihan_id`         | `pembayaran.tagihan_id`            |
| `amount`             | `pembayaran.amount` (float)        |
| `payment_date`       | `pembayaran.payment_date` (date)   |
| `payment_method`     | `pembayaran.payment_method`        |
| `verification_status`| `pending`/`approved`/`rejected`    |
| `paid_at`            | `verified_at` (datetime, nullable) |
| `admin_notes`        | `admin_notes` (alasan, nullable)   |
| `gateway_provider`   | `gateway_provider` (nullable)      |
| `proof_available`    | `static bool`: ada bukti (bukan path) |

Tidak ada pembayaran → `404 {"message":"Resource not found."}`.

## 8. Error Contracts

- `401` → `{"message":"Unauthenticated."}`
- `403` → `{"message":"This action is unauthorized."}`
- `404` → `{"message":"Resource not found."}`
- `422` → `{"message":"The given data was invalid.","errors":{field:[msg]}}` (validasi OR konflik bisnis)
- `500` (production) → `{"message":"Server Error."}` — tanpa stack trace/SQL/kredensial/path
- Error handling web **tidak diubah**.

## 9. Payment-Proof Upload Rules

- Field name: `proof_file`.
- Disk: default `local` → `storage/app/bukti-pembayaran` (**private**, tidak ada URL publik).
- Ketentuan sama persis dengan web (`StorePembayaranRequest`): `proof_file` **opsional hanya untuk `cash`**;
  wajib untuk `transfer_bank`/`e_wallet`; MIME `jpg/jpeg/png/pdf`; ukuran maks `5120` KB.
- **Tidak memperlebar** accepted file types.
- **Tidak mengekspos** filesystem path di respons (hanya `proof_available` bool).
- `cash` tidak mengunggah bukti (konsisten dengan web).
- Tidak ada penambahan payment gateway baru; reuse alur upload manual yang ada.

## 10. Business-Rule Mapping

Seluruh aturan memakai logika web existing (`TenantPembayaranController`) yang kini dikapsulkan ke
`PaymentService::createManual`:
- transaksi DB + `lockForUpdate` pada tagihan (cegah race),
- cek status tagihan `PAYABLE`,
- cek duplikasi pembayaran aktif (`ACTIVE_VERIFICATIONS` = pending/approved),
- jumlah = `total` (rounded),
- simpan bukti privat, delete bukti bila persistensi gagal,
- set tagihan → `pending_verification`,
- notification + audit (dipanggil controller setelah sukses, sama seperti web).

## 11. Security / Concurrency

- Ownership dijamin `TagihanPolicy::view` + `role:tenant` + `PaymentService::resolveOwnTagihan`.
- Duplicate payment dicegah dalam transaksi dengan `lockForUpdate` + guard `ACTIVE_VERIFICATIONS`.
- Tagihan terminal (paid/pending/cancelled) tidak dapat dibayar.
- Pembayaran tidak dapat diubah tenant lain; verifikasi pembayaran tetap otomatis/manual sesuai web
  (tidak dibuka via API).
- Tidak ada bypass authorization existing.

## 12. Files Created

- `app/Http/Controllers/Api/V1/BillingController.php` — controller API (index/show/paymentStore/paymentShow).
- `app/Http/Resources/TagihanResource.php` — transformer tagihan.
- `app/Http/Resources/PembayaranResource.php` — transformer pembayaran (tanpa path sensitif).
- `app/Http/Requests/Api/V1/StorePaymentRequest.php` — validasi upload (reuse rule web).
- `app/Services/PaymentService.php` — shared submit-payment business logic (web + API).
- `tests/Feature/ApiV1BillingTest.php` — suite Batch 4 (31 tes).
- `docs/PHASE_7_P7D_BATCH4_API.md` — dokumen ini.

## 13. Files Modified

- `app/Http/Controllers/Owner/TenantPembayaranController.php` — `store()` mendelegasikan ke
  `PaymentService::createManual` (perilaku web tidak berubah; diverifikasi regresi).
- `routes/api.php` — 4 route Batch 4.

## 14. Tests

`tests/Feature/ApiV1BillingTest.php` — 31 tes:
- **Auth:** list/detail/payment unauthenticated → 401.
- **Authorization:** tenant dapat lihat tagihan sendiri; tidak dapat lihat tagihan tenant lain (403);
  tidak dapat submit pembayaran untuk tagihan tenant lain (403) + tidak ada pembayaran dibuat;
  role owner ditolak (403).
- **Listing:** koleksi milik sendiri; pagination default 15 & cap 50; koleksi kosong `200`;
  absen field sensitif.
- **Detail:** existing → 200; missing → 404.
- **Payment:** submit sukses `201` + state DB (pembayaran pending, tagihan pending_verification,
  bukti tersimpan di disk private); cash tanpa bukti; validasi amount required; salah jumlah 422;
  method invalid 422; wajib bukti untuk transfer 422; tipe file invalid 422; ukuran >5MB 422;
  tagihan terminal 422; duplikasi pembayaran aktif 422.
- **Payment show:** contract shape; 404 bila belum ada; deny tenant lain; absen field sensitif
  (`proof_file`, verifier, gateway_reference/instructions, dll).

## 15. Verification

- `php artisan test --filter=ApiV1BillingTest` → **31 passed (136 assertions)**.
- `php artisan test --filter="ApiV1|PembayaranTest|TagihanTest"` → **120 passed (564 assertions)**
  (Batch 1–4 + web billing/payment regression).
- `php artisan test` (full suite) → **724 passed (2367 assertions)**.
- `vendor/bin/pint --test` → **PASS**; `php artisan route:cache` → **ok** (route dikembalikan setelah
  `route:clear` untuk dev); `npm run build` → **PASS**.
- Rule dari Batch 1/2: selalu `route:clear` + `config:clear` sebelum run test (config cache mengganggu
  `DB_DATABASE=:memory:`).

## 16. Out of Scope (tidak dikerjakan)

- `/api/v1/kontrak`, `/api/v1/notifications`, `/api/v1/users`.
- Owner API, admin API, superadmin API.
- WebSocket / realtime notification.
- Payment gateway baru (tidak dibuat).
- Mobile frontend, dashboard baru.
- Master data, deployment/container, database backup.
- Endpoint lain selain 4 locked endpoints Batch 4.