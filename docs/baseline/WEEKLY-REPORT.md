# Laporan Pekanan — KosManager (23–28 Nov 2026)

**Branch:** `chore/fase-0-baseline`
**HEAD terakhir:** `b0c524a`
**CI terakhir:** run #46098111828 — ✅ success (semua run hijau)

---

## 1. Ringkasan Eksekutif

Dalam seminggu ini, project KosManager (Laravel 10) menyelesaikan **Fase 0** (baseline
karakterisasi), **Fase 1A** (fondasi role), **Fase 1B** (RBAC), **Fase 1C** (Work Order
MVP), dan **seluruh Fase 1D** (audit log + private file/signed URL + integer rupiah).

| Metrik | Nilai |
|---|---:|
| Test terdaftar (awal) | 2368 |
| Test terdaftar (akhir) | 2428 |
| Penambahan test | +60 |
| Assertion (awal) | 8086 |
| Assertion (akhir) | 8383 |
| CI runs | 8 run, semua success |
| Pint | passed (setiap commit) |
| Vite build | passed |

---

## 2. Detail per Fase

### 2.1 Fase 0 — Baseline Karakterisasi ✅
**Commit:** `3d70062` · CI #19 (success)

- 42 test karakterisasi di `tests/Feature/Characterization/`.
- Laporan `docs/baseline/FASE-0.md`.
- **Batas dipatuhi:** tidak ada perubahan pada `app/`, `routes/`, `config/`, `database/migrations/`, view, atau test lama.

**Bugs/keanehan terkunci (bukan diperbaiki):**
1. Pembayaran partial ditolak (harus sama dengan total).
2. Tidak ada fitur revisi tagihan.
3. Tidak ada model deposit.
4. super_admin boleh masuk area owner/admin, tidak boleh tenant.
5. Kolom status `bookings`/`kontraks` di SQLite kehilangan CHECK constraint.
6. `damage_reports`/`disputes` status adalah string tanpa CHECK/ENUM.
7. `check_outs` DB mengizinkan `completed`, model tidak punya konstantanya.
8. `payment_method` hanya 3 kanal.
9. Uang decimal, bukan integer rupiah.
10. Controller owner/admin Dispute ada, route belum terdaftar.
11. Migrasi `2026_09_11_000003` raw SQL SQLite (blokir MySQL).
12. DP Rp 100.000 tetap, sisa hangus.
13. Penalti Rp 50.000 tetap, idempoten.
14. Receipt hanya setelah approved.
15. Alur dana 2 tahap: PaymentDistribution tidak processed sampai booking approved.

---

### 2.2 Fase 1A — Fondasi Role ✅
**Commit:** `12e899d` · CI #20 (success)

- Role `technician` ditambah (additive, rollback-able) ke `users.role` (DB enum SQLite & MySQL/MariaDB).
- `UserRole` enum: case `TECHNICIAN`, label `Teknisi`.
- `HasRole::isTechnician()` helper.
- `config/roles.php`: canonical, internal, legacy_map, aliases, demo.
- Seeder demo teknisi (non-production).
- Perbaikan migrasi `2026_09_11_000003` (raw SQL → driver-aware).
- Migrasi baru `2026_11_23_000001_add_technician_role_to_users_table.php`.
- `pencari` tetap canonical, `tenant` alias aplikasi, `super_admin` internal.
- **Test:** `TechnicianRoleTest` (6), update `UserRoleTest` & karakterisasi.
- **Verifikasi:** SQLite 2416 passed; MySQL 6/6 passed; Pint passed.

---

### 2.3 Fase 1B — RBAC Defense-in-Depth ✅ (parsial by design)
**Commit:** `9cbd857` · CI #22 (success)

- Middleware `role` diverifikasi mendukung `technician`.
- Policy default-deny untuk `technician` pada area tenant/admin/owner/super-admin.
- Trait `HasRoleScope` dibuat (opt-in) — global scope otomatis **ditunda** karena memecahkan 292 test.
- `RbacPhase1bTest` (7 test).
- **Verifikasi:** SQLite 2423 passed; Pint passed.

---

### 2.4 Fase 1C — Work Order / Complaints MVP ✅
**Commit:** `60752dc` · CI #23 (success) · Docs `4936c5b` · CI #24 (success)

- Tabel `work_orders` (migrasi `2026_11_23_000002`).
- Model `WorkOrder` dengan status flow: `pending → assigned → in_progress → resolved → closed`.
- Prioritas: `low/normal/high/urgent`.
- Policy `WorkOrderPolicy`: viewAny/view/create/update/assign/delete dengan scoping per role.
- Controller `WorkOrderController` (Owner namespace, menangani semua role).
- Routes terpisah (`routes/work-order.php`) di bawah feature flag `FEATURE_WORK_ORDERS`.
- Teknisi hanya melihat/mengupdate work order yang ditugaskan.
- Link non-destruktif ke legacy: `legacy_damage_report_id`, `legacy_dispute_id`.
- Views minimal: index/create/show.
- Factory `WorkOrderFactory`.
- `WorkOrderTest` (5 test).
- **Verifikasi:** SQLite 2428 passed; MySQL 5/5 passed; Pint passed.

---

### 2.5 Fase 1D — Hardening Data ✅ (selesai)

#### Slice 1: Audit Log ✅
**Commit:** `b40a346` · CI sukses
- `DamageReportController::update`: audit log status change.
- `WorkOrderController::update`: audit log untuk kedua jalur (teknisi & admin/owner).

