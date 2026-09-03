# Naskah Presentasi KosManager — Versi Lengkap & Detail

> **Cara memakai naskah ini:** setiap slide memiliki (a) judul slide yang disarankan, (b) **teks narasi lengkap** yang bisa dibacakan langsung, (c) catatan penyampaian dalam *italic*. Durasi total narasi ± 18–22 menit; jika waktu hanya 7–10 menit, bacakan bagian inti (slide 2, 6, 7, 9–14, 17–18) dan ringkas sisanya.
>
> Angka dan nama file di naskah ini diambil langsung dari kode project — aman disebut saat ditanya detail.

---

## SLIDE 1 — Judul & Pembukaan
**Judul slide:** KosManager — Sistem Manajemen Kos Berbasis Web (Laravel 10)

### Teks Narasi

Assalamualaikum warahmatullahi wabarakatuh / selamat pagi Bapak-Ibu penguji, Bapak-Ibu pembimbing, dan teman-teman sekalian.

Perkenalkan, saya **[nama lengkap]**, dari **[kampus/divisi/universitas]**. Pada kesempatan kali ini, izinkan saya mempresentasikan hasil project yang saya kerjakan, yaitu **KosManager — Sistem Manajemen Kos Berbasis Web**.

Project ini dibangun menggunakan framework **Laravel versi 10**, dengan database **MySQL**, frontend **Blade + Tailwind CSS**, dan dilengkapi **246 automated test** yang seluruhnya lulus, serta pipeline **CI/CD GitHub Actions** yang berjalan otomatis.

Selama presentasi berikutnya, saya akan menjelaskan lima hal: pertama, latar belakang mengapa aplikasi ini dibuat; kedua, arsitektur dan struktur database-nya; ketiga, logika di balik setiap fitur; keempat, sisi keamanan dan testing; dan kelima, demonstrasi singkat aplikasi secara live.

*Kiat penyampaian: sebut angka "246 test" sejak awal — ini langsung memberi kesan project ini serius, bukan sekadar CRUD biasa.*

---

## SLIDE 2 — Latar Belakang Masalah
**Judul slide:** Mengapa KosManager Dibuat?

### Teks Narasi

Sebelum masuk ke teknis, saya mulai dari masalah yang ingin diselesaikan.

Pengelolaan kos pada umumnya masih dilakukan secara manual atau semi-manual. Dari observasi saya, ada empat masalah utama yang berulang:

**Pertama, pencatatan tersebar.** Data penghuni dicatat di buku induk, tagihan dikirim lewat chat WhatsApp, bukti transfer tersimpan di galeri pribadi pemilik. Ketika butuh riwayat satu penghuni, harus mencari di banyak tempat, dan tidak ada satu sumber kebenaran.

**Kedua, booking rawan bentrok.** Karena tidak ada sistem yang mencatat periode sewa, satu kamar bisa saja dijanjikan kepada dua calon penyewa untuk periode yang sama. Akibatnya kompromi kualitas pelayanan dan potensi konflik.

**Ketiga, tagihan sulit dipantau.** Mana penghuni yang sudah bayar, mana yang telat bayar, berapa denda yang seharusnya — semuanya bergantung pada ingatan pengelola. Keterlambatan penagihan pun sulit dideteksi.

**Keempat, bukti pembayaran tidak aman.** Foto transfer penghuni biasanya dikirim lewat chat. File itu bisa tersebar, bisa diakses orang yang tidak berwenang, padahal isinya adalah dokumen finansial.

Keempat masalah inilah yang ingin diselesaikan KosManager dalam **satu aplikasi terintegrasi dengan alur yang saling terhubung** — dari booking sampai check-out.

---

## SLIDE 3 — Solusi Sekilas
**Judul slide:** Satu Aplikasi, Satu Alur Utuh

### Teks Narasi

KosManager adalah aplikasi web yang mengelola **seluruh siklus bisnis kos** dalam satu tempat:

> **Booking → Approval → Check-in → Kontrak → Tagihan → Pembayaran → Verifikasi → Check-out**

Yang menjadi kekuatan utama aplikasi ini adalah **setiap tahap saling terkait dan saling menjaga**: kamar baru bisa di-check-in kalau booking-nya disetujui; check-out tidak bisa diproses kalau masih ada tagihan belum lunas; dan pembayaran tidak dianggap sah sampai diverifikasi pengelola.

Dengan demikian, **status kamar dan kondisi keuangan selalu konsisten** — tidak ada kamar terisi tanpa kontrak, tidak ada pendapatan tanpa verifikasi.

Aplikasi ini mendukung empat peran: Super Admin, Owner, Admin operasional, dan Tenant — masing-masing dengan hak akses yang ketat dan tampilan menu yang berbeda sesuai tanggung jawabnya.

---

## SLIDE 4 — Peran Pengguna (Role & Tanggung Jawab)
**Judul slide:** Empat Peran, Empat Hak Akses

### Teks Narasi

Mari saya bedah perannya satu per satu.

**Super Admin** bertindak sebagai administrator sistem. Ia mengelola akun semua pengguna melalui fitur manajemen user — termasuk membuat owner baru, menonaktifkan akun, dan menghapus user dengan aturan proteksi data historis. Super Admin juga mengelola master data fasilitas yang dipakai semua kos, melihat laporan gabungan seluruh kos, serta membaca audit log aktivitas seluruh sistem.

**Owner** adalah pemilik properti. Ia memiliki kendali penuh atas kos miliknya: mendaftarkan kos, menambah kamar beserta foto, fasilitas, dan harga harian maupun bulanan. Owner juga menyetujui booking, mengelola kontrak dan penghuni, memproses check-in/check-out, menerbitkan tagihan, memverifikasi pembayaran, hingga mengekspor laporan PDF dan Excel khusus miliknya sendiri. Yang penting: **owner sama sekali tidak dapat melihat atau mengubah data kos milik owner lain** — ini dijaga oleh policy dan diuji otomatis.

**Admin** adalah staf operasional yang ditugaskan owner untuk mengelola kos tertentu. Penugasannya disimpan di tabel relasi `kos_user`, sehingga admin hanya bisa mengakses kos yang memang ditugaskan kepadanya. Tugasnya mirip owner untuk sisi operasional — booking, check-in/out, tagihan, verifikasi — tetapi tanpa kepemilikan data, dan tanpa akses ke laporan lintas kos.

