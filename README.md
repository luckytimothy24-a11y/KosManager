# KosManager

Aplikasi manajemen kos (boarding house) berbasis Laravel 10 dengan alur lengkap: booking → check-in → tagihan → pembayaran → check-out.

## Fitur

- **Multi-role**: Super Admin, Owner, Admin (pengelola kos), Tenant (penyewa)
- **Kelola Kos & Kamar**: CRUD kos/kamar, fasilitas, foto, harga harian/bulanan
- **Booking**: tenant booking kamar, owner/admin approve/reject, deteksi konflik periode, auto-expire
- **Check-in / Check-out**: proses check-in membuat penghuni + kontrak otomatis; check-out menyetujui/menolak pengajuan dan memutus kontrak
- **Tagihan**: generate tagihan per kontrak (bulanan/harian), diskonen/penalti, penandaan overdue otomatis
- **Pembayaran**: tenant upload bukti (disimpan di disk privat), owner/admin verifikasi/tolak dengan rekonsiliasi nominal
- **Laporan**: export PDF (barryvdh/laravel-dompdf) & Excel (maatwebsite/excel)
- **Notifikasi**: in-app + email terqueue untuk setiap peristiwa penting
- **Audit Log**: pencatatan aksi login/CRUD/approve/reject

## Persyaratan

- PHP >= 8.1
- Composer
- MySQL
- Node.js & NPM

## Instalasi

```bash
composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

php artisan migrate --seed
php artisan storage:link

npm run dev   # atau npm run build untuk production
```

## Akun Demo (setelah `db:seed`)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@example.com | password |
| Admin | admin@example.com | password |
| Owner | owner@example.com | password |
| Tenant | tenant@example.com | password |

## Perintah Terjadwal (scheduler)

Jalankan `php artisan schedule:work` (atau cron `schedule:run`):

- `tagihan:mark-overdue` — tandai tagihan unpaid/pending_verification yang lewat jatuh tempo sebagai overdue
- `tagihan:remind-due` — ingatkan tagihan mendekati jatuh tempo
- `booking:expire-old` — kedaluwarsakan booking pending lama
- `kontrak:expire-old` — kedaluwarsakan kontrak yang sudah berakhir

## Queue

Email dikirim via queue. Set `QUEUE_CONNECTION=database` di `.env` lalu jalankan:

```bash
php artisan queue:work
```

## Struktur Utama

```
app/
├── Console/Commands/    # Perintah terjadwal (overdue, expire, remind)
├── Http/
│   ├── Controllers/
│   │   ├── Owner/       # Controller utama (dipakai owner/admin/super-admin/tenant)
│   │   └── SuperAdmin/  # Manajemen user, fasilitas, laporan global
│   ├── Middleware/      # RoleMiddleware (role:a,b)
│   └── Requests/        # Validasi FormRequest
├── Models/              # Kos, Kamar, Booking, Penghuni, Kontrak, Tagihan, Pembayaran, ...
├── Policies/            # Otorisasi level record
├── Services/            # AuditLogService, NotificationService
└── Traits/HasRole.php   # Helper role (isAdmin, isOwner, canAccessKos, ...)
resources/views/
├── owner/  admin via prefix route sama
├── tenant/
├── super-admin/
└── exports/pdf/         # Template PDF laporan
docs/ERD.md              # Diagram relasi database
```

## Testing

```bash
php artisan test
```

Menggunakan SQLite in-memory (lihat `phpunit.xml`).
