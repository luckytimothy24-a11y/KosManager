# P7-D Batch 9: API Resource Contract (Owner Contract & Billing API — READ-ONLY)

Dokumen ini merangkum implementasi **P7-D Batch 9** — tiga endpoint **read-only** untuk **owner**
(daftar & detail kontrak + tagihan kontrak properti miliknya) di atas fondasi Batch 1–8
(auth Sanctum + role + resource + pagination + error) dan **domain kontrak/billing existing**.
Tidak ada mutation; contract/billing/payment lifecycle tidak diubah.

## 1. Objective

Ekspose kontrak sewa dan tagihan yang terkait dengan property owner agar owner dapat:
1. melihat daftar kontrak di kos miliknya;
2. melihat detail kontrak miliknya;
3. melihat tagihan yang berasal dari kontrak miliknya.

## 2. Audit Findings

- **Model** `Kontrak` (`app/Models/Kontrak.php`): relasi `penghuni()`, `kos()`, `kamar()`, `tagihans() (hasMany)`;
  casts `start_date/end_date` (date), `rental_price` (decimal:2); field `contract_number`.
- **Model** `Tagihan` (`app/Models/Tagihan.php`): relasi `penghuni()`, `kontrak()`, `kamar()`, `pembayarans()`;
  status enum `unpaid/overdue/paid/pending_verification/cancelled`; konstanta `PAYABLE`/`OUTSTANDING`;
  scopes `scopePayable()/scopeOutstanding()`; casts tanggal + `subtotal/discount/penalty/total` (decimal:2).
- **Ownership**: `Kontrak.kos_id → Kos.owner_id` (owner); juga `Kontrak.penghuni_id → Penghuni.user_id` (tenant).
- **Policy** `KontrakPolicy::view` (`app/Policies/KontrakPolicy.php`): owner via `$kontrak->kos->owner_id === $user->id`
  → **default deny**. `KontrakPolicy::viewAny` owner diizinkan.
- **Policy** `TagihanPolicy::view`: owner via `$tagihan->kamar->kos->owner_id === $user->id` (dari `kamar`, bukan kontrak).
- **Web controller** `Owner\KontrakController::index` query established:
  `Kontrak::with(['penghuni.user','kos','kamar'])->whereHas('kos', owner_id)->latest()` — di-reuse untuk API list.
- **Resources existing** `KontrakResource`/`TagihanResource` bersifat tenant (Batch 5/4), **tanpa identitas penghuni**.
  Untuk owner, identitas penghuni (siapa pemegang kontrak) legitimate → dibuat `OwnerKontrakResource`/`OwnerTagihanResource`.

## 3. Ownership Model

```
Owner ─→ Kos ─→ Kamar ─→ Kontrak ─→ Tagihan  (via kontrak.kos_id / tagihan.kontrak_id)
```

Owner hanya dapat melihat kontrak & tagihan pada kos dengan `kos.owner_id === auth`. Jalur konkret di query:
- List kontrak: `Kontrak::whereHas('kos', owner_id)`.
- Detail kontrak: `KontrakPolicy::view` (`kos->owner_id === user->id`).
- Tagihan: **authorize parent kontrak dulu**, lalu `$kontrak->tagihans()` (nested security).

## 4. Locked Endpoints

| Method | Endpoint                                  | Auth                        | Status |
| ------ | ----------------------------------------- | --------------------------- | ------ |
| GET    | `/api/v1/owner/kontrak`                   | `auth:sanctum + role:owner` | ✅ |
| GET    | `/api/v1/owner/kontrak/{kontrak}`         | `auth:sanctum + role:owner` | ✅ |
| GET    | `/api/v1/owner/kontrak/{kontrak}/tagihan` | `auth:sanctum + role:owner` | ✅ |

Route names: `api.v1.owner.kontrak.index/show/tagihan`.

## 5. Authentication

- `auth:sanctum` (constructor controller). Tanpa/token rusak → `401 {"message":"Unauthenticated."}`.
- Hanya owner. Role lain → `403`.

## 6. Authorization

- `role:owner` → tenant/admin/super_admin → `403`.
- Detail & tagihan: `authorize('view', $kontrak)` via `KontrakPolicy::view` (owner via `kos->owner_id`).
- Tagihan: **parent kontrak ter-authorize dahulu** sebelum tagihan di-query (menolak get Bills B via Contract B oleh owner lain).
- Tidak ada `?owner_id=`/`?user_id=`/`?kos_owner_id=` sebagai mekanisme keamanan.

