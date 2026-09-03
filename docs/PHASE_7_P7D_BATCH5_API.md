# P7-D Batch 5: API Resource Contract (Tenant Contract API)

Dokumen ini merangkum implementasi **P7-D Batch 5** — tiga endpoint resource untuk **kontrak tenant**
yang mengikuti kontrak JSON LOCKED, di atas fondasi Batch 1–4 (auth Sanctum + resource + pagination + error).

## 1. Objective

Menyediakan API agar tenant dapat: melihat daftar kontrak miliknya, melihat detail kontrak, dan melihat
tagihan yang terkait dengan kontrak — seluruhnya memakai **business logic domain existing** (read-only,
tanpa mengarang aturan baru).

## 2. Locked Scope

Batch 5 hanya berisi **3 endpoint** tenant contract (disetujui), seluruhnya **read-only**.

| Method | Endpoint                        | Auth                  | Fungsi                             |
| ------ | ------------------------------- | --------------------- | ---------------------------------- |
| `GET`  | `/api/v1/kontrak`               | `auth:sanctum` + role tenant | List kontrak tenant          |
| `GET`  | `/api/v1/kontrak/{kontrak}`     | `auth:sanctum` + role tenant | Detail kontrak tenant        |
| `GET`  | `/api/v1/kontrak/{kontrak}/tagihan` | `auth:sanctum` + role tenant | List tagihan terkait kontrak |

**Tidak ada** POST/PUT/PATCH/DELETE kontrak, maupun API terminate kontrak.

## 3. Endpoint Table

Lihat tabel **2. Locked Scope**. Route names: `api.v1.kontrak.index/show/tagihan`.

## 4. Authentication

- `auth:sanctum` → wajib token Sanctum valid; tanpa/token rusak → `401 {"message":"Unauthenticated."}`.
- Dibuat di constructor controller: `middleware('auth:sanctum')` + `middleware('role:tenant')`.

## 5. Authorization

- `role:tenant` → hanya role `tenant` (owner/admin/super_admin ditolak `403`).
- Kepemilikan kontrak tenant ditentukan lewat relasi `Kontrak → penghuni → user_id`.
- `show` dan `tagihan` memakai **policy existing** `KontrakPolicy::view`
  (cabang tenant: `penghuni->user_id === user->id`). Kontrak tenant lain → `403`.
- `index` memberi scope langsung `whereHas('penghuni', user_id = auth)` — tidak memakai `viewAny`
  (yang hanya untuk super_admin/admin/owner). Ownership diverifikasi **server-side**.
- Tidak ada authorization baru.

## 6. Request Contract

Batch 5 seluruhnya GET — tidak ada request body. Pagination query (`per_page`) opsional:
default `15`, cap maks `50` (konvensi Batch 2/4).

## 7. Success Response Contract

Semua respons `Content-Type: application/json`.

### List kontrak — `{"data": [...], "meta": {...}}`

`KontrakResource` field (mengikuti existing DB):
| Field            | Sumber existing            |
| ---------------- | -------------------------- |
| `id`             | `kontrak.id`               |
| `contract_number`| `kontrak.contract_number`  |
| `kos`            | objek publik (id, name, address, city=null, photo) |
| `kamar`          | objek publik (id, name, type, photo)              |
| `rental_type`    | `kontrak.rental_type` (`daily`/`monthly`) |
| `rental_price`   | `kontrak.rental_price` (float) |
| `start_date`     | `kontrak.start_date` (date) |
| `end_date`       | `kontrak.end_date` (date)   |
| `status`         | `kontrak.status` enum existing |
| `notes`          | `kontrak.notes`             |

### Detail kontrak — `{"data": {...}}` (sama shape)

### List tagihan kontrak — `{"data": [...], "meta": {...}}`

Reuse **`TagihanResource` (Batch 4)** — tidak ada transformer duplikat. Tagihan yang dikembalikan
adalah benar-benar milik kontrak tersebut (`kontrak.tagihans` relasi `Kontrak → hasMany Tagihan`).

## 8. Error Contract

