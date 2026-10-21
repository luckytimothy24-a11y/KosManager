<x-app-layout>
    <div class="space-y-6">
        <x-alert />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-slate-50">Kampanye Advertising</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola kampanye iklan Anda — promosi kos atau iklan partner pihak ketiga.</p>
            </div>
            <a href="{{ route('owner.advertising.create') }}" class="inline-flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30 shrink-0">
                <i class="ri-megaphone-line"></i> + Buat Kampanye
            </a>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('owner.advertising.index') }}" class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="text-sm rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-700 dark:text-slate-200 px-4 py-2.5">
                    <option value="">Semua Status</option>
                    @foreach(['draft', 'pending_payment', 'paid', 'pending_review', 'approved', 'active', 'completed', 'rejected', 'suspended', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                            {{ \App\Support\AdvertisingLabels::campaignLabel($s) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-xl transition">
                    <i class="ri-filter-3-line"></i> Filter
                </button>
                @if(request('status'))
                    <a href="{{ route('owner.advertising.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/[0.08] px-4 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Campaign Cards --}}
        @if($campaigns->isEmpty())
            <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-12 text-center">
                <i class="ri-megaphone-line text-4xl text-slate-300 dark:text-slate-600"></i>
                <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-slate-50">Belum ada kampanye</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Mulai buat kampanye — promosi kos Anda atau menjadi advertiser partner di marketplace.</p>
                <a href="{{ route('owner.advertising.create') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 px-5 py-2.5 rounded-xl transition">
                    <i class="ri-add-line"></i> Buat Kampanye
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($campaigns as $c)
                    @php
                        $thumbPath = $c->displayImage() ?: ($c->kos ? (method_exists($c->kos, 'coverPath') ? $c->kos->coverPath() : $c->kos->photo) : null);
                        $thumbInitial = strtoupper(mb_substr($c->displayLabel(), 0, 1));
                    @endphp
                    <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                        <div class="aspect-[16/9] relative overflow-hidden bg-gradient-to-br from-primary-50 via-white to-warn-50/30 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated">
                            @if($thumbPath)
                                <img src="{{ asset('storage/' . $thumbPath) }}" alt="Banner {{ $c->campaign_number }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="hidden absolute inset-0 items-center justify-center bg-gradient-to-br from-primary-50 to-warn-50 dark:from-surface-elevated dark:to-surface-elevated text-2xl font-bold text-primary-600/40 dark:text-primary-300/30">{{ $thumbInitial }}</div>
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-primary-600/40 dark:text-primary-300/30">{{ $thumbInitial }}</div>
                            @endif
                            <span class="absolute top-3 left-3 font-mono text-[10px] font-bold px-2 py-1 rounded-lg bg-white/90 dark:bg-surface-elevated/90 backdrop-blur border border-slate-100 dark:border-white/10 text-slate-600 dark:text-slate-300">{{ $c->campaign_number }}</span>
                            <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ \App\Support\AdvertisingLabels::campaignBadge($c->status) }}">{{ \App\Support\AdvertisingLabels::campaignLabel($c->status) }}</span>
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                        <h3 class="font-bold text-slate-900 dark:text-slate-50">{{ $c->displayLabel() }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            @if($c->isThirdParty())
                                <span class="inline-flex items-center gap-1">
                                    <i class="ri-megaphone-line text-primary-400"></i>
                                    Iklan Partner
                                </span>
                                <span class="mx-1">·</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ \App\Support\AdvertisingLabels::placementBadge($c->placement) }}">
                                    {{ \App\Support\AdvertisingLabels::placementLabel($c->placement) }}
                                </span>
                            @else
                                {{ $c->kos?->address }}
                            @endif
                        </p>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">Paket</p>
                                <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $c->package->name }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">Budget</p>
                                <p class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($c->budget, 0, ',', '.') }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">Periode</p>
                                <p class="font-medium text-slate-700 dark:text-slate-300">{{ $c->starts_at?->format('d/m/Y') }} - {{ $c->ends_at?->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        <div class="mt-auto pt-4">
                            <a href="{{ route('owner.advertising.show', $c) }}" class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold bg-slate-100 dark:bg-surface-elevated hover:bg-primary-50 dark:hover:bg-primary-500/10 text-slate-700 dark:text-slate-200 hover:text-primary-600 px-4 py-2.5 rounded-xl transition">
                                Lihat Detail & Statistik <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-center">{{ $campaigns->links() }}</div>
        @endif
    </div>
</x-app-layout>