**Tenant** adalah penyewa. Dari sisi tenant, aplikasi berfungsi seperti marketplace kos: ia browse daftar kos aktif, melihat detail kamar yang tersedia, melakukan booking, lalu setelah tinggal ia bisa melihat kontraknya, menerima tagihan, meng-upload bukti pembayaran, dan memantau status verifikasinya secara transparan — termasuk membaca alasan bila pembayarannya ditolak.

*Jika ditanya "kenapa admin tidak boleh lihat semua kos?" — jawab: prinsip least privilege; penugasan eksplisit membuat jejak tanggung jawab jelas dan permukaan serangan lebih kecil.*

---

## SLIDE 5 — Fitur Profil, Dark Mode & Manajemen Pengguna
**Judul slide:** Profil Pengguna, Mode Gelap & Manajemen Akun

### Teks Narasi

Sebelum masuk ke fitur inti bisnis, saya jelaskan dulu fitur pendukung yang penting untuk pengalaman pengguna.

**Profil pengguna.** Setiap pengguna — baik super admin, owner, admin, maupun tenant — dapat mengelola profilnya sendiri melalui halaman `/profile`. Fitur yang tersedia meliputi: mengubah nama, email, nomor telepon, dan alamat; mengganti password dengan validasi password saat ini; serta menghapus akun. Untuk menghapus akun, sistem meminta konfirmasi password dan melakukan proteksi: **owner yang masih memiliki kos aktif tidak bisa menghapus akun**, dan **super admin terakhir tidak bisa menghapus dirinya sendiri** — ini menjaga integritas sistem.

**Dark mode.** Aplikasi mendukung tema gelap dan terang yang dapat dialihkan melalui tombol toggle di sidebar atas. Pilihan tema disimpan di `localStorage` browser sehingga tetap persisten antar sesi. Deteksi juga dilakukan otomatis berdasarkan preferensi sistem operasi pengguna pada kunjungan pertama. Seluruh komponen UI — sidebar, kartu statistik, tabel, form, badge status — mendukung tema gelap dengan konsisten.

**Manajemen pengguna oleh Super Admin.** Super Admin memiliki halaman khusus untuk CRUD pengguna di `/super-admin/users`. Fitur ini memungkinkan pencarian berdasarkan nama atau email, filter berdasarkan role, dan menampilkan jumlah kos serta booking yang dimiliki setiap user. **Proteksi penghapusan berlapis**: (1) super admin tidak bisa menghapus diri sendiri; (2) super admin terakhir tidak bisa dihapus; (3) owner yang masih memiliki kos harus mentransfer atau menghapus kosnya terlebih dahulu; dan (4) pengguna yang sudah memiliki riwayat booking atau penghuni hanya bisa di-soft-delete — tidak dihapus permanen — sehingga data historis keuangan tetap utuh.

*Kiat penyampaian: sebutkan proteksi soft delete untuk user bertransaksi — ini menunjukkan kesadaran akan integritas data historis.*

---

## SLIDE 6 — Tech Stack & Alasan Pemilihannya
**Judul slide:** Teknologi yang Digunakan

### Teks Narasi

Sekarang sisi teknologi. Saya jelaskan bukan hanya *apa*-nya, tapi *kenapa*-nya.

**Laravel 10 dengan PHP 8.2** dipilih karena menyediakan ekosistem lengkap out-of-the-box: Eloquent ORM untuk database, Blade templating, middleware, policy, queue, task scheduling, dan testing — semua konsisten dengan satu cara kerja, sehingga fokus pengembangan jatuh ke logika bisnis.

**MySQL** sebagai database production karena relasional dan cocok dengan struktur data kos yang sangat relasional: kos–kamar–kontrak–tagihan–pembayaran.

Menariknya, **automated test dijalankan di SQLite in-memory** — bukan MySQL. Alasannya: SQLite in-memory membuat test suite berjalan sangat cepat dan tanpa setup server database. SQL yang digunakan aplikasi adalah SQL standar yang kompatibel di kedua engine, dan ini sudah dibuktikan oleh suite test itu sendiri.

Untuk frontend, saya memilih **Blade + Tailwind CSS + Alpine.js** yang di-build dengan **Vite**. Blade server-rendered membuat keamanan state tidak tersebar ke client; Tailwind memberi konsistensi desain dengan sistem utility; dan Alpine.js cukup untuk interaktivitas ringan seperti **preview foto real-time**, **modal konfirmasi**, **toggle show/hide password**, **dropdown notifikasi**, dan **estimasi biaya booking** — tanpa perlu kompleksitas SPA framework. Icon menggunakan **Remix Icon** yang di-load via CDN, dan font menggunakan **Lato** via Bunny CDN.

Autentikasi memakai **Laravel Breeze** sebagai fondasi, lalu diperluas dengan sistem role sendiri. Otorisasi granular memakai **Policy Laravel** — ada **sembilan policy** di project ini, satu untuk setiap entitas penting.

Validasi input terpusat di **14 class FormRequest** terpisah — seperti `StoreBookingRequest`, `StoreTagihanRequest`, `StorePembayaranRequest`, `StoreKosRequest`, `StoreKamarRequest` — sehingga aturan validasi teruji, terisolasi, dan bisa digunakan ulang.

Untuk laporan, export **PDF memakai barryvdh/laravel-dompdf** dan **CSV memakai generator native PHP** dengan encoding BOM UTF-8 — sehingga file CSV langsung terbaca benar di Excel Indonesia tanpa masalah encoding.

Terakhir, email notifikasi dikirim melalui **queue dengan driver database**, sehingga pengiriman email tidak memblokir request user.

---

## SLIDE 7 — Arsitektur Aplikasi
**Judul slide:** Arsitektur Request Lifecycle & Struktur Kode

### Teks Narasi

Arsitektur kode mengikuti pola MVC Laravel dengan tambahan layer service. Begini alurnya ketika sebuah request masuk:

```
HTTP Request
   ↓ Middleware autentikasi (auth) + Role Middleware (role:owner,admin)
   ↓ Controller (misal BookingController)
   ↓ FormRequest Validation — validasi input terpusat di class terpisah
   ↓ Policy Authorization — cek hak akses level record
   ↓ Service Layer — AuditLogService & NotificationService
   ↓ Eloquent Model → Database
   ↓ Response Blade view (Tailwind + Alpine)
```

Struktur controller-nya terbagi rapi berdasarkan konteks: ada grup controller utama untuk operasional — `KosController`, `KamarController`, `BookingController`, `KontrakController`, `PenghuniController`, `CheckInController`, `CheckOutController`, `TagihanController`, dan `PembayaranController` — lalu grup khusus tenant: `TenantKosController`, `TenantBookingController`, dan `TenantPembayaranController`; serta grup SuperAdmin untuk `UserController`, `FasilitasController`, dan `LaporanController`.

