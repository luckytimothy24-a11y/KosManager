# P8 Batch 4 — Admin Notifications & Scheduler Resilience

## Audit Findings

### Notification Architecture (Verified Correct)
- Custom `notifications` table with `user_id`, `type`, `title`, `message`, `is_read`, `data`
- `NotificationService` with 17 convenience methods + `create()` with uniqueKey dedup (24h window)
- Owner receives: bookingNew, paymentSubmitted, checkoutRequested, bookingCancelledByTenant
- Tenant receives: all other business event notifications
- NotificationPolicy enforces `user_id` ownership
- Email queue via `KosManagerMail` (ShouldQueue) with soft failure handling

### Scheduler Architecture (Verified Correct)
- 4 domain commands scheduled daily at 00:05–07:00
- `backup:run` with `withoutOverlapping()` + `onOneServer()`
- `ExpireOldBookings` and `ExpireOldKontraks` use `DB::transaction` with `lockForUpdate()`
- `MarkOverdueTagihans` uses atomic conditional UPDATE
- `RemindDueTagihans` uses uniqueKey dedup

### Admin/SuperAdmin Notification Gap — Verified NOT a Real Gap
- Admins use identical Owner routes and can see payment/booking status directly
- Owners ARE notified when admin performs actions
- SuperAdmin has separate panel, no business event notification requirement from PRD

## Actual Gaps Found & Fixed

| # | Gap | Severity | Fix |
|---|-----|----------|-----|
| 1 | 4 scheduled commands lack `withoutOverlapping()` | HIGH | Added to all 4 commands |
| 2 | Single item failure halts entire batch | HIGH | Added per-item try/catch with logging |
| 3 | `checkoutApproved` unique_key collision (per user_id) | MEDIUM | Changed to per-penghuni_id |
| 4 | Auto-checkout notification outside transaction | MEDIUM | Wrapped in try/catch per notification |
| 5 | No notification cleanup (unbounded growth) | MEDIUM | Added `notification:cleanup` command + monthly schedule |
| 6 | No composite index on (user_id, is_read) | LOW | Added migration |

## Files Changed

| File | Change |
|------|--------|
| `app/Console/Kernel.php` | Added `withoutOverlapping()` to 4 commands; added `notification:cleanup` monthly |
| `app/Console/Commands/ExpireOldBookings.php` | Added per-item try/catch around DB::transaction + notification |
| `app/Console/Commands/ExpireOldKontraks.php` | Added per-item try/catch; fixed unique_key to per-penghuni_id; added `penghuni_id` to return |
| `app/Console/Commands/MarkOverdueTagihans.php` | Added try/catch around notification send |
| `app/Console/Commands/RemindDueTagihans.php` | Added per-item try/catch around notification send |
| `app/Services/NotificationService.php` | Added `cleanup()` static method |
| `app/Console/Commands/CleanupNotifications.php` | New command: `notification:cleanup` |
| `database/migrations/2026_09_02_000003_add_user_read_index_to_notifications_table.php` | New migration: composite index |
| `tests/Feature/SchedulerResilienceTest.php` | New test file: 9 tests |

## Notification Changes
- None. All existing notification behavior preserved. No new notification types added.

## Scheduler Changes
- All 4 domain commands now have `withoutOverlapping()` (prevents concurrent execution)
- `notification:cleanup` added as monthly scheduled task
- Per-item isolation in `ExpireOldBookings` and `ExpireOldKontraks` (one failure doesn't halt batch)
- Notification sends wrapped in try/catch (failure logged, not propagated)

## Idempotency
- All commands remain idempotent: running twice produces same result
- `withoutOverlapping()` adds defense-in-depth for concurrent scheduler invocations
- `MarkOverdueTagihans`: atomic conditional UPDATE prevents double-transition
- `ExpireOldBookings`: lockForUpdate + re-check of `status === 'approved'` prevents double-expire
- `ExpireOldKontraks`: lockForUpdate + re-check of status prevents double-checkout

## Concurrency
- `withoutOverlapping()` on all scheduled commands prevents overlapping execution
- Per-item DB::transaction with lockForUpdate protects individual record processing
- Notification dedup via uniqueKey prevents duplicate notifications within 24h window

## Security
- Authorization recipients unchanged (owner/tenant correctly targeted)
- Tenant isolation preserved (NotificationPolicy, API role:tenant middleware)
- Admin/superadmin boundaries unchanged
- No new endpoints or API contract changes
- No sensitive data in log warnings
- Scheduler commands remain trusted backend processes

## Database Changes
- Migration: composite index `(user_id, is_read)` on `notifications` table for badge count query performance
- No destructive changes; index is additive only
- MySQL/PostgreSQL/SQLite compatible

## Tests
- **Before:** 883 tests, 3014 assertions
- **After:** 892 tests, 3042 assertions
- **New tests:** 9 (all in `SchedulerResilienceTest.php`)
- 0 regression

### New Test Coverage
- Idempotency of all 4 scheduled commands
- `notification:cleanup` with default and custom days
- Auto-checkout unique key per penghuni_id
- Admin notification isolation (tenant receives payment verified notification)

## Pint
PASS

## Routes
No changes to `routes/web.php` or `routes/api.php`.

## Build
No frontend changes; build not required.

## Known Limitations
- Notification dedup uses 24-hour window (existing behavior, not changed)
- `notification:cleanup` runs monthly by default (configurable via `--days` option)
- `withoutOverlapping()` requires cache driver (database or Redis) to function

## Explicitly Verified Out-of-Scope
- P8 Batch 5 (report/dashboard optimization)
- Payment gateway/webhook
- Billing calculation
- Booking architecture
- Audit-log redesign
- UI redesign
- Infrastructure changes
- No new features added
