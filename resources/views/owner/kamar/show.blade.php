<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.kamar.index'); $canManage = Auth::user()->hasRole('owner', 'super_admin'); @endphp
    <x-slot name="header">
        <x-page-header title="Kamar {{ $kamar->room_number }}" description="{{ $kamar->room_name }}">
            <div class="flex items-center gap-2">
                <x-button href="{{ $backUrl }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
                @if($canManage)
                    <a href="{{ route('owner.kamar.edit', $kamar) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-white bg-primary-500 rounded-xl hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30">
                        <i class="ri-edit-line"></i> Edit
                    </a>
                @endif
            </div>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom utama --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Hero kamar --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <div class="h-28 bg-gradient-to-br from-primary-600 to-blue-800 dark:from-primary-700 dark:to-blue-900 relative">
                        <span class="absolute inset-y-0 right-6 flex items-center text-[6.5rem] leading-none font-black uppercase text-white/10 select-none" aria-hidden="true">{{ mb_substr($kamar->room_number, 0, 2) }}</span>
                        <div class="absolute bottom-4 left-6 flex items-center gap-3">
                            <span class="w-11 h-11 rounded-xl bg-white/95 shadow-sm flex items-center justify-center shrink-0">
                                <i class="ri-door-open-line text-xl text-primary-600"></i>
                            </span>
                            <div>
                                <p class="text-lg font-bold text-white leading-tight">Kamar {{ $kamar->room_number }}</p>
                                <p class="text-xs text-white/80">{{ $kamar->kos->name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm flex-1 min-w-0">
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-price-tag-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tipe</dt>
                                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kamar->room_type }}</dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-stairs-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lantai</dt>
                                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kamar->floor ?? '-' }}</dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-expand-height-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Luas</dt>
                                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kamar->area ? $kamar->area.' m²' : '-' }}</dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-information-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</dt>
                                        <dd class="mt-0.5"><x-status-badge :status="$kamar->status" context="kamar" /></dd>
                                    </div>
                                </div>
                            </dl>
                            <x-status-badge :status="$kamar->status" context="kamar" class="sm:hidden" />
                        </div>

                        <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-800 px-4 py-3">
                                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"><i class="ri-calendar-line"></i> Harian</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($kamar->daily_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/50 dark:bg-primary-500/[0.06] px-4 py-3">
                                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400"><i class="ri-calendar-check-line"></i> Bulanan</span>
                                <span class="font-bold text-primary-700 dark:text-primary-300">Rp {{ number_format($kamar->monthly_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fasilitas --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                        <i class="ri-sofa-line text-primary-500"></i> Fasilitas Kamar
                    </h3>
                    @if($kamar->fasilitas->count())
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($kamar->fasilitas as $f)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                                    @if($f->icon)
                                        <i class="{{ str_starts_with($f->icon, 'ri-') ? $f->icon : 'ri-'.$f->icon.'-line' }} text-xs"></i>
                                    @endif
                                    {{ $f->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-slate-400 dark:text-slate-500">Belum ada fasilitas yang ditambahkan.</p>
                    @endif
                </div>
            </div>

            {{-- Sidebar penghuni --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-user-star-line text-primary-500"></i> Penghuni Aktif
                    </h3>
                    <div class="space-y-2.5">
                        @forelse($kamar->penghunis->where('status', 'active') as $p)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                                @php $initial = strtoupper(substr($p->user?->name ?? '?', 0, 1)); @endphp
                                <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0">{{ $initial }}</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $p->user?->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $p->phone ?: '-' }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center">
                                <span class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                    <i class="ri-user-search-line text-lg text-slate-300 dark:text-slate-600"></i>
                                </span>
                                <p class="mt-2.5 text-sm text-slate-400 dark:text-slate-500">Tidak ada penghuni aktif.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-flashlight-line text-primary-500"></i> Aksi Cepat
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route("$prefix.booking.index") }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-calendar-check-line text-lg"></i> Lihat Booking
                        </a>
                        <a href="{{ route("$prefix.checkin.index") }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-login-box-line text-lg"></i> Proses Check-in
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
