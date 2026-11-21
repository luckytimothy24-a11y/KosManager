# PHASE 8 — P8 BATCH 2 — BILLING CORRECTNESS & INTEGRITY

> Status dokumen: **COMPLETE**
> Lingkup: billing calculation, pencegahan duplikat tagihan, dan proses overdue.
> Auto-first-tagihan saat check-in **TIDAK** diaktifkan pada Batch 2 (lihat bagian `AUTO-FIRST-TAGIHAN`).

---

## 1. Audit Findings

### 1.1 Billing calculation — `ceilingMonths`

Seluruh logika billing bulanan sebelumnya berada di satu tempat:
`app/Http/Controllers/Owner/TagihanController.php` (metode privat `ceilingMonths`).

| Aspek | Temuan |
| ---- | ---- |
| Metode | `ceilingMonths()` iteratif: `while (cursor <= end) { units++; cursor->addMonth(); }`, `max(units, 1)`. |
| Masalah 1 — non-deterministik | `Carbon::addMonth()` memakai semantik *overflow* (bukan clamp ke akhir bulan): `2024-01-31 +addMonth = 2024-03-02`. Akibatnya periode yang dimulai tanggal akhir bulan dihitung tidak konsisten dibanding periode serupa yang dimulai tanggal lain. |
| Masalah 2 — selisih dengan UI | Preview klien (`owner/tagihan/create.blade.php`) menggunakan `(tahun diff)*12 + (bulan diff) + 1`, server memakai loop di atas → untuk periode parsial yang melewati batas bulan keduanya bisa beda (mis. `15 Jan – 14 Feb`: server=1, klien=2). |
| Masalah 3 — melanggar rule terdokumentasi | Dokumen bisnis menyebut *ceiling months* = bulan parsial dihitung satu bulan penuh. Loop yang ada gagal menghitung bulan parsial di ujung periode secara benar. |
| Daily | Kode `daily`: `diffInDays + 1` (inklusif) — konsisten dengan klien, tidak diubah. |
| Due date | Diambil langsung dari input owner, tidak dikaitkan ke `period_end`/`end_date` kontrak — dibiarkan (di luar scope, perilaku existing). |
| Kontrak start/end | Tidak dipakai dalam billing (periode manual) — dibiarkan (perilaku existing). |

Keputusan: **formula diubah ke aritmetika kalender deterministik** (lihat §2).

### 1.2 Duplicate tagihan

Perilaku existing sebelum Batch 2:

- Hanya ada `bill_number` unique di level DB.
- Pencegahan duplikat `(kontrak_id, period_start, period_end)` **hanya app-level**: `exists()` di dalam transaksi + `lockForUpdate` pada baris `Kontrak`.
- Tidak ada backstop database untuk kombinasi tersebut → masih ada potensi race antara `exists()` dan `insert` pada kode path yang tidak me-lock baris kontrak.
- `tagihans` menggunakan `SoftDeletes` → index unik polos pada `(kontrak_id, period_start, period_end)` akan bermasalah dengan baris soft-deleted.
- Audit data: DB produksi saat ini **0 baris tagihan**, tidak ada duplikat historis. Tetap dibuat strategi backfill yang aman untuk skenario data lama yang berduplikat.

Keputusan: penambahan **`active_billing_key` (nullable + unique)** sebagai backstop level DB, mengikuti pola `active_payment_key` dari P8 Batch 1 (§3).

### 1.3 Overdue

Perilaku existing:

- `app/Console/Commands/MarkOverdueTagihans.php` dijalankan scheduler harian 00:05 (`app/Console/Kernel.php`).
- Query: `status = unpaid AND due_date < today`, lalu `update(['status' => 'overdue'])` per baris + notifikasi.
- Hanya menyentuh `unpaid`; status terminal (`paid`, `cancelled`, `pending_verification`) tidak disentuh — sesuai test `TagihanOverdueSweepTest`.
- Race: dua worker dapat memproses baris `unpaid` yang sama → duplikasi notifikasi / transisi ganda.

