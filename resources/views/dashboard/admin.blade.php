<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-primary-600 via-primary-700 to-indigo-800 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white dark:bg-slate-900 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-white dark:bg-slate-900 rounded-full translate-y-1/2 -translate-x-1/4"></div>
            </div>
            <div class="relative">
                <h1 class="text-2xl sm:text-3xl font-bold">Dashboard Admin</h1>
                <p class="mt-2 text-primary-100/80 text-sm sm:text-base">Selamat datang, {{ $user->name }}! Kelola operasional kos hari ini.</p>
            </div>
        </div>

        {{-- Primary Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Booking Perlu Check-in</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['pending_bookings'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-100 dark:group-hover:bg-orange-500/20 transition">
                        <i class="ri-login-box-line text-2xl text-orange-600 dark:text-orange-400"></i>
                    </div>
                </div>
                @if($stats['pending_bookings'] > 0)
                    <a href="{{ route('admin.checkin.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-orange-600 hover:text-orange-700 transition">
                        Proses check-in <i class="ri-arrow-right-s-line"></i>
                    </a>
                @endif
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pembayaran Pending</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['pending_payments'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition">
                        <i class="ri-money-dollar-circle-line text-2xl text-amber-600 dark:text-amber-400"></i>
                    </div>
                </div>
                @if($stats['pending_payments'] > 0)
                    <a href="{{ route('admin.pembayaran.index') }}?status=pending" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                        Verifikasi <i class="ri-arrow-right-s-line"></i>
                    </a>
                @endif
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
                <a href="{{ route('admin.penghuni.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 transition">
                    Lihat daftar <i class="ri-arrow-right-s-line"></i>
                </a>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tagihan Overdue</p>
                        <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['tagihan_overdue'] }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center group-hover:bg-red-100 dark:group-hover:bg-red-500/20 transition">
                        <i class="ri-error-warning-line text-2xl text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
                @if($stats['tagihan_overdue'] > 0)
                    <a href="{{ route('admin.tagihan.index') }}?status=overdue" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700 transition">
                        Lihat detail <i class="ri-arrow-right-s-line"></i>
                    </a>
                @endif
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                        <i class="ri-login-box-line text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Check-in Hari Ini</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['checkin_today'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center">
                        <i class="ri-logout-box-line text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Check-out Hari Ini</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['checkout_today'] }}</p>
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
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['kamar_available'] }}</p>
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
                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $stats['kamar_occupied'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        @if($stats['pending_bookings'] > 0 || $stats['pending_payments'] > 0 || $stats['tagihan_overdue'] > 0)
            <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center shrink-0">
                        <i class="ri-alarm-warning-line text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-amber-800">Butuh Perhatian</h3>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @if($stats['pending_bookings'] > 0)
                                <a href="{{ route('admin.checkin.index') }}" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-slate-900 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-500/30 rounded-lg text-xs font-semibold hover:bg-orange-50 dark:hover:bg-orange-500/10 transition">
                                    <i class="ri-login-box-line mr-1"></i> {{ $stats['pending_bookings'] }} Booking Perlu Check-in
                                </a>
                            @endif
                            @if($stats['pending_payments'] > 0)
                                <a href="{{ route('admin.pembayaran.index') }}?status=pending" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-slate-900 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 rounded-lg text-xs font-semibold hover:bg-amber-50 dark:hover:bg-amber-500/10 transition">
                                    <i class="ri-money-dollar-circle-line mr-1"></i> {{ $stats['pending_payments'] }} Pembayaran Pending
                                </a>
                            @endif
                            @if($stats['tagihan_overdue'] > 0)
                                <a href="{{ route('admin.tagihan.index') }}?status=overdue" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-slate-900 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/30 rounded-lg text-xs font-semibold hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                    <i class="ri-error-warning-line mr-1"></i> {{ $stats['tagihan_overdue'] }} Tagihan Overdue
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Workflow Guide --}}
        <x-workflow-guide title="Panduan Alur Kerja Admin" :steps="[
            ['title' => 'Pantau Kamar', 'desc' => 'Cek status kamar: tersedia, terisi, atau maintenance'],
            ['title' => 'Proses Check-in', 'desc' => 'Aktifkan penghuni dari booking yang sudah terkonfirmasi'],
            ['title' => 'Proses Check-out', 'desc' => 'Kelola kepergian penghuni dan kembalikan status kamar'],
            ['title' => 'Buat Tagihan', 'desc' => 'Kirim tagihan sewa bulanan ke penghuni aktif'],
            ['title' => 'Verifikasi Pembayaran', 'desc' => 'Konfirmasi bukti bayar yang diupload penghuni'],
        ]"/>

        {{-- Room Status Bar --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-white">Status Kamar</h3>
                <a href="{{ route('admin.kamar.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lihat Semua</a>
            </div>
            @php
                $total = ($stats['kamar_available'] + $stats['kamar_occupied']) ?: 1;
                $availPct = round(($stats['kamar_available'] / $total) * 100);
                $occPct = round(($stats['kamar_occupied'] / $total) * 100);
            @endphp
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden flex">
                <div class="bg-emerald-500 h-full transition-all" style="width: {{ $availPct }}%"></div>
                <div class="bg-blue-500 h-full transition-all" style="width: {{ $occPct }}%"></div>
            </div>
            <div class="flex flex-wrap gap-4 mt-3">
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-600 dark:text-slate-300">Tersedia ({{ $stats['kamar_available'] }})</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span class="text-slate-600 dark:text-slate-300">Terisi ({{ $stats['kamar_occupied'] }})</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
