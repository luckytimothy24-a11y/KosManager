# Fase 1C — Work Order / Complaints MVP (Hasil)

**Branch:** `chore/fase-0-baseline` @ `60752dc`
**Status:** Selesai. Menunggu persetujuan eksplisit sebelum Fase 1D.

## Ringkasan

- Modul Work Order baru (tabel `work_orders`) **non-destruktif** terhadap legacy `DamageReport`/`Dispute`.
- Link opsional ke legacy via `legacy_damage_report_id` / `legacy_dispute_id`.
- Status flow: `pending → assigned → in_progress → resolved → closed`.
- Prioritas: `low/normal/high/urgent`.
- Policy `WorkOrderPolicy`: viewAny/view/create/update/assign/delete dengan scoping per role.
- Routes terpisah (`routes/work-order.php`) di bawah feature flag `FEATURE_WORK_ORDERS`.
- Teknisi hanya melihat/mengupdate work order yang ditugaskan kepadanya.
- Tenant (pencari) membuat work order dari hunian aktif; admin/owner/super_admin mengelola & assign teknisi.

## Verifikasi

- SQLite full suite: **2428 passed**, 8383 assertion, exit 0.
- MySQL/MariaDB: 5/5 WorkOrderTest passed.
- Pint: passed.
- CI run #45141462795: **success**.

## File berubah

- `app/Models/WorkOrder.php`
- `app/Policies/WorkOrderPolicy.php`
- `app/Http/Controllers/Owner/WorkOrderController.php`
- `database/migrations/2026_11_23_000002_create_work_orders_table.php`
- `database/factories/WorkOrderFactory.php`
- `config/features.php`
- `routes/work-order.php`, `routes/web.php`
- `resources/views/owner/work-orders/*`
- `app/Providers/AuthServiceProvider.php`
- `tests/Feature/WorkOrderTest.php`

## Gate berikutnya

Menunggu persetujuan eksplisit untuk Fase 1D (hardening data: integer rupiah, file privat + signed URL, audit log).
