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
- [x] Butir 1: Wizard booking 3 langkah (commit c9feffd, 93ceda1)
- [x] Butir 2: Image preview + sticky submit di payment (commit cd6bea0)
- [x] Butir 3: Timeline mobile summary (commit cd6bea0)
- [x] Butir 4: Aksesibilitas - x-form-field + focus management (commit 48cc6e7)
- [x] Butir 5: BookingWizardTest (commit 93ceda1)

## Tahap 6: Super Admin Flow Improvements
- [x] Bottom nav khusus super admin (Beranda | User | Moderasi | Settings | Profil)
- [x] Perlu Tindakan section di dashboard super admin
- [x] Sidebar reorganisasi (4 grup: Manajemen, Transaksi, Moderasi, Sistem)
- [x] Notifikasi ke super admin (damage report + advertising campaign)

## Tahap 5: Dokumentasi, E2E, dan Monitoring
- [x] Butir 1: README.md sudah ada (comprehensive)
- [x] Butir 2: CI GitHub Actions sudah ada (.github/workflows/ci.yml)
- [x] Butir 3: E2E Playwright - 9 tests, 3 flows (commit 297700c)
- [x] Butir 4: Health check endpoint /health + monitoring recommendations (commit 45b6b5b)
- [x] Butir 5: Dockerfile, docker-compose, nginx, supervisor, .env.docker (commit 97120dc)

## Tahap 7: Finalisasi Presentasi
### Fase A — Notifikasi & Laporan
- [x] A1: Notifikasi moderasi kos (super admin saat kos baru pending, owner saat approved/rejected)
  - `NotificationType::KOS_MODERATION`, `NotificationService::kosCreatedForModeration/kosApproved/kosRejected`
  - Dashboard super admin menghitung `moderation_status = pending`
  - `KosModerationNotificationTest` (5 tests)
- [x] A2: Export laporan sudah mendukung filter tanggal (terverifikasi di `ReportDashboardHardeningTest`)

### Fase B — Keamanan Lanjutan
- [x] B1: Kebijakan riwayat bukti bayar
  - Record pembayaran tetap disimpan (audit trail); file lama dihapus saat replace
  - Command `payments:purge-rejected-proofs --days=180 [--dry-run]`, dijadwalkan mingguan
  - `PaymentProofRetentionTest` (3 tests)
- [x] B2: Enkripsi NIK at-rest
  - Trait `HasEncryptedIdentityNumber` (accessor/mutator + hash SHA-256 + masking)
  - Migration `2026_11_19_000002_encrypt_identity_numbers` (users, penghunis, bookings, booking_occupants)
  - Pencarian NIK exact-match via hash; endpoint reveal dengan throttle + audit log
  - `IdentityEncryptionTest` (10 tests)
- [x] B3: 2FA TOTP
  - `TwoFactorService` (RFC 6238, Base32, QR), `TwoFactorController`, middleware `EnsureTwoFactorVerified`
  - Halaman setup/konfirmasi/challenge + recovery codes; enforcement opsional `TWO_FACTOR_ENFORCE`
  - `TwoFactorAuthTest` (12 tests)
- [x] B4: Backup database — sudah tersedia lengkap (`BackupDatabase`, `config/backup.php`, scheduler)

### Fase C — UX & Performa
- [x] C1: Ringkasan validasi form global (`x-validation-summary`) di layout app & guest
- [x] C2: Loading state global untuk form server-rendered (anti double-submit, spinner, `aria-busy`)
- [x] C3: Audit N+1 — `Model::preventLazyLoading()` (aktif default di local) + `LazyLoadingAuditTest` untuk halaman utama semua role
- [x] C4: Polish iOS/Lighthouse — manifest `id`, `color-scheme`, theme-color dark, apple-touch-icon 180x180, mask-icon, meta PWA di layout guest

## Catatan
- Branch: feature/alur-dana-2-tahap
- Tests terakhir: 2364 passed (seluruh suite; 1 kegagalan lama pada tema iklan sudah diperbaiki)
- Build Vite: sukses
- Audit temuan: file sensitif sudah di disk private + policy check. Rate limiting ditambahkan. SafeUpload rule dibuat. .env.example diperbarui.
- Semua TODO keamanan sebelumnya (bukti bayar, enkripsi NIK, 2FA, backup) sudah diselesaikan.