Ada dua **service class** yang dipakai lintas modul. Pertama `NotificationService` — satu pintu untuk mengirim notifikasi in-app sekaligus email berqueue ke user. Kedua `AuditLogService` — satu pintu untuk mencatat setiap aksi penting: login, create, update, delete, approve, reject — lengkap dengan user pelaku, modul, deskripsi, dan IP address.

Validasi input tidak ditulis di controller, melainkan di **class FormRequest terpisah** — misalnya `StoreBookingRequest`, `StoreTagihanRequest` — sehingga aturan validasi bisa diuji dan digunakan ulang, dan controller tetap ramping.

Dan otorisasi tidak pernah bergantung pada UI saja: meskipun tombol disembunyikan, **policy tetap memeriksa hak akses di level record** setiap kali data diakses.

---

## SLIDE 8 — Struktur Database
**Judul slide:** Database: 15 Tabel dengan Relasi yang Menjaga Integritas

### Teks Narasi

Database KosManager terdiri dari tabel-tabel berikut — saya sebutkan beserta kolom-kolom kuncinya:

**Tabel identitas:**
1. **users** — name, email, password, `role` (super_admin / owner / admin / tenant), phone, address, avatar, `is_active`, plus soft delete agar user bersejarah tidak hilang permanen.
2. **kos** — owner_id, name, address, description, phone, photo, general_facilities, rules, dan `payment_info` yaitu instruksi rekening/instruksi pembayaran yang tampil ke tenant saat mau bayar.
3. **kamar** — kos_id, room_number, room_name, floor, room_type, area, daily_price, monthly_price, photo, dan `status`: available, booked, occupied, atau maintenance.
4. **fasilitas** + pivot **kamar_fasilitas** — many-to-many antara kamar dan fasilitas, lengkap dengan icon untuk ditampilkan di UI.

**Tabel penugasan:**

5. **kos_user** — pivot yang memetakan admin ke kos yang boleh ia kelola. Ini dasar dari isolasi akses admin.

**Tabel transaksi inti:**

6. **bookings** — booking_code unik (`BK-{8 random chars}`), user_id si penyewa, kos_id, kamar_id, booking_date, start_date, end_date, rental_type (daily/monthly), price, notes, soft delete, dan `status` dengan enam nilai: pending, approved, rejected, cancelled, completed, expired.
7. **penghunis** — profil penghuni aktif: user_id, kos_id, kamar_id, identity_number, phone, address, check_in_date, dan status.
8. **kontraks** — contract_number (`KT-{uniqid}`), rental_type, rental_price, start_date, end_date, dan status: active, expired, terminated, completed.
9. **check_ins** — penghuni_id, kamar_id, check_in_date, check_in_time, officer_id petugas yang memproses, dan notes.
10. **check_outs** — penghuni_id, kamar_id, request_date, check_out_date, `room_condition` kondisi kamar saat keluar, notes, status, dan verified_by.

**Tabel keuangan:**

11. **tagihans** — bill_number unik (`TB-{uniqid}`), bill_type (sewa_kamar, listrik, air, internet, kebersihan, lainnya), period_start, period_end, subtotal, discount, penalty, total, due_date, dan status: unpaid, pending_verification, paid, overdue, cancelled.
12. **pembayarans** — payment_number unik (`PY-{uniqid}`), tagihan_id, penghuni_id, amount, payment_date, payment_method (transfer_bank, e_wallet, cash), proof_file, verification_status (pending/approved/rejected), admin_notes alasan penolakan, verified_by, dan verified_at.

**Tabel pendukung:**

13. **notifications** — notifikasi in-app: user_id, type (booking/payment/billing/checkin/checkout/kontrak), title, message, is_read, dan data payload.
14. **audit_logs** — user_id, action, module, description, ip_address.
15. Ditambah tabel teknikal Laravel: jobs untuk queue, password_reset_tokens, dan lainnya.

Relasi intinya satu kalimat: **satu Kos punya banyak Kamar; satu Kamar punya banyak Booking, Kontrak, Penghuni, dan Tagihan; satu Tagihan punya banyak Pembayaran.** Foreign key dikunci dengan constraint onDelete cascade pada relasi yang tepat, dan ada migrasi khusus yang menambahkan performance index pada kolom-kolom yang sering difilter: `notifications(user_id, is_read)`, `bookings(kamar_id, status, start_date, end_date)`, dan `tagihans(kontrak_id, period_start, period_end)`.

Diagram ERD lengkap tersedia di dokumentasi project, file `docs/ERD.md`.

---

## SLIDE 9 — Manajemen Kos & Kamar
**Judul slide:** Fondasi Data Properti

### Teks Narasi

Fitur pertama adalah manajemen properti — pintu masuk semua alur lain.

Owner dapat membuat kos dengan informasi lengkap: nama, alamat, deskripsi, nomor telepon, foto, daftar fasilitas umum, peraturan kos, dan **payment_info** — yaitu teks instruksi pembayaran seperti nomor rekening. Informasi ini nantinya otomatis ditampilkan kepada tenant pada halaman tagihan ketika mereka akan membayar, sehingga tenant tahu harus transfer ke mana tanpa perlu ditanya satu-satu.

Setiap kamar dicatat dengan nomor, nama, lantai, tipe, luas, harga harian, harga bulanan, foto, dan fasilitas berupa checkbox dari master data. Status awal kamar adalah *available*.

Di sini ada dua aturan integritas data yang penting:

**Pertama**, kamar yang masih memiliki penghuni aktif **tidak boleh diubah statusnya** menjadi available, dan tidak boleh dihapus jika masih ada booking atau riwayat pembayaran. Ini mencegah hilangnya data historis keuangan.

**Kedua**, super admin dapat mengelola kamar atas nama owner, tetapi tetap tidak bisa membuat kamar pada kos yang tidak ada — validasi foreign key tetap berlaku.

**Master data fasilitas** dikelola oleh Super Admin melalui `/super-admin/fasilitas`. Setiap fasilitas memiliki nama unik dan icon (menggunakan Remix Icon). Fasilitas-fasilitas ini kemudian tersedia sebagai checkbox saat owner membuat atau mengedit kamar — sehingga tidak perlu input ulang nama fasilitas yang sama di setiap kos.