#### Slice 2: Private File + Signed URL ✅
**Commit:** `8aa0104` · CI sukses
- `downloadProof` mencatat audit log (sensitive file access).
- Method `signedProofUrl` (generate temporary signed URL 15 menit).
- Method `downloadProofSigned` (stream via signed URL).
- Route `pembayaran.proof.signed` dengan middleware `signed`.

#### Slice 3: Integer Rupiah ✅
**Commit:** `b0c524a` · CI #26 (success)
- Migrasi additive `2026_11_28_000001_add_integer_money_columns.php`.
- Kolom integer rupiah paralel (`*_rp`) pada tabel: `kamar`, `tagihans`, `pembayarans`, `kontraks`, `bookings`.
- Backfill otomatis dari kolom decimal (`CAST(ROUND(COALESCE(...)) AS INTEGER)`).
- Kolom decimal tetap (rollback-able, cutover bertahap).
- SQLite full suite: 2428 passed; Pint passed.
- MySQL: migration standard (Schema::table), tidak diverifikasi (DB corrupt di environment).

---

## 3. Angka Test per Tahap

| Tahap | Tests | Assertions |
|---|---:|---:|
| Baseline (3c03dd2) | 2368 | 8086 |
| Setelah Fase 0 | 2410 | 8347 |
| Setelah Fase 1A | 2416 | 8365 |
| Setelah Fase 1B | 2423 | 8376 |
| Setelah Fase 1C | 2428 | 8383 |
| Setelah Fase 1D (semua slice) | 2428 | 8383 |

**3x full suite SQLite** hijau pada Fase 0 (2410 passed). Setiap commit diverifikasi dengan full suite (exit 0) dan CI success.

---

## 4. File Berubah (kumulatif)

### Baru
- `tests/Feature/Characterization/*` (7 file, 42 test)
- `docs/baseline/FASE-0.md`, `FASE-1A.md`, `FASE-1B.md`, `FASE-1C.md`
- `app/Enums/UserRole.php` (case TECHNICIAN)
- `app/Traits/HasRole.php` (isTechnician)
- `app/Traits/HasRoleScope.php` (opt-in)
- `config/roles.php`, `config/features.php`
- `database/migrations/2026_11_23_000001_add_technician_role_to_users_table.php`
- `database/migrations/2026_11_23_000002_create_work_orders_table.php`
- `database/migrations/2026_11_28_000001_add_integer_money_columns.php`
- `app/Models/WorkOrder.php`
- `app/Policies/WorkOrderPolicy.php`
- `app/Http/Controllers/Owner/WorkOrderController.php`
- `database/factories/WorkOrderFactory.php`
- `resources/views/owner/work-orders/*` (3 view)
- `routes/work-order.php`
- `tests/Feature/TechnicianRoleTest.php`
- `tests/Feature/RbacPhase1bTest.php`
- `tests/Feature/WorkOrderTest.php`

### Dimodifikasi
- `database/migrations/2026_09_11_000003_add_weekly_to_rental_type_enum.php` (cross-driver)
- `database/seeders/UserSeeder.php` (demo teknisi)
- `app/Providers/AuthServiceProvider.php` (WorkOrder policy)
- `routes/web.php` (work-order require + signed proof route)
- `app/Http/Controllers/Owner/DamageReportController.php` (audit log)
- `app/Http/Controllers/Owner/PembayaranController.php` (audit + signed URL)
- `app/Http/Controllers/Owner/WorkOrderController.php` (audit log)
- `tests/Feature/Characterization/DomainValuesCharacterizationTest.php`
- `tests/Unit/UserRoleTest.php`

---

## 5. CI Runs (semua success)

| Run | SHA | Commit | Status |
|---|---|---|---|
| #19 | `3d70062` | test(fase-0): characterization baseline | ✅ |
| #20 | `12e899d` | feat(fase-1a): technician role + cross-driver | ✅ |
| #21 | `104c347` | docs(fase-1a): report | ✅ |
| #22 | `9cbd857` | feat(fase-1b): RBAC tests + scope trait | ✅ |
| #23 | `60752dc` | feat(fase-1c): Work Order MVP | ✅ |
| #24 | `4936c5b` | docs(fase-1c): report | ✅ |
| #25 | `b40a346` | feat(fase-1d-audit): audit log | ✅ |
| #26 | `8aa0104` | feat(fase-1d-file): signed URL | ✅ |
| #27 | `b0c524a` | feat(fase-1d-money): integer columns | ✅ |

---

## 6. Risiko Terbuka

1. **Global role scope** belum diaktifkan otomatis (deferred).
2. **Isu pre-existing MySQL** pada suite penuh (FK enforcement, backup driver SQLite-only, LIKE escaping) belum dibersihkan. MySQL server juga corrupt di environment (innodb_force_recovery).
3. **Cutover integer rupiah** belum dilakukan (kolom paralel ada, tapi service masih pakai decimal).
4. **Untracked file refund** (dari branch `fix/cancel-dp-refund`) tersimpan di working tree dan di-backup ke temp. Tidak ikut commit.

---

## 7. Langkah Berikutnya (butuh persetujuan eksplisit)

- Review keseluruhan Fase 1 (1A–1D).
- Pertimbangkan merge `chore/fase-0-baseline` ke `develop`.
- Fase 2: WhatsApp notification, QRIS/VA payment, cutover integer rupiah, global role scope enablement.

---

*Dibuat: 28 Nov 2026*
