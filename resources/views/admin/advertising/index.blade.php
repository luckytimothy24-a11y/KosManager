<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Moderasi Advertising</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review kampanye iklan untuk kos yang Anda kelola.</p>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('admin.advertising.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ \App\Support\AdvertisingLabels::campaignLabel($s) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-xl transition">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                @if(request('status'))
                    <a href="{{ route('admin.advertising.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">Reset</a>
                @endif
            </div>
        </form>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/40 text-left">
                        <tr>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kampanye</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Owner</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kos</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Paket</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Periode</th>
                            <th class="px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($campaigns as $c)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3 font-mono text-xs font-bold text-slate-500 dark:text-slate-400">{{ $c->campaign_number }}</td>
                                <td class="px-5 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $c->owner->name }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $c->kos?->name ?? $c->advertiser_name }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $c->package->name }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold {{ \App\Support\AdvertisingLabels::campaignBadge($c->status) }}">
                                        {{ \App\Support\AdvertisingLabels::campaignLabel($c->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">{{ $c->starts_at?->format('d/m/Y') }} - {{ $c->ends_at?->format('d/m/Y') }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.advertising.show', $c) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center">
                                    <i class="ri-bill-line text-3xl text-slate-300 dark:text-slate-600"></i>
                                    <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">Tidak ada kampanye untuk kos yang Anda kelola.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800 flex justify-center">{{ $campaigns->links() }}</div>
        </div>
    </div>
</x-app-layout>