Semua form CRUD dilengkapi preview foto real-time menggunakan Alpine.js, validasi tipe dan ukuran file di sisi server (maksimal 2MB untuk foto), dan foto lama otomatis dihapus dari storage saat diganti.

---

## SLIDE 10 — Katalog Kos & Booking untuk Tenant
**Judul slide:** Dari Pencarian ke Pengajuan Sewa

### Teks Narasi

Sebelum masuk ke booking, saya jelaskan pengalaman tenant dari awal.

Ketika tenant membuka halaman katalog kos (`/tenant/kos`), yang tampil hanya **kos berstatus aktif**. Setiap kartu kos menampilkan nama, alamat, jumlah kamar tersedia, dan harga mulai sewa bulanan — sehingga tenant bisa langsung membandingkan. Pencarian tersedia berdasarkan nama atau alamat kos. Halaman detail kos (`/tenant/kos/{id}`) menampilkan deskripsi lengkap, fasilitas umum, peraturan, instruksi pembayaran, serta daftar **kamar yang tersedia** beserta harga harian dan bulanan.

Ketika tenant memilih "Booking Sekarang", form booking multi-step muncul: memilih kamar (hanya kamar tersedia yang ditampilkan), memilih tipe sewa (harian atau bulanan), memilih tanggal mulai dan akhir, serta mengisi data identitas. **Estimasi biaya terhitung otomatis secara real-time** di form sehingga tenant tahu total yang harus dibayar sebelum mengajukan.

Di sisi server, sebelum booking dibuat, sistem melakukan rangkaian pemeriksaan:

**Satu** — apakah kamar tersedia? Kamar berstatus *occupied*, *booked*, atau *maintenance* otomatis ditolak. Ini dicek ulang di server, bukan hanya disembunyikan di UI.

**Dua** — apakah kombinasi kos dan kamar konsisten? Tenant tidak bisa memilih kamar dari kos yang berbeda dengan kos yang dipilih.

**Tiga — yang paling penting — deteksi overlap periode.** Sistem memeriksa apakah rentang tanggal yang diajukan beririsan dengan booking lain yang berstatus approved pada kamar yang sama. Termasuk kasus rentang yang *terkandung di dalam* booking existing. Jika bentrok, booking ditolak dengan pesan yang jelas.

**Empat** — harga sewa harus terdefinisi. Jika tenant memilih tipe harian tapi harga harian kosong, atau tipe bulanan tapi harga bulanan kosong, booking ditolak.

Jika lolos semua, booking tersimpan dengan kode booking unik `BK-{8 random}` dan status *pending*, notifikasi terkirim ke owner, dan tenant bisa memantau statusnya di halaman "Booking Saya". Tenant juga bisa **membatalkan booking** selama statusnya masih *pending* atau *approved* — jika dibatalkan setelah approved, status kamar otomatis kembali ke *available*.

Di sisi owner: approve mengonfirmasi reservasi dengan **transaction lockForUpdate**; reject menolaknya dan **status kamar otomatis bebas kembali**. Ada perlindungan race condition: **booking yang sudah diproses tidak bisa diproses dua kali** — double-reject atau reject-setelah-approve diblokir, dan ini diuji khusus.

Terakhir, booking pending yang tidak pernah diproses dan melewati tanggal sewanya **kedaluwarsa otomatis** melalui scheduled command `booking:expire-old`, sehingga tidak ada kamar yang terkunci selamanya oleh booking lupa.

---

## SLIDE 11 — Check-In
**Judul slide:** Check-In: Satu Aksi, Tiga Record Terbentuk

### Teks Narasi

Check-in adalah momen transisi dari calon penyewa menjadi penghuni resmi.

Aturan pertamanya tegas: **yang bisa di-check-in hanyalah booking berstatus approved** — booking pending atau rejected pasti ditolak oleh controller.

Ketika owner atau admin memproses check-in, sistem dalam satu proses mencatat tiga hal:

**Pertama**, dibuat record **Penghuni** — berisi identitas, nomor identitas, kontak, alamat, dan tanggal check-in.

**Kedua**, dibuat **Kontrak sewa** dengan nomor kontrak, tipe sewa, harga sesuai booking, dan periode mulai–selesai.

**Ketiga**, status **kamar berubah menjadi occupied**.

Record check_in juga menyimpan **siapa petugas yang memproses** — kolom officer_id — sehingga selalu ada jejak akuntabilitas.

Untuk admin, ada pembatasan tambahan: **admin hanya dapat memproses check-in pada kos yang ditugaskan kepadanya** via tabel kos_user. Percobaan check-in di kos lain akan ditolak oleh policy — dan skenario "admin tanpa penugasan mencoba memproses" adalah salah satu test yang ada.

Notifikasi dikirim ke tenant bahwa check-in berhasil — baik in-app maupun email berqueue.

---

## SLIDE 12 — Kontrak & Check-Out
**Judul slide:** Kontrak Terkelola, Check-Out Terkendali

### Teks Narasi

Kontrak adalah jembatan antara sisi operasional dan sisi keuangan. Setiap penghuni aktif terikat satu kontrak berjalan dengan periode dan harga yang jelas.

Status kontrak dikelola otomatis: command terjadwal `kontrak:expire-old` setiap hari memeriksa kontrak aktif yang sudah melewati tanggal berakhir, lalu **meng-expire kontrak dan otomatis men-check-out penghuninya** — dengan satu syarat bijak: **kalau penghuni masih punya tunggakan, ia tidak dilepas sampai tagihannya beres**. Jadi hutang tidak bisa "lolos" hanya karena kontrak habis. Dan ada fitur retry sweep: ketika tunggakan lunas, sistem otomatis menyelesaikan check-out yang tertunda.

Proses **check-out manual** dimulai dari dua arah: penghuni mengajukan sendiri dari dashboard-nya, atau diinisiasi pengelola. Pengajuan mencatat tanggal rencana keluar, kondisi kamar saat keluar, dan catatan.

Nah, di sinilah gerbang keuangannya bekerja: **check-out tidak bisa disetujui kalau penghuni masih memiliki tagihan unpaid, overdue, atau pembayaran yang sedang menunggu verifikasi.** Bahkan tagihan lunas yang ternyata dibatalkan pembayarannya akan memblokir lagi. Semua pemeriksaan ini berjalan **di dalam database transaction dengan lockForUpdate** — jadi dua klik approve bersamaan tidak akan merusak data.