Keputusan: update atomik bersyarat (`where status = unpaid`) untuk idempotency & race-safety (§4). Desain ulang scheduler **tidak** dilakukan.

---

## 2. Billing Formula — Keputusan

### Rule bisnis

> Untuk sewa bulanan, sistem menggunakan perhitungan *ceiling months* — **bulan parsial dihitung sebagai satu bulan penuh** (dokumentasi produk). Tidak ada pro-rata.

### Formula yang benar (deterministik)

```php
units = (end.year - start.year) * 12 + (end.month - start.month) + 1
units = max(units, 1)
```

Artinya: setiap **bulan kalender yang tersentuh** oleh rentang `[start, end]` dihitung satu bulan penuh.

### Mengapa diubah, bukan dipertahankan

- Rule ceiling **disengaja** (terdokumentasi) → pro-rata **tidak** diperkenalkan.
- Namun **implementasi loop yang lama salah/tidak konsisten**: bergantung pada bulan lama & perilaku overflow `Carbon::addMonth`, sehingga:
  - hasil tidak deterministik dan berbeda-beda untuk rentang yang serupa saat dimulai dari tanggal 29/30/31;
  - tidak sesuai preview UI dan rule ceiling yang terdokumentasi.
- Formula aritmetika kalender di atas:
  - konsisten dengan rule terdokumentasi & dengan preview klien;
  - deterministik lintas DB/bulan (tidak terpengaruh clamping/overflow);
  - tetap menjaga perilaku existing untuk periode bulan-terpadu: `1 Jan–31 Jan = 1`, `1 Feb–29 Feb = 1`, dst.

Contoh hasil formula baru (diverifikasi oleh test):

| Periode | Unit |
| ---- | ---- |
| `2024-01-01` – `2024-01-31` | 1 (bulan penuh) |
| `2024-02-01` – `2024-02-29` | 1 (kabisat) |
| `2024-01-15` – `2024-01-25` | 1 (parsial dalam satu bulan) |
| `2024-01-15` – `2024-02-14` | 2 (menyentuh Jan + Feb) |
| `2024-01-31` – `2024-02-29` | 2 (deterministik walau mulai akhir bulan) |
| `2024-01-31` – `2024-03-31` | 3 |
| `2024-12-15` – `2025-02-14` | 3 (lintas tahun) |
| `2024-01-15` – `2024-01-16` | 1 (1 hari → ceiling) |

Harga/subtotal tidak diubah: `subtotal = rental_price * units`. Rumus total `subtotal - discount + penalty` tidak diubah.

---

## 3. Duplicate Tagihan — Pencegahan

Lapisan pertahanan berlapis:

1. **Cek app-level** (existing, dipertahankan): `Tagihan::where(kontrak_id)->where(period_start)->where(period_end)->exists()` di dalam `DB::transaction`.
2. **Lock antrian** (existing, dipertahankan): `Kontrak::lockForUpdate()` menserialisasi request untuk kontrak yang sama.
3. **Backstop DB-level (baru)**: kolom `active_billing_key` (nullable, string) + **unique index**. `active_billing_key` hanya boleh ada satu nilai non-NULL untuk kombinasi kontrak+periode yang sama. Duplikat yang lolos melewati cek app-level ditolak oleh constraint, lalu ditangkap menjadi error "sudah ada".

### Skema

```text
tagihans.active_billing_key  string?  nullable, unique
format: "{kontrak_id}:{period_start}:{period_end}"
```

### Migrasi

File: `database/migrations/2026_09_02_000002_add_billing_integrity_to_tagihans_table.php`

- KB: tambah kolom nullable, backfill, lalu `unique('active_billing_key', 'tagihans_active_billing_unique')`.
- **Backfill aman untuk data historis**: untuk kombinasi (kontrak, periode) yang terduplikasi, **hanya baris pertama (id terkecil)** yang diberi kunci; baris duplikat lain dibiarkan `NULL`. Migrasi tidak pernah menghapus/mengubah data lama, sehingga tidak gagal pada DB yang sudah punya duplikat.
- `down()`: drop unique + drop kolom.
- Rollback & forward diverifikasi (dev DB: ok, DB test `RefreshDatabase` ok pada SQLite in-memory).

