<x-app-layout>
    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Cari Kos', 'url' => route('tenant.kos.index')],
            ['label' => $kos->name],
        ]" />

        {{-- Hero Section --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="relative h-56 sm:h-72 overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700"
                 x-data="{ viewerOpen: false }">
                <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-primary-100/70 dark:bg-slate-700/60"></div>
                <div class="absolute -left-12 bottom-[-3rem] w-48 h-48 rounded-full bg-blue-100/60 dark:bg-slate-700/40"></div>
                @if($kos->photo)
                    <button type="button"
                            @click="viewerOpen = true; $nextTick(() => $refs.viewerClose?.focus())"
                            class="absolute inset-0 w-full h-full p-0 border-0 bg-transparent cursor-zoom-in group focus:outline-none focus-visible:ring-4 focus-visible:ring-white/40"
                            aria-label="Perbesar foto {{ $kos->name }}"
                            aria-haspopup="dialog">
                        <img src="{{ asset('storage/' . $kos->photo) }}" alt="Foto {{ $kos->name }}"
                             class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                             onerror="this.style.display='none'">
                    </button>
                    <span role="status" class="absolute bottom-4 right-4 inline-flex items-center gap-1.5 bg-black/40 backdrop-blur text-white text-[11px] font-bold px-2.5 py-1 rounded-lg shadow-sm">
                        <i class="ri-image-2-line text-xs"></i>
                        1 foto
                    </span>
                @else
                    <span class="absolute inset-0 flex items-center justify-center text-[9rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($kos->name, 0, 1) }}</span>
                @endif
                <span class="absolute top-4 left-4 inline-flex items-center gap-1 bg-white/95 dark:bg-slate-900/95 backdrop-blur text-xs font-bold {{ $kamarTersedia->count() > 0 ? 'text-green-700 dark:text-green-300' : 'text-slate-600 dark:text-slate-300' }} px-3 py-1.5 rounded-lg shadow-sm z-10">
                    <i class="{{ $kamarTersedia->count() > 0 ? 'ri-door-open-line' : 'ri-door-closed-line' }}"></i>
                    {{ $kamarTersedia->count() }} kamar tersedia
                </span>
                @auth
                    <form method="POST" action="{{ route('tenant.favorites.toggle') }}" class="absolute top-4 right-4 z-20"
                          x-data="{ submitting: false }" x-on:submit="submitting = true">
                        @csrf
                        <input type="hidden" name="kos_id" value="{{ $kos->id }}">
                        <button type="submit" :disabled="submitting"
                                class="w-10 h-10 rounded-full flex items-center justify-center bg-black/20 hover:bg-white/30 dark:hover:bg-white/20 backdrop-blur-sm transition disabled:opacity-60 disabled:cursor-wait {{ $isFavorited ? 'text-red-500' : 'text-white/80 hover:text-white' }}"
                                aria-label="{{ $isFavorited ? 'Hapus dari favorit' : 'Tambah ke favorit' }}">
                            <i class="{{ $isFavorited ? 'ri-heart-3-fill' : 'ri-heart-3-line' }} text-lg"></i>
                        </button>
                    </form>
                @endauth

                {{-- Single-image viewer / lightbox --}}
                <template x-if="viewerOpen && {{ $kos->photo ? 'true' : 'false' }}">
                    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Foto {{ $kos->name }}"
                         @keydown.escape.window="viewerOpen = false; $nextTick(() => $refs.viewerTrigger?.focus())">
                        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="viewerOpen = false"></div>
                        <div class="relative max-w-4xl w-full rounded-2xl overflow-hidden bg-slate-900 shadow-2xl">
                            <img src="{{ asset('storage/' . $kos->photo) }}" alt="Foto {{ $kos->name }}"
                                 class="w-full max-h-[80vh] object-contain bg-slate-900">
                            <div class="absolute top-3 right-3 z-10">
                                <button type="button" x-ref="viewerClose" @click="viewerOpen = false"
                                        class="w-10 h-10 rounded-full flex items-center justify-center bg-black/50 hover:bg-black/70 text-white transition focus:outline-none focus-visible:ring-4 focus-visible:ring-white/60"
                                        aria-label="Tutup foto">
                                    <i class="ri-close-line text-xl"></i>
                                </button>
                            </div>
                            <p role="status" class="absolute bottom-3 left-1/2 -translate-x-1/2 text-xs font-semibold text-white/90 bg-black/40 px-2.5 py-1 rounded-lg">1/1</p>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-5">
                    {{-- Name & Address --}}
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">{{ $kos->name }}</h1>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                            <span class="inline-flex items-center gap-1.5"><i class="ri-map-pin-2-fill text-primary-500"></i> {{ $kos->address }}</span>
                            @if($kos->phone)
                                <span class="inline-flex items-center gap-1.5"><i class="ri-phone-line text-primary-500"></i> {{ $kos->phone }}</span>
                            @endif
                            <span class="inline-flex items-center gap-1.5"><i class="ri-heart-3-fill text-red-400"></i> {{ $favoriteCount }} favorit</span>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Kamar</p>
                            <p class="mt-0.5 text-lg font-black text-slate-900 dark:text-white">{{ $allKamar->count() }}</p>
                        </div>
                        <div class="bg-green-50 dark:bg-green-500/10 rounded-xl p-3 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-green-600/70 dark:text-green-400/60">Tersedia</p>
                            <p class="mt-0.5 text-lg font-black text-green-700 dark:text-green-300">{{ $kamarTersedia->count() }}</p>
                        </div>
                        @if(!is_null($hargaMulai))
                            <div class="bg-primary-50 dark:bg-primary-500/10 rounded-xl p-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500/70 dark:text-primary-400/60">Mulai dari</p>
                                <p class="mt-0.5 text-sm font-black text-primary-700 dark:text-primary-300">Rp {{ number_format($hargaMulai, 0, ',', '.') }}<span class="text-[10px] font-medium text-primary-500/60">/bln</span></p>
                            </div>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($kos->description)
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tentang Kos</h3>
                            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $kos->description }}</p>
                        </div>
                    @else
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tentang Kos</h3>
                            <p class="mt-1.5 text-sm text-slate-400 dark:text-slate-500 italic">Belum ada deskripsi dari pengelola.</p>
                        </div>
                    @endif

                    {{-- Facilities (categorized) --}}
                    @php
                        $groupLabels = [
                            'kamar' => 'Fasilitas Kamar',
                            'kamar_mandi' => 'Kamar Mandi',
                            'bersama' => 'Fasilitas Bersama',
                            'parkir_keamanan' => 'Parkir & Keamanan',
                            'layanan' => 'Layanan',
                            'lainnya' => 'Fasilitas Lainnya',
                        ];
                        $groupIcons = [
                            'kamar' => 'ri-hotel-bed-line',
                            'kamar_mandi' => 'ri-drop-line',
                            'bersama' => 'ri-restaurant-2-line',
                            'parkir_keamanan' => 'ri-parking-line',
                            'layanan' => 'ri-tools-line',
                            'lainnya' => 'ri-apps-2-line',
                        ];
                    @endphp
                    <div x-data="{ open: null }">
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Fasilitas Kos</h3>
                        @if(count($facilityGroups))
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Fasilitas yang tersedia pada kos ini.</p>
                            <div class="mt-3 space-y-2.5">
                                @foreach($facilityGroups as $key => $group)
                                    @php $label = $groupLabels[$key] ?? 'Fasilitas'; $gicon = $groupIcons[$key] ?? 'ri-check-line'; @endphp
                                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                                        <button type="button"
                                                class="w-full flex items-center justify-between gap-2 px-4 py-3 text-left bg-slate-50/60 dark:bg-slate-800/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40"
                                                @click="open = open === '{{ $key }}' ? null : '{{ $key }}'"
                                                :aria-expanded="(open === '{{ $key }}').toString()">
                                            <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-800 dark:text-slate-100">
                                                <i class="{{ $gicon }} text-primary-500"></i> {{ $label }}
                                            </span>
                                            <span class="inline-flex items-center gap-2">
                                                <span class="text-[11px] font-bold text-slate-400">{{ $group->count() }}</span>
                                                <i class="ri-arrow-down-s-line text-slate-400 transition-transform" :class="open === '{{ $key }}' ? 'rotate-180' : ''"></i>
                                            </span>
                                        </button>
                                        <ul x-show="open === '{{ $key }}'" x-cloak x-transition
                                            class="px-4 pb-3 pt-1 grid grid-cols-2 gap-1.5">
                                            @foreach($group as $f)
                                                <li class="flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                                                    <i class="ri-check-line text-green-500 text-[11px]"></i>
                                                    @if(!empty($f['icon']))
                                                        <i class="{{ str_starts_with($f['icon'], 'ri-') ? $f['icon'] : 'ri-'.$f['icon'].'-line' }} text-primary-500 text-[11px]"></i>
                                                    @endif
                                                    {{ $f['name'] }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-1.5 text-sm text-slate-400 dark:text-slate-500">Informasi fasilitas belum tersedia. Hubungi pemilik untuk detail lengkap.</p>
                        @endif
                    </div>

                    {{-- Rules --}}
                    @if($kos->rules)
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Peraturan</h3>
                            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $kos->rules }}</p>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
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

                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Kenapa Booking di KosManager?</h3>
                        <ul class="mt-3 space-y-2.5 text-xs text-slate-500 dark:text-slate-400">
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Booking langsung terkonfirmasi</li>
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Harga ditampilkan sebelum booking</li>
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Data pembayaran aman</li>
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Pembayaran diverifikasi pengelola</li>
                            <li class="flex gap-2"><span class="shrink-0 text-emerald-500"><i class="ri-check-line"></i></span> Lokasi kos di Google Maps</li>
                        </ul>
                    </div>

                    @if($kos->payment_info)
                        <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Cara Pembayaran</h3>
                            <div class="mt-3 text-xs text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $kos->payment_info }}</div>
                        </div>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('tenant.favorites.toggle') }}">
                            @csrf
                            <input type="hidden" name="kos_id" value="{{ $kos->id }}">
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 text-sm font-semibold {{ $isFavorited ? 'text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' : 'text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10' }} px-4 py-2.5 rounded-xl transition">
                                <i class="{{ $isFavorited ? 'ri-heart-3-fill text-red-500' : 'ri-heart-3-line' }}"></i>
                                {{ $isFavorited ? 'Hapus dari Favorit' : 'Tambahkan ke Favorit' }}
                            </button>
                        </form>
                    @endauth
                </aside>
            </div>
        </div>

        {{-- All Rooms --}}
        <div id="kamar" x-data="{ detailKamar: null }">
            <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Pilihan Kamar</h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Temukan kamar yang sesuai kebutuhan dan budgetmu.</p>
                </div>
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $allKamar->count() }} kamar total · {{ $kamarTersedia->count() }} tersedia</span>
            </div>

            @if($allKamar->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center mx-auto">
                        <i class="ri-door-closed-line text-2xl text-primary-400"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada kamar di kos ini.</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Silakan cek kembali lain waktu atau hubungi pemilik.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($allKamar as $kamar)
                        @php
                            $isAvailable = $kamar->status === 'available';
                            $statusLabel = \StatusLabels::kamarLabel($kamar->status);
                            $statusColor = match($kamar->status) {
                                'available' => 'text-green-700 dark:text-green-300',
                                'booked' => 'text-amber-700 dark:text-amber-300',
                                'occupied' => 'text-blue-700 dark:text-blue-300',
                                'maintenance' => 'text-red-700 dark:text-red-300',
                                default => 'text-slate-600 dark:text-slate-300',
                            };
                            $statusBg = match($kamar->status) {
                                'available' => 'bg-green-50 dark:bg-green-500/10',
                                'booked' => 'bg-amber-50 dark:bg-amber-500/10',
                                'occupied' => 'bg-blue-50 dark:bg-blue-500/10',
                                'maintenance' => 'bg-red-50 dark:bg-red-500/10',
                                default => 'bg-slate-50 dark:bg-slate-800',
                            };
                            $fasilitasCount = $kamar->fasilitas->count();
                            $showFasilitas = $kamar->fasilitas->take(3);
                            $extraCount = max(0, $fasilitasCount - 3);
                        @endphp
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                            <div class="relative h-28 overflow-hidden bg-gradient-to-br from-slate-50 to-primary-50 dark:from-slate-800 dark:to-slate-800">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-10 h-10 rounded-xl bg-white/90 dark:bg-slate-700 shadow-sm flex items-center justify-center">
                                        <i class="ri-hotel-bed-line text-lg text-primary-500 dark:text-primary-300"></i>
                                    </div>
                                </div>
                                @if($kamar->photo)
                                    <img src="{{ asset('storage/' . $kamar->photo) }}" alt="Foto Kamar {{ $kamar->room_number }}"
                                         class="absolute inset-0 w-full h-full object-cover"
                                         loading="lazy" onerror="this.style.display='none'">
                                @endif
                                <span class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-primary-900/[0.05] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($kamar->room_number, 0, 2) }}</span>
                                <span class="absolute top-2.5 right-2.5 inline-flex items-center gap-1 bg-white/95 dark:bg-slate-900/95 backdrop-blur text-[10px] font-bold px-2 py-1 rounded-md shadow-sm {{ $statusColor }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isAvailable ? 'bg-green-500' : ($kamar->status === 'booked' ? 'bg-amber-500' : ($kamar->status === 'occupied' ? 'bg-blue-500' : 'bg-red-500')) }}"></span>
                                    {{ strtoupper($statusLabel) }}
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

                                {{-- Room Facilities Preview --}}
                                @if($fasilitasCount > 0)
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        @foreach($showFasilitas as $f)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-2 py-0.5 rounded-md">
                                                @if($f->icon)
                                                    <i class="{{ $f->icon }} text-[10px]"></i>
                                                @endif
                                                {{ $f->name }}
                                            </span>
                                        @endforeach
                                        @if($extraCount > 0)
                                            <span class="inline-flex items-center text-[10px] font-semibold text-primary-500">+{{ $extraCount }}</span>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-4 space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    @if($kamar->daily_price)
                                        <p class="flex items-center justify-between"><span><i class="ri-calendar-line mr-1.5 text-primary-500"></i>Harian</span><span class="font-bold text-slate-800 dark:text-slate-100">Rp {{ number_format($kamar->daily_price, 0, ',', '.') }}</span></p>
                                    @endif
                                    @if($kamar->monthly_price)
                                        <p class="flex items-center justify-between"><span><i class="ri-calendar-2-line mr-1.5 text-primary-500"></i>Bulanan</span><span class="font-bold text-slate-800 dark:text-slate-100">Rp {{ number_format($kamar->monthly_price, 0, ',', '.') }}</span></p>
                                    @endif
                                </div>

                                <div class="mt-auto pt-4 flex gap-2">
                                    <button type="button" x-on:click="detailKamar = {{ json_encode([
                                        'id' => $kamar->id,
                                        'room_number' => $kamar->room_number,
                                        'room_name' => $kamar->room_name,
                                        'room_type' => $kamar->room_type,
                                        'floor' => $kamar->floor,
                                        'area' => $kamar->area,
                                        'description' => $kamar->description,
                                        'daily_price' => (int) $kamar->daily_price,
                                        'monthly_price' => (int) $kamar->monthly_price,
                                        'status' => $kamar->status,
                                        'status_label' => $statusLabel,
                                        'photo' => $kamar->photo ? asset('storage/' . $kamar->photo) : null,
                                        'fasilitas' => $kamar->fasilitas->map(fn ($f) => ['name' => $f->name, 'icon' => $f->icon])->toArray(),
                                    ]) }}"
                                    class="flex-1 text-center text-xs font-semibold py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                        Lihat Detail
                                    </button>
                                    @auth('web')
                                        @if(auth()->user()->role === 'tenant' && $isAvailable)
                                            <a href="{{ route('tenant.booking.create', ['kos_id' => $kos->id, 'kamar_id' => $kamar->id]) }}"
                                               class="flex-1 text-center text-xs font-semibold py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white transition shadow-sm shadow-primary-500/30">
                                                Booking
                                            </a>
                                        @endif
                                    @endauth
                                    @guest
                                        @if($isAvailable)
                                            <a href="{{ route('login') }}"
                                               class="flex-1 text-center text-xs font-semibold py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white transition shadow-sm shadow-primary-500/30">
                                                Booking
                                            </a>
                                        @endif
                                    @endguest
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Room Detail Modal --}}
            <template x-if="detailKamar">
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="detailKamar = null"></div>
                    <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 w-full max-w-lg max-h-[85vh] overflow-y-auto"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        <div class="sticky top-0 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Detail Kamar</h3>
                            <button type="button" x-on:click="detailKamar = null"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    aria-label="Tutup detail kamar">
                                <i class="ri-close-line text-lg"></i>
                            </button>
                        </div>

                        <div class="p-6 space-y-5">
                            {{-- Photo --}}
                            <template x-if="detailKamar.photo">
                                <div class="rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800">
                                    <img :src="detailKamar.photo" :alt="'Foto Kamar ' + detailKamar.room_number" class="w-full h-48 object-cover">
                                </div>
                            </template>

                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white" x-text="'Kamar ' + detailKamar.room_number"></p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5" x-text="detailKamar.room_name"></p>
                                </div>
                                <span class="shrink-0 text-xs font-bold px-2.5 py-1 rounded-lg bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-300" x-text="detailKamar.room_type"></span>
                            </div>

                            {{-- Status --}}
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg"
                                      :class="{
                                          'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-300': detailKamar.status === 'available',
                                          'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300': detailKamar.status === 'booked',
                                          'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300': detailKamar.status === 'occupied',
                                          'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300': detailKamar.status === 'maintenance',
                                      }">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="{
                                              'bg-green-500': detailKamar.status === 'available',
                                              'bg-amber-500': detailKamar.status === 'booked',
                                              'bg-blue-500': detailKamar.status === 'occupied',
                                              'bg-red-500': detailKamar.status === 'maintenance',
                                          }"></span>
                                    <span x-text="detailKamar.status_label"></span>
                                </span>
                            </div>

                            {{-- Info Grid --}}
                            <div class="grid grid-cols-2 gap-3">
                                <template x-if="detailKamar.floor">
                                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lantai</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white" x-text="detailKamar.floor"></p>
                                    </div>
                                </template>
                                <template x-if="detailKamar.area">
                                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Ukuran</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white" x-text="detailKamar.area"></p>
                                    </div>
                                </template>
                                <template x-if="detailKamar.daily_price">
                                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Harga Harian</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white" x-text="'Rp ' + Number(detailKamar.daily_price).toLocaleString('id-ID')"></p>
                                    </div>
                                </template>
                                <template x-if="detailKamar.monthly_price">
                                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Harga Bulanan</p>
                                        <p class="mt-0.5 text-sm font-bold text-slate-900 dark:text-white" x-text="'Rp ' + Number(detailKamar.monthly_price).toLocaleString('id-ID')"></p>
                                    </div>
                                </template>
                            </div>

                            {{-- Description --}}
                            <template x-if="detailKamar.description">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Deskripsi</h4>
                                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed" x-text="detailKamar.description"></p>
                                </div>
                            </template>

                            {{-- Facilities --}}
                            <div>
                                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Fasilitas Kamar</h4>
                                <template x-if="detailKamar.fasilitas && detailKamar.fasilitas.length > 0">
                                    <ul class="mt-2.5 flex flex-wrap gap-2">
                                        <template x-for="f in detailKamar.fasilitas" :key="f.name">
                                            <li class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 px-3 py-1.5 rounded-lg">
                                                <i class="ri-check-line text-green-600 dark:text-green-400 font-bold"></i>
                                                <span x-text="f.name"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="!detailKamar.fasilitas || detailKamar.fasilitas.length === 0">
                                    <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Belum ada informasi fasilitas.</p>
                                </template>
                            </div>

                            {{-- CTA --}}
                            <div class="pt-2">
                                <template x-if="detailKamar.status === 'available'">
                                    @auth('web')
                                        <a :href="'{{ route('tenant.booking.create', ['kos_id' => $kos->id]) }}&kamar_id=' + detailKamar.id"
                                           class="block w-full text-center bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                                            Booking Sekarang
                                        </a>
                                    @endauth
                                    @guest
                                        <a href="{{ route('login') }}"
                                           class="block w-full text-center bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl transition shadow-sm shadow-primary-500/30">
                                            Login untuk Booking
                                        </a>
                                    @endguest
                                </template>
                                <template x-if="detailKamar.status !== 'available'">
                                    <div class="w-full text-center text-xs font-semibold py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500">
                                        Kamar Tidak Tersedia
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Google Maps Location --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="p-6">
                @php
                    $hasCoordinates = $kos->hasValidCoordinates();
                    $googleMapsUrl = $kos->googleMapsSearchUrl();
                    $directionsUrl = $kos->googleMapsDirectionsUrl();
                @endphp

                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                    <i class="ri-map-pin-2-fill text-primary-500"></i> Lokasi Kos
                </h2>

                @if($hasCoordinates)
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ $kos->address }}</p>

                    <div class="mt-4 rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                        <a href="{{ $googleMapsUrl }}"
                           target="_blank" rel="noopener noreferrer"
                           aria-label="Lihat lokasi {{ $kos->name }} di Google Maps"
                           class="group relative block focus:outline-none focus-visible:ring-4 focus-visible:ring-primary-500/50">
                            <iframe
                                src="https://maps.google.com/maps?q={{ urlencode($kos->coordinatesDestination()) }}&z=15&output=embed"
                                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Peta lokasi {{ $kos->name }}"
                                class="w-full block"
                            ></iframe>
                            <span aria-hidden="true"
                                  class="absolute inset-0 block cursor-pointer transition group-hover:bg-black/10 group-focus-visible:bg-black/10"></span>
                            <span aria-hidden="true"
                                  class="pointer-events-none absolute bottom-3 left-1/2 -translate-x-1/2 inline-flex items-center gap-1.5 bg-white/95 dark:bg-slate-900/95 backdrop-blur text-xs font-bold text-slate-700 dark:text-slate-200 px-3 py-2 rounded-lg shadow-lg whitespace-nowrap">
                                <i class="ri-external-link-line"></i> Klik untuk membuka Maps
                            </span>
                        </a>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ $googleMapsUrl }}"
                           target="_blank" rel="noopener noreferrer"
                           aria-label="Buka lokasi {{ $kos->name }} di Google Maps"
                           class="inline-flex items-center justify-center gap-2 min-h-11 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 active:bg-primary-700 px-4 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30">
                            <i class="ri-google-fill"></i> Buka di Google Maps
                        </a>
                        <x-kos-directions-button :kos="$kos" label="Petunjuk Arah" variant="ghost" />
                    </div>
                @elseif($directionsUrl)
                    <div class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 p-5">
                        <p class="text-sm text-slate-600 dark:text-slate-300 flex items-start gap-2">
                            <i class="ri-map-pin-2-line text-primary-500 shrink-0 mt-0.5" aria-hidden="true"></i>
                            <span>{{ $kos->address }}</span>
                        </p>
                        <div class="mt-4">
                            <x-kos-directions-button :kos="$kos" label="Arah ke Kos" />
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 p-5 text-center">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto">
                            <i class="ri-map-pin-line text-lg text-slate-400 dark:text-slate-500"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Lokasi peta belum tersedia.</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Alamat kos belum tersedia.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Similar Kos --}}
        @if($similarKos->isNotEmpty())
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Kos Lain yang Mungkin Kamu Suka</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($similarKos as $sk)
                        <a href="{{ route('tenant.kos.show', $sk) }}"
                           class="block bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                            <div class="relative h-36 overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-primary-100/70 dark:bg-slate-700/60"></div>
                                <div class="absolute -left-8 bottom-[-2rem] w-28 h-28 rounded-full bg-blue-100/60 dark:bg-slate-700/40"></div>
                                @if($sk->photo)
                                    <img src="{{ asset('storage/' . $sk->photo) }}" alt="Foto {{ $sk->name }}"
                                         class="absolute inset-0 w-full h-full object-cover"
                                         loading="lazy" onerror="this.style.display='none'">
                                @endif
                                <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-black uppercase text-primary-900/[0.06] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($sk->name, 0, 1) }}</span>
                                <span class="absolute top-2.5 right-2.5 inline-flex items-center gap-1 text-[10px] font-bold bg-white/95 dark:bg-slate-900/95 backdrop-blur px-2 py-1 rounded-md shadow-sm text-green-700 dark:text-green-300">
                                    <i class="ri-door-open-line text-[10px]"></i> {{ $sk->kamar_tersedia }} kamar
                                </span>
                            </div>
                            <div class="p-4">
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white leading-snug truncate">{{ $sk->name }}</h4>
                                <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                                    <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-500"></i> {{ $sk->address }}
                                </p>
                                <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                    @if(!is_null($sk->harga_mulai))
                                        <p class="text-xs font-bold text-slate-900 dark:text-white">Rp {{ number_format($sk->harga_mulai, 0, ',', '.') }}<span class="text-[10px] font-medium text-slate-400 dark:text-slate-500">/bln</span></p>
                                    @else
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Hubungi pemilik</p>
                                    @endif
                                    <span class="text-[10px] font-bold text-primary-500 flex items-center gap-0.5">Lihat <i class="ri-arrow-right-s-line"></i></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Iklan detail — advertiser PIHAK KETIGA, terpisah dari info kos organik --}}
        @if(($detailAds ?? collect())->isNotEmpty())
            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Partner untuk Penghuni Kos</h2>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                        Iklan
                    </span>
                </div>
                <div class="space-y-2.5">
                    @foreach($detailAds as $ad)
                        @include('tenant.partials.partner-ad', ['ad' => $ad, 'placement' => 'detail', 'variant' => 'compact'])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($kamarTersedia->count())
        <div class="fixed bottom-16 inset-x-0 z-40 lg:hidden bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 px-4 py-3 safe-area-pb shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    @if(!is_null($hargaMulai))
                        <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Mulai dari</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-white leading-tight">Rp {{ number_format($hargaMulai, 0, ',', '.') }}<span class="text-xs font-medium text-slate-400 dark:text-slate-500">/bln</span></p>
                    @else
                        <p class="text-sm font-bold text-slate-900 dark:text-white leading-tight">Tersedia untuk disewa</p>
                    @endif
                </div>
                <a href="#kamar" class="shrink-0 inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-primary-600/25 transition active:scale-95">
                    <i class="ri-door-open-line"></i> Pilih Kamar
                </a>
            </div>
        </div>
        <div class="h-32 lg:hidden"></div>
    @endif

    {{-- Record this kos into recently-viewed (localStorage) --}}
    @php
        $recentRecord = [
            'id' => $kos->id,
            'name' => $kos->name,
            'address' => $kos->address,
            'url' => route('tenant.kos.show', $kos),
            'price' => !is_null($hargaMulai) ? 'Rp '.number_format($hargaMulai, 0, ',', '.').'/bln' : 'Harga hubungi pemilik',
        ];
    @endphp
    <script type="application/json" id="record-view">{{ json_encode($recentRecord) }}</script>
    <script>
        (function () {
            var KEY = 'kosmanager:recently_viewed';
            var MAX = 6;
            var el = document.getElementById('record-view');
            if (!el) return;
            try {
                var item = JSON.parse(el.textContent);
                var list = JSON.parse(localStorage.getItem(KEY) || '[]');
                list = Array.isArray(list) ? list.filter(function (i) { return i.id !== item.id; }) : [];
                list.unshift(item);
                localStorage.setItem(KEY, JSON.stringify(list.slice(0, MAX)));
            } catch (e) {}
        })();
    </script>
</x-app-layout>
