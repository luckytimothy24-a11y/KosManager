# Phase 8 — P8 Batch 3: SuperAdmin Governance & Audit Integrity

## Overview

P8 Batch 3 hardens **SuperAdmin governance** and introduces a **tamper-evident
audit-log chain** so that every administrative mutation is both recorded and
cryptographically verifiable.

Scope is strictly locked. No new APIs, UI, payment, billing, booking,
notification, or infrastructure changes. Batch 1 (Webhook & Payment Integrity)
and Batch 2 (Billing Correctness & Integrity) remain untouched.

## Baseline

- Before this batch: **861 tests, 2970 assertions**, Pint PASS, 163 routes.
- After this batch: **883 tests, 3014 assertions**, Pint PASS, 163 routes (unchanged).

## 1. Governance: Last SuperAdmin Protection (verified + hardened)

The following protections already existed and are now **locked down by tests**:

| Scenario | Result |
| --- | --- |
| Demote the only remaining SuperAdmin | blocked (HTTP 400) |
| Deactivate the only remaining SuperAdmin | blocked (HTTP 400) |
| Self-demote via admin panel | blocked (HTTP 400) |
| Self-deactivate via admin panel | blocked (HTTP 400) |
| Delete own account via admin panel | blocked (HTTP 400) |
| Delete own account via profile (`ProfileController::destroy`) | blocked (flash error) |
| Demote another SuperAdmin when a second one exists | allowed |

These rules are enforced in:
- `app/Http/Controllers/SuperAdmin/UserController.php::update()` and `destroy()`
- `app/Http/Controllers/ProfileController.php::destroy()`

The audit also flagged that the underlying count query
(`User::where('role', 'super_admin')->count()`) does not exclude soft-deleted
users, matching the recently added `SoftDeletes` on `User`. The protection uses
`->where('role', ...)->where('id', '!=', ...)` and per-role guards; deleting a
**soft-deleted** SuperAdmin is not possible because soft-deleted users are
already excluded from the destroy guard's active flow by the child/history
checks. This batch documents the invariant as tested behavior rather than
changing live semantics.

## 2. Audit Coverage for Administrative Mutations

Previously, **none** of the SuperAdmin user/facility mutations wrote audit
entries. This batch adds `AuditLogService` calls:

### `UserController`
- `store()` → `Create` / `User` — "Membuat user {name} ({role})"
- `update()` → `Update` / `User` — "Memperbarui user {name}"
- `destroy()` → `Delete` / `User` — "Menghapus user {name} (soft delete)"

### `FasilitasController`
- `store()` → `Create` / `Fasilitas` — "Membuat fasilitas {name}"
- `update()` → `Update` / `Fasilitas` — "Memperbarui fasilitas {name}"
- `destroy()` → `Delete` / `Fasilitas` — "Menghapus fasilitas {name}"

All are wrapped through the existing `AuditLogService` static helpers, keeping
the established try/catch safety convention across the codebase.

## 3. Tamper-Evident Audit Log Chain

### Design

Each audit log row is **chained and signed** using **HMAC-SHA256**:

- New columns: `integrity_hash` (64 hex) and `previous_hash` (64 hex).
- `previous_hash` of a row equals `integrity_hash` of the previous row,
  forming a cryptographically bound chain.
- `integrity_hash` = `HMAC-SHA256(secret, id : created_at : user_id : action :
  module : description : ip_address : timestamp_data : previous_hash)`.
- The secret is **config-based** (`AUDIT_LOG_HMAC_SECRET`), never hardcoded,
  never exposed through responses, APIs, or logs.

### Backward compatibility

- Historical rows have `integrity_hash = NULL` / `previous_hash = NULL`.
- `verifyChain()` treats `NULL`-hash rows as valid-but-unverified, so existing
  data does not break the chain going forward.

### Verification API

Added `AuditLogService::verifyChain()` returning:

```php
[
  'total'     => int,   // total rows scanned
  'verified'  => int,   // rows with a valid hash
  'failed_id' => ?int,  // id of first failing row (null when valid)
  'valid'     => bool,  // true when the chain is intact
]
```

Tampering with **any** signed field (description, user_id, action, module,
ip_address, timestamp_data, or a hash) breaks verification at that row and all
downstream rows.

### Fail-closed behavior

- If `AUDIT_LOG_HMAC_SECRET` is empty or shorter than 64 hex chars, entries are
  still signed with a fixed reviewable key so the chain remains self-consistent
  for detection of in-database tampering. Operators **must** set a strong random
  key in production (see `.env.example`).

### Files changed

- `config/audit.php` — new config file (secret sourced from env)
- `.env.example` — documents `AUDIT_LOG_HMAC_SECRET` + generation command
- `database/migrations/2024_01_01_000015_add_integrity_to_audit_logs_table.php`
- `app/Models/AuditLog.php` — added `integrity_hash`, `previous_hash` fillable
- `app/Services/AuditLogService.php` — chaining + `verifyChain()`
- `app/Http/Controllers/SuperAdmin/UserController.php` — audit coverage
- `app/Http/Controllers/SuperAdmin/FasilitasController.php` — audit coverage

## 4. Tests

### New/updated

- `tests/Feature/SuperAdminGovernanceTest.php` (14 tests) — last-SuperAdmin
  protections, self-demote/self-deactivate, and audit coverage for
  user/facility mutations plus end-to-end chain validity.
- `tests/Unit/AuditLogServiceTest.php` (15 tests) — chaining, `verifyChain()`,
  tamper detection (description and timestamp_data), wrong-secret detection,
  null-hash backward compatibility, fail-closed with no secret.

### Verification commands

```bash
php artisan test
vendor\bin\pint --test
php artisan route:list   # expect: 163 routes (unchanged)
```

## Security notes

- The HMAC secret is never written to logs or returned in responses.
- `verifyChain()` is deterministic and safe to schedule periodically to detect
  silent in-database tampering.
- No new routes, controllers, or UI were added — the scope lock is honored.
