# P7-D Batch 6: API Resource Contract (Tenant Notifications API)

Dokumen ini merangkum implementasi **P7-D Batch 6** — tiga endpoint resource untuk **notifikasi tenant**
yang mengikuti kontrak JSON LOCKED, di atas fondasi Batch 1–5 (auth Sanctum + resource + pagination + error)
dan **sistem notifikasi existing dari P7-C** (tidak membuat sistem baru).

## 1. Objective

Expose existing notification system melalui REST API agar tenant dapat: melihat daftar notifikasi miliknya,
melihat detail satu notifikasi, dan menandai notifikasi sebagai sudah dibaca — seluruhnya memakai
**existing notification domain (P7-C)**. Tidak ada notification behavior baru.

## 2. Locked Scope

Batch 6 hanya berisi **3 endpoint** tenant notifications (disetujui).

| Method | Endpoint                                     | Auth                  | Fungsi                                  |
| ------ | -------------------------------------------- | --------------------- | --------------------------------------- |
| `GET`  | `/api/v1/notifications`                      | `auth:sanctum` + role tenant | List notifikasi tenant           |
| `GET`  | `/api/v1/notifications/{notification}`       | `auth:sanctum` + role tenant | Detail notifikasi tenant         |
| `POST` | `/api/v1/notifications/{notification}/read`  | `auth:sanctum` + role tenant | Tandai notifikasi sudah dibaca   |

**Tidak ada**: unread-count, mark-all-read, delete, preferences, settings.

## 3. Endpoint Table

Lihat **2. Locked Scope**. Route names: `api.v1.notifications.index/show/read`.

## 4. Authentication

- `auth:sanctum` → wajib token Sanctum valid; tanpa/token rusak → `401 {"message":"Unauthenticated."}`.
- Dibuat di constructor controller: `middleware('auth:sanctum')` + `middleware('role:tenant')`.

## 5. Authorization

- `role:tenant` → hanya role `tenant` (owner/admin/super_admin ditolak `403`).
- `show` dan `read` memakai **policy existing** `NotificationPolicy::view` / `NotificationPolicy::update`
  (pemilik: `notification.user_id === auth id`) → **default deny**, tidak fail-open.
- `index` memberi scope langsung `where('user_id', auth id)` — hanya notifikasi milik sendiri.
- Ownership diverifikasi **server-side**; tidak bergantung pada frontend/mobile client.

## 6. Request Contracts

Batch 6 seluruhnya tanpa request body:
- `GET /api/v1/notifications?per_page=<1..50>` — pagination query opsional.
- `POST /api/v1/notifications/{notification}/read` — tanpa body.

Pagination convention Batch 2/4: default `per_page=15`, cap maks `50`. Ordering `latest()` (konsisten web P7-C).

## 7. Success Response Contracts

Semua respons `Content-Type: application/json`.

### List — `{"data": [...], "meta": {...}}`

### Detail — `{"data": {...}}`

`NotificationResource` field (mengikuti existing DB):
| Field        | Sumber existing        |
| ------------ | ---------------------- |
| `id`         | `notification.id`      |
| `type`       | `notification.type` (string literal existing, P7-C) |
| `title`      | `notification.title`   |
| `message`    | `notification.message` |
| `is_read`    | `notification.is_read` (boolean) |
| `created_at` | `created_at` (ISO-8601) |
| `updated_at` | `updated_at` (ISO-8601) |

### Mark as read — `{"data": {...}}` (resource yang sama; `is_read` kini `true`)

### Contoh nyata

```json
{
  "data": {
    "id": 12,
    "type": "booking",
    "title": "Booking Disetujui",
    "message": "Booking Anda disetujui oleh pengelola.",
    "is_read": true,
    "created_at": "2026-09-01T08:00:00+00:00",
    "updated_at": "2026-09-01T09:00:00+00:00"
  }
}
```

## 8. Error Contracts

- `401` → `{"message":"Unauthenticated."}`
- `403` → `{"message":"This action is unauthorized."}`
- `404` → `{"message":"Resource not found."}`
- `500` (production) → `{"message":"Server Error."}` — tanpa stack trace/SQL/kredensial/path
- Error handling web **tidak diubah**.

## 9. Notification Ownership

