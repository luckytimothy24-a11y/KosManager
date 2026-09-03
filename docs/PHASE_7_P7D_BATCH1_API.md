# P7-D Batch 1: API Foundation (Endpoint Foundation)

Dokumen ini merangkum implementasi **Batch 1 API Endpoint Foundation** — empat endpoint inti untuk
membuktikan **authentication, authorization, response standard, dan token lifecycle** tanpa melebar
ke domain bisnis.

## Ringkasan

- **Status:** Selesai (implementasi + dokumentasi). Scope Batch 1 terkunci pada 4 endpoint foundation.
- **Tujuan:** Sebelum membangun REST API domain (P7-D lanjutan), fondasi autentikasi token & kontrak
  respons JSON dibuktikan dengan benar dan diuji.
- **Keputusan arsitektur:**
  - API menggunakan **Sanctum** (token `personal_access_tokens`) — model `User` sudah memakai
    `HasApiTokens` dan migrasi tabel token sudah ada.
  - Versi API diawali `/api/v1`.
  - Identitas selalu berasal dari **authenticated token**, tidak pernah dari payload/query request.
  - Login API **tidak membuat session web** (`Auth::guard('web')->validate()` — stateless, credential-only),
    sehingga flow login web Breeze tidak berubah.
  - Response standar: sukses → `{"data": {...}}`; error mengikuti format Laravel
    (`401 {"message": ...}`, `422 {"message", "errors"}`).

## Endpoint Batch 1 (locked)

| Method | Endpoint              | Auth          | Fungsi                                     |
| ------ | --------------------- | ------------- | ------------------------------------------ |
| `POST` | `/api/v1/auth/login`  | Public        | Validasi kredensial + menerbitkan API token |
| `POST` | `/api/v1/auth/logout` | `auth:sanctum` | Mencabut token aktif milik user             |
| `GET`  | `/api/v1/me`          | `auth:sanctum` | Profil user dari token                      |
| `GET`  | `/api/v1/health`      | Public        | Health/readiness JSON (200 / 503)           |

## Perubahan yang Dilakukan

### 1. Routes — `routes/api.php`

- Route `/api/user` bawaan Laravel (closure) **dihapus**; diganti group `prefix('v1')`:
  - `POST /v1/auth/login` → `api.v1.auth.login`
  - `GET /v1/health` → `api.v1.health`
  - `POST /v1/auth/logout` (auth:sanctum) → `api.v1.auth.logout`
  - `GET /v1/me` (auth:sanctum) → `api.v1.me`
- Tidak ada closure → `php artisan route:cache` tetap aman.

### 2. Login — `AuthController::login`

- `app/Http/Requests/Api/V1/LoginRequest.php` — validasi `email` (required|email) dan `password` (required|string).
- Validasi kredensial via `Auth::guard('web')->validate([...email, password, is_active => true])` —
  **tanpa membuat session**, tanpa Remember Token, dan user **inaktif ditolak** (401).
- Setelah sukses: `$user->createToken('api-v1')` → `plainTextToken` dikembalikan sebagai `access_token`
  berjenis `Bearer`, bersama profil publik via `UserResource`.
- Password/hash tidak pernah dikembalikan; token hanya dibuat setelah autentikasi berhasil.
- Error kredensial salah → `401 {"message": "Kredensial tidak valid."}` (tidak menyebut field mana).
- Web login Breeze (`POST /login`) tidak tersentuh.

### 3. Logout — `AuthController::logout`

- Hanya mencabut **token aktif yang dipakai request ini** (`currentAccessToken()`).
- Guarding: bila request diautentikasi lewat **sesi web** (Sanctum memberi `TransientToken`), tidak ada
  token API yang dicabut dan **sesi web tidak terpengaruh** — endpoint hanya melepas autentikasi token API.
- Implementasi: hanya `PersonalAccessToken` yang di-`delete()`.

### 4. Me — `MeController::show`