### Kompatibilitas database

- MySQL / PostgreSQL / SQLite-test: menggunakan **unique index standar pada kolom nullable** (bukan partial/generated index), sehingga menjalankan di ketiganya termasuk test SQLite in-memory.
- Perilaku soft-delete: saat `Tagihan` di-soft-delete, `active_billing_key` dikosongkan (event `deleting` di model) sehingga baris yang dihapus tidak memblokir pembuatan ulang periode yang sama. Saat di-restore, kunci diregenerasi (event `restoring`).
- `active_billing_key` dikontrol sepenuhnya oleh aplikasi (bukan input klien) → tidak ada risiko mass-assignment / injeksi kunci.

---

## 4. Overdue — Idempotency & Race Safety

`app/Console/Commands/MarkOverdueTagihans.php` diubah menjadi **update atomik bersyarat**:

```php
$changed = Tagihan::whereKey($tagihan->id)
    ->where('status', 'unpaid')
    ->whereDate('due_date', '<', today())
    ->update(['status' => 'overdue']);

if ($changed === 0) {
    continue; // worker lain sudah memproses / status bukan unpaid
}
```

- **Race (`worker A` vs `worker B`)**: hanya worker yang berhasil mengubah baris (`$changed > 0`) yang mengirim notifikasi; worker yang kalah memengaruhi 0 baris dan di-skip → tidak ada duplikasi notifikasi.
- **Eksekusi berulang**: baris yang sudah `overdue` tidak lagi `unpaid` → tidak diproses ulang.
- **Status terminal** (`paid`, `cancelled`, `pending_verification`) tidak pernah disentuh.
- `due_date == today` tidak ditandai (hanya `< today`).
- Tidak ada redesign scheduler; jadwal `tagihan:mark-overdue` harian 00:05 dipertahankan.

---

## 5. Auto-First-Tagihan

> **Auto-first-tagihan saat check-in TIDAK diaktifkan pada Batch 2.**

- Audit: belum ada auto-first-tagihan pada check-in (`CheckInController` tidak membuat tagihan).
- Tidak ada perubahan yang menanibahkan auto-first-tagihan.
- Owner tetap membuat tagihan lewat flow existing (`owner.tagihan.store`).
- Jika di masa depan ada kebutuhan bisnis auto-first-tagihan: **document only, do not implement** (di luar scope Batch 2).

---

## 6. Tests

File baru:

| File test | Coverage |
| ---- | ---- |
| `tests/Feature/P8Batch2BillingCalculationTest.php` | Exact full month, leap year, partial month, boundary/crossing, month-end determinism, multi-month, cross-year, 1-day, same start/end validation, daily unit. |
| `tests/Feature/P8Batch2DuplicateTagihanTest.php` | Normal creation + kunci deterministik, duplicate rejection, beda periode diizinkan, DB constraint, soft-delete melepas slot, historis tetap valid. |
| `tests/Feature/P8Batch2OverdueIdempotencyTest.php` | Due passed/today/future, already overdue/paid/cancelled/pending, command run twice, concurrent-style transition. |

Regression: seluruh suite existing (termasuk Tagihan/Overdue/Payment/P8 Batch 1) tetap hijau.

---

## 7. Security Review

