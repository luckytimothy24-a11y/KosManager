# P7-D Batch 8: API Resource Contract (Owner Booking API — READ-ONLY)

Dokumen ini merangkum implementasi **P7-D Batch 8** — dua endpoint **read-only** untuk **owner**
(daftar & detail booking properti miliknya) di atas fondasi Batch 1–7 (auth Sanctum + role + resource
+ pagination + error) dan **domain booking existing (Instant Booking)**. Tidak ada mutation.
Booking lifecycle tidak diubah.

## 1. Objective

Ekspose booking yang terkait dengan properti milik authenticated owner agar owner dapat:
1. melihat daftar booking di kos/kamar miliknya;
2. melihat detail booking di kos/kamar miliknya.

## 2. Audit Findings

- **Model** `Booking` (`app/Models/Booking.php`): relasi `user()`, `kos()`, `kamar()`; status enum
  `pending/approved/rejected/cancelled/completed/expired`; casts `booking_date/start_date/end_date` (date),
  `price` (decimal:2); scope `scopeNeedsCheckin` (approved tanpa penghuni aktif), `scopeWithoutActivePenghuni`,
  helpers `cancellableStatuses/activeStatuses/terminalStatuses/isCancellable/isTerminal`.
- **Ownership trace**: `Booking.kos_id → Kos.owner_id === authenticated owner`.
- **Policy** `BookingPolicy` (`app/Policies/BookingPolicy.php`): `view()` memakai `isKosOwner`
  (`$booking->kos->owner_id === $user->id`) untuk owner → **default deny**.
- **Web controller owner** `Owner\BookingController::index` memakai query established:
  `Booking::with(['user','kos','kamar'])->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->latest()`.
  Di-reuse untuk API list.
- **Resource existing** `BookingResource` bersifat tenant-marketplace (slug/city, tanpa identitas tenant).
  Untuk owner, tenant booking perlu diidentifikasi → dibuat `OwnerBookingResource` (biarkan eksisting apa adanya).

## 3. Locked Endpoints

| Method | Endpoint                           | Auth                        | Status |
| ------ | ---------------------------------- | --------------------------- | ------ |
| GET    | `/api/v1/owner/bookings`           | `auth:sanctum + role:owner` | ✅ |
| GET    | `/api/v1/owner/bookings/{booking}` | `auth:sanctum + role:owner` | ✅ |

Route names: `api.v1.owner.bookings.index/show`.

## 4. Authentication

- `auth:sanctum` (constructor controller). Tanpa/token rusak → `401 {"message":"Unauthenticated."}`.
- Hanya owner. Role lain → `403`.

## 5. Ownership Rules

- Owner hanya melihat booking di kos miliknya: `whereHas('kos', owner_id = auth)` (list) dan
  `BookingPolicy::view` (detail).
- Cross-owner: booking milik owner lain → **403**; tidak muncul di list; tidak bocorkan ID/data lain.

## 6. Authorization

- `role:owner` → tenant/admin/super_admin → `403`.
- Detail: `authorize('view', $booking)` memakai `BookingPolicy::view` (owner via `isKosOwner`).
- List: scope **sejak query database** (`whereHas('kos', owner_id)`), bukan fetch-semua lalu filter PHP.
- Tidak ada `?owner_id=`/`?user_id=`/`?kos_owner_id=` sebagai mekanisme keamanan.

## 7. Request/Query Parameters

GET tanpa body. Query opsional:
- `per_page` (default `15`, cap `50`) — konvensi API Batch 2.
- **Tidak ada filter baru** (status/search/sort) ditambahkan demi API; hanya pagination sesuai pola existing.
  Web owner page punya `search`/`status` khusus web; tidak diekspos untuk menjaga scope API read-only yang minimal.

## 8. Response JSON Contract

Semua `Content-Type: application/json`.

### GET `/api/v1/owner/bookings` — `{"data": [...], "meta": {...}}`

`OwnerBookingResource` per item:
| Field | Sumber |
| ----- | ------ |
| `id` | `booking.id` |
| `booking_code` | `booking.booking_code` |
| `tenant.id` | `booking.user.id` |
| `tenant.name` | `booking.user.name` |
| `kos.id` | `booking.kos.id` |
| `kos.name` | `booking.kos.name` |
| `kamar.id` | `booking.kamar.id` |
| `kamar.name` | `booking.kamar.room_name` |
| `kamar.room_number` | `booking.kamar.room_number` |
| `booking_date` | `booking_date` (date) |
| `start_date` | `start_date` (date) |
| `end_date` | `end_date` (date) |
| `rental_type` | `rental_type` (`daily`/`monthly`) |
| `price` | `price` (decimal:2) |
| `status` | `status` |
| `notes` | `notes` |