- Mengambil identity dari `$request->user()` (token), **bukan** `user_id` dari request body/query.
- `app/Http/Resources/UserResource.php` — serialization whitelist: `id, name, email, phone, address, role, avatar`.
  Tidak mengekspos `password`, `remember_token`, kolom internal, atau nilai sensitif.

### 5. Health — reuse `HealthController`

- `/api/v1/health` memakai `App\Http\Controllers\HealthController` yang sama dengan web `/health` (P7-B):
  cek koneksi DB via `SELECT 1`; `200 {"status":"ok",...}` bila sehat, `503 {"status":"error",...}` bila tidak.
- Publik (tanpa auth), JSON-only, tidak membocorkan config, `.env`, SQL, stack trace, atau path filesystem.

### 6. Test — `tests/Feature/ApiV1FoundationTest.php` (16 test / 65 assertion)

- **Login:** sukses (token + profil publik, tanpa `password`/`remember_token`), kredensial salah → 401 tanpa
  token, user inaktif → 401, validasi → 422, API login **tidak** menciptakan session web,
  web login Breeze tetap berfungsi.
- **Me:** user diambil dari token; parameter `user_id` di query **diabaikan** (identitas tetap dari token);
  tanpa token → 401; token yang sudah dicabut → 401.
- **Logout:** mencabut **hanya token aktif** (token kedua tetap valid); tidak memengaruhi session web;
  tanpa token → 401.
- **Health:** publik & `200` saat DB tersedia; `503` tanpa bocorkan `SQLSTATE`/path/`APP_KEY` saat DB
  down (koneksi `broken_sqlite` terisolasi, dipulihkan di `finally`); response tidak memuat variabel sensitif
  environment (`DB_PASSWORD`, `APP_KEY`, `MAIL_PASSWORD`).

> Catatan pengujian: Laravel feature test memakai satu instance app per test, sehingga guard `sanctum`
> ter-memoize antar request dalam test yang sama. Test yang memverifikasi **pencabutan token** memanggil
> `app('auth')->forgetGuards()` di antara request agar token benar-benar ditanyakan ulang ke database.

## Hasil Verifikasi (Regression Gate)

- `php artisan test` → **635 passed** (1894 assertions); baseline P7-C 619 (1829) + 16 test baru (65 assertion) — **PASS**.
- `vendor/bin/pint --test` → **PASS**.
- `npm run build` → **PASS**.
- `php artisan view:cache` → **PASS**.
- `php artisan route:cache` + `php artisan config:cache` → **PASS** (route `/api/user` closure dihapus;
  seluruh route kini controller-based).

## Scope yang Sengaja TIDAK Dibuat (endpoint freeze)

Batch 1 **tidak** membuat endpoint domain/business apa pun:
`/users`, `/kos`, `/kamar`, `/bookings`, `/tagihan`, `/pembayaran`, `/kontrak`, `/notifications`,
`/favorites`, owner/admin/superadmin CRUD, marketplace API, maupun WebSocket/realtime.

Endpoint tambahan hanya boleh dibuat sebagai **dukungan teknis langsung** bagi 4 endpoint foundation dan
harus dilaporkan sebelum implementasi.

## Risiko / Rekomendasi Lanjutan (di luar scope Batch 1)

1. **Throttling login:** saat ini memakai rate limiter global API (60/menit). Bila perlu, tambahkan
   rate limiter khusus `api.v1.auth.login` (mis. 5/menit/IP) untuk brute force.
2. **Penerusan token & TTL:** token tanpa `expires_at`. Batch lanjutan dapat menambahkan
   `expiresAt`/refresh token bila klien mobile membutuhkannya.
3. **Rate limiter / pagination untuk endpoint domain:** akan dirancang saat batch resource (P7-D lanjutan).
4. **Pagination dan OpenAPI/documentation schema** untuk resource mendatang sebaiknya disusun bersama
   batch berikutnya agar kontrak `{"data": ...}` konsisten.