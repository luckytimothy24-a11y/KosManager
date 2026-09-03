# TECHNICAL DEBT — Migration Chain Repair

## Problem

`php artisan migrate` fails on MySQL production with:

```text
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'timestamps' in 'audit_logs'
```

This aborts the migration chain at `2024_01_01_000015_add_integrity_to_audit_logs_table`, which in turn blocks every migration after it — including the P8 Batch 4 migration `2026_09_02_000003_add_user_read_index_to_notifications_table` (which stayed Pending).

## Root Cause

`database/migrations/2024_01_01_000015_add_integrity_to_audit_logs_table.php` line 12 used a MySQL column-position modifier that referenced a non-existent column:

```php
$table->string('integrity_hash', 64)->nullable()->after('timestamps');
```

There is **no column named `timestamps`**. In the preceding migration `2024_01_01_000014_create_audit_logs_table`, `$table->timestamps()` creates **two separate columns**: `created_at` and `updated_at` — never a single `timestamps` column.

`->after()` is **MySQL-only**. Laravel silently ignores it on SQLite and PostgreSQL. This is why the bug was masked:

- On **SQLite** (the test environment): `after()` ignored → migration succeeds → all 900 tests stayed green.
- On **PostgreSQL**: `after()` unsupported/ignored → succeeds → masked.
- On **MySQL** (production): `ALTER TABLE ... AFTER \`timestamps\`` throws `Unknown column 'timestamps' (1054)`.

### Root cause requirement answers

