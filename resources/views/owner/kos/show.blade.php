<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $kos->name }}" description="Informasi lengkap properti kos Anda.">
            <div class="flex items-center gap-2">
                <x-button href="{{ route('owner.kos.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
                <x-button href="{{ route('owner.kos.edit', $kos) }}" type="primary"><i class="ri-edit-line"></i> Edit</x-button>
            </div>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom utama --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Hero foto + info --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    @if($kos->photo && @file_exists(public_path('storage/'.$kos->photo)))
                        <img src="{{ asset('storage/'.$kos->photo) }}" alt="Foto {{ $kos->name }}" class="w-full h-56 sm:h-64 object-cover bg-slate-100 dark:bg-slate-800">
                    @else
                        <div class="h-36 sm:h-44 bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 relative flex items-center justify-center">
                            <span class="absolute inset-y-0 left-6 flex items-center text-[7rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none" aria-hidden="true">{{ mb_substr($kos->name, 0, 1) }}</span>
                            <span class="relative w-14 h-14 rounded-2xl bg-white/90 dark:bg-slate-800/90 shadow-sm flex items-center justify-center">
                                <i class="ri-home-heart-line text-2xl text-primary-500"></i>
                            </span>
                        </div>
                    @endif

                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $kos->name }}</h2>
                            <x-status-badge :status="$kos->status" context="kos" />
                        </div>

                        <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <i class="ri-map-pin-2-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Alamat</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 break-words">{{ $kos->address }}</dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="ri-phone-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Telepon</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kos->phone ?: '-' }}</dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="ri-user-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pemilik</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kos->owner?->name ?? '-' }}</dd>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <i class="ri-door-open-line text-slate-400 dark:text-slate-500 mt-0.5 shrink-0"></i>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jumlah Kamar</dt>
                                    <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kos->kamar_count }} kamar</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @if($kos->description)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <i class="ri-file-text-line text-primary-500"></i> Deskripsi
                        </h3>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $kos->description }}</p>
                    </div>
                @endif

                {{-- Fasilitas umum --}}
                @php
                    $kosFacilities = $kos->fasilitas->isNotEmpty()
                        ? $kos->fasilitas
                        : collect();
                @endphp
                @if($kosFacilities->isNotEmpty() || $kos->general_facilities)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <i class="ri-sofa-line text-primary-500"></i> Fasilitas Umum
                        </h3>
                        @if($kosFacilities->isNotEmpty())
                            <ul class="mt-3 flex flex-wrap gap-2">
                                @foreach($kosFacilities as $f)
                                    <li class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-lg">
                                        @if($f->icon)
                                            <i class="{{ str_starts_with($f->icon, 'ri-') ? $f->icon : 'ri-'.$f->icon.'-line' }} text-primary-500"></i>
                                        @else
                                            <i class="ri-check-line text-green-600 dark:text-green-400"></i>
                                        @endif
                                        {{ $f->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @elseif($kos->general_facilities)
                            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $kos->general_facilities }}</p>
                        @endif
                    </div>
                @endif

                {{-- Peraturan --}}
                @if($kos->rules)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <i class="ri-shield-check-line text-primary-500"></i> Peraturan Kos
                        </h3>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $kos->rules }}</p>
                    </div>
                @endif

                {{-- Informasi pembayaran --}}
                @if($kos->payment_info)
                    <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-6 bg-blue-50/50 dark:bg-blue-500/[0.06]">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-blue-900 dark:text-blue-200">
                            <i class="ri-bank-card-line"></i> Informasi Pembayaran
                        </h3>
                        <p class="mt-3 text-sm text-blue-800/90 dark:text-blue-300/90 leading-relaxed whitespace-pre-line">{{ $kos->payment_info }}</p>
                    </div>
                @endif
            </div>

            {{-- Sidebar statistik --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-bar-chart-box-line text-primary-500"></i> Statistik
                    </h3>
                    <div class="space-y-4">
                        <a href="{{ route('owner.kamar.index', ['kos_id' => $kos->id]) }}" class="group flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-800 px-4 py-3 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                            <span class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"><i class="ri-door-open-line text-primary-500"></i> Total Kamar</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition">{{ $kos->kamar_count }}</span>
                        </a>
                        <a href="{{ route('owner.penghuni.index') }}" class="group flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-800 px-4 py-3 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                            <span class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"><i class="ri-user-star-line text-green-500"></i> Total Penghuni</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition">{{ $kos->penghunis_count }}</span>
                        </a>
                        <a href="{{ route('owner.booking.index') }}" class="group flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-800 px-4 py-3 hover:border-primary-200 dark:hover:border-primary-500/30 transition">
                            <span class="flex items-center gap-2.5 text-sm text-slate-600 dark:text-slate-300"><i class="ri-calendar-check-line text-amber-500"></i> Total Booking</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white group-hover:text-primary-600 transition">{{ $kos->bookings_count }}</span>
                        </a>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-flashlight-line text-primary-500"></i> Aksi Cepat
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('owner.kamar.create', ['kos_id' => $kos->id]) }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-add-circle-line text-lg"></i> Tambah Kamar
                        </a>
                        <a href="{{ route('owner.tagihan.create') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-file-add-line text-lg"></i> Buat Tagihan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
