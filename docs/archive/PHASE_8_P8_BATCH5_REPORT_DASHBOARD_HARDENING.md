# P8 Batch 5 — Report & Dashboard Hardening

## Audit Findings

### Dashboard Queries
- Chart aggregation (super admin revenue, super admin booking, owner revenue) loaded the full 6-month dataset via `->get()` and performed `groupBy()` + `sum()`/`count()` in PHP.
- Tenant dashboard N+1: `$penghuni->kos->name` and `$penghuni->kamar->room_number` lazy-loaded relations (2 extra queries per load).
- Redundant `Favorite::pluck('kos_id')` executed twice in the tenant dashboard.

### Report Summaries
- Pendapatan, tagihan, booking, kamar, and penghuni summary values were computed in PHP by iterating the fully-loaded `$items` collection (`->sum()` and `->where('status', ...)->count()`).

### Verified Correct / NOT Changed
- Report unbounded `get()` for exports: inherently required to produce full CSV/PDF rows; changing would break the export contract.
- PDF/CSV export architecture (library unchanged).
- Admin dashboard queries: already DB-side aggregation with acceptable query count.
- `AppServiceProvider` badge queries: 4 simple COUNT queries, acceptable overhead.
- Tenant dashboard complex discovery/recommendation queries: feature-rich but bounded by `limit()`.

## Findings Fixed

| # | Finding | Severity | Fix |
|---|---------|----------|-----|
| 1 | Super admin revenue chart: full `get()` + PHP `groupBy()->sum()` | HIGH | DB `GROUP BY month` + `SUM(amount)` |
| 2 | Super admin booking chart: full `get()` + PHP count | HIGH | DB `GROUP BY month` + `COUNT(*)` |
| 3 | Owner revenue chart: full `get()` + PHP `sum()` | HIGH | DB `GROUP BY month` + `SUM(amount)` |
| 4 | Tenant dashboard N+1 on `kos`/`kamar` | MEDIUM | Added `->with(['kos', 'kamar'])` |
| 5 | Duplicate `Favorite::pluck` query | LOW | Single pluck + `array_slice` for 4-item cap |
| 6 | Pendapatan/tagihan summary PHP `->sum()` | MEDIUM | DB `SUM()` via parallel stats query |
| 7 | Booking/kamar/penghuni PHP status counts | LOW | DB `SUM(CASE WHEN ...)` aggregation |

## Files Changed

| File | Change |
|------|--------|
| `app/Http/Controllers/DashboardController.php` | DB-side chart aggregation (3 charts); `monthKeyExpr()` driver-portable month helper; eager-loaded tenant relations; consolidated Favorite query |
| `app/Http/Controllers/SuperAdmin/LaporanController.php` | DB-side summary aggregation for pendapatan, tagihan, booking, kamar, penghuni reports |
| `tests/Feature/ReportDashboardHardeningTest.php` | New test file: 8 tests |

## Behavior Preservation
- Chart output contract unchanged: same `Y-m` month keys and integer values as the previous PHP aggregation, now computed DB-side.
- Report summary labels, formats, and values unchanged (same filters applied to the parallel stats query).
- Report status definitions, period, formulas, prices, amounts, due dates, booking state unchanged.
- Report output format/contracts unchanged (headers, rows, summary block).
- Export rows still sourced from the full `$items` collection (unchanged).

## Database Portability
- Chart month aggregation uses `DB::connection()->getDriverName()` to pick the correct month expression:
  - MySQL: `DATE_FORMAT(col, '%Y-%m')`
  - PostgreSQL: `TO_CHAR(col::date, 'YYYY-MM')`
  - SQLite (tests): `strftime('%Y-%m', col)`
- No new indexes, no migration, no schema change in this batch.
- Existing Batch 4 migration `2026_09_02_000003_add_user_read_index...` unchanged (still pending due to pre-existing `audit_logs` migration bug — out of scope).

## Security
- Owner revenue chart keeps `whereHas('penghuni.kos', fn($q) => $q->where('owner_id', $user->id))` — owner isolation preserved.
- Report summaries use a parallel stats query with the exact same `where`/`whereHas`/`whereIn`/`whereDate` filters as the items query; `accessibleKosIds()` still scopes owners/admins and the 403 kos-access guard is unchanged.
- Tenant eager load only touches the authenticated tenant's own `Penghuni` record.
- No authorization changes, no new endpoints, no API contract changes.
- SQL values use query-builder bindings; `monthKeyExpr()` only inserts hardcoded column names, never user input.

## Tests
- **Before:** 892 tests, 3042 assertions
- **After:** 900 tests, 3061 assertions
- **New tests:** 8 (all in `ReportDashboardHardeningTest.php`)
- 0 regression

### New Test Coverage
- Super admin revenue chart reflects DB data
- Super admin booking chart counts accurately
- Owner revenue chart scoped to owner (1000000 vs 9000000 isolation)
- Tenant dashboard renders with eager-loaded relations
- Pendapatan report summary uses approved payments only
- Kamar report summary counts statuses correctly
- Booking report summary status breakdown
- Pendapatan report respects date filter

## Pint
PASS

## Routes
163 routes — unchanged. No changes to `routes/web.php` or `routes/api.php`.

## Build
No frontend changes; build not required.

## Out of Scope (verified)
This is the FINAL planned P8 batch and completes the P8 program. After this batch: hard stop — no P8 Batch 6, no P9, no new features, no deployment/production work.
- Report export unbounded `get()` (exports require full dataset)
- PDF/CSV architecture changes
- Redis/Horizon/APM/caching infrastructure
- Admin dashboard query consolidation (already efficient)
- P7-D API contract (CLOSED)

## Known Limitations
- Report exports still load full datasets into memory (inherent to CSV/PDF export architecture).
- `AppServiceProvider` badge queries run per authenticated page (acceptable, out of scope).
