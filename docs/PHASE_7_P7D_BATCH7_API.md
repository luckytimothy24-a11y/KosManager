# P7-D Batch 7: API Resource Contract (Owner Property & Dashboard API)

Dokumen ini merangkum implementasi **P7-D Batch 7** — empat endpoint **read-only** untuk **owner**
(properti kos + dashboard operasional) yang mengikuti kontrak JSON LOCKED, di atas fondasi Batch 1–6
(auth Sanctum + role + resource + pagination + error) dan **domain owner existing aplikasi web**.
Tidak ada mutation, tidak ada business rule / metric baru.

## 1. Objective

Ekspose data owner yang sudah ada di aplikasi web ke REST API secara aman dan konsisten agar client/mobile dapat:
1. melihat daftar kos milik owner yang login;
2. melihat detail kos milik owner;
3. melihat daftar kamar dari kos milik owner;
4. melihat ringkasan dashboard operasional owner.

## 2. Audit Findings

- **Ownership**: `Kos.owner_id` → `User` (relasi `Kos::owner()`, `User::ownedKos()`). Tidak ada kolom `owner_id` pada `kamar` —
  kepemilikan kamar diturunkan dari parent `Kos`.
- **Policies existing**:
  - `KosPolicy::view` → owner diizinkan bila `(int) $kos->owner_id === (int) $user->id`; `KosPolicy::viewAny` → owner diizinkan.
  - `KamarPolicy::view` → owner diizinkan bila `(int) $kamar->kos->owner_id === (int) $user->id`.
  - Semua **default deny**, tidak fail-open.
- **Dashboard owner existing** (`DashboardController::owner()`): statistik yang benar-benar ditampilkan di
  `resources/views/dashboard/owner.blade.php` — `total_kos, total_kamar, total_kamar_available, total_kamar_occupied,
  total_kamar_maintenance, needs_checkin, pending_payments, total_penghunis, total_revenue, tagihan_outstanding,
  tagihan_overdue`. Scope ke `owner_id = auth`.
- **Resource existing**: `KosResource`, `KosDetailResource`, `KamarResource` adalah **tenant-marketplace**-oriented
  (slug/city/price_min/max/is_available dst). Tidak tepat untuk owner → dibuat resource owner baru
  (spesifikasi mengizinkan: `OwnerKosResource`, `OwnerKamarResource`, `OwnerDashboardResource`).
- **Kamar status enum**: `available, booked, occupied, maintenance`.

## 3. Locked Endpoints

| Method | Endpoint                        | Auth                | Status |
| ------ | ------------------------------- | ------------------- | ------ |
| GET    | `/api/v1/owner/kos`             | `auth:sanctum + role:owner` | ✅ |
| GET    | `/api/v1/owner/kos/{kos}`       | `auth:sanctum + role:owner` | ✅ |
| GET    | `/api/v1/owner/kos/{kos}/kamar` | `auth:sanctum + role:owner` | ✅ |
| GET    | `/api/v1/owner/dashboard`       | `auth:sanctum + role:owner` | ✅ |

Route names: `api.v1.owner.kos.index/show/kamar`, `api.v1.owner.dashboard.show`.

## 4. Authentication

- `auth:sanctum` (di constructor controller). Tanpa/token rusak → `401 {"message":"Unauthenticated."}`.
- Seluruh endpoint **harus dipanggil hanya oleh owner**. Role lain → `403`.

## 5. Authorization

- `role:owner` middleware: non-owner ditolak `403` (tenant/admin/super_admin).
- `GET .../owner/kos` → `authorize('viewAny', Kos::class)` + scope query `where('owner_id', auth id)`.
- `GET .../owner/kos/{kos}` & `.../kos/{kos}/kamar` → model binding + `authorize('view', $kos)`
  (reuse `KosPolicy::view`; owner lain → `403`; kos tidak ada → `404`).
- **Ownership diverifikasi server-side**; tidak ada `?owner_id=`/`?user_id=` sebagai mekanisme keamanan.

## 6. Ownership Rules

- Owner A hanya melihat Kos milik A sendiri (list tersaring server-side; pola Batch 5).
- Owner B mengakses `kos/{kosA}` atau `kos/{kosA}/kamar` → **403**; tidak ada respons yang membocorkan data A.
- Kamar di `kos/{kos}/kamar` seluruhnya berasal dari parent `{kos}` (query `$kos->kamar()`), tidak ada cross-owner.
- Dashboard hanya menghitung data milik owner tersebut.

