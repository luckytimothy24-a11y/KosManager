# Laporan Release-Wide Security & Dependency

- **Tanggal:** 17 November 2026
- **Branch:** `fix/cancel-dp-refund`
- **Commit batch ini:** `02f8346` (dependency npm), lalu commit mitigasi CRLF/allowlist/laporan setelahnya
- **Lingkup:** C3 DP non-refundable, verifikasi full suite, audit dependency npm/Composer, mitigasi CRLF email, dan inventaris TODO C2.

## Ringkasan

| Item | Status |
|------|--------|
| C3 DP non-refundable saat booking batal | Selesai (`eb4444d`) |
| Full suite | **2332 passed, 7970 assertions** |
| `npm audit` | **0 vulnerabilities** |
| `npm run build` (Vite 6.4.6) | Sukses |
| `composer audit` | **exit 0** — 5 advisory di-allowlist dengan alasan |
| Mitigasi CRLF email Laravel 10 | Selesai + 5 test |
| TODO C2 | Didokumentasikan, belum diubah |

## 1. C3 — DP non-refundable saat booking batal

Commit: `eb4444d fix(booking): exclude non-refundable DP from cancellation refunds`.

Perilaku yang dikunci:

- DP tidak ikut dihitung sebagai refund saat booking dibatalkan.
- `refund_status` DP tetap `none`; distribusi DP di-release.
- Notifikasi menyebut DP tidak dapat dikembalikan dan menjadi hak pemilik kos.
- Pembayaran non-DP legacy tetap refund penuh dengan reason/notifikasi yang akurat (tidak menyebut "oleh owner" bila diajukan tenant).
- Pembayaran manual/extra pada booking ber-DP tidak auto-refund; masuk review manual.

Data lama tidak diubah otomatis. Command pelaporan bersifat read-only:

```powershell
php artisan refunds:report-cancelled-dp
```

Verifikasi C3: 5 test / 41 assertions, regresi cancellation/refund 37 test / 119 assertions, regresi DP/distribution/notification/state 60 test / 243 assertions, Pint lulus.

## 2. Full suite

```
php artisan test
Tests: 2332 passed (7970 assertions)
Duration: 955.88s
```

Kenaikan 5 test dari baseline 2327 berasal dari `tests/Feature/SafeEmailValidationTest.php`.

## 3. Dependency npm

`npm audit` awal menemukan vulnerability pada axios, follow-redirects, dan rantai Vite/esbuild. Perbaikan dilakukan tanpa lompat ke Vite 8:

| Paket | Sebelum | Sesudah |
|-------|---------|---------|
| axios | 1.19.0 | 1.21.1 |
| vite | 4.x | 6.4.6 |
| laravel-vite-plugin | 0.7.8 | 1.3.0 |
| puppeteer-core | 25.10.0 | 25.17.0 |

Hasil akhir:

```
npm audit
found 0 vulnerabilities

npm run build
vite v6.4.6 building for production...
✓ built
```

`public/build` tidak di-track; hanya `package.json` dan `package-lock.json` yang berubah.

## 4. Advisory Composer (Laravel 10.50.4)

`composer audit` menemukan 5 advisory pada `laravel/framework`. Laravel 10 tidak menerima patch untuk seluruh advisory tersebut, dan upgrade ke Laravel 11/12 berada di luar lingkup.

| Advisory | CVE | Severity | Analisis | Tindakan |
|----------|-----|----------|----------|----------|
| `PKSA-yxbx-3c9s-cjt9` | CVE-2026-128083 | low | Laravel 10 tidak memiliki komponen `Illuminate\Concurrency` | Allowlist |
| `PKSA-d5tc-s1qs-h781` | CVE-2026-102279 | low | XSS debug page hanya aktif bila `APP_DEBUG=true` | Allowlist + wajib `APP_DEBUG=false` di produksi |
| `PKSA-m5cs-t1y6-qpcs` | — | medium | Aplikasi tidak memakai temporary signed URL/`temporaryUrl()` | Allowlist |
| `PKSA-3r5d-mb8f-1qw9` | — | high | CRLF injection pada rule `email` | Mitigasi aplikasi + allowlist |
| `PKSA-mdq4-51ck-6kdq` | CVE-2026-48019 | — | Duplikat advisory CRLF yang sama | Mitigasi aplikasi + allowlist |

Allowlist beserta alasan berada di `composer.json` pada `config.policy.advisories.ignore-id`. Verifikasi:

```
composer validate --no-check-publish
./composer.json is valid

composer audit
Found 5 ignored security vulnerability advisories affecting 1 package...
AUDIT_EXIT=0
```

### Mitigasi CRLF email

Payload rentan menggunakan quoted local-part yang mengandung CRLF, misalnya:

```
"victim\r\nBcc: attacker@example.com"@example.com
```

`egulias/email-validator` menerima folding whitespace tersebut, sehingga alamat lolos rule `email` sebelum mitigasi.

Mitigasi:

- `app/Validation/SafeValidator.php` menolak nilai yang mengandung `\r` atau `\n` sebelum memanggil `validateEmail()` bawaan.
- `AppServiceProvider::boot()` mendaftarkan `SafeValidator` lewat `Validator::resolver()` agar berlaku untuk seluruh validasi aplikasi.
- Test `tests/Feature/SafeEmailValidationTest.php` (5 test / 6 assertions) mencakup payload quoted dan non-quoted, rule `email` polos maupun `email:rfc`, serta memastikan alamat valid tetap diterima.

Bukti red → green: sebelum implementasi 3 test gagal; setelah implementasi 5 test lulus. Full suite tetap hijau.

**Persyaratan operasional:** produksi wajib `APP_DEBUG=false`. Ini juga menjadi alasan allowlist advisory debug-page XSS.

## 5. TODO C2 — Escrow/refund lanjutan (belum diubah)

Sesuai keputusan, `EscrowService`, alur `approved → completed`, dan `PayoutAdjustment` tidak diubah pada batch ini. Temuan yang harus diselesaikan di C2:

1. `EscrowService::processRefund()` (`app/Services/EscrowService.php:269`) tidak memiliki pemanggil.
2. `Pembayaran::REFUND_COMPLETED` (`app/Models/Pembayaran.php:29`) tidak pernah di-set oleh kode aplikasi; hanya dipakai helper model dan test.
3. `PropertyOwnerPayoutService::createAdjustment()` (`app/Services/PropertyOwnerPayoutService.php:474`) menghitung cap refund dari **semua** `PayoutAdjustment` (`:525-527`), sehingga adjustment non-refund ikut memakan kuota refund.
4. `EscrowService::processRefund()` melakukan mass update `WalletTransaction` (`app/Services/EscrowService.php:279-282`) yang melewati guard append-only model (`app/Models/WalletTransaction.php:29-41`).
5. `SettlementService` (`app/Services/SettlementService.php:54-61`) dan `SettlementReportService` (`app/Services/SettlementReportService.php:23-33`, `:102-106`) menghitung pembayaran approved tanpa filter refund/DP non-refundable.

## 6. Sisa backlog release-wide

Belum dikerjakan pada batch ini:

- C2 FASE 1 (escrow/refund di atas).
- CI H3.
- H1/H2 mode.
- H5 verifikasi MySQL lokal.
- H6 retensi KTP.
- Repo hygiene.

## 7. Perintah verifikasi

```powershell
php artisan test
vendor\bin\pint --test
npm audit
npm run build
composer validate --no-check-publish
composer audit
```