Begitu check-out disetujui: kontrak berakhir dengan status yang sesuai — *terminated* bila berakhir lebih cepat, *expired* bila tepat di atau setelah masa kontrak — penghuni dinonaktifkan, kondisi kamar tercatat, dan **kamar kembali available** siap dibooking orang berikutnya.

---

## SLIDE 13 — Tagihan
**Judul slide:** Tagihan: Rumus Jelas, Status Terkontrol

### Teks Narasi

Fitur tagihan menerbitkan bill sewa untuk penghuni yang terikat kontrak aktif.

Formulanya transparan dan divalidasi:

> **Total = Subtotal − Diskon + Denda**

Subtotal dihitung dari harga kontrak dikali durasi — per bulan untuk sewa bulanan, per hari untuk harian. Untuk sewa bulanan, sistem menggunakan perhitungan *ceiling months* — bulan parsial dihitung sebagai satu bulan penuh. Ada validasi khusus: **diskon tidak boleh melebihi subtotal**, dan **total tidak boleh negatif** — test-nya ada, termasuk kasus diskon sama dengan subtotal plus denda.

Setiap tagihan memiliki **nomor bill unik** (`TB-{uniqid}`), tipe tagihan (sewa kamar, listrik, air, internet, kebersihan, lainnya), rentang periode yang ditagih, dan tanggal jatuh tempo. **Pencegahan duplikasi**: sistem memastikan tidak ada dua tagihan dengan kontrak yang sama dan periode yang sama — ini dicek di dalam transaksi dengan lockForUpdate.

Status tagihan bergerak dalam siklus: **unpaid** → ketika tenant upload bukti menjadi **pending_verification** → setelah disetujui menjadi **paid**. Dan ada status **overdue**: scheduled command `tagihan:mark-overdue` berjalan rutin menandai tagihan unpaid yang sudah lewat jatuh tempo — dengan pengecualian cerdas: **tagihan yang sedang menunggu verifikasi tidak ikut di-overdue-kan**, karena buktinya sudah masuk, tinggal diperiksa.

Command pendampingnya, `tagihan:remind-due-soon`, mengirim pengingat ke tenant beberapa hari sebelum jatuh tempo (default 3 hari, bisa dikonfigurasi).

Di halaman detail tagihan, tenant melihat rincian lengkap: breakdown subtotal-diskon-denda-total, riwayat semua percobaan pembayaran beserta status verifikasinya, dan instruksi rekening dari pengelola. Di dashboard, tenant juga melihat kartu ringkas: berapa tagihan belum bayar, total nominalnya, dan jatuh tempo terdekat.

---

## SLIDE 14 — Pembayaran & Verifikasi
**Judul slide:** Pembayaran: Bukti Privat, Verifikasi Berkala

### Teks Narasi

Ini fitur yang paling sensitif, karena menyangkut uang. Saya jelaskan alurnya lengkap.

Ketika tagihan belum lunas, tenant menekan "Bayar". Halaman menampilkan **instruksi pembayaran dari pengelola** — nomor rekening yang sudah diatur di data kos. Tenant memilih metode — transfer bank, e-wallet/QRIS, atau tunai — lalu meng-upload bukti berupa gambar atau PDF (maksimal 5MB).

Di titik ini ada **empat proteksi**:

**Satu — nominal terkunci.** Field nominal bersifat readonly; nilai yang dikirim wajib sama dengan total tagihan dan **divalidasi ulang di server**. Percobaan memanipulasi nilai lewat developer tools pasti tertolak.

**Dua — satu submission aktif per tagihan.** Selama ada pembayaran berstatus menunggu verifikasi, tenant tidak bisa mengirim bukti kedua untuk tagihan yang sama. Ini mencegah spam submission dan kebingungan verifikasi.

**Tiga — bukti disimpan di private disk.** File tidak diletakkan di folder publik. URL-nya tidak bisa ditebak dan tidak bisa dibuka langsung. Akses ke bukti hanya melalui **route download terproteksi** (`/owner/pembayaran/{id}/proof`) yang memeriksa: apakah pemohon adalah pemilik pembayaran, atau pengelola yang berwenang. Guest dan tenant lain akan ditolak — dan ini pun punya test khusus.

**Empat — status tagihan ikut bergerak.** Begitu bukti terkirim, tagihan berubah pending_verification sehingga tidak bisa ditagih dobel.

Lalu verifikasi: owner/admin melihat bukti, mencocokkan nominal. **Approve** → pembayaran terverifikasi, tercatat siapa verifikatornya dan kapan, **tagihan menjadi lunas**, dan nominal masuk ke statistik pendapatan. **Reject** → verifikator **wajib memberikan alasan penolakan**; alasan itu tersimpan, tampil ke tenant di halaman pembayaran, status tagihan kembali unpaid, dan tombol "Bayar Kembali" langsung tersedia. Pembayaran yang sudah ditolak tidak bisa diverifikasi belakangan — mencegah inkonsistensi.

Semua aksi verify/reject berjalan dalam transaction dengan lockForUpdate, tercatat di audit log, dan memicu notifikasi ke kedua pihak.

---

## SLIDE 15 — Dashboard & Laporan
**Judul slide:** Dashboard Informatif, Laporan Siap Ekspor

### Teks Narasi

Setiap peran mendapat dashboard yang dirancang sesuai kebutuhannya.

Dashboard **super admin** menampilkan statistik global: total user (dengan rincian owner/tenant), total kos, total kamar (tersedia/terisi), total pendapatan dari seluruh pembayaran terverifikasi, serta grafik batang pendapatan dan booking enam bulan terakhir.

Dashboard **owner** menampilkan kartu ringkasan: jumlah kos, jumlah kamar, penghuni aktif, dan total pendapatan dari pembayaran terverifikasi; di bawahnya rincian okupansi — berapa kamar tersedia, terisi, maintenance, dan booking pending. Ada panel "perhatian diperlukan" yang muncul otomatis ketika ada booking menunggu persetujuan atau bukti pembayaran menunggu verifikasi — jadi pekerjaan yang menunggu selalu terlihat. Grafik batang pendapatan enam bulan terakhir dan indikator persentase okupansi melengkapi gambaran bisnis.

Dashboard **admin** menampilkan statistik yang lebih spesifik: booking pending, pembayaran pending, check-in/check-out hari ini, kamar tersedia/terisi, tagihan overdue, dan penghuni aktif — semuanya ter-scope hanya untuk kos yang ditugaskan.

Dashboard **tenant** menampilkan kamar yang dihuni, status kontraknya dalam bahasa Indonesia, tagihan belum bayar, jatuh tempo terdekat, status verifikasi, pembayaran terakhir, dan aksi cepat termasuk pengajuan check-out.

