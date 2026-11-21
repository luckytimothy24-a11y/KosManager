# P7-D Batch 2: API Resource Contract (Marketplace & Favorites)

Dokumen ini merangkum implementasi **P7-D Batch 2** — lima endpoint resource yang mengikuti kontrak
JSON LOCKED untuk marketplace kos, kamar, dan favorites, di atas fondasi Batch 1 (auth token Sanctum).

## Ringkasan

- **Status:** Selesai (implementasi + dokumentasi). Scope terkunci pada 5 endpoint sesuai kontrak.
- **Tujuan:** Membuktikan kontrak JSON resource (kos listing, kos detail, kamar, favorites, toggle)
  dengan struktur field, tipe data, HTTP status, pagination, dan penanganan error yang seragam.
- **Field stability:** Semua adaptasi field mengikuti aturan kontrak — *adaptasikan value/field ke
  domain existing tanpa mengubah kontrak semantik*. Tidak ada field yang dikarang.

## Endpoint Batch 2 (locked)

| Method | Endpoint                        | Auth          | Fungsi                                   |
| ------ | ------------------------------- | ------------- | ---------------------------------------- |
| `GET`  | `/api/v1/kos`                   | Public        | Daftar kos aktif (marketplace listing)   |
| `GET`  | `/api/v1/kos/{kos}`             | Public        | Detail kos aktif                         |
| `GET`  | `/api/v1/kos/{kos}/kamar`       | Public        | Daftar kamar milik kos                   |
| `GET`  | `/api/v1/favorites`             | `auth:sanctum` + role tenant | Daftar favorit tenant terautentikasi |
| `POST` | `/api/v1/favorites/{kos}/toggle`| `auth:sanctum` + role tenant | Tambah/hapus favorit → state akhir DB |

Semua respons `Content-Type: application/json`. Koleksi kosong tetap `200 OK`, bukan `404`.

## Adaptasi Field ke Domain Existing

Kontrak menyebut field yang tidak ada persis di schema DB. Adaptasi dilakukan tanpa mengubah kontrak
semantik dan **tanpa menambah kolom database**:

| Field Kontrak         | Sumber existing                                     |
| --------------------- | --------------------------------------------------- |
| `slug`                | Tidak ada di DB → `null` (kontrak mengizinkan)      |
| `city`                | Tidak ada di DB → `null` (kontrak mengizinkan)      |
| `photo` (listing)     | Kolom `kos.photo`                                   |
| `photos` (detail)     | Kolom `kos.photo` → `[$photo]` / `[]` (array)       |
| `price_min`           | `MIN(monthly_price)` kamar `available` (agregasi)   |
| `price_max`           | `MAX(monthly_price)` kamar `available` (agregasi)   |
| `facilities`          | relasi `fasilitas` (aktif) → array of `name` string |
| `available_rooms`     | `COUNT(kamar.status='available')` (domain logic)    |
| `is_available`        | `status='active' AND available_rooms > 0`           |
| `name` (kamar)        | `kamar.room_name`                                   |
| `type` (kamar)        | `kamar.room_type`                                   |
| `price` (kamar)       | `kamar.monthly_price`                               |
| `status` (kamar)      | `kamar.status` enum existing                        |
| `is_available` (kamar)| `status === 'available'`                            |

Availability mengikuti **logic domain existing** yang sama dengan marketplace web — tidak ada definisi
availability baru khusus API.

## Kontrak Response (ringkas)

- Sukses koleksi: `{"data": [...], "meta": {current_page, per_page, last_page, total}}`
- Sukses tunggal: `{"data": {...}}`
- Toggle: `{"data": {"kos_id", "is_favorited"}, "message": "..."}`
- `401` → `{"message": "Unauthenticated."}`
- `403` → `{"message": "This action is unauthorized."}`
- `404` → `{"message": "Resource not found."}`
- `422` → `{"message": "The given data was invalid.", "errors": {field: [msg]}}`
- `500` (production) → `{"message": "Server Error."}` — tanpa stack trace / SQL / kredensial / path

### Pagination

- Default `per_page = 15`; batas maksimum aman `50`.
- Metadata minimal: `current_page`, `per_page`, `last_page`, `total`.
- Client tidak dapat meminta jumlah record tak terbatas.

## Struktur File

```
app/Http/Resources/
  KosResource.php            # listing kos → kontrak item list
  KosDetailResource.php      # detail kos → kontrak detail (photos array)
  KamarResource.php          # kamar → kontrak kamar
  FavoriteResource.php       # favorite → id favorite + nested kos public fields
app/Http/Controllers/Api/V1/
  KosController.php          # index / show / kamar
  FavoriteController.php     # index / toggle (auth:sanctum + role:tenant)
routes/api.php               # 5 route Batch 2 + group foundation Batch 1
database/factories/
  FavoriteFactory.php        # factory favorites untuk test
tests/Feature/
  ApiV1KosTest.php           # 17 test kontrak kos/kamar
  ApiV1FavoriteTest.php      # 14 test kontrak favorites/toggle
app/Exceptions/Handler.php   # normalisasi 404/403/500 utk JSON API
```

## Keamanan Data

- Tidak mengekspos `owner`, `owner_id`, `tenant`, `penghuni`, `payment`, `kontrak`, `booking`,
  `latitude/longitude`, `phone`, `rules`, `payment_info`, kolom status/timestamps internal.
- Nested `kos` pada favorites hanya memakai public marketplace fields.
- Identitas favorite selalu dari **authenticated token** (`$request->user()->id`), tidak menerima `user_id`.
- Favorite milik tenant lain tidak pernah muncul (query difilter owner).
- Login tidak membuat session web; identitas tidak pernah dari payload/query.

## Penanganan Error Tambahan

`app/Exceptions/Handler.php` menambah renderable khusus untuk JSON API agar kontrak error stabil:
- `NotFoundHttpException` → `404 {"message": "Resource not found."}` (menutup leak nama model).
- `HttpException` 403 → `403 {"message": "This action is unauthorized."}`.
- Exception non-HTTP → `500 {"message": "Server Error."}` tanpa trace/SQL/path (detail hanya ke log server).

Semua branch ini hanya aktif untuk `expectsJson()`; alur web (redirect/Blade) tidak berubah.

## Hasil Verifikasi (Regression Gate)

- `php artisan test --filter="ApiV1KosTest|ApiV1FavoriteTest|ApiV1FoundationTest"` → **47 passed (269 assertions)**.
- `vendor/bin/pint --test` → **PASS**.
- `php artisan route:cache` + `php artisan config:cache` + `php artisan view:cache` → **PASS**.
- `npm run build` → **PASS**.

> Catatan pengujian: `php artisan config:cache`/`route:cache` yang aktif dapat membuat test membaca
> config/produk cache dan mengabaikan env `:memory:` di `phpunit.xml`. Sebelum menjalankan suite test,
> jangan meninggalkan `config:cache` aktif — test memakai `DB_DATABASE=:memory:` (sqlite) di phpunit.

## Scope yang TIDAK Dibuat (kontrol skop)

Hanya 5 endpoint yang dikunci yang diimplementasikan. Tidak dibuat: owner/tenant/payment/billing CRUD,
booking endpoint, pencarian/filter/sort API Lanjutan, maupun field tambahan di luar kontrak.