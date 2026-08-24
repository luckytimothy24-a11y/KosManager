<x-app-layout>
    <div class="space-y-6">

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-primary-600 via-primary-700 to-indigo-800 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white dark:bg-slate-900 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white dark:bg-slate-900 rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative">
                <h1 class="text-2xl sm:text-3xl font-bold">Super Admin Panel</h1>
                <p class="mt-2 text-primary-100/80 text-sm sm:text-base">Selamat datang, {{ $user->name }}! Pantau seluruh sistem KosManager.</p>
            </div>
        </div>

        {{-- Primary Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Pengguna</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stats['total_users']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-100 dark:group-hover:bg-purple-500/20 transition">
                        <i class="ri-team-line text-2xl text-purple-600 dark:text-purple-400"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-slate-400 dark:text-slate-500">Owner: {{ $stats['total_owners'] }} | Tenant: {{ $stats['total_tenants'] }}</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kos</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stats['total_kos']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/20 transition">
                        <i class="ri-building-2-line text-2xl text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
                <a href="{{ route('owner.kos.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition">
                    Kelola kos <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kamar</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($stats['total_kamar']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition">
                        <i class="ri-door-open-line text-2xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-emerald-600 font-medium">{{ $stats['total_kamar_available'] }} tersedia</span>
                    <span class="text-slate-400 dark:text-slate-500">|</span>
                    <span class="text-blue-600 font-medium">{{ $stats['total_kamar_occupied'] }} terisi</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl p-5 text-white hover:shadow-lg hover:shadow-primary-500/25 transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-primary-100">Pendapatan Masuk</p>
                        <p class="text-xl font-bold mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center group-hover:bg-white/25 transition">
                        <i class="ri-money-dollar-circle-line text-2xl"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-primary-100/70">Total pembayaran terverifikasi</p>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-500/10 flex items-center justify-center">
                        <i class="ri-user-star-line text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Penghuni Aktif</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($stats['total_penghunis']) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center">
                        <i class="ri-calendar-todo-line text-orange-600 dark:text-orange-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Booking Pending</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($stats['total_pending_bookings']) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                        <i class="ri-checkbox-circle-line text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kamar Tersedia</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($stats['total_kamar_available']) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <i class="ri-lock-line text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kamar Terisi</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($stats['total_kamar_occupied']) }}</p>
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

        {{-- Quick Links --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('super-admin.users.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-100 dark:group-hover:bg-purple-500/20 transition">
                    <i class="ri-team-line text-2xl text-purple-600 dark:text-purple-400"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">Manajemen User</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola akun pengguna</p>
                </div>
                <i class="ri-arrow-right-s-line text-slate-300 ml-auto group-hover:text-blue-500 transition"></i>
            </a>
            <a href="{{ route('super-admin.laporan.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition">
                    <i class="ri-bar-chart-grouped-line text-2xl text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">Laporan</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Export PDF & Excel</p>
                </div>
                <i class="ri-arrow-right-s-line text-slate-300 ml-auto group-hover:text-blue-500 transition"></i>
            </a>
            <a href="{{ route('super-admin.audit-log.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover:bg-slate-200 transition">
                    <i class="ri-history-line text-2xl text-slate-600 dark:text-slate-300"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">Activity Log</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Riwayat aktivitas sistem</p>
                </div>
                <i class="ri-arrow-right-s-line text-slate-300 ml-auto group-hover:text-blue-500 transition"></i>
            </a>
        </div>
    </div>
</x-app-layout>
