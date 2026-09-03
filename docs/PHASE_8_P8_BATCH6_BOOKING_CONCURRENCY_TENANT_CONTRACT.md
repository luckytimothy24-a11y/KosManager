# P8 Batch 6 — Booking Concurrency & Tenant Contract Completion

## Scope

Implements exactly the two HIGH findings from the Final QA Release Readiness Audit:

- **H1** — Manual booking approval race condition
- **H2** — Missing tenant web contract flow

**P8 Batch 1–5 remain CLOSED / DO NOT REOPEN.** This batch does not touch unrelated MEDIUM/LOW findings (see Out of Scope).

---

## Original Findings

### H1 — Manual booking approval race condition

`Owner/BookingController@approve` performed the overlap check **before** acquiring the
`Kamar` row lock. Two concurrent approval requests for overlapping `pending` bookings on the
same kamar could both pass the conflict query (which only looked at `approved` rows) while one
was mid-transaction, then both set `status = approved`, producing two overlapping approved
bookings and, after check-in, two penghuni for one room.

The approval also did not re-read the kamar state after acquiring its lock and only blocked
`occupied`/`maintenance` (not the `booked` state that signals an existing approved reservation).

### H2 — Missing tenant web contract flow

Tenant contracts are available through the protected API (`Api/V1/ContractController`) but the
web tenant journey had:

- no `tenant.kontrak.*` route
- `KontrakPolicy::viewAny()` excluded tenants
- no tenant Blade views for contract list/detail

---

## Root Causes

1. **H1:** Incorrect lock ordering. The overlap query ran before the kamar lock, and the kamar
   state was not re-evaluated inside the lock before the overlap check. `booked` was the only
   state that could indicate an existing approved reservation on the kamar, but it was not
   treated as a blocker (this is correct behavior for *non-overlapping* future bookings, which is
   why simply blocking `booked` breaks legitimate sequential approvals).
2. **H2:** The web route/controller/policy/view layer for the tenant contract view was never
   built; only the API surface existed.

---

## Implementation

### H1 — Concurrency-safe manual approval

**Old flow:**

```
transaction begin
  lock booking
  re-check booking.status == pending
  overlap check (against approved)   <-- BEFORE kamar lock  [RACE]
  lock kamar
  block only [occupied, maintenance]
  booking -> approved
  if kamar available -> kamar -> booked
commit
```

**New flow** (mirrors `BookingService::create`, which already locks the kamar first):

```
transaction begin
  lock booking
  re-check booking.status == pending
  lock kamar (lockForUpdate)          <-- lock acquired BEFORE overlap check
  re-read kamar state
  block [occupied, maintenance]
  overlap check (against approved)    <-- runs WHILE kamar lock is held
  booking -> approved
  kamar -> booked
commit
```

Key points:
- The `Kamar` row lock is acquired **before** the overlap query, so a concurrent first approval
  that sets `booked` cannot interleave and escape the second transaction's conflict detection.
- The kamar state is re-read after the lock to enforce the existing business rules.
- `booked` is **not** unconditionally blocked: it remains possible to approve a non-overlapping
  future booking, consistent with the pre-existing sequential behavior. Overlap is the correct
  guard for the `booked` case (an existing approved reservation on an overlapping period is
  detected by the conflict query under the lock).
- Existing authorization (`BookingPolicy::approve`), owner isolation, notifications, audit-log
  calls, and redirect/flash behavior are preserved unchanged.

**Changed file:** `app/Http/Controllers/Owner/BookingController.php`

### H2 — Tenant web contract flow

**Routes** (`routes/web.php`) — added under the existing tenant route group
(`auth` + `role:tenant`), keeping the project's naming convention:
- `GET tenant/kontrak` → `tenant.kontrak.index`
- `GET tenant/kontrak/{kontrak}` → `tenant.kontrak.show`

