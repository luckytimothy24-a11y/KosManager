<x-app-layout>
    <div class="space-y-6">

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-primary-600 via-primary-700 to-primary-900 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white dark:bg-slate-900 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white dark:bg-slate-900 rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative">
                <h1 class="text-2xl sm:text-3xl font-bold">Selamat Datang, {{ $user->name }}!</h1>
                <p class="mt-2 text-blue-100/80 text-sm sm:text-base">Kelola bisnis kos Anda dari satu tempat.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('owner.kos.create') }}" class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-white/25 transition">
                        <i class="ri-add-line"></i> Tambah Kos
                    </a>
                    <a href="{{ route('owner.kamar.create') }}" class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-white/25 transition">
                        <i class="ri-add-circle-line"></i> Tambah Kamar
                    </a>
                </div>
            </div>
        </div>

        {{-- Primary Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kos</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['total_kos'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition">
                        <i class="ri-building-2-line text-2xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
                <a href="{{ route('owner.kos.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition">
                    Lihat semua <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Kamar</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['total_kamar'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/20 transition">
                        <i class="ri-door-open-line text-2xl text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                </div>
                <a href="{{ route('owner.kamar.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition">
                    Lihat semua <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Penghuni Aktif</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['total_penghunis'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center group-hover:bg-green-100 dark:group-hover:bg-green-500/20 transition">
                        <i class="ri-user-star-line text-2xl text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <a href="{{ route('owner.penghuni.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition">
                    Lihat semua <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>

            <div class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl p-5 text-white hover:shadow-lg hover:shadow-primary-500/25 transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-primary-100">Pendapatan Masuk</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
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
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                        <i class="ri-checkbox-circle-line text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tersedia</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['total_kamar_available'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <i class="ri-lock-line text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Terisi</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['total_kamar_occupied'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center">
                        <i class="ri-tools-line text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Maintenance</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['total_kamar_maintenance'] }}</p>
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
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['total_pending_bookings'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        @if($stats['total_pending_bookings'] > 0 || ($stats['pending_payments'] ?? 0) > 0)
            <div class="bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/30 rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center shrink-0">
                        <i class="ri-alert-line text-orange-600 dark:text-orange-400"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-orange-800">Perhatian Diperlukan</h3>
                        @if($stats['total_pending_bookings'] > 0)
                            <p class="text-sm text-orange-700 mt-1">Anda memiliki <strong>{{ $stats['total_pending_bookings'] }}</strong> booking yang menunggu persetujuan.</p>
                            <a href="{{ route('owner.booking.index') }}?status=pending" class="mt-3 inline-flex items-center gap-1.5 bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-xl hover:bg-orange-700 transition">
                                <i class="ri-calendar-check-line"></i> Review Booking
                            </a>
                        @endif
                        @if(($stats['pending_payments'] ?? 0) > 0)
                            <p class="text-sm text-orange-700 {{ $stats['total_pending_bookings'] > 0 ? 'mt-2' : 'mt-1' }}"><strong>{{ $stats['pending_payments'] }}</strong> bukti pembayaran menunggu verifikasi.</p>
                            <a href="{{ route('owner.pembayaran.index') }}?status=pending" class="mt-3 inline-flex items-center gap-1.5 bg-amber-600 text-white text-xs font-semibold px-4 py-2 rounded-xl hover:bg-amber-700 transition">
                                <i class="ri-bank-card-line"></i> Verifikasi Pembayaran
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Workflow Guide --}}
        <x-workflow-guide title="Panduan Alur Kerja Owner" :steps="[
            ['title' => 'Kelola Kos & Kamar', 'desc' => 'Daftarkan properti dan kamar yang tersedia'],
            ['title' => 'Setujui Booking', 'desc' => 'Tinjau permintaan sewa yang masuk dari calon penghuni'],
            ['title' => 'Proses Check-in', 'desc' => 'Aktifkan penghuni — kontrak sewa dibuat otomatis'],
            ['title' => 'Buat Tagihan', 'desc' => 'Kirim tagihan sewa bulanan ke penghuni aktif'],
            ['title' => 'Verifikasi Pembayaran', 'desc' => 'Konfirmasi bukti bayar agar tercatat sebagai pendapatan'],
        ]"/>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2">
                <x-bar-chart title="Grafik Pendapatan 6 Bulan Terakhir" :labels="$revenueChart['labels']" :values="$revenueChart['values']" prefix="Rp " />
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 flex flex-col">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
                        <i class="ri-pie-chart-line text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Okupansi Kamar</h3>
                </div>
                <div class="flex-1 flex flex-col items-center justify-center py-2">
                    <p class="text-4xl font-bold text-slate-900 dark:text-white">{{ $stats['occupancy_percent'] }}%</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $stats['total_kamar_occupied'] }} dari {{ $stats['total_kamar'] }} kamar terisi</p>
                    <div class="w-full mt-4 h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-indigo-500 transition-all" style="width: {{ $stats['occupancy_percent'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Room Status Overview --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-white">Status Kamar</h3>
                <a href="{{ route('owner.kamar.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lihat Semua</a>
            </div>
            @php
                $total = $stats['total_kamar'] ?: 1;
                $availPct = round(($stats['total_kamar_available'] / $total) * 100);
                $occPct = round(($stats['total_kamar_occupied'] / $total) * 100);
                $maintPct = round(($stats['total_kamar_maintenance'] / $total) * 100);
            @endphp
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden flex">
                <div class="bg-emerald-500 h-full transition-all" style="width: {{ $availPct }}%"></div>
                <div class="bg-blue-500 h-full transition-all" style="width: {{ $occPct }}%"></div>
                <div class="bg-amber-500 h-full transition-all" style="width: {{ $maintPct }}%"></div>
            </div>
            <div class="flex flex-wrap gap-4 mt-3">
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-600 dark:text-slate-300">Tersedia ({{ $stats['total_kamar_available'] }})</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span class="text-slate-600 dark:text-slate-300">Terisi ({{ $stats['total_kamar_occupied'] }})</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-slate-600 dark:text-slate-300">Maintenance ({{ $stats['total_kamar_maintenance'] }})</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
