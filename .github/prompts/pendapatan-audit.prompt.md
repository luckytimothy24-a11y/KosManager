---
description: 'PHASE — AUDIT & IMPLEMENTASI FITUR PENDAPATAN (Owner, Admin, Super Admin)'
mode: 'agent'
tools: ['vscode', 'execute', 'read', 'edit', 'search', 'web']
---

# PHASE — AUDIT & IMPLEMENTASI FITUR PENDAPATAN

## KosManager — Owner, Admin, Super Admin

Anda bekerja pada repository KosManager.

### TUJUAN UTAMA

Pastikan fitur **Pendapatan** tersedia, konsisten, dan dapat diakses dengan benar pada akun:

1. `owner`
2. `admin`
3. `super_admin`

Jika file/fitur Pendapatan **sudah ada**, jangan membuat duplikasi. Audit dan gunakan implementasi yang sudah ada.

Jika belum ada pada role tertentu, implementasikan dengan pola yang konsisten terhadap arsitektur aplikasi yang sudah ada.

Fokus bukan hanya membuat halaman terlihat ada, tetapi memastikan:

* route benar;
* controller benar;
* authorization benar;
* data pendapatan benar;
* query tidak bocor antar owner;
* menu/sidebar benar;
* dashboard/link menuju Pendapatan benar;
* UI konsisten;
* tidak merusak fitur existing;
* test tetap GREEN.

---

# ATURAN KERAS

## 1. JANGAN langsung coding

Mulai dari AUDIT.

Jangan mengubah file apa pun sebelum seluruh struktur existing dipahami.

## 2. Jangan membuat fitur duplikat

Cari terlebih dahulu seluruh implementasi yang berkaitan dengan:

* Pendapatan
* Revenue
* Grafik Pendapatan
* Laporan Pendapatan
* Riwayat Pendapatan
* Pembayaran
* Tagihan
* Payment
* Revenue Chart
* `revenueChart`

Gunakan:

* routes
* controllers
* models
* services
* policies
* middleware
* Blade
* components
* tests

## 3. Jangan mengubah schema database jika tidak diperlukan

Jangan membuat migration baru hanya untuk membuat halaman Pendapatan.

Gunakan data transaksi/pembayaran yang sudah ada jika struktur existing sudah cukup.

Jika ternyata schema memang tidak mencukupi, STOP setelah audit dan laporkan kebutuhan migration terlebih dahulu.

## 4. Jangan mengubah business logic pembayaran tanpa alasan

Fitur Pendapatan harus membaca data pembayaran/transaksi existing.

Jangan mengubah:

* PaymentService
* Tagihan
* Kontrak
* Booking
* approval payment
* status payment

kecuali audit membuktikan memang diperlukan.

## 5. Authorization wajib aman

Jangan sampai:

OWNER A dapat melihat pendapatan OWNER B.

Pastikan filtering data berdasarkan ownership yang benar.

Jangan hanya mengandalkan hidden menu.

Route/controller/service tetap harus memiliki authorization server-side.

---

# PHASE A — AUDIT STRUKTUR EXISTING

Lakukan audit lengkap terlebih dahulu.

Cari:

### A. Routes

Periksa:

```text
routes/web.php
routes/api.php
```

Cari route terkait:

```text
pendapatan
revenue
laporan
payment
payments
tagihan
```

Catat:

* route name;
* URL;
* HTTP method;
* middleware;
* controller;
* role restriction.

---

### B. Controllers

Cari seluruh controller terkait:

```text
Pendapatan
Revenue
Payment
Pembayaran
Laporan
Dashboard
```

Periksa apakah sudah ada method seperti:

```text
index()
pendapatan()
revenue()
laporan()
chart()
```

Jangan mengasumsikan nama file.

---

### C. Models

Audit minimal:

```text
Payment
Pembayaran
Tagihan
Kontrak
Kamar
Kos
User
```

Pahami relasi:

```text
User
  ↓
Owner
  ↓
Kos
  ↓
Kamar
  ↓
Kontrak
  ↓
Tagihan
  ↓
Pembayaran
```

Gunakan struktur relasi yang benar-benar ada pada repository.

---

### D. Service

Cari:

```text
PaymentService
RevenueService
ReportService
```

Jika sudah ada kalkulasi pendapatan, gunakan kembali.

Jangan membuat kalkulasi kedua yang berpotensi menghasilkan angka berbeda.

---

### E. Existing Dashboard

Audit dashboard:

```text
Owner
Admin
Super Admin
```

Cari apakah sudah terdapat:

```text
Grafik Pendapatan
Total Pendapatan
Pendapatan Bulan Ini
Pendapatan 6 Bulan
Revenue Chart
```

Perhatikan implementasi existing seperti:

```php
$revenueChart
```

Jika chart pendapatan sudah ada, pahami sumber datanya.

Jangan membuat sistem revenue baru tanpa alasan.

---

# PHASE B — AUDIT AKSES PER ROLE

Audit ketiga role secara terpisah.

## OWNER