- `401` → `{"message":"Unauthenticated."}`
- `403` → `{"message":"This action is unauthorized."}`
- `404` → `{"message":"Resource not found."}`
- `500` (production) → `{"message":"Server Error."}` — tanpa stack trace/SQL/kredensial/path
- Error handling web **tidak diubah**.

## 9. Ownership Rules

- `Tenant A → GET /api/v1/kontrak/{kontrak-tenant-B}` → **MUST NOT** menerima data kontrak tenant B.
  Dijamin server-side via `KontrakPolicy::view` (relasi `penghuni.user_id === auth id`).
- List `index` disaring ke penghuni milik authenticated user.
- Tidak ada dependensi pada frontend/mobile client untuk ownership.

## 10. Contract → Tagihan Relationship

- `Kontrak::tagihans()` = `hasMany(Tagihan::class)` (via `kontrak_id`).
- Endpoint tagihan memakai relasi tersebut; hanya tagihan kontrak yang terotorisasi yang dibaca.
- Tidak ada cara membaca tagihan kontrak tenant lain (authorize lebih dulu).

## 11. Performance / Eager Loading

- `index`: `with(['kos','kamar'])` — hanya relasi yang dipakai resource; tidak loading `penghuni`
  atau `tagihans` karena tidak diperlukan.
- `show`/`tagihan`: `load(['penghuni','kos','kamar', 'tagihans.pembayarans'])` — `penghuni` & `kos`
  menghindari lazy-load saat `KontrakPolicy::view` mengecek ownership; `tagihans.pembayarans` dipakai
  `TagihanResource` nested. Tidak ada eager loading berlebihan (relasi tak terpakai tidak dimuat).
- N+1 dihindari lewat eager loading yang seperlunya.

## 12. Files Created

- `app/Http/Controllers/Api/V1/ContractController.php` — controller API (index/show/tagihan).
- `app/Http/Resources/KontrakResource.php` — transformer kontrak (nested public kos/kamar).
- `tests/Feature/ApiV1ContractTest.php` — suite Batch 5 (19 tes).
- `docs/PHASE_7_P7D_BATCH5_API.md` — dokumen ini.

## 13. Files Modified

- `routes/api.php` — 3 route Batch 5.

## 14. Tests

`tests/Feature/ApiV1ContractTest.php` — 19 tes:
- **Auth:** list/detail/kontrak-tagihan unauthenticated → 401.
- **Tenant authorization:** detail kontrak sendiri → 200; kontrak tenant lain → 403;
  kontrak-tagihan tenant lain → 403; role owner ditolak 403.
- **List:** hanya kontrak milik authenticated tenant; pagination default 15 & cap 50;
  koleksi kosong → 200; absen field sensitif (`penghuni_id`, `kos_id`, `kamar_id`, timestamps).
- **Detail:** existing → 200; missing → 404; foreign tenant → 403.
- **Contract bills:** kontrak milik → tagihan terkait benar; tanpa tagihan → `data: []` (200);
  kontrak foreign → 403; tagihan kontrak lain tidak bocor (assertNotContains).

## 15. Verification

- `php artisan test --filter=ApiV1ContractTest` → **19 passed (94 assertions)**.
- `php artisan test --filter=ApiV1` → **124 passed (632 assertions)** (Batch 1–5).
- `php artisan test` (full suite) → **743 passed (2461 assertions)**.
- `vendor/bin/pint --test` → **PASS**; `php artisan route:cache` → **ok** (dikembalikan via `route:clear`
  untuk dev); `npm run build` → **PASS**.
- Rule Batch 1/2: selalu `route:clear` + `config:clear` sebelum run test.

## 16. Out of Scope (tidak dikerjakan)

- POST/PUT/PATCH/DELETE `/api/v1/kontrak` + contract termination API.
- `/api/v1/notifications`, `/api/v1/users`.
- Owner API, admin API, superadmin API.
- WebSocket / realtime notification.
- Payment gateway baru.
- Mobile frontend, dashboard baru.
- Master data, deployment/container, database backup.
- Endpoint lain selain 3 locked endpoints Batch 5.