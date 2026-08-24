<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan" description="Laporan operasional kos." />
    </x-slot>

    @php
        $currentUser = auth()->user();
        $laporanPrefix = $currentUser->isSuperAdmin() ? 'super-admin' : ($currentUser->isAdmin() ? 'admin' : 'owner');
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 mb-6">
        <form id="filterForm" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Kos</label>
                <select name="kos_id" id="kos_id" class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                    <option value="">Semua Kos</option>
                    @foreach($kosList as $k)
                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $reports = [
                ['type' => 'pendapatan', 'title' => 'Laporan Pendapatan', 'desc' => 'Total pendapatan dari seluruh kos', 'color' => 'blue', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['type' => 'penghuni', 'title' => 'Laporan Penghuni', 'desc' => 'Data penghuni aktif per kos', 'color' => 'green', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['type' => 'booking', 'title' => 'Laporan Booking', 'desc' => 'Riwayat booking dan status', 'color' => 'yellow', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['type' => 'kamar', 'title' => 'Laporan Kamar', 'desc' => 'Status kamar per kos', 'color' => 'purple', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                ['type' => 'tagihan', 'title' => 'Laporan Tagihan', 'desc' => 'Status tagihan dan pembayaran', 'color' => 'red', 'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
            ];
            $colorMap = [
                'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'btnBg' => 'bg-blue-600', 'btnHover' => 'hover:bg-blue-700'],
                'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'btnBg' => 'bg-green-600', 'btnHover' => 'hover:bg-green-700'],
                'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'btnBg' => 'bg-yellow-600', 'btnHover' => 'hover:bg-yellow-700'],
                'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'btnBg' => 'bg-purple-600', 'btnHover' => 'hover:bg-purple-700'],
                'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'btnBg' => 'bg-red-600', 'btnHover' => 'hover:bg-red-700'],
            ];
        @endphp

        @foreach($reports as $report)
            @php $c = $colorMap[$report['color']]; @endphp
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-lg {{ $c['bg'] }} flex items-center justify-center">
                        <svg class="w-6 h-6 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $report['icon'] }}"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $report['title'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-slate-400">{{ $report['desc'] }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route($laporanPrefix.'.laporan.export-pdf', $report['type']) }}?{{ http_build_query(request()->only('start_date', 'end_date', 'kos_id')) }}"
                       class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-xs font-medium text-white {{ $c['btnBg'] }} rounded-lg {{ $c['btnHover'] }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        PDF
                    </a>
                    <a href="{{ route($laporanPrefix.'.laporan.export-excel', $report['type']) }}?{{ http_build_query(request()->only('start_date', 'end_date', 'kos_id')) }}"
                       class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
