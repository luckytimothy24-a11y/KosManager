<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Laporan Revenue Advertising</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ringkasan pendapatan dari pemasangan iklan.</p>
            </div>
            <form method="GET" action="{{ route('super-admin.advertising.revenue.export') }}" class="inline-flex items-center gap-2">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="owner_id" value="{{ request('owner_id') }}">
                <input type="hidden" name="package_id" value="{{ request('package_id') }}">
                <input type="hidden" name="campaign_status" value="{{ request('campaign_status') }}">
                <input type="hidden" name="status" value="{{ request('status', 'paid') }}">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-green-600 hover:bg-green-700 px-5 py-2.5 rounded-xl transition shadow-sm">
                    <i class="ri-download-2-line"></i> Export CSV
                </button>
            </form>
        </div>

        {{-- Total Revenue card --}}
        <div class="bg-gradient-to-br from-indigo-500 to-primary-700 rounded-2xl p-6 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-48 h-48 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
            </div>
            <div class="relative">
                <p class="text-sm font-medium text-indigo-100">Total Revenue (Order Paid)</p>
                <p class="text-3xl font-black mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-indigo-100/70">{{ count($orders) }} order ditampilkan</p>
            </div>
        </div>

        {{-- Rekonsiliasi: piutang & pengembalian dipisah dari revenue net --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1"><i class="ri-timer-line text-amber-500"></i> Belum Diterima (Pending)</p>
                <p class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">Rp {{ number_format($pendingRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1"><i class="ri-refund-2-line text-slate-400"></i> Dikembalikan (Refunded)</p>
                <p class="text-xl font-black text-slate-500 dark:text-slate-300 mt-1">Rp {{ number_format($refundedRevenue, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('super-admin.advertising.revenue') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                <select name="owner_id" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                    <option value="">Semua Owner</option>
                    @foreach($owners as $o)
                        <option value="{{ $o->id }}" {{ request('owner_id') == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                    @endforeach
                </select>
                <select name="package_id" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                    <option value="">Semua Paket</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                    @foreach(['paid' => 'Dibayar', 'pending' => 'Belum Diterima', 'refunded' => 'Dikembalikan'] as $val => $lbl)
                        <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select name="campaign_status" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-3 py-2.5">
                    <option value="">Semua Status Kampanye</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ request('campaign_status') === $s ? 'selected' : '' }}>{{ \App\Support\AdvertisingLabels::campaignLabel($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3 flex gap-3">
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-xl transition">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                @if(request()->anyFilled(['start_date', 'end_date', 'owner_id', 'package_id', 'campaign_status']))
                    <a href="{{ route('super-admin.advertising.revenue') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Reset</a>
                @endif
            </div>
        </form>

        {{-- Orders table --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Order</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Campaign</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Owner</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kos</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paket</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nominal</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 font-mono text-xs font-bold text-slate-500 dark:text-slate-400">{{ $order->order_number }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $order->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $order->campaign?->campaign_number ?? '-' }}</td>
                                <td class="px-5 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $order->campaign?->owner?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $order->campaign?->kos?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $order->campaign?->package?->name ?? '-' }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ \App\Support\AdvertisingLabels::orderBadge($order->status) }}">
                                        {{ \App\Support\AdvertisingLabels::orderLabel($order->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center">
                                    <i class="ri-line-chart-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Tidak ada order berbayar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-center">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
