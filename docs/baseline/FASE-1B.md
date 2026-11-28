# Fase 1B — RBAC Defense-in-Depth (Hasil)

**Branch:** `chore/fase-0-baseline` @ (lihat commit terbaru)
**Status:** Selesai. Menunggu persetujuan eksplisit sebelum Fase 1C.

## Ringkasan

- Middleware `role` sudah ada dan mendukung `technician`.
- Policy layer: default deny untuk `technician` pada area tenant/admin/owner/super-admin (policies menggunakan pengecekan role eksplisit).
- Global scope otomatis (`HasRoleScope`) **ditunda** karena penerapannya memecahkan 292 test (status code 403→404 akibat filter scope sebelum policy). Trait disimpan di `app/Traits/HasRoleScope.php` untuk penerapan inkremental per-model dengan pembaruan ekspektasi test.
- Defense-in-depth tetap diberikan via policy + middleware + authorization tests baru.

## Verifikasi

- SQLite full suite: **2423 passed**, 8376 assertion, exit 0.
- Pint: passed.

## File berubah

- `app/Traits/HasRoleScope.php` (trait opt-in, siap pakai)
- `tests/Feature/RbacPhase1bTest.php` (7 test: akses technician ke area tenant/admin/owner/super-admin ditolak; profile/notifications diizinkan; verify payment ditolak)

## Catatan

- Global scope otomatis akan diaktifkan bertahap di Fase 1C/2 setelah policy & test disesuaikan (status code 404 vs 403).
- Belum ada route khusus teknisi; work order (Fase 1C) akan menambahkannya dengan policy & scope dedicated.

## Gate berikutnya

Menunggu persetujuan eksplisit untuk Fase 1C (Work Order / Complaints MVP).