## 7. Contract Query Behavior

- List: `Kontrak::with(['penghuni.user','kos','kamar'])->whereHas('kos', owner_id)->latest()->paginate()`.
- Detail: route binding + `authorize` + eager-load `penghuni.user,kos,kamar`.
- Ordering deterministik `latest()` (created_at desc).

## 8. Billing/Tagihan Query Behavior

- `$kontrak->tagihans()->with(['penghuni.user'])->latest()->paginate()` — authentic setelah parent ter-authorize.
- Tidak memanggil service mutation; murni read via relationship.

## 9. Request/Query Parameters

GET tanpa body. Satu query opsional: `per_page` (default `15`, cap `50`) untuk list & tagihan (konvensi API Batch 2).
Tidak ada filter baru (search/status/date) ditambahkan demi API read-only; pagination mengikuti pola existing.

## 10. Response JSON Contract

Semua `Content-Type: application/json`.

### GET `/api/v1/owner/kontrak` — `{"data": [...], "meta": {...}}`

`OwnerKontrakResource` per item:
| Field | Sumber |
| ----- | ------ |
| `id` | `kontrak.id` |
| `contract_number` | `kontrak.contract_number` |
| `penghuni.id` | `kontrak.penghuni.user_id` |
| `penghuni.name` | `kontrak.penghuni.user.name` |
| `kos.id` | `kontrak.kos.id` |
| `kos.name` | `kontrak.kos.name` |
| `kamar.id` | `kontrak.kamar.id` |
| `kamar.name` | `kontrak.kamar.room_name` |
| `kamar.room_number` | `kontrak.kamar.room_number` |
| `rental_type` | `rental_type` (`daily`/`monthly`) |
| `rental_price` | `rental_price` (float) |
| `start_date` | `start_date` (date) |
| `end_date` | `end_date` (date) |
| `status` | `status` |
| `notes` | `notes` |

Contoh:
```json
{
  "id": 1,
  "contract_number": "KT123456",
  "penghuni": { "id": 7, "name": "Andi Wijaya" },
  "kos": { "id": 10, "name": "Kos Melati" },
  "kamar": { "id": 5, "name": "Kamar A1", "room_number": "A1" },
  "rental_type": "monthly",
  "rental_price": 1500000,
  "start_date": "2026-09-01",
  "end_date": "2027-08-31",
  "status": "active",
  "notes": null
}
```

### GET `/api/v1/owner/kontrak/{kontrak}` — `{"data": {...}}`

Resource sama (`OwnerKontrakResource`), struktur `data` identik contoh di atas.

### GET `/api/v1/owner/kontrak/{kontrak}/tagihan` — `{"data": [...], "meta": {...}}`

`OwnerTagihanResource` per item:
| Field | Sumber |
| ----- | ------ |
| `id` | `tagihan.id` |
| `bill_number` | `tagihan.bill_number` |
| `kontrak_id` | `tagihan.kontrak_id` |
| `penghuni.id` | `tagihan.penghuni.user_id` |
| `penghuni.name` | `tagihan.penghuni.user.name` |
| `bill_type` | `bill_type` |
| `kamar_id` | `kamar_id` |
| `period_start` | `period_start` (date) |
| `period_end` | `period_end` (date) |
| `subtotal` | `subtotal` (float) |
| `discount` | `discount` (float) |
| `penalty` | `penalty` (float) |
| `total` | `total` (float) |
| `due_date` | `due_date` (date) |
| `status` | `status` |

Contoh:
```json
{
  "id": 101,
  "bill_number": "TG456789",
  "kontrak_id": 1,
  "penghuni": { "id": 7, "name": "Andi Wijaya" },
  "bill_type": "rent",
  "kamar_id": 5,
  "period_start": "2026-09-01",
  "period_end": "2026-09-30",
  "subtotal": 1500000,
  "discount": 0,
  "penalty": 0,
  "total": 1500000,
  "due_date": "2026-09-10",
  "status": "unpaid"
}
```

`meta` (list & tagihan): `current_page, last_page, per_page, total`.

## 11. Error Contract

- `401` → `{"message":"Unauthenticated."}`
- `403` → `{"message":"This action is unauthorized."}`
- `404` → `{"message":"Resource not found."}`
- `500` (production) → `{"message":"Server Error."}`
Tidak ada format error baru.

## 12. Resources

- `app/Http/Resources/OwnerKontrakResource.php` — transform kontrak owner (list & detail).
- `app/Http/Resources/OwnerTagihanResource.php` — transform tagihan kontrak owner.