- **Authorization tidak berubah**: `StoreTagihanRequest::authorize()` tetap hanya `super_admin/owner/admin`; tenant tidak dapat membuat tagihan (403, `test_tenant_cannot_create_tagihan`).
- **Isolasi ownership**: `store()` tetap `abort_unless($user->canAccessKos(...), 403)` — tidak ada cross-owner leakage.
- **Input kunci tidak dapat dieksploitasi**: `active_billing_key` di-set oleh controller dari nilai internal (`billingKey()`), bukan dari request; kolom tidak dipetakan dari input klien.
- **Duplikasi tidak dapat dieksploitasi via concurrent request**: backstop unique index menolak baris duplikat; eksepsi ditangkap menjadi pesan "sudah ada".
- **Status transition tidak dapat dipaksa**: overdue hanya transisi `unpaid → overdue` dengan update bersyarat atomik; status terminal tidak tersentuh.
- **Historical data tidak rusak**: strategi backfill tidak menghapus/mengubah baris lama; duplikat historis dibiarkan dengan kunci NULL.
- Tidak ada pengurangan authorization demi kemudahan test.

---

## 8. Files Changed

### App (backend)

| File | Change |
| ---- | ------ |
| `app/Http/Controllers/Owner/TagihanController.php` | `ceilingMonths` deterministik (aritmetika kalender); tambah `billingKey()`; `store()` set `active_billing_key` + catch eksepsi unik sebagai duplicate. |
| `app/Models/Tagihan.php` | `active_billing_key` di `$fillable`; `booted()` null-kan kunci saat soft-delete & regeneras ` restoring`; helper statis `billingKey()`. |
| `app/Console/Commands/MarkOverdueTagihans.php` | Update atomik bersyarat (`where status = unpaid`) utk idempotency/race; notifikasi hanya pada baris yang benar-benar berubah. |

### Database

| File | Change |
| ---- | ------ |
| `database/migrations/2026_09_02_000002_add_billing_integrity_to_tagihans_table.php` | Baru — kolom `active_billing_key` + backfill dedup + unique index; `down()` friendly. |

### Tests

| File | Change |
| ---- | ------ |
| `tests/Feature/P8Batch2BillingCalculationTest.php` | Baru. |
| `tests/Feature/P8Batch2DuplicateTagihanTest.php` | Baru. |
| `tests/Feature/P8Batch2OverdueIdempotencyTest.php` | Baru. |

### Frontend

Tidak ada perubahan. Build: **not required / unchanged**.

---

## 9. Database Changes

- Tambah kolom `tagihans.active_billing_key` (string, nullable).
- Tambah unique index `tagihans_active_billing_unique` pada `active_billing_key`.
- Backfill deterministik & dedup (baris pertama per kontrak+periode diberi kunci; duplikat NULL).
- Rollback/forward diuji.

---

## 10. Verification

| Check | Result |
| ---- | ---- |
| `php artisan test` | **861 passed / 2970 assertions** (baseline 835 / 2861) |
| Targeted (Batch 2) | 26 tests / 109 assertions — PASS |
| `vendor/bin/pint --test` | PASS |
| `php artisan route:list` | 163 routes, tidak berubah |
| Migration | up/down PASS (dev & test) |
| Frontend build | Not required / unchanged |

---

## 11. Known Limitations

- `due_date` masih di-input manual oleh owner; tidak ada enforcemen `due_date >= period_end` atau kaitan ke `end_date` kontrak (perilaku existing, di luar scope).
- Periode billing masih manual (owner memasukkan `period_start`/`period_end`); kontrak `start_date`/`end_date` tidak dipakai untuk membatasi periode (perilaku existing).
- Backfill hanya men-set kunci untuk baris non-soft-deleted pertama; duplikat historis tidak otomatis dibersihkan (sengaja, demi keamanan data).
- Race concurrent diuji secara deterministik (simulasi update bersyarat); uji beban konkuren penuh di luar jangkauan.

---

## 12. Out of Scope (tidak diubah)

- Payment gateway; webhook; P7-D API; Owner/Tenant/Admin/SuperAdmin API.
- Payment verification behavior; notification system; contract & booking lifecycle.
- Auto-first-tagihan; UI redesign/JS preview (preview klien sudah selaras dengan formula baru tanpa perubahan JS); WebSocket; deployment; backup; reports; dashboard; scheduler redesign.
- Pricing model (harga/bulan & per hari kontrak tidak diubah).

