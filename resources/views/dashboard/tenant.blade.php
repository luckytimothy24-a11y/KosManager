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
                <h1 class="text-2xl sm:text-3xl font-bold">Halo, {{ $user->name }}!</h1>
                <p class="mt-2 text-primary-100/80 text-sm sm:text-base">Selamat datang di portal penghuni KosManager.</p>
            </div>
        </div>

        {{-- Workflow Guide --}}
        <x-workflow-guide title="Panduan Untuk Anda" :steps="[
            ['title' => 'Booking Kamar', 'desc' => 'Pilih kamar yang tersedia lalu ajukan booking'],
            ['title' => 'Tunggu Persetujuan', 'desc' => 'Pemilik kos akan menyetujui booking Anda'],
            ['title' => 'Check-in', 'desc' => 'Datang ke kos — status kamar menjadi aktif'],
            ['title' => 'Bayar Tagihan', 'desc' => 'Upload bukti bayar saat tagihan bulanan terbit'],
        ]"/>

        @if(!$penghuni)
            {{-- No Active Room --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center mx-auto">
                    <i class="ri-home-heart-line text-3xl text-primary-400"></i>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Belum Memiliki Kamar</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">Anda belum memiliki kamar aktif. Silakan booking kamar untuk memulai.</p>
                <a href="{{ route('tenant.kos.index') }}" class="mt-6 inline-flex items-center gap-2 bg-primary-500 text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-primary-600 transition shadow-lg shadow-primary-500/25">
                    <i class="ri-search-eye-line"></i> Cari Kos Sekarang
                </a>
            </div>
        @else
            {{-- Primary Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Kamar Saya</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['kamar_number'] ?? '-' }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition">
                            <i class="ri-door-open-line text-2xl text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">{{ $stats['kos_name'] ?? '-' }}</p>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Status Kontrak</p>
                            <div class="mt-1 flex items-center gap-2">
                                @php
                                    $kStatus = $stats['kontrak_status'] ?? '-';
                                    $kColor = match($kStatus) {
                                        'active' => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300',
                                        'expired' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                        'completed' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300',
                                        default => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold {{ $kColor }}">
                                    {{ \StatusLabels::kontrakLabel($kStatus) }}
                                </span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/20 transition">
                            <i class="ri-file-text-line text-2xl text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                    </div>
                    @if(!empty($stats['kontrak_end']))
                        <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">Berakhir {{ $stats['kontrak_end']->translatedFormat('d M Y') }}</p>
                    @endif
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Jatuh Tempo Terdekat</p>
                            <p class="text-lg font-bold mt-1 {{ ($stats['tagihan_pending'] ?? 0) > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">
                                @if(!empty($stats['nearest_due']))
                                    {{ $stats['nearest_due']->translatedFormat('d M Y') }}
                                @else
                                    Tidak ada
                                @endif
                            </p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-100 dark:group-hover:bg-orange-500/20 transition">
                            <i class="ri-calendar-deadline-line text-2xl text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tagihan Belum Bayar</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['tagihan_pending'] ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition">
                            <i class="ri-file-list-3-line text-2xl text-amber-600 dark:text-amber-400"></i>
                        </div>
                    </div>
                    @if(($stats['total_belum_dibayar'] ?? 0) > 0)
                        <p class="mt-1 text-xs font-semibold text-slate-600 dark:text-slate-300">Total Rp {{ number_format((float) $stats['total_belum_dibayar'], 0, ',', '.') }}</p>
                    @endif
                    @if(($stats['tagihan_pending'] ?? 0) > 0)
                        <a href="{{ route('tenant.tagihan.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-amber-600 hover:text-amber-700 transition">
                            Bayar sekarang <i class="ri-arrow-right-s-line"></i>
                        </a>
                    @endif
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Menunggu Verifikasi</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['tagihan_pending_verification'] ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-yellow-50 dark:bg-yellow-500/10 flex items-center justify-center group-hover:bg-yellow-100 dark:group-hover:bg-yellow-500/20 transition">
                            <i class="ri-time-line text-2xl text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                    </div>
                    @if(($stats['tagihan_pending_verification'] ?? 0) > 0)
                        <a href="{{ route('tenant.tagihan.index') }}?status=pending_verification" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-yellow-600 hover:text-yellow-700 transition">
                            Lihat detail <i class="ri-arrow-right-s-line"></i>
                        </a>
                    @else
                        <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">Tidak ada bukti yang sedang diperiksa</p>
                    @endif
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Tagihan Overdue</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['tagihan_overdue'] ?? 0 }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl {{ ($stats['tagihan_overdue'] ?? 0) > 0 ? 'bg-red-100 dark:bg-red-500/10' : 'bg-slate-100 dark:bg-slate-800' }} flex items-center justify-center group-hover:scale-105 transition">
                            <i class="ri-error-warning-line text-2xl {{ ($stats['tagihan_overdue'] ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        </div>
                    </div>
                    @if(($stats['tagihan_overdue'] ?? 0) > 0)
                        <a href="{{ route('tenant.tagihan.index') }}?status=overdue" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700 transition">
                            Lihat detail <i class="ri-arrow-right-s-line"></i>
                        </a>
                    @endif
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pembayaran Terakhir</p>
                            @if(!empty($stats['last_payment_amount']))
                                <p class="text-lg font-bold text-slate-900 dark:text-white mt-1">Rp {{ number_format((float) $stats['last_payment_amount'], 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $stats['last_payment_date']->translatedFormat('d M Y') }}</p>
                            @else
                                <p class="text-lg font-bold text-slate-400 dark:text-slate-500 mt-1">Belum ada</p>
                            @endif
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-100 dark:group-hover:bg-emerald-500/20 transition">
                            <i class="ri-wallet-3-line text-2xl text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('tenant.booking.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition">
                        <i class="ri-calendar-check-line text-2xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">Booking Saya</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Lihat riwayat booking</p>
                    </div>
                    <i class="ri-arrow-right-s-line text-slate-300 ml-auto group-hover:text-blue-500 transition"></i>
                </a>
                <a href="{{ route('tenant.tagihan.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition">
                        <i class="ri-file-list-3-line text-2xl text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">Tagihan Saya</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Cek & bayar tagihan</p>
                    </div>
                    <i class="ri-arrow-right-s-line text-slate-300 ml-auto group-hover:text-blue-500 transition"></i>
                </a>
                <a href="{{ route('tenant.pembayaran.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md transition-shadow group flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center group-hover:bg-green-100 dark:group-hover:bg-green-500/20 transition">
                        <i class="ri-money-dollar-circle-line text-2xl text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">Pembayaran Saya</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Riwayat pembayaran</p>
                    </div>
                    <i class="ri-arrow-right-s-line text-slate-300 ml-auto group-hover:text-blue-500 transition"></i>
                </a>
                @if($pendingCheckout)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-amber-200 dark:border-amber-500/20 p-5 flex items-center gap-4" title="Pengajuan check-out Anda sedang menunggu persetujuan">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center">
                            <i class="ri-time-line text-2xl text-amber-600 dark:text-amber-400"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Check-Out Diproses</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pengajuan menunggu persetujuan</p>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('tenant.checkout.request', $penghuni) }}"
                          onsubmit="return confirm('Ajukan check-out dari kamar {{ $stats['kamar_number'] ?? '' }}?')" class="contents">
                        @csrf
                        <button type="submit" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 hover:shadow-md hover:border-red-200 transition group flex items-center gap-4 text-left w-full cursor-pointer">
                            <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-500/10 flex items-center justify-center group-hover:bg-red-100 dark:group-hover:bg-red-500/20 transition">
                                <i class="ri-logout-box-r-line text-2xl text-red-600 dark:text-red-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Ajukan Check-Out</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Keluar dari kamar saat ini</p>
                            </div>
                            <i class="ri-arrow-right-s-line text-slate-300 ml-auto group-hover:text-red-500 transition"></i>
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