Pastikan owner dapat:

```text
Dashboard
→ Pendapatan
```

Owner hanya boleh melihat pendapatan yang berasal dari kos yang menjadi kewenangannya.

Tidak boleh melihat data owner lain.

---

## ADMIN

Audit terlebih dahulu definisi admin di aplikasi.

Jangan mengasumsikan admin sama dengan owner.

Tentukan berdasarkan code existing apakah:

1. admin memiliki akses seluruh pendapatan;
2. admin hanya memiliki akses pendapatan kos tertentu;
3. admin memiliki akses laporan tetapi bukan data finansial penuh;
4. atau fitur Pendapatan memang belum tersedia.

Gunakan authorization existing.

Jangan membuat keputusan berdasarkan nama role saja.

---

## SUPER ADMIN

Audit akses super admin.

Jika secara arsitektur super admin memang memiliki akses global, halaman Pendapatan harus dapat menampilkan agregasi global sesuai data existing.

Jika sudah ada dashboard/report global, gunakan logic existing.

---

# PHASE C — TENTUKAN STATUS FITUR

Setelah audit, buat matriks:

| Role        | Route | Controller | View | Menu | Authorization | Data | Status |
| ----------- | ----- | ---------- | ---- | ---- | ------------- | ---- | ------ |
| Owner       | ?     | ?          | ?    | ?    | ?             | ?    | ?      |
| Admin       | ?     | ?          | ?    | ?    | ?             | ?    | ?      |
| Super Admin | ?     | ?          | ?    | ?    | ?             | ?    | ?      |

Klasifikasikan:

* EXISTING & WORKING
* EXISTING BUT INCOMPLETE
* EXISTING BUT BROKEN
* MISSING

---

# PHASE D — IMPLEMENTASI MINIMAL

Hanya setelah audit selesai, implementasikan bagian yang memang missing/broken.

## Prinsip

Gunakan arsitektur existing.

Prefer:

```text
existing controller
existing service
existing model relationships
existing authorization
existing layout
existing chart component
```

daripada membuat struktur baru.

---

# PHASE E — HALAMAN PENDAPATAN

Jika halaman Pendapatan memang belum ada, buat halaman yang profesional dan konsisten dengan UI KosManager.

Minimal berisi:

### Summary

```text
Total Pendapatan
Pendapatan Bulan Ini
Pendapatan Bulan Sebelumnya
Jumlah Transaksi Berhasil
```

Gunakan status pembayaran yang memang dianggap berhasil berdasarkan business logic existing.

Jangan menganggap semua tagihan sebagai pendapatan.

---

### Grafik

Jika data mencukupi:

```text
Grafik Pendapatan 6 Bulan Terakhir
```

Gunakan data pembayaran yang valid/berhasil.

Jangan menghitung dari tagihan yang belum dibayar.

---

### Riwayat Pendapatan

Minimal:

```text
Tanggal
Nama Penyewa
Kos
Kamar
Nominal
Status
```

Sesuaikan dengan data yang memang tersedia.

---

### Filter

Jika arsitektur existing memungkinkan:

```text
Periode
Kos
Status
```

Jangan membuat filter kompleks jika belum diperlukan.

---

# PHASE F — OWNERSHIP SECURITY

Ini WAJIB diuji.

Buat/cek skenario:

```text
Owner A
Owner B
```

Owner A tidak boleh mendapatkan:

* total pendapatan Owner B;
* transaksi Owner B;
* grafik pendapatan Owner B;
* detail pembayaran Owner B.

Test juga direct URL access.

Contoh:

```text
Owner A login
→ buka route Pendapatan
→ tidak boleh mendapatkan data Owner B
```

Jangan hanya mengandalkan menu/sidebar.

---

# PHASE G — ADMIN SECURITY

Verifikasi role admin berdasarkan authorization existing.

Jika admin memang memiliki akses global:

```text
Admin
→ Pendapatan
→ data sesuai scope admin
```

Jika admin memiliki scope tertentu:

```text
Admin
→ hanya data sesuai scope
```

Jangan memperluas privilege hanya karena fitur Pendapatan ditambahkan.

---

# PHASE H — SUPER ADMIN

Verifikasi:

```text
Super Admin
→ Pendapatan
```

Jika memang global:

* total seluruh pendapatan;
* grafik global;
* transaksi global;

sesuai authorization existing.

Jangan mengubah privilege super admin yang sudah ada.

---

# PHASE I — NAVIGATION / MENU

Audit:

```text
sidebar
navbar
dashboard quick action
mobile navigation
```

Jika Pendapatan sudah ada:

* jangan membuat menu kedua.

Jika belum ada:

tambahkan menu pada lokasi yang paling konsisten.

Pastikan menu hanya muncul pada role yang memang berhak.

Contoh konseptual:

```text
Owner
├── Dashboard
├── Kos Saya
├── Pendapatan
└── ...

Admin
├── Dashboard
├── ...
└── Pendapatan

Super Admin
├── Dashboard
├── ...
└── Pendapatan
```

