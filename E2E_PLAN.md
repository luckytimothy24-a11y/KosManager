# Rencana E2E Playwright (Tahap 5, Butir 3)

## Dependency yang Diperlukan
- @playwright/test (npm devDependency)
- playwright browsers (chromium)

## Struktur Test
```
tests/e2e/
├── fixtures/
│   └── test-data.json          # Test users, kos, kamar
├── pages/
│   ├── LoginPage.ts            # Page object
│   ├── BookingPage.ts
│   ├── PaymentPage.ts
│   ├── CheckInPage.ts
│   └── CheckOutPage.ts
├── flows/
│   ├── tenant-booking-payment.spec.ts    # (a) tenant booking sampai bayar DP
│   ├── owner-approve-identity.spec.ts    # (b) owner approve booking + verifikasi data diri
│   └── tenant-checkout-approve.spec.ts   # (c) tenant request check-out + owner approve
└── playwright.config.ts
```

## Alur Test

### (a) Tenant Booking sampai Bayar DP
1. Login sebagai tenant (tenant@example.com)
2. Navigasi ke Cari Kos → pilih kos → pilih kamar
3. Klik "Ajukan Booking" → isi form (tanggal, tipe sewa, catatan)
4. Submit booking
5. Verifikasi redirect ke halaman booking detail
6. Verifikasi status "Menunggu pembayaran"
7. Pilih metode pembayaran (QRIS/transfer)
8. Upload bukti pembayaran (untuk transfer)
9. Submit pembayaran
10. Verifikasi status "Menunggu verifikasi Owner"

### (b) Owner Approve Booking dan Verifikasi Data Diri
1. Login sebagai owner (owner@example.com)
2. Navigasi ke Booking → lihat pending booking
3. Klik booking → approve booking
4. Verifikasi status berubah ke "Terkonfirmasi"
5. Navigasi ke Check-In → lihat pending identity
6. Klik detail → confirm identity
7. Verifikasi status berubah ke "Terkonfirmasi"

### (c) Tenant Request Check-Out dan Owner Approve
1. Login sebagai tenant
2. Navigasi ke Kontrak → lihat kontrak aktif
3. Klik "Ajukan Check-Out"
4. Isi kondisi kamar + catatan
5. Submit check-out
6. Verifikasi status "Menunggu persetujuan"
7. Login sebagai owner
8. Navigasi ke Check-Out → lihat pending
9. Klik detail → approve check-out
10. Verifikasi status berubah ke "Disetujui"

## Database Test
- Gunakan SQLite in-memory (sama seperti PHPUnit)
- Seeder khusus: `DatabaseSeeder` sudah menyediakan demo accounts
- Reset DB sebelum setiap test (Playwright globalSetup)

## Estimasi Usaha
- Setup Playwright: 1-2 jam
- Page objects: 2-3 jam
- 3 test flows: 2-3 jam
- Debug & CI integration: 1-2 jam
- Total: 6-10 jam

## Pertanyaan
1. Apakah Anda ingin menambahkan dependency @playwright/test?
2. Apakah perlu CI integration (GitHub Actions) untuk E2E?
3. Apakah ada device/browser khusus yang harus di-test (mobile viewport)?