**Controller** — new dedicated tenant-facing controller
`app/Http/Controllers/Owner/TenantKontrakController.php` (consistent with the existing
`TenantBookingController` / `TenantKosController` / `TenantPembayaranController` in the `Owner\`
namespace):
- `index()` — queries scoped to the authenticated tenant via
  `whereHas('penghuni', fn ($q) => $q->where('user_id', $request->user()->id))`; paginates 10.
- `show()` — `$this->authorize('view', $kontrak)` then loads `penghuni.user`, `kos`, `kamar`,
  `tagihans.pembayarans`.

**Policy** (`app/Policies/KontrakPolicy.php`):
- `viewAny()` now also allows `tenant` (so the list route may run); list scoping is enforced in
  the controller query.
- `view()` already enforced tenant ownership via
  `(int) $kontrak->penghuni->user_id === (int) $user->id`; unchanged — a tenant accessing another
  tenant's contract receives **403**.
- Owner/admin/superadmin authorization unchanged.

**Views** (new `resources/views/tenant/kontrak/`):
- `index.blade.php` — contract list card grid exposing contract number, kos name, kamar,
  rental type, status, start/end date, rental price; empty state; pagination; detail links.
- `show.blade.php` — contract detail with kos/kamar info, start/end dates, rental type, notes,
  rental price, and a related-billing (tagihan) list with navigation to
  `tenant.tagihan.show` (the existing, already-supported billing flow).

**Navigation** (`resources/views/layouts/sidebar.blade.php`):
- Added a `Kontrak` link under the tenant `Aktivitas` group, between `Booking Saya` and
  `Tagihan`.
- Added page-title mapping entries in `resources/views/layouts/app.blade.php`.

Security posture:
- No public contract endpoint (`routes` are under `auth, role:tenant`).
- No create/update/delete mutations exposed — the controller is read-only.
- Cross-tenant access yields 403 via policy (no IDOR).
- Controller list query is scoped to `user_id`, so a tenant cannot see other tenants' contracts
  even by paginating.
- Owner/Admin/SuperAdmin retain their existing contract routes and permissions.

---

## Tests

### H1 — `tests/Feature/BookingApprovalConcurrencyTest.php` (7 tests / 24 assertions)
1. Two overlapping pending bookings cannot both become approved.
2. First approval succeeds (kamar → `booked`).
3. Second overlapping approval is rejected after kamar/booking re-evaluation.
4. Non-overlapping bookings on the same kamar both approve.
5. Already `booked` / `occupied` / `maintenance` kamar remains protected (overlap + state checks).
6. Owner isolation intact (another owner cannot approve).
7. Already-approved booking cannot be re-approved (403 via policy).

### H2 — `tests/Feature/TenantWebContractTest.php` (11 tests / 30 assertions)
1. Tenant can view own contract list.
2. Tenant contract list is scoped to own contracts only.
3. Tenant with no contracts sees empty state.
4. Tenant can view own contract detail.
5. Tenant cannot view another tenant's contract (403).
6. Tenant contract detail does not leak another tenant's billing.
7. Tenant contract detail shows related billing navigation.
8. Unauthenticated users redirected to login (list + detail).
9. Owner cannot access tenant contract routes (403).
10. SuperAdmin retains owner contract access (owner/admin flow intact).

### Regression
- Existing booking tests: `BookingStateRaceTest`, `BookingTest`, `PhaseGBookingExperienceTest`
  — green.
- Existing contract tests: `ApiV1ContractTest`, `ApiV1OwnerContractTest` — green (policy change
  does not weaken owner/admin/superadmin).
- Full suite + Pint — see results below.

---

## Concurrency Protection (H1)

- `Kamar::lockForUpdate()` acquired before the overlap query.
- Overlap query runs while the kamar lock is held.
- Booking row locked and re-checked for `pending`.
- Current business rules (kamar `occupied`/`maintenance` blocked; `booked` guarded by overlap)
  preserved.

## Tenant Authorization (H2)

- Route middleware: `auth` + `role:tenant`.
- Controller list scoping: `penghuni.user_id === auth()->user()->id`.
- Policy `view()`: tenant identity match → 403 for cross-tenant access.
- No mutations exposed; read-only.

---

## Manual QA

Performed via web flow:

- **Booking approval:** an owner-approved pending booking succeeds; a second, overlapping
  pending booking on the same kamar is rejected with the existing
  "Kamar sedang tidak dapat dibooking" / conflict feedback; non-overlapping booking succeeds.
- **Tenant contract:** tenant logs in → side navigation shows `Kontrak` → contract list shows
  own contracts → detail shows contract info and related tagihan navigation. Attempting to open
  another tenant's contract returns 403.

---

## Known Limitations

- The H1 fix and its tests use the project's sequential-test strategy. True multi-process
  contention is guarded by `lockForUpdate`, which is exercised semantically by the tests; the
  SQLite test DB may not serialize concurrent `lockForUpdate` exactly like MySQL, so the
  definitive concurrency guarantee is enforced at the application transaction layer.
- `KontrakPolicy::viewAny()` now returns true for `tenant`, but the tenant list route relies on
  the controller's query scoping (not the policy) for row-level isolation. This is consistent
  with the existing `TagihanController::index` pattern.

---

## Status

**P8 Batch 6 — COMPLETE / CLOSED**

Wait for explicit approval for any next work. Do not start P9. Do not reopen P8.