## 7. Request Requirements

`GET` tanpa request body. Query opsional:
- `per_page` (default `15`, cap `50`) untuk listing kos & kamar (konvensi Batch 2).

## 8. Response JSON Contract

Semua respons `Content-Type: application/json`.

### GET `/api/v1/owner/kos` — `{"data": [...], "meta": {...}}`

`OwnerKosResource` per item:
| Field | Sumber |
| ----- | ------ |
| `id` | `kos.id` |
| `name` | `kos.name` |
| `address` | `kos.address` |
| `description` | `kos.description` |
| `phone` | `kos.phone` |
| `photo` | `kos.photo` |
| `latitude` | `kos.latitude` (decimal:7) |
| `longitude` | `kos.longitude` (decimal:7) |
| `general_facilities` | `kos.general_facilities` |
| `rules` | `kos.rules` |
| `payment_info` | `kos.payment_info` |
| `status` | `kos.status` |
| `facilities` | array nama fasilitas (dari `kos.fasilitas`) — `[]` bila tidak di-load |
| `kamar_count` | `withCount('kamar')` |
| `penghuni_count` | `withCount('penghunis')` |
| `bookings_count` | `withCount('bookings')` (hanya pada detail) |

### GET `/api/v1/owner/kos/{kos}` — `{"data": {...}}`

Resource sama dengan `OwnerKosResource`; `facilities` diisi dari relasi (di-load), `bookings_count` tersedia,
`kamar_count` & `penghuni_count` dari `withCount`.

Contoh detail:
```json
{
  "data": {
    "id": 10,
    "name": "Kos Melati",
    "address": "Jl. Melati No. 3, Yogyakarta",
    "description": "Kos asri dekat kampus.",
    "phone": "0812-3456-7890",
    "photo": "kos/abc.jpg",
    "latitude": "-7.7970682",
    "longitude": "110.3705293",
    "general_facilities": "WiFi, Air panas",
    "rules": "Tidak boleh membawa hewan.",
    "payment_info": "Transfer BCA 1234…",
    "status": "active",
    "facilities": ["AC", "WiFi"],
    "kamar_count": 5,
    "penghuni_count": 3,
    "bookings_count": 8
  }
}
```

### GET `/api/v1/owner/kos/{kos}/kamar` — `{"data": [...], "meta": {...}}`

`OwnerKamarResource` per item:
| Field | Sumber |
| ----- | ------ |
| `id` | `kamar.id` |
| `kos_id` | `kamar.kos_id` |
| `room_number` | `kamar.room_number` |
| `room_name` | `kamar.room_name` |
| `floor` | `kamar.floor` |
| `room_type` | `kamar.room_type` |
| `daily_price` | `kamar.daily_price` (decimal:2) |
| `monthly_price` | `kamar.monthly_price` (decimal:2) |
| `area` | `kamar.area` |
| `description` | `kamar.description` |
| `photo` | `kamar.photo` |
| `status` | `kamar.status` |

