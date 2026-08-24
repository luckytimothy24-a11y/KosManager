<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan" description="Rekap operasional & keuangan, siap diekspor." />
    </x-slot>

    @php
        $currentUser = auth()->user();
        $laporanPrefix = $currentUser->isSuperAdmin() ? 'super-admin' : ($currentUser->isAdmin() ? 'admin' : 'owner');
    @endphp

    <div class="space-y-6">
        {{-- Filter periode --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-5 sm:p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                    <i class="ri-filter-3-line text-primary-600 dark:text-primary-400"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">Filter Periode</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Filter diterapkan pada semua ekspor laporan di bawah.</p>
                </div>
            </div>

            <form method="GET" action="{{ route($laporanPrefix.'.laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label for="kos_id" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Kos</label>
                    <select name="kos_id" id="kos_id"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Semua Kos</option>
                        @foreach($kosList as $k)
                            <option value="{{ $k->id }}" {{ (string) request('kos_id') === (string) $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 sm:justify-end">
                    <button type="submit"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-primary-500 text-white hover:bg-primary-600 active:bg-primary-700 transition shadow-sm shadow-primary-500/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                        <i class="ri-search-line"></i> Terapkan
                    </button>
                    <a href="{{ route($laporanPrefix.'.laporan.index') }}"
                       class="inline-flex items-center justify-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Reset filter">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>

            @if(request()->anyFilled(['start_date', 'end_date', 'kos_id']))
                <p class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-primary-600 dark:text-primary-300 bg-primary-50 dark:bg-primary-500/10 border border-primary-100 dark:border-primary-500/20 px-3 py-1.5 rounded-lg">
                    <i class="ri-information-line"></i> Filter aktif pada hasil ekspor
                </p>
            @endif
        </div>

        {{-- Kartu laporan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @php
                $reports = [
                    ['type' => 'pendapatan', 'title' => 'Laporan Pendapatan', 'desc' => 'Total pendapatan dari seluruh kos', 'icon' => 'ri-money-dollar-circle-line', 'tone' => 'primary'],
                    ['type' => 'penghuni', 'title' => 'Laporan Penghuni', 'desc' => 'Data penghuni aktif per kos', 'icon' => 'ri-user-star-line', 'tone' => 'green'],
                    ['type' => 'booking', 'title' => 'Laporan Booking', 'desc' => 'Riwayat booking dan statusnya', 'icon' => 'ri-calendar-check-line', 'tone' => 'amber'],
                    ['type' => 'kamar', 'title' => 'Laporan Kamar', 'desc' => 'Status kamar per kos', 'icon' => 'ri-door-open-line', 'tone' => 'purple'],
                    ['type' => 'tagihan', 'title' => 'Laporan Tagihan', 'desc' => 'Status tagihan dan pembayaran', 'icon' => 'ri-file-list-3-line', 'tone' => 'blue'],
                ];
                $tones = [
                    'primary' => 'bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400',
                    'green' => 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400',
                    'amber' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400',
                    'purple' => 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400',
                    'blue' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400',
                ];
            @endphp

            @foreach($reports as $report)
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-11 h-11 rounded-xl {{ $tones[$report['tone']] }} flex items-center justify-center shrink-0">
                            <i class="{{ $report['icon'] }} text-xl"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $report['title'] }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $report['desc'] }}</p>
                        </div>
                    </div>
                    <div class="mt-auto grid grid-cols-2 gap-2">
                        <a href="{{ route($laporanPrefix.'.laporan.export-pdf', $report['type']) }}?{{ http_build_query(request()->only('start_date', 'end_date', 'kos_id')) }}"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                            <i class="ri-file-pdf-line text-base"></i> PDF
                        </a>
                        <a href="{{ route($laporanPrefix.'.laporan.export-excel', $report['type']) }}?{{ http_build_query(request()->only('start_date', 'end_date', 'kos_id')) }}"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                            <i class="ri-file-excel-line text-base"></i> Excel
                        </a>
                    </div>
                </div>
            @endforeach

            <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 p-6 flex flex-col items-center justify-center text-center min-h-[12rem]">
                <i class="ri-download-cloud-2-line text-3xl text-slate-300 dark:text-slate-600"></i>
                <p class="mt-3 text-sm font-semibold text-slate-500 dark:text-slate-400">Ekspor mengikuti filter</p>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 max-w-[16rem] leading-relaxed">Atur periode dan kos di panel filter agar file yang diunduh sesuai kebutuhan Anda.</p>
            </div>
        </div>
    </div>
</x-app-layout>