Untuk **laporan**, tersedia lima jenis:

| Jenis Laporan | Isi |
|---------------|-----|
| **Pendapatan** | Total pemasukan dari pembayaran terverifikasi per kos |
| **Penghuni** | Jumlah penghuni aktif dan nonaktif per kos |
| **Booking** | Riwayat booking beserta status (pending/approved/rejected) |
| **Kamar** | Status setiap kamar: tersedia, di-booking, terisi, maintenance |
| **Tagihan** | Status tagihan: lunas, belum bayar, terlambat, beserta total nominal |

SuperAdmin melihat rekap global seluruh kos; owner melihat rekap miliknya saja. Keduanya dapat **filter berdasarkan rentang tanggal dan kos tertentu**, lalu diekspor ke **PDF via DomPDF** (dengan layout branded) dan **CSV** (dengan encoding BOM UTF-8 untuk kompatibilitas Excel). Ada test yang memastikan **export owner hanya berisi data kos miliknya**, tidak bocor lintas owner.

---

## SLIDE 16 — Notifikasi, Audit Log & Otomasi
**Judul slide:** Sistem yang Bekerja Sendiri Saat Kita Tidak Melihat

### Teks Narasi

Tiga komponen membuat aplikasi ini terasa "hidup".

**Pertama, notifikasi.** Melalui NotificationService, setiap peristiwa penting memicu notifikasi — ada **12 tipe notifikasi**:
- Booking baru untuk owner
- Booking disetujui/ditolak/diexpirasi untuk tenant
- Kontrak aktif untuk tenant
- Pembayaran dikirim untuk owner
- Pembayaran diverifikasi/ditolak untuk tenant
- Tagihan terbit untuk tenant
- Tagihan mendekati jatuh tempo untuk tenant
- Tagihan terlambat untuk tenant
- Check-in berhasil untuk tenant
- Check-out diajukan untuk owner
- Check-out disetujui untuk tenant

Notifikasi masuk ke bell notification in-app dengan badge unread, dan sekaligus dikirim sebagai **email melalui queue** — pengguna tidak perlu refresh mencari tahu apa yang berubah. Tombol "Tandai semua sudah dibaca" tersedia di dropdown notifikasi.

**Kedua, audit log.** Setiap aksi signifikan — login, logout, create, update, delete, approve, reject — dicatat oleh AuditLogService: siapa, melakukan apa, pada modul apa, kapan, dan dari IP berapa. SuperAdmin dapat menelusuri seluruh jejak ini di halaman timeline yang terpaginasi. Setiap entri menampilkan badge aksi berwarna (create=biru, update=hijau, delete=merah, login/logout=ungu), timestamp dalam zona WIB, dan metadata JSON yang bisa di-expand. Ini penting untuk akuntabilitas: kalau suatu hari ada sengketa "saya sudah bayar kok ditolak", jejaknya bisa ditelusuri.

**Ketiga, otomasi terjadwal.** Empat artisan command berjalan via scheduler:
- `tagihan:mark-overdue` (jam 00:05) — menandai tagihan lewat tempo,
- `booking:expire-old` (jam 00:15) — mengakhiri booking pending yang basi dan approved yang lewat masa tanpa check-in,
- `kontrak:expire-old` (jam 00:25) — mengakhiri kontrak berakhir masa, lengkap dengan auto check-out yang hormati tunggakan,
- `tagihan:remind-due-soon` (jam 07:00) — mengingatkan tagihan mendekati tempo (default 3 hari).

Artinya banyak urusan rumah tangga sistem **beres sendiri tanpa intervensi manual**, dan perilaku semua command ini pun diuji oleh feature test khusus.

---

## SLIDE 17 — Keamanan
**Judul slide:** Keamanan Berlapis, Dibuktikan dengan Test

### Teks Narasi

Keamanan di KosManager bukan satu fitur, tapi **lapisan-lapisan** yang saling menguatkan.

**Lapis 1 — Autentikasi & sesi.** Laravel Breeze dengan hashing bcrypt, proteksi CSRF di semua form, rate limiting (5 percobaan per email+IP), dan **cek status aktif** — jika akun dinonaktifkan oleh super admin, user yang mencoba login akan langsung ditolak meskipun password benar.

**Lapis 2 — Role middleware.** Route dikelompokkan per peran: grup owner, grup admin, grup tenant, grup super-admin. Tenant yang mencoba membuka URL owner akan tertolak — ada test yang membuktikan tiap kombinasi peran salah.

**Lapis 3 — Policy authorization.** Sembilan policy menjaga level record: `KosPolicy`, `KamarPolicy`, `BookingPolicy`, `KontrakPolicy`, `PenghuniPolicy`, `CheckInPolicy`, `CheckOutPolicy`, `TagihanPolicy`, `PembayaranPolicy`. Contoh konkret: owner A membuka ID booking milik owner B di address bar → 403. Admin membuka tagihan kos yang tidak ditugaskan → ditolak. Tenant membuka pembayaran tenant lain → ditolak.

**Lapis 4 — Isolasi multi-owner.** Semua query listing ter-scope ke kepemilikan/penugasan, bukan hanya detail. Jadi tidak cuma halaman detail yang aman, tapi daftar, laporan, dan export pun.

**Lapis 5 — Keamanan pembayaran.** Sudah saya jelaskan di slide 13: bukti private storage, download terotorisasi, nominal divalidasi ulang, satu submission aktif, verifikasi dalam transaction.

**Lapis 6 — Integritas data historis.** User yang punya riwayat pembayaran/booking tidak bisa dihapus keras oleh super admin — hanya soft delete — sehingga laporan keuangan tidak pernah rusak referensinya. Bahkan self-delete profile oleh tenant dengan riwayat tetap menjaga rekam finansial.

**Lapis 7 — Akuntabilitas.** Audit log dengan IP address untuk semua aksi penting. Login dan logout juga tercatat.

Dan poin terpenting yang ingin saya tekankan: **semua klaim keamanan ini tidak berhenti di slide — masing-masing punya automated test** yang berjalan setiap ada perubahan kode. Ada test suite khusus bernama SecurityHardeningTest, SecurityTest, IsolationBoundaryTest, PaymentSecurityTest, dan BookingStateRaceTest.

---

## SLIDE 18 — Automated Testing
**Judul slide:** 246 Test · 704 Assertion · Hijau

### Teks Narasi