Contoh:
```json
{
  "data": [
    {
      "id": 1,
      "kos_id": 10,
      "room_number": "A1",
      "room_name": "Kamar A1",
      "floor": 1,
      "room_type": "Standard",
      "daily_price": "120000.00",
      "monthly_price": "1500000.00",
      "area": "16m2",
      "description": "Kamar sudut, jendela luas.",
      "photo": "kamar/xyz.jpg",
      "status": "available"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### GET `/api/v1/owner/dashboard` — `{"data": {...}}`

`OwnerDashboardResource` hanya memetakan statistik yang sudah ada di dashboard owner web.

```json
{
  "data": {
    "kos": {
      "total": 2
    },
    "kamar": {
      "total": 10,
      "available": 4,
      "occupied": 6,
      "maintenance": 0
    },
    "penghuni": {
      "active": 5
    },
    "booking": {
      "needs_checkin": 1
    },
    "billing": {
      "pending_payments": 0,
      "tagihan_outstanding": 2,
      "tagihan_overdue": 1
    },
    "finance": {
      "total_revenue": 15000000
    }
  }
}
```

Catatan: `total_revenue` dikembalikan sebagai angka (float). Semua nilai lain integer.

## 9. Error Contract

- `401` → `{"message":"Unauthenticated."}`
- `403` → `{"message":"This action is unauthorized."}` (role salah atau akses kos milik owner lain)
- `404` → `{"message":"Resource not found."}`
- `500` (production) → `{"message":"Server Error."}`
Tidak ada format error baru.

## 10. Resources

- `app/Http/Resources/OwnerKosResource.php` — transform kos owner (list & detail).
- `app/Http/Resources/OwnerKamarResource.php` — transform kamar owner.
- `app/Http/Resources/OwnerDashboardResource.php` — transform statistik dashboard owner.

Resource deterministic; tidak mengekspos `owner_id`, payload internal, atau data authorization-sensitive.

## 11. Controllers

- `app/Http/Controllers/Api/V1/Owner/OwnerKosController.php` — `index`, `show`, `kamar` (thin: scope+policy+resource).
- `app/Http/Controllers/Api/V1/Owner/OwnerDashboardController.php` — `show` (reuse query dashboard owner existing).

## 12. Existing Service/Domain Reuse

- `KosPolicy::view`/`viewAny` — authorization.
- `Booking::needsCheckin()` — hitung `needs_checkin`.
- `Tagihan::payable()` — hitung `tagihan_outstanding`.
- Query dashboard **identik** dengan `DashboardController::owner()` (tidak mengubah/refactor web controller).

## 13. Security

- Ownership hanya via `owner_id` di server; tidak ada parameter client untuk scope.
- Cross-owner list → resource tidak muncul; detail/kamar → 403; dashboard → data owner lain tidak terhitung (diuji).
- Policy default deny; tidak permission escalation; 403 untuk tenant/admin/super_admin.

## 14. Performance Considerations

- `index`: satu query `paginate` + `withCount('kamar','penghunis')` + `latest()` → tanpa N+1.
- `show`: satu query + `loadCount` + eager-load `fasilitas`.
- `kamar`: query `$kos->kamar()->paginate()` → hanya kamar parent.
- `dashboard`: 11 aggregate `count()`/`sum()` scoped via `whereHas` ke `owner_id` — tidak ada iterasi collection besar
  atau query explosion. Tanpa premature optimization.

## 15. Tests

`tests/Feature/ApiV1OwnerTest.php` — 19 tes (106 assertions):
- **Auth**: unauthenticated → 401 (4 endpoint); tenant/admin/super_admin → 403 (kos & dashboard).
- **Kos list**: hanya kos milik owner; empty-state; tidak bocor kos owner lain; `per_page`/pagination & JSON contract.
- **Kos detail**: detail milik owner; owner lain → 403; missing → 404.
- **Kamar**: kamar milik owner; kos tanpa kamar → kosong; owner lain → 403; hanya kamar parent kos; pagination.
- **Dashboard**: statistik owner-scoped; data owner lain terhitung nol; empty-state valid; outstanding & overdue tagihan.
- **Keamanan eksplisit**: Owner A vs Owner B (list, detail, kamar) — tidak ada data leakage.

## 16. Verification

- Batch 7: `php artisan test tests/Feature/ApiV1OwnerTest.php` → **19 passed (106 assertions)**.
- Full suite: `php artisan test` → **783 passed (2637 assertions)** (sebelumnya 764 → bertambah 19).
- Pint: `vendor/bin/pint --test` → **PASS** (6 file baru di-fix EOF lalu lulus).
- Routes: `php artisan route:list --path=api/v1/owner` → 4 route terdaftar benar.
- Route cache: `php artisan route:cache` → **OK** (lalu `route:clear` untuk dev).
- Build: `npm run build` → **PASS**; tidak ada perubahan frontend.
- Regresi Batch 1–6, P7-C, owner/web, tenant marketplace → semua hijau pada full suite.

## 17. Out of Scope

- WebSocket/broadcasting/realtime/push/FCM/email/SMS.
- Owner CRUD Kos/Kamar (create/update/delete property).
- Owner booking/contract/billing/payment/notification API.
- Admin/SuperAdmin/Users API.
- Perubahan Tenant API / Batch 1–6 endpoint.
- Mobile frontend, dashboard UI redesign, payment gateway changes, notification changes.
- New business rules, deployment/container/backup, master data.
- Endpoint lain selain 4 locked endpoints Batch 7.