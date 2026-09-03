<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Kontrak {{ $kontrak->contract_number }}" description="Detail kontrak sewa &amp; tagihan terkait Anda.">
            <x-button href="{{ route('tenant.kontrak.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Kontrak', 'url' => route('tenant.kontrak.index')],
            ['label' => $kontrak->contract_number],
        ]" />
        <x-alert />

        {{-- Ringkasan status --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nomor Kontrak</p>
                <p class="mt-1 text-xl font-bold font-mono text-slate-900 dark:text-white">{{ $kontrak->contract_number }}</p>
            </div>
            <x-status-badge :status="$kontrak->status" context="kontrak" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Informasi kontrak --}}
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <i class="ri-file-text-line text-primary-500"></i> Informasi Kontrak
                    </h3>
                    <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div class="flex items-start gap-2.5">
                            <i class="ri-home-4-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kos</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kontrak->kos->name }}</dd>
                                @if($kontrak->kos->address)
                                    <dd class="text-xs text-slate-400 dark:text-slate-500">{{ $kontrak->kos->address }}</dd>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kamar</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kontrak->kamar->room_number }} · {{ $kontrak->kamar->room_name }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-play-circle-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kontrak->start_date->translatedFormat('d F Y') }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-flag-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Berakhir</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kontrak->end_date->translatedFormat('d F Y') }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="ri-repeat-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe Sewa</dt>
                                <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ \StatusLabels::rentalTypeLabel($kontrak->rental_type) }}</dd>
                            </div>
                        </div>

                        @if($kontrak->notes)
                            <div class="sm:col-span-2 flex items-start gap-2.5">
                                <i class="ri-quill-pen-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Catatan</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $kontrak->notes }}</dd>
                                </div>
                            </div>
                        @endif
                    </dl>

                    {{-- Total nilai kontrak --}}
                    <div class="mx-6 mb-6 flex items-center justify-between rounded-xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/50 dark:bg-primary-500/[0.06] px-4 py-3.5">
                        <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400"><i class="ri-money-dollar-circle-line"></i> Harga Sewa / Periode</span>
                        <span class="font-bold text-primary-700 dark:text-primary-300 whitespace-nowrap">Rp {{ number_format($kontrak->rental_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Tagihan terkait --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden h-full">
                    <h3 class="flex items-center justify-between text-sm font-bold text-slate-900 dark:text-white px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <span class="flex items-center gap-2"><i class="ri-bill-line text-primary-500"></i> Tagihan Terkait</span>
                        @if($kontrak->tagihans->count())
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">{{ $kontrak->tagihans->count() }} tagihan</span>
                        @endif
                    </h3>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-[26rem] overflow-y-auto scrollbar-thin">
                        @forelse($kontrak->tagihans as $t)
                            <a href="{{ route('tenant.tagihan.show', $t) }}" class="flex items-center justify-between gap-4 px-6 py-3.5 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors" aria-label="Lihat tagihan {{ $t->bill_number }}">
                                <div class="min-w-0">
                                    <p class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300 truncate">{{ $t->bill_number }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $t->period_start->format('d M') }} — {{ $t->period_end->format('d M Y') }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ \PaymentLabels::tagihanBadge($t->status) }}">{{ \PaymentLabels::tagihanLabel($t->status) }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="py-8 text-center">
                                <span class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                    <i class="ri-bill-line text-lg text-slate-300 dark:text-slate-600"></i>
                                </span>
                                <p class="mt-2.5 text-sm text-slate-400 dark:text-slate-500">Belum ada tagihan untuk kontrak ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>