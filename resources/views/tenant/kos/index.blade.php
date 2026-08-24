<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Cari Kos" description="Temukan kos dan kamar yang tersedia untuk Anda." />
    </x-slot>

    <div class="space-y-6">
        {{-- Search --}}
        <form method="GET" action="{{ route('tenant.kos.index') }}" class="flex gap-2">
            <div class="relative flex-1 max-w-md">
                <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kos atau alamat..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500/10 transition">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl transition">Cari</button>
        </form>

        @if($kosList->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center mx-auto">
                    <i class="ri-search-eye-line text-3xl text-primary-400"></i>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Tidak Ada Kos Ditemukan</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Coba kata kunci lain atau lihat semua kos.</p>
                <a href="{{ route('tenant.kos.index') }}" class="mt-4 inline-block text-sm font-semibold text-primary-500 hover:text-primary-600">Lihat Semua Kos</a>
            </div>
        @else
            {{-- Kos Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($kosList as $kos)
                    <a href="{{ route('tenant.kos.show', $kos) }}"
                       class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-lg hover:-translate-y-1 hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-200 cursor-pointer">
                        <div class="relative h-44 overflow-hidden">
                            @if($kos->photo && @file_exists(public_path('storage/'.$kos->photo)))
                                <img src="{{ asset('storage/' . $kos->photo) }}" alt="Foto {{ $kos->name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                                    <span class="absolute inset-0 flex items-center justify-center text-[7rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none" aria-hidden="true">{{ mb_substr($kos->name, 0, 1) }}</span>
                                    <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-primary-100/70 dark:bg-slate-700/60"></div>
                                    <div class="absolute -left-10 bottom-[-2.5rem] w-36 h-36 rounded-full bg-blue-100/60 dark:bg-slate-700/40"></div>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-14 h-14 rounded-2xl bg-white/90 dark:bg-slate-800/90 shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <i class="ri-home-heart-line text-2xl text-primary-500 dark:text-primary-300"></i>
                                    </div>
                                </div>
                            @endif
                            <span class="absolute top-3 right-3 inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm {{ $kos->kamar_tersedia > 0 ? 'bg-white/95 dark:bg-slate-900/95 backdrop-blur text-green-700 dark:text-green-300' : 'bg-slate-800/90 dark:bg-slate-950/90 backdrop-blur text-slate-200' }}">
                                <i class="{{ $kos->kamar_tersedia > 0 ? 'ri-door-open-line' : 'ri-door-closed-line' }} text-xs"></i>
                                {{ $kos->kamar_tersedia > 0 ? $kos->kamar_tersedia.' kamar tersedia' : 'Penuh' }}
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-900 dark:text-white leading-snug group-hover:text-primary-500 dark:group-hover:text-primary-300 transition">{{ $kos->name }}</h3>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                                <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-500"></i> {{ $kos->address }}
                            </p>
                            @if($kos->description)
                                <p class="mt-2.5 text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2 min-h-[2rem]">{{ \Illuminate\Support\Str::limit($kos->description, 90) }}</p>
                            @endif
                            <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                                @if(!is_null($kos->harga_mulai))
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai dari</p>
                                        <p class="text-base font-black text-slate-900 dark:text-white">Rp {{ number_format($kos->harga_mulai, 0, ',', '.') }}<span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/bln</span></p>
                                    </div>
                                @else
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Harga hubungi pemilik</p>
                                @endif
                                <span class="shrink-0 inline-flex items-center gap-1 text-xs font-bold text-primary-500 group-hover:gap-2 transition-all">Lihat Detail <i class="ri-arrow-right-line"></i></span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="flex justify-center">{{ $kosList->links() }}</div>
        @endif
    </div>
</x-app-layout>