Contoh:
```json
{
  "id": 1,
  "booking_code": "BK123456",
  "tenant": { "id": 7, "name": "Budi Santoso" },
  "kos": { "id": 10, "name": "Kos Melati" },
  "kamar": { "id": 5, "name": "Kamar A1", "room_number": "A1" },
  "booking_date": "2026-08-01",
  "start_date": "2026-09-01",
  "end_date": "2026-12-01",
  "rental_type": "monthly",
  "price": "1500000.00",
  "status": "approved",
  "notes": null
}
```

### GET `/api/v1/owner/bookings/{booking}` — `{"data": {...}}`

Resource sama dengan `OwnerBookingResource` (struktur `data` identik seperti contoh di atas).

`meta` (list hanya): `current_page, last_page, per_page, total`.

## 9. Error Contract

- `401` → `{"message":"Unauthenticated."}`
- `403` → `{"message":"This action is unauthorized."}`
- `404` → `{"message":"Resource not found."}`
- `500` (production) → `{"message":"Server Error."}`
Tidak ada format error baru.

## 10. Resources

- `app/Http/Resources/OwnerBookingResource.php` — transform booking owner (list & detail).
  Aman: ekspos hanya identitas tenant `id` + `name` (konteks booking), tanpa email/phone/alamat/payment proof.
  Tidak membocorkan payment proof path/internal metadata/authorization data/popup yang tidak diperlukan.

## 11. Controllers

- `app/Http/Controllers/Api/V1/Owner/OwnerBookingController.php` — `index`, `show` (thin: scope + policy + resource).

## 12. Existing Domain/Service Reuse

- Query list **identik** dengan `Owner\BookingController::index` (web) — tidak mengubah web controller.
- `BookingPolicy::view` untuk authorization detail (default deny).
- Model `Booking`, relasi `user/kos/kamar`, casts sudah ada. Tidak membuat service/abstraction read baru
  (read operation tidak memaksa reuse `BookingService` yang untuk lifecycle mutation).

## 13. Security

- Ownership di-server via `Booking.kos.owner_id`; tidak ada parameter client untuk scope.
- Cross-owner explicit test: Owner A↔Owner B (list + detail) → tanpa data leakage.
- Policy default deny; tidak permission escalation.

## 14. Performance

- `index`: satu query `paginate(per_page)` + eager-load `user`,`kos`,`kamar` + scope `whereHas('kos', owner_id)`
  → tanpa N+1; tidak fetch-semua lalu filter.
- `show`: route binding + `authorize` + eager-load 3 relasi.
- Tanpa premature optimization.

## 15. Tests

`tests/Feature/ApiV1OwnerBookingTest.php` — 13 tes (71 assertions):
- **Auth**: unauthenticated → 401 (2 endpoint); tenant/admin/super_admin → 403 (list & detail existing booking).
- **List**: hanya booking milik property owner; foreign booking tidak muncul; data relasi (kos/kamar/tenant) benar;
  empty-state valid; pagination; ordering deterministik `latest()`.
- **Detail**: detail milik owner → 200 + contract; booking owner lain → 403; booking tidak ada → 404.
- **Cross-owner security**: dua tes owner-tunggal eksplisit — Owner A akses booking A OK / booking B 403;
  Owner B akses booking A 403 / booking B OK.
- **Data leakage**: respons list tidak bocorkan booking owner lain maupun `notes` internal owner lain.

## 16. Verification

- Batch 8: `php artisan test tests/Feature/ApiV1OwnerBookingTest.php` → **13 passed (71 assertions)**.
- Full suite: `php artisan test` → **796 passed (2708 assertions)** (sebelumnya 783 → +13).
- Pint: `vendor/bin/pint --test` → **PASS** (3 file baru di-fix EOF lalu lulus).
- Routes: `php artisan route:list --path=api/v1/owner` → **6 routes** (Batch 7: 4 + Batch 8: 2).
- Route cache: `php artisan route:cache` → **OK** (lalu `route:clear` untuk dev).
- Build: `npm run build` → **PASS**; tanpa perubahan frontend.
- Regresi Booking lifecycle/Instant Booking, Batch 1–7, P7-C, tenant booking → semua hijau di full suite.

## 17. Out of Scope

- approve/reject/cancel-by-owner/check-in/check-out API; POST/PUT/PATCH/DELETE booking.
- Owner contract/billing/payment/notification API.
- Owner CRUD Kos/Kamar.
- Admin/SuperAdmin/Users API.
- Tenant API changes / Batch 1–7 endpoint changes.
- WebSocket/broadcasting/realtime/push/FCM/email/SMS; mobile frontend; dashboard redesign.
- Payment gateway changes; notification changes; new notification types/events/rules.
- Deployment/Docker/backup; master data.
- Endpoint lain selain 2 locked endpoint Batch 8.

## 18. Known Issues / Future Recommendations

- (tidak ada issue). Future/rekomendasi (di luar scope, tidak diimplementasi):
  - Jika owner memerlukan filter `status`/`search` pada API, buat batch tersendiri dengan ekspos parameter web
    yang sudah established, bukan ditambahkan impulsif di API read-only ini.