- `Tenant A → GET /api/v1/notifications/{Tenant-B-notification}` → **403** (policy default-deny).
- `Tenant A → POST /api/v1/notifications/{Tenant-B-notification}/read` → **403**, tidak mengubah record.
- List hanya berisi notifikasi `user_id = auth`. Diverifikasi server-side.

## 10. Read/Unread Behavior

- State memakai **`is_read` boolean** (existing P7-C; tidak ada kolom `read_at`).
- `POST .../read` hanya mengubah `is_read = true` bila belum dibaca (`if (! $notification->is_read)`) — **idempotent**
  (already-read tetap `true`, tidak merusak state) — persis logika web `NotificationController::markAsRead`.
- `GET` detail **read-only**, tidak mengubah read state.

## 11. Privacy Considerations

- Payload `data` (array JSON) dapat berisi metadata internal booking/payment/`unique_key`.
  **Tidak diekspos** di resource guna mencegah pembocoran data internal.
- Tidak membocorkan: password, authorization data, private storage path, payment proof path/URL,
  tenant lain, field internal (`user_id`, dll).

## 12. Performance Considerations

- `Notification` model **tanpa relasi aktif** → tidak ada eager-load tambahan; tidak ada query N+1
  (satu query untuk list/detail).
- `index` memakai `paginate` + `latest()` — satu query dengan `LIMIT/OFFSET`.
- Tidak melakukan query tambahan per notifikasi untuk membentuk respons.

## 13. P7-C Integration

- Memakai custom model `App\Models\Notification` (P7-C) — **bukan** database notifications bawaan Laravel.
- Reuse `NotificationPolicy::view`/`update` dan scope `forUser`/`unread` (P7-C).
- Reuse mekanisme read/unread existing (`is_read`) dan kriteria owning `user_id`.
- Tidak membuat notification type, event, business rule, service, atau sistem baru.

## 14. Files Created

- `app/Http/Controllers/Api/V1/NotificationController.php` — controller API (index/show/read).
- `app/Http/Resources/NotificationResource.php` — transformer notifikasi (safe exposure, tanpa payload `data`).
- `tests/Feature/ApiV1NotificationTest.php` — suite Batch 6 (21 tes).
- `docs/PHASE_7_P7D_BATCH6_API.md` — dokumen ini.

## 15. Files Modified

- `routes/api.php` — 3 route Batch 6.

Tidak ada perubahan domain/service P7-C — mengekspos fungsi existing tanpa refactor.

## 16. Tests

`tests/Feature/ApiV1NotificationTest.php` — 21 tes:
- **Auth:** list/detail/read unauthenticated → 401.
- **Tenant ownership:** lihat notifikasi sendiri; tidak dapat melihat notifikasi tenant lain (403);
  tidak dapat mark-read notifikasi tenant lain (403) + state tetap; role owner ditolak 403.
- **Listing:** hanya notifikasi authenticated tenant; pagination default 15 & cap 50; koleksi kosong → 200;
  ordering `latest()`; absen `data` & `user_id`.
- **Detail:** existing → 200; missing → 404; foreign → 403; detail **tidak** mengubah read state;
  tidak membocorkan internal payload.
- **Mark read:** unread → read (`is_read=true`, DB berubah); already-read idempotent; foreign → 403;
  missing → 404.
- **Response contract & privacy:** structure & sensitive-field absence asserted.

## 17. Verification

- `php artisan test --filter=ApiV1NotificationTest` → **21 passed (70 assertions)**.
- `php artisan test --filter="ApiV1|NotificationTest"` → **149 passed (706 assertions)**
  (Batch 1–6 API + P7-C `NotificationSystemTest`/`EmailNotificationTest`).
- `php artisan test` (full suite) → **764 passed (2531 assertions)**.
- `vendor/bin/pint --test` → **PASS**; `php artisan route:cache` → **ok** (dikembalikan via `route:clear`
  untuk dev); `npm run build` → **PASS**.
- Rule Batch 1/2: selalu `route:clear` + `config:clear` sebelum run test.

## 18. Out of Scope (tidak dikerjakan)

- WebSocket / broadcasting / realtime / push / FCM / email / SMS notification.
- Notification preferences/settings, mark-all-read, delete, unread-count endpoint.
- `/api/v1/users`, owner/admin/superadmin notification API.
- Perubahan contract/booking/billing API.
- Notification type/event/business-rule baru.
- Mobile frontend, dashboard, deployment/container, database backup, master data.
- Endpoint lain selain 3 locked endpoints Batch 6.