Ini bagian yang paling membedakan project ini dari kebanyakan aplikasi serupa: **automated testing yang sungguh-sungguh**.

Angka terbaru: **246 test, 704 assertion, semuanya lulus**, dieksekusi di SQLite in-memory sehingga selesai dalam waktu di bawah satu menit.

Cakupannya terbagi dalam beberapa kategori:

**Unit test** menguji logika murni: perhitungan total tagihan dengan diskon dan denda (termasuk kasus edge: diskon sama dengan subtotal, total negatif, ceiling months untuk periode parsial), transisi status booking, pembangkitan kode booking dan nomor bill unik, perilaku service notifikasi dan audit log.

**Feature test** mensimulasikan alur nyata end-to-end: registrasi, login, booking sampai checkout penuh dalam satu lifecycle test, upload pembayaran, verifikasi, export laporan, hingga seluruh command otomasi.

**Race condition test** — ini yang jarang ada di project pemula: apa yang terjadi kalau tombol approve diklik dua kali hampir bersamaan? Kalau pembayaran ditolak lalu dicoba diverifikasi? Test ini memastikan state tidak pernah korup.

**Security & isolation test**: tenant A tidak bisa lihat finansial tenant B, owner A tidak bisa akses kos owner B, guest tidak bisa unduh bukti bayar, admin tanpa penugasan tidak bisa proses check-in, dan seterusnya — puluhan skenario negatif.

**Regression protection test**: kamar bersejarah tidak bisa dihapus, user bertransaksi tidak bisa dihapus permanen, tagihan menunggu verifikasi tidak ikut di-overdue-kan, akun dinonaktifkan tidak bisa login.

Selain PHPUnit, code style dijaga **Laravel Pint** dengan standar PSR-12 — statusnya bersih — dan build frontend diverifikasi dengan Vite.

Filosofinya sederhana: **setiap bug yang ditemukan, ditutup dengan test baru** — sehingga bug yang sama tidak pernah kambuh.

---

## SLIDE 19 — CI/CD dengan GitHub Actions
**Judul slide:** Setiap Push Diuji Otomatis

### Teks Narasi

Untuk menjaga kualitas tetap terjaga setelah project selesai, saya pasang pipeline **CI/CD dengan GitHub Actions** di `.github/workflows/ci.yml`.

Pipeline berjalan otomatis pada dua kejadian: **setiap push ke branch develop** dan **setiap pull request menuju develop**.

Langkah-langkahnya di runner Ubuntu:

Satu — checkout repository. Dua — install PHP 8.2 dengan extension yang dibutuhkan Laravel, DomPDF, dan Excel: mbstring, dom, zip, gd, intl, dan lainnya. Tiga — `composer install`. Empat — menyiapkan environment dari `.env.example` dan generate APP_KEY baru — **tanpa menyentuh environment production sama sekali, dan tanpa satu pun secret ditulis di repository**. Lima — install Node.js 20 LTS, `npm ci`, lalu **build aset frontend** dengan Vite. Enam — migration pada **SQLite in-memory**. Tujuh — jalankan **seluruh PHPUnit test suite**. Delapan — jalankan **Pint code style check**.

Aturannya keras: **pipeline gagal jika salah satu saja gagal** — test gagal, build gagal, atau gaya kode menyimpang. Artinya, kode yang rusak tidak akan pernah diam-diam masuk ke branch develop.

Pipeline ini sudah berjalan dan **berhasil hijau pada run pertama** — badge statusnya terpasang di README repository. Untuk pengembangan tim ke depan, langkah naturalnya adalah branch protection rule agar merge hanya boleh jika CI hijau.

---

## SLIDE 20 — Demo Live
**Judul slide:** Demonstrasi

### Teks Narasi

Sekarang saya tunjukkan aplikasinya secara langsung. Saya akan mengikuti alur bisnis dari awal sampai akhir.

*(Buka browser — login page)*

**Langkah 1 — Landing page.** Saya buka halaman utama — terlihat hero section, daftar kos aktif, statistik jumlah kos dan kamar tersedia, serta fitur-fitur unggulan. Saya bisa search kos berdasarkan nama.

**Langkah 2 — Sebagai tenant.** Saya login sebagai tenant. Di katalog, saya cari kos, buka detail — terlihat daftar kamar tersedia beserta harga harian dan bulanan, fasilitas, dan peraturan. Saya pilih kamar, ajukan booking dengan tipe bulanan — perhatikan estimasi biaya terhitung otomatis. *(Opsional: coba pilih tanggal yang bentrok untuk menunjukkan validasi overlap.)* Booking masuk dengan status menunggu persetujuan. Di dashboard tenant terlihat ringkasan: belum ada kamar aktif, tapi ada 1 booking pending.

**Langkah 3 — Sebagai owner.** Saya ganti akun ke owner. Di dashboard terlihat panel "perhatian diperlukan" — ada satu booking pending. Saya buka, tinjau, lalu **setujui**. Notifikasi muncul. Lanjut ke menu check-in — saya proses check-in booking tadi. Perhatikan: **penghuni dan kontrak terbentuk otomatis**, kamar berubah occupied.

**Langkah 4 — Terbitkan tagihan.** Owner membuat tagihan untuk kontrak itu — sistem menghitung subtotal dari harga kontrak dikali durasi, saya tambahkan diskon kecil, total terbentuk dengan rumus `subtotal - discount + penalty`.

**Langkah 5 — Bayar sebagai tenant.** Ganti akun ke tenant. Di dashboard muncul tagihan baru dengan jumlah yang harus dibayar. Saya buka detail tagihan — instruksi rekening tampil dari data kos. Upload bukti pembayaran, pilih metode transfer bank, kirim. Status berubah "menunggu verifikasi", dan coba perhatikan: tombol bayar menghilang — **tidak bisa dobel bayar**.

**Langkah 6 — Verifikasi.** Kembali ke owner, buka verifikasi pembayaran, lihat buktinya, cocokkan nominal, **verifikasi**. Tagihan lunas, pendapatan naik di grafik. *(Opsional: tunjukkan alur tolak dengan alasan penolakan di pembayaran lain.)*

**Langkah 7 — Tutup dengan dashboard.** Kembali ke dashboard owner: grafik pendapatan, okupansi, notifikasi — semuanya hidup mengikuti transaksi yang baru saja kita lakukan.

Itu satu siklus lengkap: **booking sampai lunas, dalam hitungan menit.**

*Akun demo dari seeder: superadmin@ / owner@ / admin@ / tenant@example.com, password: password.*

---

