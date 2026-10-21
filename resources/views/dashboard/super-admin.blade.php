<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        {{-- Welcome Banner — distinctive system admin identity --}}
        <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 dark:from-primary-700 dark:via-primary-800 dark:to-primary-900 px-6 py-6 sm:px-8 sm:py-7 text-white">
            <div class="absolute inset-0 opacity-[0.04]" aria-hidden="true">
                <div class="hero-pattern absolute inset-0"></div>
            </div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 badge-pulse"></span> System Control
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Super Admin</h1>
                    <p class="mt-1.5 text-sm text-white/60">Selamat datang, {{ $user->name }}. Pantau seluruh sistem KosManager.</p>
                </div>
            </div>
        </header>

        {{-- Primary Stat Cards — unique gradient treatment per metric --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="metric-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Pengguna</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 mt-1 tracking-tight">{{ number_format($stats['total_users']) }}</p>
                    </div>
<div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-500/20 transition-colors duration-300">
                            <i class="ri-team-line text-2xl text-primary-600 dark:text-primary-400"></i>
                        </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-[11px]">
                    <span class="text-slate-400 dark:text-slate-500">Owner: {{ $stats['total_owners'] }} · Tenant: {{ $stats['total_tenants'] }}</span>
                </div>
            </div>

            <div class="metric-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Properti</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 mt-1 tracking-tight">{{ number_format($stats['total_kos']) }}</p>
                    </div>
<div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-500/20 transition-colors duration-300">
                            <i class="ri-building-2-line text-2xl text-primary-600 dark:text-primary-400"></i>
                        </div>
                </div>
                <a href="{{ route('owner.kos.index') }}" class="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-primary-500 hover:text-primary-600 transition-colors duration-200">
                    Kelola kos <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>

            <div class="metric-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Kamar</p>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-slate-50 mt-1 tracking-tight">{{ number_format($stats['total_kamar']) }}</p>
                    </div>
<div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-500/20 transition-colors duration-300">
                            <i class="ri-door-open-line text-2xl text-primary-600 dark:text-primary-400"></i>
                        </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-[11px]">
                    <span class="text-emerald-600 font-semibold">{{ $stats['total_kamar_available'] }} tersedia</span>
                    <span class="text-slate-300">·</span>
                    <span class="text-blue-600 font-semibold">{{ $stats['total_kamar_occupied'] }} terisi</span>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 p-5 text-white shadow-lg shadow-primary-500/20">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-white/70">Pendapatan Masuk</p>
                        <p class="text-xl font-extrabold mt-1 tracking-tight">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ri-money-dollar-circle-line text-2xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-[11px] text-white/50">Total pembayaran terverifikasi</p>
            </div>
        </div>

        {{-- Secondary Stats — operational view --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="metric-card">
                <div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-success-50 dark:bg-success-500/10 flex items-center justify-center shrink-0">
                            <i class="ri-user-star-line text-success-600 dark:text-success-400"></i>
                        </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Penghuni Aktif</p>
                        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-50">{{ number_format($stats['total_penghunis']) }}</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-warn-50 dark:bg-warn-500/10 flex items-center justify-center shrink-0">
                            <i class="ri-login-box-line text-warn-600 dark:text-warn-400"></i>
                        </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Perlu Check-in</p>
                        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-50">{{ number_format($stats['total_active_bookings']) }}</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-success-50 dark:bg-success-500/10 flex items-center justify-center shrink-0">
                            <i class="ri-checkbox-circle-line text-success-600 dark:text-success-400"></i>
                        </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Kamar Tersedia</p>
                        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-50">{{ number_format($stats['total_kamar_available']) }}</p>
                    </div>
                </div>
            </div>
            <div class="metric-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                        <i class="ri-lock-line text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Kamar Terisi</p>
                        <p class="text-lg font-extrabold text-slate-900 dark:text-slate-50">{{ number_format($stats['total_kamar_occupied']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Workflow Guide --}}
        <x-workflow-guide title="Panduan Alur Kerja Super Admin" :steps="[
            ['title' => 'Kelola User', 'desc' => 'Tambah akun owner, admin, dan tenant'],
            ['title' => 'Kelola Fasilitas', 'desc' => 'Master data fasilitas untuk semua kos'],
            ['title' => 'Pantau Laporan', 'desc' => 'Rekap pendapatan & okupansi, export PDF/Excel'],
            ['title' => 'Cek Activity Log', 'desc' => 'Riwayat aktivitas seluruh sistem'],
        ]"/>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-bar-chart title="Grafik Pendapatan 6 Bulan Terakhir" :labels="$revenueChart['labels']" :values="$revenueChart['values']" prefix="Rp " />
            <x-bar-chart title="Grafik Booking 6 Bulan Terakhir" :labels="$bookingChart['labels']" :values="$bookingChart['values']" suffix=" booking" />
        </div>

        {{-- Quick Links — editorial style, not all cards --}}
        <div class="section-tint rounded-2xl p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 mb-4">Akses Cepat</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('super-admin.users.index') }}" class="metric-card group flex items-center gap-4">
<div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0 group-hover:bg-primary-100 dark:group-hover:bg-primary-500/20 transition-colors duration-300">
                            <i class="ri-team-line text-2xl text-primary-600 dark:text-primary-400"></i>
                        </div>
                    <div class="min-w-0">
                        <p class="font-bold text-sm text-slate-900 dark:text-slate-50">Manajemen User</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Kelola akun pengguna</p>
                    </div>
                    <i class="ri-arrow-right-s-line text-slate-300 dark:text-slate-600 ml-auto group-hover:text-primary-500 transition-colors duration-300"></i>
                </a>
                <a href="{{ route('super-admin.laporan.index') }}" class="metric-card group flex items-center gap-4">
<div class="w-12 h-12 rounded-xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0 group-hover:bg-primary-100 dark:group-hover:bg-primary-500/20 transition-colors duration-300">
                            <i class="ri-bar-chart-grouped-line text-2xl text-primary-600 dark:text-primary-400"></i>
                        </div>
                    <div class="min-w-0">
                        <p class="font-bold text-sm text-slate-900 dark:text-slate-50">Laporan</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Export PDF & Excel</p>
                    </div>
                    <i class="ri-arrow-right-s-line text-slate-300 dark:text-slate-600 ml-auto group-hover:text-primary-500 transition-colors duration-300"></i>
                </a>
                <a href="{{ route('super-admin.audit-log.index') }}" class="metric-card group flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-surface-elevated flex items-center justify-center shrink-0 group-hover:bg-slate-200 dark:group-hover:bg-slate-700 transition-colors duration-300">
                        <i class="ri-history-line text-2xl text-slate-600 dark:text-slate-300"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-sm text-slate-900 dark:text-slate-50">Activity Log</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Riwayat aktivitas sistem</p>
                    </div>
                    <i class="ri-arrow-right-s-line text-slate-300 dark:text-slate-600 ml-auto group-hover:text-primary-500 transition-colors duration-300"></i>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
