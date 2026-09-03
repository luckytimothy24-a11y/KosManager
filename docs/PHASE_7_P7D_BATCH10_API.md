# P7-D Batch 10: API Resource Contract (Owner Billing & Payment API — READ-ONLY)

Dokumen ini merangkum implementasi **P7-D Batch 10** — satu endpoint **read-only** untuk **owner**
(melihat pembayaran yang terkait dengan tagihan properti miliknya) di atas fondasi Batch 1–9
(auth Sanctum + role + resource + pagination + error) dan **domain billing/payment existing**.
Tidak ada mutation; billing/payment lifecycle, nominal, status, dan gateway tidak diubah.

## 1. Objective

Menutup gap terakhir API sisi owner terkait billing/payment:
1. owner sudah dapat melihat tagihan kontrak miliknya (Batch 9);
2. **owner dapat melihat pembayaran yang terkait dengan tagihan (milik kontraknya)** — hal ini sebelumnya
   hanya tersedia untuk tenant (Batch 4 `/tagihan/{tagihan}/pembayaran`, `role:tenant`).

## 2. Audit Findings

- **Gap teridentifikasi**: Satu-satunya endpoint pembayaran yang ada adalah
  `GET /api/v1/tagihan/{tagihan}/pembayaran` (Batch 4) yang dibatasi `role:tenant` di
  `BillingController::__construct` → owner **tidak punya akses melihat pembayaran** lewat API.
- **Model** `Pembayaran` (`app/Models/Pembayaran.php`): relasi `tagihan()`, `penghuni()`;
  casts `payment_date` (date), `amount` (decimal:2), `verified_at` (datetime).
- **Ownership**: `Pembayaran.tagihan_id → Tagihan → Kamar → Kos.owner_id` (owner via `Tagihan`).
- **Policy** `TagihanPolicy::view` (`app/Policies/TagihanPolicy.php`): owner via
  `$tagihan->kamar->kos->owner_id === $user->id` → **default deny**.
- **Policy** `PembayaranPolicy::view`: owner via `penghuni->kamar->kos->owner_id === $user->id` → default deny.
- **Resource existing** `PembayaranResource` (Batch 4) — **di-reuse untuk owner**: hanya mengekspos
  `proof_available` (boolean), **bukan** jalur storage `proof_file`; tidak mengekspos verifier/data tenant lain.

## 3. Ownership Model

```
Owner ─→ Kos ─→ Kamar ─→ Tagihan ─→ Pembayaran
                             (via tagihan.kamar.kos.owner_id)
```

Owner hanya dapat melihat pembayaran dari tagihan pada kamar yang kos-nya memilikinya.
Jalur konkret: **authorize tagihan dulu** (`TagihanPolicy::view`), lalu `$tagihan->pembayarans()`
(nested security). Pembayaran tagihan milik owner lain → `403`.

## 4. Locked Endpoints

| Method | Endpoint                                                          | Auth                        | Status |
| ------ | ----------------------------------------------------------------- | --------------------------- | ------ |
| GET    | `/api/v1/owner/tagihan/{tagihan}/pembayaran`                      | `auth:sanctum + role:owner` | ✅ |

Route name: `api.v1.owner.tagihan.pembayaran.index`.

> Endpoint `GET /api/v1/owner/kontrak/{kontrak}/tagihan` (daftar tagihan kontrak) sudah tersedia dari
> Batch 9 → **TIDAK dibuat duplikat**.

## 5. Authentication

- `auth:sanctum` (constructor controller). Tanpa/token rusak → `401 {"message":"Unauthenticated."}`.
- Hanya owner. Role lain → `403`.

## 6. Authorization

- `role:owner` → tenant/admin/super_admin → `403`.
- Detail: `authorize('view', $tagihan)` via `TagihanPolicy::view` (owner via `kamar->kos->owner_id`).
- Tagihan milik owner lain → `403`.
- Tagihan tidak ada → `404` (route-model binding).

## 7. Resource Contract

### `GET /api/v1/owner/tagihan/{tagihan}/pembayaran`

Return: `PembayaranResource::collection` (pagination). Field per item (reuse `PembayaranResource`):

| Field                | Type    | Keterangan                                        |
| -------------------- | ------- | ------------------------------------------------- |
| `id`                 | int     | ID pembayaran                                     |
| `payment_number`     | string  | Nomor pembayaran                                  |
| `tagihan_id`         | int     | ID tagihan terkait                                |
| `amount`             | float   | Nominal pembayaran                                |
| `payment_date`       | string? | Tanggal bayar (`Y-m-d`)                           |
| `payment_method`     | string? | Metode bayar                                      |
| `verification_status`| string  | `pending/approved/rejected`                       |
| `paid_at`            | string? | Timestamp verifikasi (`Y-m-d H:i:s`)              |
| `admin_notes`        | string? | Catatan verifikasi                                |
| `gateway_provider`   | string? | Provider gateway (nominal, bukan secret)          |
| `proof_available`    | bool    | Hanya ada/tidaknya bukti — **bukan** jalur storage |

Pagination mengikuti konvensi API (Batch 2–9): default `per_page=15`, cap maks 50, urutan `latest()`.

Nama paket JSON: `data` (array), `links`, `meta` — konsisten API existing.

## 8. Error Contract

| Kondisi                                | Status | Body                              |
| -------------------------------------- | ------ | --------------------------------- |
| Tidak/token rusak                      | 401    | `{"message":"Unauthenticated."}`  |
| Role selain owner                      | 403    | `{"message":"This action is unauthorized."}` |
| Tagihan milik owner lain               | 403    | `{"message":"This action is unauthorized."}` |
| Tagihan tidak ada                      | 404    | `{"message":"Resource not found."}` |

## 9. File Changes

**New**
- `app/Http/Controllers/Api/V1/Owner/OwnerTagihanController.php` — method `pembayaran()` (read-only).
- `tests/Feature/ApiV1OwnerPaymentTest.php` — 9 tests / 37 assertions.

**Modified**
- `routes/api.php` — register `api.v1.owner.tagihan.pembayaran.index`.

**Reused (no change)**
- `PembayaranResource`, `TagihanPolicy`, `Pembayaran`, `Tagihan`, `PembayaranFactory`.

## 10. Verification

- `php artisan test` → **822 passed (2833 assertions)** — termasuk `ApiV1OwnerPaymentTest` (9 passed / 37 assertions).
- `vendor/bin/pint --test` → PASS.
- `route:list --path=api/v1/owner` → 10 owner routes (B7: 4, B8: 2, B9: 3, B10: 1) termasuk `api.v1.owner.tagihan.pembayaran.index`.
- `route:cache` → OK, lalu `route:clear` untuk dev.
- `npm run build` → PASS (tidak ada perubahan frontend).

Owner route names (final, 10 routes):
`api.v1.owner.kos.index/show/kamar`, `api.v1.owner.dashboard.show`, `api.v1.owner.bookings.index/show`,
`api.v1.owner.kontrak.index/show/tagihan`, `api.v1.owner.tagihan.pembayaran.index`.

## 11. Constraints Respected

- READ-ONLY (satu endpoint `GET`).
- Tidak ada endpoint mutation baru.
- Lifecycle pembayaran / nominal / status / payment gateway / notification system TIDAK diubah.
- Tidak membuat CRUD Kos/Kamar, API Admin/SuperAdmin.
- Tenant API (Batch 3–6) CLOSED, tidak diubah.
- Tidak ada WebSocket/realtime, tidak ada refactor besar di luar kebutuhan Batch 10.