## SLIDE 21 — Penutup & Rencana Pengembangan
**Judul slide:** Kesimpulan

### Teks Narasi

Sebagai penutup, izinkan saya merangkum tiga hal.

**Pertama, dari sisi produk** — KosManager menyelesaikan empat masalah nyata pengelolaan kos: pencatatan tersebar, booking bentrok, tagihan tak terpantau, dan bukti bayar tidak aman — dalam satu alur terintegrasi yang menjaga konsistensi status kamar dan keuangan. Empat peran dengan hak akses berbeda memastikan setiap pengguna hanya melihat dan mengelola apa yang menjadi tanggung jawabnya.

**Kedua, dari sisi engineering** — project ini menerapkan praktik pengembangan modern secara utuh: 13 model Eloquent dengan migrasi ber-index, 14 FormRequest validation, 9 Policy authorization, 2 Service class, 4 scheduled command, 12 tipe notifikasi, queue email, 246 automated test dengan coverage race condition dan security, code style PSR-12, dan pipeline CI/CD yang hijau.

**Ketiga, dari sisi ke depan** — pengembangan berikutnya sudah terpetakan: REST API untuk integrasi mobile, notifikasi realtime via WebSocket, integrasi payment gateway agar verifikasi otomatis, deployment automation dengan container, backup database otomatis, serta master data untuk tipe kamar dan tipe tagihan. Semua ini tercatat sebagai roadmap di dokumentasi — bukan klaim fitur.

Demikian presentasi saya. Terima kasih atas perhatian Bapak-Ibu dan teman-teman. Saya dan tim siap menerima pertanyaan serta masukan.

Wassalamualaikum warahmatullahi wabarakatuh.

---
---

# LAMPIRAN A — Antisipasi Pertanyaan (Q&A Prep)

| # | Kemungkinan pertanyaan | Jawaban ringkas |
|---|------------------------|-----------------|
| 1 | Kenapa Blade, bukan React/Vue? | Scope fokus ke business logic, keamanan, dan testing. Blade + Alpine ringan, SEO-friendly, tanpa kompleksitas SPA dan tanpa API layer yang belum dibutuhkan. |
| 2 | Kenapa test pakai SQLite, production MySQL? | SQLite in-memory membuat test cepat (<1 menit) tanpa setup DB. SQL yang dipakai standar ANSI — terbukti kompatibel karena suite yang sama memvalidasi logic yang jalan di MySQL. |
| 3 | Bagaimana mencegah satu kamar dibooking dobel? | Validasi server-side: cek status kamar + query overlap periode terhadap booking approved pada kamar yang sama, termasuk kasus rentang terkandung. |
| 4 | Bagaimana mencegah dobel bayar / dobel verifikasi? | Satu submission aktif per tagihan, nominal divalidasi ulang saat verifikasi, dan semua aksi approve/reject dalam transaction + lockForUpdate. |
| 5 | Kalau admin "nakal" akses kos lain? | Tidak bisa — penugasan via pivot kos_user, query ter-scope, dan policy menolak level record. Ada isolation test khusus untuk skenario ini. |
| 6 | Bukti transfer disimpan di mana? Siapa yang bisa lihat? | Private disk Laravel (bukan public). Hanya bisa diunduh lewat route terproteksi (`/owner/pembayaran/{id}/proof`) yang cek kepemilikan/wewenang. Guest dan tenant lain ditolak. |
| 7 | Apa yang terjadi kalau kontrak habis tapi masih utang? | Auto-expire kontrak menahan penghuni sampai tagihan beres; check-out manual juga diblokir selama ada unpaid/overdue/pending verification. Ada retry sweep: ketika tunggakan lunas, check-out otomatis selesai. |
| 8 | Kenapa tidak pakai payment gateway langsung? | Direncanakan sebagai fase berikutnya (roadmap). Fase ini fokus ke alur manual-transfer yang mencerminkan praktik umum kos saat ini, dengan verifikasi berlapis. |
| 9 | Bagaimana performa kalau data besar? | Kolom filter sudah di-index (3 migrasi khusus), pagination di semua listing, eager loading untuk hindari N+1, queue untuk email, scheduled command berjalan ringan. |
| 10 | Bagaimana onboarding developer baru? | README berisi instalasi lengkap + akun demo seeder + ERD di docs/ERD.md + naskah presentasi ini; CI memastikan kontribusi baru tidak merusak apapun. |
| 11 | Apakah bisa deploy sekarang? | Ya untuk skala kecil (VPS + MySQL + queue worker + cron scheduler). Deployment automation tercatat di roadmap. |
| 12 | Kenapa ada dark mode? | UX — banyak pengelola kos bekerja malam hari. Persistensi via localStorage, deteksi preferensi sistem di kunjungan pertama. |
| 13 | Bagaimana cara kerja notifikasi? | 12 tipe notifikasi via NotificationService: in-app (bell icon) + email berqueue. Setiap peristiwa penting memicu notifikasi otomatis ke pihak terkait. |
| 14 | Format export laporan apa yang tersedia? | PDF via DomPDF (layout branded) dan CSV via native PHP (BOM UTF-8 untuk Excel). Filter tanggal dan kos tersedia sebelum export. |
| 15 | Bagaimana penanganan user yang dinonaktifkan? | LoginRequest mengecek `is_active` setelah password valid. Jika false, session langsung di-destroy dengan pesan "Akun Anda telah dinonaktifkan." |

# LAMPIRAN B — Cheat Sheet Angka Penting

| Fakta | Nilai |
|-------|-------|
| Framework | Laravel 10, PHP 8.2 |
| Test | **246 passed, 704 assertions** |
| Code style | Pint clean (PSR-12) |
| Routes | ±120 routes terdaftar |
| Model | 13 model Eloquent |
| Database | 15 tabel + 3 performance indexes |
| Policy | 9 policy |
| FormRequest | 14 class validasi |
| Service | 2 (AuditLogService, NotificationService) |
| Notifikasi | 12 tipe (booking/payment/billing/checkin/checkout/kontrak) |
| Scheduled commands | 4 (overdue, remind, expire booking, expire kontrak) |
| Role | 4 (super_admin, owner, admin, tenant) |
| Export | 2 format (PDF via DomPDF, CSV via native PHP) |
| Landing page | Hero + search + property grid + features + stats |
| CI | GitHub Actions — push/PR ke develop, hijau di run pertama |
| Dark mode | localStorage persistence + system preference detection |
| Commit terakhir | `chore: add CI pipeline and finalize project documentation` |
