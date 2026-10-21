<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Saya" description="Ringkasan tagihan dan pembayaran milik Anda." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="metric-card">
                <p class="flex items-center gap-1.5 text-label"><i class="ri-file-list-3-line text-amber-500"></i> Belum Bayar</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $stats['tagihan_unpaid'] + $stats['tagihan_overdue'] }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Rp {{ number_format($stats['total_belum_dibayar'], 0, ',', '.') }}</p>
            </div>
            <div class="metric-card">
                <p class="flex items-center gap-1.5 text-label"><i class="ri-time-line text-yellow-500"></i> Menunggu Verifikasi</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $stats['tagihan_pending'] }}</p>
                <p class="mt-1 text-meta">tagihan dalam proses</p>
            </div>
            <div class="metric-card">
                <p class="flex items-center gap-1.5 text-label"><i class="ri-check-double-line text-emerald-500"></i> Lunas</p>
                <p class="mt-1.5 text-2xl font-bold text-slate-900 dark:text-slate-50">{{ $stats['tagihan_paid'] }}</p>
                <p class="mt-1 text-meta">tagihan terbayar</p>
            </div>
            <div class="metric-card">
                <p class="flex items-center gap-1.5 text-label"><i class="ri-money-dollar-circle-line text-green-500"></i> Total Dibayar</p>
                <p class="mt-1.5 text-lg font-bold leading-tight text-slate-900 dark:text-slate-50">Rp {{ number_format($stats['total_sudah_dibayar'], 0, ',', '.') }}</p>
                <p class="mt-1 text-meta">terverifikasi pengelola</p>
            </div>
        </div>

        <section aria-label="Pembayaran 6 bulan" class="card-surface p-5">
            <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-slate-50">Pembayaran 6 Bulan Terakhir</h2>
            <div class="mt-4 space-y-2.5">
                @php $max = max(1, collect($monthly)->max('total')); @endphp
                @foreach($monthly as $m)
                    <div class="flex items-center gap-3">
                        <span class="w-20 shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $m['label'] }}</span>
                        <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-white/[0.08]">
                            <div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-600" style="width: {{ $max > 0 ? round($m['total'] / $max * 100) : 0 }}%"></div>
                        </div>
                        <span class="w-28 shrink-0 text-right text-xs font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($m['total'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <section aria-label="Tagihan terbaru" class="card-surface p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-slate-50">Tagihan Terbaru</h2>
                    <a href="{{ route('tenant.tagihan.index') }}" class="text-xs font-semibold text-primary-500 hover:text-primary-600">Lihat Semua <i class="ri-arrow-right-s-line"></i></a>
                </div>
                <div class="mt-3 divide-y divide-slate-100 dark:divide-white/[0.06]">
                    @forelse($recentTagihan as $t)
                        <a href="{{ route('tenant.tagihan.show', $t) }}" class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $t->bill_number }}</p>
                                <p class="text-meta">{{ $t->kamar->room_number ?? '-' }} · {{ $t->kamar->kos->name ?? '-' }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($t->total, 0, ',', '.') }}</span>
                        </a>
                    @empty
                        <p class="py-4 text-center text-meta">Belum ada tagihan.</p>
                    @endforelse
                </div>
            </section>

            <section aria-label="Pembayaran terbaru" class="card-surface p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-slate-50">Pembayaran Terbaru</h2>
                    <a href="{{ route('tenant.pembayaran.index') }}" class="text-xs font-semibold text-primary-500 hover:text-primary-600">Lihat Semua <i class="ri-arrow-right-s-line"></i></a>
                </div>
                <div class="mt-3 divide-y divide-slate-100 dark:divide-white/[0.06]">
                    @forelse($recentPembayaran as $p)
                        <a href="{{ route('tenant.pembayaran.show', $p) }}" class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $p->payment_number }}</p>
                                <p class="text-meta">{{ $p->tagihan->bill_number ?? '-' }} · {{ \PaymentLabels::verificationLabel($p->verification_status) }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                        </a>
                    @empty
                        <p class="py-4 text-center text-meta">Belum ada pembayaran.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