1. **Statement causing error:** `->after('timestamps')` in 000015 `up()`.
2. **Columns actually available at runtime:** `id, user_id, action, module, description, ip_address, timestamp_data, created_at, updated_at` — no `timestamps`.
3. **Why it accessed `timestamps`:** The author used the `timestamps()` builder *method name* as if it were a column name; MySQL resolves `after()` against real column names only.
4. **Fresh vs existing:** Fails on **both** fresh migration and existing databases on MySQL (MySQL always evaluates `after()`). Only avoided on SQLite/PostgreSQL.
5. **Ever succeeded?** Only on SQLite/PostgreSQL. Never succeeded on MySQL (confirmed: local MySQL `migrate:status` showed 000015 as Pending).
6. **Later migrations depend on this?** Yes — migrations after 000015 (e.g. Batch 4's `2026_09_02_000003`) are blocked in production because 000015 aborts first. They depend on the *columns* existing, not on column position.

## Affected Migration

`database/migrations/2024_01_01_000015_add_integrity_to_audit_logs_table.php`

## Fix

Removed the invalid `->after('timestamps')` modifier (and the dependent `->after('integrity_hash')` on `previous_hash`):

```php
public function up(): void
{
    Schema::table('audit_logs', function (Blueprint $table) {
        $table->string('integrity_hash', 64)->nullable();
        $table->string('previous_hash', 64)->nullable();
    });
}
```

Column position is purely cosmetic in MySQL DDL — it has zero effect on schema correctness, queries, indexes, or integrity behavior. Removing it makes the migration portable across MySQL, SQLite, and PostgreSQL.

**Why edit the historical migration rather than add a new one:** The target MySQL DB (`kos_manager`) is at 000014 (ran) / 000015 (pending) — **000015 has never successfully run there** and has no migration row. Editing it in place is therefore safe and non-destructive (it only adds nullable columns that have never been created). A new migration would instead risk duplicate-column errors on any DB where the fixed 000015 had already run and would be misleading.

## Database Compatibility

| Driver | Behavior | Status |
|--------|----------|--------|
| MySQL | `after()` removal avoids the `Unknown column` error; columns now created | PASS |
| PostgreSQL | `after()` was already ignored; columns already created | PASS (portable) |
| SQLite | `after()` was already ignored; columns already created | PASS |

## Existing Database Considerations

- The local MySQL DB was in a **partially-migrated** state: 000014 ran, 000015 pending, so `integrity_hash`/`previous_hash` did not exist there.
- After the fix, `php artisan migrate` (MySQL) ran both 000015 and the previously-blocked `2026_09_02_000003`.
- No historical data was modified: the fix only adds nullable columns and an additive index; `down()` (dropColumn) is unchanged and non-destructive.
- Safe for fresh installs and for the existing partially-migrated DB.

## Migration Verification

- `php artisan migrate:fresh --env=testing` (SQLite): **PASS** — full 37-migration chain completes, including 000015 and `2026_09_02_000003`.
- `php artisan migrate` (SQLite scratch DB): **PASS**.
- `php artisan migrate` (MySQL, production DB): **PASS** — `Nothing to migrate` after 000015 and `2026_09_02_000003` both marked Ran.

## Rollback Verification

- Targeted rollback of 000015 on a scratch SQLite DB: **PASS** — `down()` drops `integrity_hash`/`previous_hash` cleanly with no schema corruption.
- Re-running `migrate` re-applied 000015 successfully (columns restored).
- Note: The local MySQL DB was intentionally left fully migrated (no destructive rollback performed on it, as it holds data).

## Schema Verification (MySQL)

`audit_logs` after fix:
- `id` (PK), `user_id` (FK), `action`, `module`, `description`, `ip_address`, `timestamp_data`, `created_at`, `updated_at`, **`integrity_hash`**, **`previous_hash`**
- Confirmed: `integrity_hash` present, `previous_hash` present, `timestamps` absent (as expected).
- No unneeded columns were added.

## Audit Integrity (Regression)

- `AuditLogService::log()` verified on MySQL: creates record with 64-char integrity hash; `previous_hash = null` for first entry.
- `AuditLogService::verifyChain()` returns `valid=true`.
- Full `AuditLogServiceTest` (17 tests) passes, covering create, previous-hash chaining, tamper detection, timestamp_data tampering, wrong-secret, and fail-closed. HMAC chaining is NOT redesigned.

## Security

| Check | Result |
|-------|--------|
| Audit integrity disabled | No |
| HMAC fields removed | No |
| Default secret created | No |
| Audit secret exposed | No |
| Authorization bypassed | No |
| Historical audit logs deleted | No |
| Audit coverage reduced | No |

The change is confined to dropping an invalid MySQL column-position clause. AuditLogService, AuditLog model, controller, config, auth, and authorization are untouched. P8 Batch 3 security behavior is intact; the fix actually allows the integrity columns to exist on MySQL, strengthening production audit coverage.

## Tests

- **Before:** 900 tests, 3061 assertions
- **After:** 902 tests, 3072 assertions
- **New tests (2, in `tests/Unit/AuditLogServiceTest.php`):**
  - `test_audit_logs_schema_has_integrity_columns_after_migration`
  - `test_migration_schema_supports_audit_integrity_roundtrip`
- **0 regression** (900 prior tests all still pass)

## Pint

PASS

## Routes

163 — unchanged. No route/API changes.

## Files Changed

| File | Change |
|------|--------|
| `database/migrations/2024_01_01_000015_add_integrity_to_audit_logs_table.php` | Removed invalid `->after('timestamps')` and `->after('integrity_hash')` position modifiers |
| `tests/Unit/AuditLogServiceTest.php` | Added 2 targeted migration-schema + integrity-roundtrip tests |

## Build

No frontend changes; build not required.

## Known Limitations

- PostgreSQL was not exercised against a live PG server; correctness is established from Laravel's documented `after()` behavior (ignored except on MySQL) and portability of the code.
- The destructive rollback was only performed on a scratch DB; the production MySQL DB was left fully migrated intentionally.

## Out-of-Scope Findings (DOCUMENTED ONLY — NOT FIXED)

- Migration `2026_08_21_000001_add_expired_status_to_bookings_table` rebuilds the `bookings` table using raw SQL for SQLite compatibility — general pattern noted, not a defect, no action.
- `AuditLogService::computeHash()` uses a zero-padded fallback key when the HMAC secret is shorter than 64 chars (fail-closed design). No `AUDIT_LOG_HMAC_SECRET` is currently set in `.env` — production operators should configure it so entries use a real secret. This is existing design, out of scope.
- The migration chain uses several DB-vendor-specific migrations (documented existing practice); no action taken.
