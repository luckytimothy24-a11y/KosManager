<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Revenue Advertising" description="Ringkasan pendapatan dari pemasangan iklan." icon="ri-line-chart-line">
            <form method="GET" action="{{ route('super-admin.advertising.revenue.export') }}" class="inline-flex items-center gap-2">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
                <input type="hidden" name="package_id" value="{{ request('package_id') }}">
                <input type="hidden" name="campaign_status" value="{{ request('campaign_status') }}">
                <input type="hidden" name="status" value="{{ request('status', 'paid') }}">
                <button type="submit" class="btn-primary">
                    <i class="ri-download-2-line"></i> Export CSV
                </button>
            </form>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Total Revenue --}}
        <div class="metric-card">
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Total Revenue (Order Paid)</p>
            <p class="text-metric mt-1 text-slate-900 dark:text-slate-50">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">{{ count($orders) }} order ditampilkan</p>
        </div>

        {{-- Rekonsiliasi --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="metric-card">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1"><i class="ri-timer-line text-amber-500"></i> Belum Diterima (Pending)</p>
                <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">Rp {{ number_format($pendingRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="metric-card">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1"><i class="ri-refund-2-line text-slate-400"></i> Dikembalikan (Refunded)</p>
                <p class="text-xl font-bold text-slate-500 dark:text-slate-300 mt-1">Rp {{ number_format($refundedRevenue, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('super-admin.advertising.revenue') }}" class="filter-card">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="field-input" aria-label="Tanggal mulai">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="field-input" aria-label="Tanggal selesai">
                <select name="owner_id" class="field-input" aria-label="Filter owner">
                    <option value="">Semua Owner</option>
                    @foreach($owners as $o)
                        <option value="{{ $o->id }}" {{ request('owner_id') == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                    @endforeach
                </select>
                <select name="package_id" class="field-input" aria-label="Filter paket">
                    <option value="">Semua Paket</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="field-input" aria-label="Filter status order">
                    @foreach(['paid' => 'Dibayar', 'pending' => 'Belum Diterima', 'refunded' => 'Dikembalikan'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select name="campaign_status" class="field-input" aria-label="Filter status kampanye">
                    <option value="">Semua Status Kampanye</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ request('campaign_status') === $s ? 'selected' : '' }}>{{ \App\Support\AdvertisingLabels::campaignLabel($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                @if(request()->anyFilled(['start_date', 'end_date', 'owner_id', 'package_id', 'campaign_status']))
                    <a href="{{ route('super-admin.advertising.revenue') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        {{-- Orders table --}}
        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Tanggal</th>
                            <th>Campaign</th>
                            <th>Owner</th>
                            <th>Kos</th>
                            <th>Paket</th>
                            <th>Nominal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400">{{ $order->order_number }}</td>
                                <td class="text-slate-600 dark:text-slate-300">{{ $order->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="text-slate-600 dark:text-slate-300">{{ $order->campaign?->campaign_number ?? '-' }}</td>
                                <td class="font-medium text-slate-800 dark:text-slate-200">{{ $order->campaign?->owner?->name ?? '-' }}</td>
                                <td class="text-slate-600 dark:text-slate-300">{{ $order->campaign?->kos?->name ?? '-' }}</td>
                                <td class="text-slate-600 dark:text-slate-300">{{ $order->campaign?->package?->name ?? '-' }}</td>
                                <td class="font-bold text-slate-900 dark:text-slate-50">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ \App\Support\AdvertisingLabels::orderBadge($order->status) }}">
                                        {{ \App\Support\AdvertisingLabels::orderLabel($order->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state icon="ri-line-chart-line" title="Tidak ada order berbayar" description="Ubah filter atau rentang tanggal untuk melihat data lain." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 dark:border-white/[0.06] flex justify-center">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
