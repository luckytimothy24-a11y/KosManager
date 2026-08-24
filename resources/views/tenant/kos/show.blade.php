<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $kos->name }}" description="{{ $kos->address }}" />
    </x-slot>

    <div class="space-y-6">
        {{-- Back --}}
        <a href="{{ route('tenant.kos.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-primary-500 transition">
            <i class="ri-arrow-left-line"></i> Kembali ke daftar kos
        </a>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            {{-- Media --}}
            <div class="relative h-52 sm:h-64 overflow-hidden">
                @if($kos->photo && @file_exists(public_path('storage/'.$kos->photo)))
                    <img src="{{ asset('storage/' . $kos->photo) }}" alt="Foto {{ $kos->name }}" class="absolute inset-0 w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                        <span class="absolute inset-0 flex items-center justify-center text-[9rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none" aria-hidden="true">{{ mb_substr($kos->name, 0, 1) }}</span>
                        <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-primary-100/70 dark:bg-slate-700/60"></div>
                        <div class="absolute -left-12 bottom-[-3rem] w-48 h-48 rounded-full bg-blue-100/60 dark:bg-slate-700/40"></div>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-14 h-14 rounded-2xl bg-white/90 dark:bg-slate-800/90 shadow-sm flex items-center justify-center">
                            <i class="ri-home-heart-line text-2xl text-primary-500 dark:text-primary-300"></i>
                        </div>
                    </div>
                @endif
                <span class="absolute top-4 left-4 inline-flex items-center gap-1 bg-white/95 dark:bg-slate-900/95 backdrop-blur text-xs font-bold {{ $kamarTersedia->count() > 0 ? 'text-green-700 dark:text-green-300' : 'text-slate-600 dark:text-slate-300' }} px-3 py-1.5 rounded-lg shadow-sm">
                    <i class="{{ $kamarTersedia->count() > 0 ? 'ri-door-open-line' : 'ri-door-closed-line' }}"></i>
                    {{ $kamarTersedia->count() }} kamar tersedia
                </span>
            </div>

            <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-5">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1.5"><i class="ri-map-pin-2-fill text-primary-500"></i> {{ $kos->address }}</span>
                        @if($kos->phone)
                            <span class="inline-flex items-center gap-1.5"><i class="ri-phone-line text-primary-500"></i> {{ $kos->phone }}</span>
                        @endif
                    </div>

                    @if($kos->description)
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Deskripsi</h3>
                            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $kos->description }}</p>
                        </div>
                    @endif

                    @php
                        $fasilitas = collect(preg_split('/[,;]/', (string) $kos->general_facilities))
                            ->map(fn ($f) => trim($f))
                            ->filter();
                    @endphp
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Fasilitas Umum</h3>
                        @if($fasilitas->isNotEmpty())
                            <ul class="mt-2.5 flex flex-wrap gap-2">
                                @foreach($fasilitas as $f)
                                    <li class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-lg">
                                        <i class="ri-check-line text-green-600 dark:text-green-400 font-bold"></i> {{ $f }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-1.5 text-sm text-slate-400 dark:text-slate-500">Informasi fasilitas belum tersedia. Hubungi pemilik untuk detail lengkap.</p>
                        @endif
                    </div>

                    @if($kos->rules)
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Peraturan</h3>
                            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $kos->rules }}</p>
                        </div>
                    @endif
                </div>

                {{-- Info pemilik --}}
                <aside class="space-y-4">
                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 p-5">
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Informasi Pemilik</h3>
                        <div class="mt-3 flex items-center gap-3">
                            <span class="w-11 h-11 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 flex items-center justify-center text-base font-bold uppercase shrink-0">{{ mb_substr($kos->owner?->name ?? '?', 0, 1) }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $kos->owner?->name ?? 'Pemilik Kos' }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Pemilik KosManager</p>
                            </div>
                        </div>
                        @if($kos->phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $kos->phone) }}" class="mt-4 w-full inline-flex justify-center items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-300 border border-primary-200 dark:border-primary-500/30 hover:bg-primary-50 dark:hover:bg-primary-500/10 px-4 py-2.5 rounded-xl transition">
                                <i class="ri-phone-line"></i> Hubungi Pemilik
                            </a>
                        @endif
                    </div>
                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Cara Booking</h3>
                        <ol class="mt-3 space-y-2.5 text-xs text-slate-500 dark:text-slate-400">
                            <li class="flex gap-2"><span class="shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 font-bold flex items-center justify-center text-[10px]">1</span> Pilih kamar yang tersedia di bawah.</li>
                            <li class="flex gap-2"><span class="shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 font-bold flex items-center justify-center text-[10px]">2</span> Tentukan periode sewa lalu konfirmasi booking.</li>
                            <li class="flex gap-2"><span class="shrink-0 w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-300 font-bold flex items-center justify-center text-[10px]">3</span> Setelah disetujui, lakukan pembayaran sesuai tagihan.</li>
                        </ol>
                    </div>
                </aside>
            </div>
        </div>

        {{-- Available Rooms --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Kamar Tersedia</h2>
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $kamarTersedia->count() }} kamar</span>
            </div>

            @if($kamarTersedia->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center mx-auto">
                        <i class="ri-door-closed-line text-2xl text-primary-400"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada kamar yang tersedia di kos ini.</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Silakan cek kembali lain waktu atau hubungi pemilik.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($kamarTersedia as $kamar)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                            <div class="relative h-28 overflow-hidden">
                                @if($kamar->photo && @file_exists(public_path('storage/'.$kamar->photo)))
                                    <img src="{{ asset('storage/' . $kamar->photo) }}" alt="Foto Kamar {{ $kamar->room_number }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 to-primary-50 dark:from-slate-800 dark:to-slate-800">
                                        <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.05] dark:text-white/5 select-none" aria-hidden="true">{{ mb_substr($kamar->room_number, 0, 2) }}</span>
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-700 shadow-sm flex items-center justify-center">
                                            <i class="ri-hotel-bed-line text-lg text-primary-500 dark:text-primary-300"></i>
                                        </div>
                                    </div>
                                @endif
                                <span class="absolute top-2.5 right-2.5 inline-flex items-center gap-1 bg-white/95 dark:bg-slate-900/95 backdrop-blur text-[10px] font-bold px-2 py-1 rounded-md shadow-sm text-green-700 dark:text-green-300">
                                    <i class="ri-checkbox-circle-fill text-[10px]"></i> TERSEDIA
                                </span>
                            </div>
                            <div class="p-5 flex flex-col flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 dark:text-white">Kamar {{ $kamar->room_number }}</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 truncate">{{ $kamar->room_name }} · Lantai {{ $kamar->floor }} · {{ $kamar->area }}</p>
                                    </div>
                                    <span class="shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-300">{{ $kamar->room_type }}</span>
                                </div>
                                <div class="mt-4 space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    @if($kamar->daily_price)
                                        <p class="flex items-center justify-between"><span><i class="ri-calendar-line mr-1.5 text-primary-500"></i>Harian</span><span class="font-bold text-slate-800 dark:text-slate-100">Rp {{ number_format($kamar->daily_price, 0, ',', '.') }}</span></p>
                                    @endif
                                    @if($kamar->monthly_price)
                                        <p class="flex items-center justify-between"><span><i class="ri-calendar-2-line mr-1.5 text-primary-500"></i>Bulanan</span><span class="font-bold text-slate-800 dark:text-slate-100">Rp {{ number_format($kamar->monthly_price, 0, ',', '.') }}</span></p>
                                    @endif
                                </div>
                                <a href="{{ route('tenant.booking.create', ['kos_id' => $kos->id, 'kamar_id' => $kamar->id]) }}"
                                   class="mt-auto pt-4 block w-full text-center bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30">
                                    Booking Kamar Ini
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
