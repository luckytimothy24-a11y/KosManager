# Fase 1A — Fondasi Role + Perbaikan Cross-Driver

**Branch:** `chore/fase-0-baseline` @ `12e899d`
**Status:** Selesai. Menunggu persetujuan eksplisit sebelum Fase 1B.

## Ringkasan

- Tambah role `technician` (additive, rollback-able) ke `users.role` (DB enum SQLite & MySQL/MariaDB).
- Pertahankan `super_admin` (internal), `pencari` (canonical), `tenant` (alias aplikasi).
- Perbaiki blocker MySQL/MariaDB pada migrasi `2026_09_11_000003` (raw SQL SQLite → driver-aware).
- Tambah `config/roles.php` (canonical, internal, legacy_map, aliases, demo).
- Tambah `isTechnician()` helper, update `UserRole` enum + label.
- Seeder demo teknisi (hanya non-production).
- Update karakterisasi & unit test (5 role).

## Verifikasi

- SQLite full suite: **2416 passed**, 8365 assertion, exit 0.
- MySQL/MariaDB: migrasi berjalan; 2416 test dieksekusi (25 error + 9 failure pre-existing, bukan akibat 1A — FK enforcement, SQLite-only backup driver, search escaping).
- Pint: passed.
- CI run #45128909033: **success**.

## File berubah

- `app/Enums/UserRole.php`
- `app/Traits/HasRole.php`
- `config/roles.php`
- `database/migrations/2026_09_11_000003_add_weekly_to_rental_type_enum.php`
- `database/migrations/2026_11_23_000001_add_technician_role_to_users_table.php`
- `database/seeders/UserSeeder.php`
- `tests/Feature/TechnicianRoleTest.php`
- `tests/Feature/Characterization/DomainValuesCharacterizationTest.php`
- `tests/Unit/UserRoleTest.php`

## Catatan cross-driver (pre-existing, bukan 1A)

- MySQL: FK `damage_reports.user_id` gagal pada test data (SQLite FK off by default).
- MySQL: backup driver test hanya SQLite.
- MySQL: `TenantKosBrowseTest` search escaping `%` (LIKE wildcard) tidak ter-escape di MySQL.
- Belum diperbaiki (di luar scope 1A).

## Gate berikutnya

Menunggu persetujuan eksplisit untuk Fase 1B (RBAC defense-in-depth).
