# P7-D Batch 3: API Resource Contract (Tenant Booking Flow)

Dokumen ini merangkum implementasi **P7-D Batch 3** — empat endpoint resource untuk alur
**tenant booking (pemesanan kamar kos)** yang mengikuti kontrak JSON LOCKED, di atas fondasi
Batch 1 (auth token Sanctum) dan konvensi Batch 2 (resource + pagination + error).

## Ringkasan

- **Status:** Selesai (implementasi + dokumentasi). Scope terkunci pada **alur booking tenant**.
- **Tujuan:** Membuktikan kontrak JSON untuk daftar booking, detail booking, buat booking, dan
  batal booking — dengan struktur field, HTTP status, pagination, dan penanganan error yang seragam.
- **Field stability:** Semua field diadaptasikan ke domain `Booking`/`Kos`/`Kamar` existing tanpa
  mengubah kontrak semantik. Tidak ada field yang dikarang, tidak ada kolom DB baru.
- **Business rules:** Seluruh aturan bisnis booking **dipakai ulang** (diekstrak ke
  `BookingService`) dari alur web existing — tidak ada perilaku domain baru yang diperkenalkan khusus API.

## Endpoint Batch 3 (locked)

| Method | Endpoint                        | Auth                  | Fungsi                                   |
| ------ | ------------------------------- | --------------------- | ---------------------------------------- |
| `GET`  | `/api/v1/bookings`              | `auth:sanctum` + role tenant | Daftar booking milik tenant terautentikasi |
| `GET`  | `/api/v1/bookings/{booking}`    | `auth:sanctum` + role tenant | Detail booking milik tenant            |
| `POST` | `/api/v1/bookings`              | `auth:sanctum` + role tenant | Buat booking baru (instant-approved)    |
| `POST` | `/api/v1/bookings/{booking}/cancel` | `auth:sanctum` + role tenant | Batalkan booking milik tenant        |

Semua respons `Content-Type: application/json`. Koleksi kosong tetap `200 OK`, bukan `404`.

> Catatan scope: Endpoint lain di domain yang ditunda Batch 1 (tagihan, pembayaran, kontrak,
> notifications, users) **tidak** termasuk scope Batch 3 dan tidak dibuat.

## Otorisasi

- Middleware controller: `auth:sanctum` → wajib token SanCloud valid; `role:tenant` → hanya role
  `tenant` (owner/admin ditolak `403`).
- `GET {booking}` dan `POST {booking}/cancel` menggunakan **policy existing**
  (`BookingPolicy::view` / `BookingPolicy::cancel`) sehingga tenant tidak dapat membaca/membatalkan
  booking milik tenant lain (`403`).
- Booking dengan status terminal (`cancelled`, `completed`, `expired`) **tidak** dapat dibatalkan:
  `BookingPolicy::cancel` menolak → `403`.

## Business Rules (dipakai ulang dari alur web)

`BookingService` mengkapsulasi logic eksisting yang sama dengan `TenantBookingController` web:

- Create dalam **transaksi DB** dengan `lockForUpdate` pada kamar (cegah race condition).
- Validasi periode: cek overlap terhadap booking lain yang sudah ada pada kamar yang sama.
  Jika overlap → `422` (`conflict`).
- Harga (kolom `price`) diambil dari `kamar.monthly_price`/`daily_price` sesuai `rental_type`.
  Jika harga tidak tersedia → `422` (`no_price`).
- Booking **instant-approved**: status `approved`; kamar di-set `booked` setelah booking dibuat.
- Jika kamar sudah tidak tersedia → `422` (`kamar tidak tersedia`).
- Notification (pelanggan) + AuditLog dipanggil sama seperti alur web.
- Cancel mengembalikan kamar ke `available` hanya jika tidak ada booking approved lain dgn
  `end_date >= today()` untuk kamar tersebut.

### Pemetaan status error create → HTTP 422

| Kode internal | Pesan `errors.kamar_id[0]`            | HTTP |
| ------------- | ------------------------------------- | ---- |
| `conflict`    | `Kamar sudah dibooking pada periode tersebut.` | `422` |
| `no_price`    | `Harga kamar tidak tersedia.`          | `422` |
| default       | `Kamar tidak tersedia untuk dibooking.` | `422` |

## Request Contract

### `POST /api/v1/bookings`

```json
{
  "kos_id": 1,
  "kamar_id": 5,
  "start_date": "2026-09-10",
  "end_date": "2026-11-10",
  "rental_type": "monthly",
  "notes": "Mau kamar kosong"
}
```

Validasi (reuse `StoreBookingRequest`):
- `kos_id` required, exists, `active`.
- `kamar_id` required, exists di dalam kos terkait.
- `start_date` required, date, `after_or_equal: today`.
- `end_date` required, date, `after: start_date`.
- `rental_type` required, `in: daily, monthly`.
- `notes` nullable, string, max 1000.