Sesuaikan dengan struktur aplikasi sebenarnya.

---

# PHASE J — UI/UX

Jangan hanya "mempercantik".

Prioritas:

1. hierarchy jelas;
2. tidak terlalu banyak card;
3. informasi tidak redundant;
4. angka pendapatan mudah dibaca;
5. grafik tidak mendominasi halaman;
6. tabel tetap usable di desktop;
7. responsive di mobile;
8. spacing konsisten;
9. tidak ada tombol yang tidak berguna;
10. gunakan komponen/layout existing.

Jangan memperkenalkan warna/komponen baru tanpa kebutuhan.

---

# PHASE K — TESTING

Setelah implementasi:

## 1. Targeted tests

Cari test existing terkait:

```text
Revenue
Pendapatan
Payment
Pembayaran
Dashboard
Owner
Admin
SuperAdmin
Authorization
```

Jalankan test relevan terlebih dahulu.

---

## 2. Tambahkan regression tests jika belum ada

Minimal test:

### Owner

```text
owner_can_access_pendapatan
owner_only_sees_own_revenue
owner_cannot_access_other_owner_revenue
```

### Admin

```text
admin_can_access_pendapatan_if_authorized
admin_revenue_scope_is_correct
```

### Super Admin

```text
super_admin_can_access_global_revenue_if_authorized
```

### Security

```text
unauthorized_role_cannot_access_pendapatan
```

Nama test harus menyesuaikan conventions repository.

---

# PHASE L — FULL TEST SUITE

Setelah targeted tests GREEN, jalankan full suite.

Target:

```text
0 failed
```

Bandingkan dengan baseline sebelum perubahan.

Jangan menyatakan GREEN jika ada test gagal.

Jika ada failure yang tidak berkaitan:

* investigasi;
* jangan langsung workaround;
* jangan menonaktifkan test.

---

# PHASE M — STATIC CHECK

Periksa minimal:

```text
PHP syntax
Laravel route integrity
Blade compilation
PHPUnit
npm build jika file frontend berubah
```

Jika tidak ada perubahan frontend, jangan menjalankan build hanya untuk formalitas kecuali diperlukan.

---

# PHASE N — GIT SAFETY

Sebelum commit:

```bash
git status
git diff --stat
git diff
```

Pastikan tidak ada:

```text
.env
storage artifacts
tmp-qa/
design mockup
screenshots
temporary files
debug files
```

yang ikut ter-stage.

Jangan commit file yang tidak berhubungan.

---

# PHASE O — FINAL REPORT

Berikan laporan dengan format:

## PENDAPATAN FEATURE AUDIT REPORT

### 1. Status

```text
GREEN / GREEN WITH CONDITIONS / BLOCKED
```

### 2. Existing Implementation

Jelaskan:

```text
Owner:
Admin:
Super Admin:
```

### 3. Files Audited

List file penting.

### 4. Files Changed

Hanya file yang benar-benar berubah.

### 5. Architecture

Jelaskan sumber data Pendapatan.

Contoh:

```text
Payment
→ Tagihan
→ Kontrak
→ Kos
→ Owner
```

sesuaikan dengan struktur aktual.

### 6. Authorization

Jelaskan scope:

```text
Owner:
Admin:
Super Admin:
```

### 7. UI

Jelaskan:

* route;
* menu;
* dashboard;
* halaman Pendapatan;
* responsive behavior.

### 8. Tests

Tampilkan:

```text
Targeted:
X passed

Full suite:
X passed
Y assertions
0 failed
```

Gunakan angka aktual.

### 9. Security

Konfirmasi:

```text
Cross-owner leakage:
PASS / FAIL

Unauthorized access:
PASS / FAIL
```

### 10. Git

Tampilkan:

```text
Branch:
HEAD:
Working tree:
Changed files:
```

---

# HARD STOP

Setelah final report:

**STOP.**

Jangan:

* commit;
* push;
* deploy;
* mengubah `.env`;
* melakukan migration production;
* melakukan refactor tambahan;
* memperbaiki unrelated findings.

Jika ditemukan kebutuhan perubahan besar/schema migration baru, STOP dan minta approval terlebih dahulu.

---

## PRIORITAS

Urutan kerja wajib:

```text
AUDIT
↓
UNDERSTAND EXISTING REVENUE
↓
ROLE/SCOPE ANALYSIS
↓
PLAN
↓
MINIMAL IMPLEMENTATION
↓
AUTHORIZATION TEST
↓
UI/UX CHECK
↓
TARGETED TEST
↓
FULL TEST
↓
STATIC CHECK
↓
GIT AUDIT
↓
REPORT
↓
HARD STOP
```

Tujuan akhirnya bukan sekadar "ada file Pendapatan", tetapi:

> **Owner, Admin, dan Super Admin mendapatkan fitur Pendapatan sesuai hak akses masing-masing, menggunakan sumber data existing yang benar, tanpa duplikasi business logic, tanpa kebocoran data antar owner, dan tanpa regression pada fitur KosManager lainnya.**
