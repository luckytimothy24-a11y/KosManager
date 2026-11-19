# PROGRESS.md

## Status
- [x] Tahap 0: PWA Phase 1-4 (manifest, SW, push notifications, frontend subscription)
- [x] Tahap 0: Booking flow clarity (timeline 6 tahap, nextAction dinamis)
- [x] Tahap 0: DP refund policy (visual warning di create, success, show)
- [x] Tahap 0: Pending booking reminder command (tenant + owner)
- [x] Tahap 0: Owner checkout list (kondisi kamar + catatan tenant)
- [x] Tahap 0: Dashboard tenant (progress indicator, pending booking card, checkout condition form)

## Tahap 1: Keamanan Data Sensitif
- [x] Langkah 1: Audit (selesai)
- [x] Langkah 2: Perbaikan (selesai)
  - Commit 1 (4ed31c7): Service worker /api/ -> NetworkOnly + cache clearing on logout
  - Commit 2 (c22bd73): Throttle register, forgot/reset password, booking, payment upload, messages, downloadProof
  - Commit 3 (06ba9b4): Download private files as attachment with nosniff, inline only for images
  - Commit 4 (8605c12): .env.example: SESSION_SECURE_COOKIE, SESSION_SAME_SITE, APP_DEBUG notes
  - Commit 5 (c3ac795): SafeUpload rule for payment proof (image content check, PDF mimes+max) + SafeImage for marketplace
  - Commit 6 (73a0956): SecurityAuditTest (cross-tenant 403, forbidden file type, mass assignment, throttle 429)

## Tahap 2: Perbaikan Cepat
- [x] Item 1: Ganti 'Lengkapi Identitas' -> 'Verifikasi Data Diri' (commit abc1a52)
- [x] Item 2: Kebijakan refund DP di config/booking.php (commit 9443fb9)
- [x] Item 3: Halaman error kustom (commit a478f91)
- [x] Item 4: Kontras WCAG AA (commit a63d60f) - 131 files
- [x] Item 5: x-empty-state component (commit a3337a5)

## Tahap 3: "Perlu Tindakan" dan Navigasi Owner/Admin
- [x] Butir 1-2: Perlu Tindakan section di dashboard owner & admin (commit f29f297)
- [x] Butir 3: Bottom nav reorganized - Booking sub-tabs, Profil for secondary menus (commit f29f297)
- [x] Butir 4: Scope label di admin header (commit f29f297)
- [x] Butir 5: DashboardActionItemsTest (commit e9eedb9)

## Tahap 4: Wizard Booking, Pembayaran, dan Timeline
- [ ] Belum dimulai

## Tahap 5: Dokumentasi, E2E, dan Monitoring
- [ ] Belum dimulai

## Catatan
- Branch: feature/alur-dana-2-tahap
- Tests terakhir: 112 passed (341 assertions) + 9 SecurityAuditTest
- Build Vite: sukses
- Audit temuan: file sensitif sudah di disk private + policy check. Rate limiting ditambahkan. SafeUpload rule dibuat. .env.example diperbarui.
- TODO: Kebijakan riwayat bukti bayar (hapus file lama atau simpan riwayat) - belum ditentukan
- TODO: Enkripsi identity_number - audit saja, belum diubah (sesuai instruksi)
- TODO: 2FA dan backup database - hanya rekomendasi, tidak diimplementasi