Pelanggaran validasi → `422` `{"message":"The given data was invalid.","errors":{...}}`.

## Response Contract (ringkas)

- Sukses koleksi: `{"data": [...], "meta": {current_page, per_page, last_page, total}}` — pagination
  default `per_page=15`, cap maks `50` (konvensi Batch 2).
- Sukses tunggal (detail): `{"data": {...}}`.
- Store sukses: `201` `{"data": {...}, "message": "Booking berhasil! Kamar telah dipesan untuk Anda."}`
- Cancel sukses: `200` `{"data": {...}, "message": "Booking berhasil dibatalkan."}`
- `401` → `{"message": "Unauthenticated."}`
- `403` → `{"message": "This action is unauthorized."}`
- `404` → `{"message": "Resource not found."}`
- `422` → `{"message": "The given data was invalid.", "errors": {field: [msg]}}`
- `500` (production) → `{"message": "Server Error."}` — tanpa stack trace / SQL / kredensial / path

### Field `BookingResource` (listable & detail)

| Field          | Sumber existing                         | Catatan                             |
| -------------- | --------------------------------------- | ----------------------------------- |
| `id`           | `booking.id`                            |                                    |
| `booking_code` | `booking.booking_code`                  | kode booking unik                   |
| `kos`          | relasi `booking.kos`                    | objek publik (id, name, slug, address, city, photo) |
| `kamar`        | relasi `booking.kamar`                  | objek publik (id, name, type, photo) |
| `booking_date` | `booking.booking_date`                  | tanggal booking                     |
| `start_date`   | `booking.start_date`                    | periode mulai                       |
| `end_date`     | `booking.end_date`                      | periode selesai                     |
| `rental_type`  | `booking.rental_type`                   | `daily`/`monthly`                   |
| `price`        | `booking.price`                         | harga hasil perhitungan             |
| `status`       | `booking.status` enum existing          | pending/approved/rejected/cancelled/completed/expired |
| `notes`        | `booking.notes`                         | catatan tenant (nullable)           |

Field sensitif/relasi batin (`user`, `user_id`, `kos_id`, `kamar_id`, `created_at`,
`updated_at`, `deleted_at`, `payment`, `owner`) **tidak** diekspos.

## Files: Created

- `app/Http/Controllers/Api/V1/BookingController.php` — controller API (index/show/store/cancel).
- `app/Http/Resources/BookingResource.php` — transformer respons booking.
- `app/Services/BookingService.php` — shared create/cancel business logic (dipakai ulang web + API).
- `tests/Feature/ApiV1BookingTest.php` — suite test Batch 3 (27 tes).
- `docs/PHASE_7_P7D_BATCH3_API.md` — dokumen ini.

## Files: Modified

- `app/Http/Controllers/Owner/TenantBookingController.php` — `store()`/`cancel()` kini mendelegasikan
  ke `BookingService` (perilaku web tidak berubah; diverifikasi regresi).
- `routes/api.php` — daftarkan 4 route `/api/v1/bookings*`.

## Tests

- `tests/Feature/ApiV1BookingTest.php` — 27 tes mencakup: auth `401`, role `403`, cross-tenant `403`,
  validasi `422` (required, enum, tanggal, kos invalid), occupied room `422`, overlap `422`,
  create sukses `201` + state DB (kamar `booked`, `price` benar), harga sesuai `rental_type`,
  detail `200`, `404`, koleksi kosong `200`, pagination default 15 & cap 50, pembatalan sukses `200`
  (+ kamar `available`), penolakan cancel terminal `403`, dan absen field sensitif.
- **Regresi:** seluruh suite existing tetap lulus setelah refactor — `112 passed (516 assertions)`
  untuk filter `ApiV1|BookingTest|BusinessFlowTest|PhaseGBookingExperienceTest`.

## Verification

- `php artisan route:clear` + `php artisan config:clear` **sebelum** semua run (config:cache mengganggu
  `DB_DATABASE=:memory:` pada phpunit — rule dari Batch 1/2).
- `php artisan test --filter=ApiV1BookingTest` → `27 passed (133 assertions)`.
- `php artisan test --filter="ApiV1|BookingTest|BusinessFlowTest|PhaseGBookingExperienceTest"`
  → `112 passed (516 assertions)`.
- `vendor/bin/pint` → `passed`; `php artisan route:cache` → `Routes cached successfully`; lalu
  `php artisan route:clear` dikembalikan (dev).

## Out of Scope (tidak dibuat)

- Aktifitas web/owner untuk booking (approve/reject, check-in/out) — sudah ada, tidak diubah.
- Endpoint tagihan, pembayaran, kontrak, notifications, users — domain Batch 4+.
- Tidak ada migrasi/kolom DB baru.