---

## 13. Definition of Done

```
[x] Billing calculation audited
[x] Formula corrected (implementasi buggy → aritmetika kalender deterministik; rule ceiling dipertahankan)
[x] Duplicate billing race addressed (backstop DB unique + layer existing)
[x] Database integrity verified (migrasi aman, rollback ok)
[x] Overdue process idempotent (update atomik bersyarat)
[x] Tests added
[x] Targeted tests PASS
[x] Full regression PASS (861 tests)
[x] Security PASS
[x] Pint PASS
[x] Routes verified
[x] Migration verified
[x] Documentation complete
[x] No critical/high unresolved issue
[x] Auto-first-tagihan NOT introduced
```

## 14. Final Report

### Status

```text
P8 Batch 2 — COMPLETE
```

### Audit Findings

- `ceilingMonths` lama non-deterministik (overflow `Carbon::addMonth`) & tidak konsisten dengan UI serta rule ceiling terdokumentasi.
- Pencegahan duplikat tagihan sebelumnya hanya app-level (`exists()` + lock kontrak), tanpa backstop DB; soft-delete tidak ditangani index unik polos.
- Proses overdue rawan duplikasi notifikasi pada race/eksekusi berulang.

### Billing Calculation Decision

Formula diubah ke **aritmetika kalender deterministik** (`(yearDiff)*12 + monthDiff + 1`, ceiling). Rule bisnis ceiling months (parsial = 1 bulan penuh) **dipertahankan**; tidak ada pro-rata; harga & rumus total tidak berubah. Alasan: implementasi lama terbukti salah/tidak konsisten, sedangkan rule-nya disengaja.

### Duplicate Billing Protection

Berlapis: cek `exists()` + `lockForUpdate` kontrak (app-level, existing) + unique `active_billing_key` (backstop DB, baru) dengan pembebasan slot saat soft-delete.

### Overdue Protection

Update atomik bersyarat `where status = unpaid` → idempoten & race-safe; notifikasi hanya untuk baris yang benar-benar berubah; status terminal tidak disentuh.

### Files Changed

| File | Change |
| ---- | ------ |
| `app/Http/Controllers/Owner/TagihanController.php` | ceilingMonths deterministik; `billingKey()`; `store()` set `active_billing_key` + catch unique. |
| `app/Models/Tagihan.php` | `active_billing_key` fillable + boot events (soft-delete/restore) + helper. |
| `app/Console/Commands/MarkOverdueTagihans.php` | Update atomik bersyarat. |
| `database/migrations/2026_09_02_000002_add_billing_integrity_to_tagihans_table.php` | Baru. |
| `tests/Feature/P8Batch2BillingCalculationTest.php` | Baru. |
| `tests/Feature/P8Batch2DuplicateTagihanTest.php` | Baru. |
| `tests/Feature/P8Batch2OverdueIdempotencyTest.php` | Baru. |

### Database Changes

`tagihans.active_billing_key` (nullable, unique) + index, backfill dedup aman, migrasi rollback/forward ok.

### Tests

```text
Targeted:
26 tests
109 assertions

Full suite:
861 tests
2970 assertions
```

### Security Verification

PASS (authorization & isolasi ownership tidak berubah; kunci app-controlled; duplikasi DB-level; status transition tidak dapat dipaksa; data historis aman).

### Pint

PASS.

### Routes

PASS (163 routes, tidak berubah).

### Migration

PASS (up/down).

### Build

Not required / unchanged (tidak ada perubahan frontend).

### Documentation

`docs/PHASE_8_P8_BATCH2_BILLING_INTEGRITY.md` (dokumen ini).

### Known Limitations

Lihat §11.

### Out of Scope

Lihat §12. Auto-first-tagihan tidak diaktifkan.

### Final Assessment

Batch 2 memenuhi seluruh Definition of Done tanpa perubahan scope, tanpa menghilangkan authorization, dan tanpa merusak data historis.