Keduanya ekspos **minimum identitas penghuni** (id + name); tidak mengekspos `identity_number/phone/email` penghuni,
payment proof path, internal payload, atau authorization data.

## 13. Controllers

- `app/Http/Controllers/Api/V1/Owner/OwnerKontrakController.php` — `index`, `show`, `tagihan` (thin: scope + policy + resource).

## 14. Existing Policy/Service/Domain Reuse

- `KontrakPolicy::view` untuk authorization detail & parent tagihan (default deny).
- Query list **identik** dengan `Owner\KontrakController::index` (web, tidak diubah).
- Model `Kontrak`/`Tagihan` + relasi + casts existing. Tidak memanggil service mutation (`PaymentService`, controller billing)
  untuk kebutuhan GET — murni pembacaan.

## 15. Security

- Ownership via `kos.owner_id` di server; scope di query DB (bukan filter PHP).
- **Nested ownership**: tagihan hanya dikembalikan setelah parent kontrak ter-authorize → Owner A tidak bisa memakai
  Contract B untuk mengambil Bills B; tidak ada leakage.
- Explicit cross-owner test: Owner A↔B (list, detail, bills) → tanpa bocor kontrak/bills pihak lain.

## 16. Performance

- `index`: satu query `paginate` + eager-load `penghuni.user,kos,kamar` + `whereHas('kos', owner_id)` → tanpa N+1.
- `show`: route binding + eager-load 3 relasi.
- `tagihan`: query via `$kontrak->tagihans()` (scope parent) + eager `penghuni.user` + `paginate` → tanpa N+1.
- Tanpa premature optimization.

## 17. Tests

`tests/Feature/ApiV1OwnerContractTest.php` — 17 tes (88 assertions):
- **Auth**: unauthenticated → 401 (3 endpoint); tenant/admin/super_admin → 403 (list/detail/tagihan).
- **List**: hanya kontrak milik owner; foreign tidak muncul; kosong valid; pagination; ordering deterministik;
  relasi (penghuni/kos/kamar) benar.
- **Detail**: detail milik owner → 200 + contract; owner lain → 403; missing → 404.
- **Tagihan**: bills kontrak milik owner; kosong valid; pagination; kontrak owner lain → 403 (parent authorize).
- **Cross-owner**: Owner A→B & Owner B→A (detail & tagihan) ditolak; bills tidak bocor antar owner;
  respons tidak berisi `identity_number` penghuni.

## 18. Verification

- Batch 9: `php artisan test tests/Feature/ApiV1OwnerContractTest.php` → **17 passed (88 assertions)**.
- Full suite: `php artisan test` → **813 passed (2796 assertions)** (sebelumnya 796 → +17).
- Pint: `vendor/bin/pint --test` → **PASS** (4 file baru di-fix EOF lalu lulus).
- Routes: `php artisan route:list --path=api/v1/owner` → **9 routes** (B7: 4 + B8: 2 + B9: 3).
- Route cache: `php artisan route:cache` → **OK** (lalu `route:clear` untuk dev).
- Build: `npm run build` → **PASS**; tanpa perubahan frontend.
- Regresi Batch 1–8, contract/billing/payment, P7-C, tenant — semua hijau di full suite.

## 19. Out of Scope

- POST/PUT/PATCH/DELETE kontrak; terminate/check-in/check-out.
- POST/PUT/PATCH/DELETE tagihan; POST pembayaran; owner payment mutation.
- Owner booking mutation; owner notification API.
- Owner CRUD Kos/Kamar.
- Admin/SuperAdmin/Users API.
- Tenant API changes / Batch 1–8 endpoint changes.
- WebSocket/broadcasting/realtime/push/FCM/email/SMS; mobile frontend; dashboard redesign.
- Payment gateway changes; notification changes; new contract/billing/payment rules.
- Deployment/Docker/backup; master data.
- Endpoint lain selain 3 locked endpoint Batch 9.

## 20. Known Issues / Future Recommendations

- (tidak ada issue).
- Future/rekomendasi (di luar scope, tidak diimplementasi):
  - Jika owner membutuhkan filter `status`/`search` pada API kontrak, buat batch tersendiri yang mengekspos parameter
    web established (`search`, `status`) — bukan ditambahkan impulsif di API read-only ini.
  - Jika owner memerlukan ringkasan tagihan per kontrak (jumlah/belum bayar), itu bisa menjadi batch dashboard
    tersendiri yang memakai `Tagihan::payable()/outstanding()` existing.