<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <x-alert />

        <div class="flex items-center gap-3">
            <a href="{{ route('owner.advertising.index') }}" class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Buat Kampanye Iklan</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Promosikan kos Anda, atau jadilah advertiser partner pihak ketiga.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('owner.advertising.store') }}"
              x-data="adCampaignBuilder(@json($kosPreview), @json($packagesPreview), @json($placements))"
              class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 space-y-6">
            @csrf
            <input type="hidden" name="ad_type" :value="mode">

            {{-- Mode tabs --}}
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="setMode('kos')"
                        class="rounded-2xl border-2 p-4 text-left transition text-sm"
                        :class="mode === 'kos' ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-500/10 ring-2 ring-primary-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-primary-300'">
                    <span class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                        <i class="ri-building-2-line text-primary-500"></i> Iklan Kos
                    </span>
                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Promosikan kos milik Anda agar lebih mudah ditemukan.</span>
                </button>
                <button type="button" @click="setMode('partner')"
                        class="rounded-2xl border-2 p-4 text-left transition text-sm"
                        :class="mode === 'partner' ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300'">
                    <span class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                        <i class="ri-megaphone-line text-indigo-500"></i> Iklan Partner (Pihak Ketiga)
                    </span>
                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Jangkau penghuni kos sebagai advertiser: WiFi, laundry, furniture, dan lainnya.</span>
                </button>
            </div>

            {{-- Kos tab --}}
            <div x-show="mode === 'kos'" x-cloak>
                <div>
                    <label for="kos_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Pilih Kos</label>
                    @if($kosList->isEmpty())
                        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-6 text-center">
                            <i class="ri-building-2-line text-3xl text-slate-300 dark:text-slate-600"></i>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Anda belum memiliki kos. Silakan tambahkan kos terlebih dahulu.</p>
                            <a href="{{ route('owner.kos.create') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 px-4 py-2.5 rounded-xl transition">
                                <i class="ri-add-line"></i> Tambah Kos
                            </a>
                        </div>
                    @else
                        <select name="kos_id" id="kos_id" x-model="selectedKos" :required="mode === 'kos'"
                                class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">-- Pilih Kos --</option>
                            @foreach($kosList as $kos)
                                <option value="{{ $kos->id }}" {{ old('kos_id') == $kos->id ? 'selected' : '' }}>
                                    {{ $kos->name }} — {{ $kos->address }}
                                </option>
                            @endforeach
                        </select>
                        @error('kos_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            {{-- Partner tab --}}
            <div x-show="mode === 'partner'" x-cloak class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="advertiser_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Nama Advertiser/Brand <span class="text-red-500">*</span></label>
                        <input type="text" name="advertiser_name" id="advertiser_name" x-model="partner.advertiser_name" :required="mode === 'partner'" value="{{ old('advertiser_name') }}" maxlength="120" placeholder="cth: Demo Partner WiFi"
                               class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('advertiser_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="advertiser_logo" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Logo URL (opsional)</label>
                        <input type="url" name="advertiser_logo" id="advertiser_logo" x-model="partner.advertiser_logo" value="{{ old('advertiser_logo') }}" maxlength="255" placeholder="https://example.com/logo.png"
                               class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('advertiser_logo')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="headline" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Headline <span class="text-red-500">*</span></label>
                    <input type="text" name="headline" id="headline" x-model="partner.headline" :required="mode === 'partner'" value="{{ old('headline') }}" maxlength="190" placeholder="cth: WiFi Cepat Tanpa Ribet untuk Anak Kos"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('headline')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="advertiser_description" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Deskripsi Iklan</label>
                    <textarea name="advertiser_description" id="advertiser_description" x-model="partner.description" rows="3" maxlength="1000" placeholder="Jelaskan penawaran untuk penghuni kos."
                              class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('advertiser_description') }}</textarea>
                    @error('advertiser_description')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="image" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Banner/Creative URL (opsional)</label>
                        <input type="url" name="image" id="image" x-model="partner.image" value="{{ old('image') }}" maxlength="255" placeholder="https://example.com/banner.jpg"
                               class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('image')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="cta_label" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Teks Tombol (CTA)</label>
                        <input type="text" name="cta_label" id="cta_label" x-model="partner.cta" value="{{ old('cta_label', 'Lihat Penawaran') }}" maxlength="60" placeholder="Lihat Penawaran"
                               class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('cta_label')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="destination_url" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Destination URL <span class="text-red-500">*</span></label>
                    <input type="url" name="destination_url" id="destination_url" x-model="partner.destination" :required="mode === 'partner'" value="{{ old('destination_url') }}" maxlength="500" placeholder="https://example.com/promo"
                           class="w-full text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Pengguna diarahkan ke halaman ini setelah mengklik iklan.</p>
                    @error('destination_url')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Placement</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($placements as $value => $label)
                            <label class="cursor-pointer group">
                                <input type="radio" name="placement" value="{{ $value }}" x-model="partner.placement"
                                       class="peer sr-only" {{ old('placement', 'marketplace') === $value ? 'checked' : '' }}>
                                <div class="border-2 border-slate-200 dark:border-slate-700 rounded-xl p-3 text-center peer-checked:border-indigo-500 peer-checked:ring-2 peer-checked:ring-indigo-500/20 group-hover:border-indigo-300 transition">
                                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">{{ $label }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('placement')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Package selection (shared) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Pilih Paket</label>
                @if($packages->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-6 text-center">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Belum ada paket iklan tersedia.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($packages as $pkg)
                            <label class="cursor-pointer group">
                                <input type="radio" name="package_id" value="{{ $pkg->id }}" required
                                       x-model="selectedPackage"
                                       class="peer sr-only" {{ old('package_id') == $pkg->id ? 'checked' : '' }}>
                                <div class="border-2 border-slate-200 dark:border-slate-700 rounded-2xl p-5 peer-checked:border-primary-500 peer-checked:ring-2 peer-checked:ring-primary-500/20 group-hover:border-primary-300 transition h-full">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $pkg->name }}</span>
                                        @if($pkg->placement)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ \App\Support\AdvertisingLabels::placementBadge($pkg->placement) }}">
                                                {{ \App\Support\AdvertisingLabels::placementLabel($pkg->placement) }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-2xl font-black text-primary-600 dark:text-primary-400">Rp {{ number_format($pkg->price, 0, ',', '.') }}</p>
                                    <ul class="mt-4 space-y-2 text-xs text-slate-600 dark:text-slate-300">
                                        <li class="flex items-center gap-2"><i class="ri-time-line text-slate-400"></i> {{ $pkg->duration_days }} hari</li>
                                        @if($pkg->placement)
                                            <li class="flex items-center gap-2"><i class="ri-layout-grid-line text-indigo-400"></i> Placement {{ \App\Support\AdvertisingLabels::placementLabel($pkg->placement) }}</li>
                                        @else
                                            @if($pkg->is_featured)
                                                <li class="flex items-center gap-2"><i class="ri-star-fill text-amber-400"></i> Tampil Featured</li>
                                            @else
                                                <li class="flex items-center gap-2"><i class="ri-megaphone-line text-indigo-400"></i> Tampil Sponsored</li>
                                            @endif
                                            <li class="flex items-center gap-2"><i class="ri-home-line text-slate-400"></i> {{ $pkg->is_homepage ? 'Juga ditampilkan di Homepage' : 'Tampil di Marketplace' }}</li>
                                        @endif
                                    </ul>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('package_id')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            {{-- Live Preview Iklan --}}
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800"
                 x-show="(mode === 'kos' && previewKos) || (mode === 'partner' && (
                     partner.advertiser_name || partner.headline || partner.description || partner.cta || partner.destination
                 ))" x-cloak>
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                    <i class="ri-eye-line text-primary-500"></i> Preview Iklan
                </p>

                {{-- Kos preview --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-4" x-show="mode === 'kos'" x-cloak>
                    <div class="max-w-sm mx-auto bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
                        <div class="relative aspect-[16/10] bg-gradient-to-br from-slate-100 via-primary-50 to-blue-100 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700">
                            <template x-if="previewKos && previewKos.photo">
                                <img :src="previewKos.photo" :alt="'Foto ' + previewKos.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="previewKos && !previewKos.photo">
                                <div class="w-full h-full flex items-center justify-center text-6xl font-black uppercase text-primary-900/10 dark:text-white/10 select-none" aria-hidden="true">
                                    <span x-text="previewKos.initial"></span>
                                </div>
                            </template>
                            <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1.5 rounded-lg shadow-sm bg-white/95 dark:bg-slate-900/95 backdrop-blur text-green-700 dark:text-green-300">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                <span x-text="'TERSEDIA · ' + ((previewKos && previewKos.kamar_tersedia) || 0) + ' kamar'"></span>
                            </span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-slate-900 dark:text-white line-clamp-1" x-text="previewKos ? previewKos.name : ''"></h3>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 flex items-start gap-1 line-clamp-1">
                                <i class="ri-map-pin-2-fill mt-0.5 shrink-0 text-primary-500"></i>
                                <span x-text="previewKos ? previewKos.address : ''"></span>
                            </p>
                            <div class="mt-2.5 flex flex-wrap gap-1.5" x-show="previewKos && previewKos.facility_tags && previewKos.facility_tags.length">
                                <template x-for="tag in (previewKos ? previewKos.facility_tags : [])" :key="tag">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                                        <i class="ri-check-line text-green-500"></i> <span x-text="tag"></span>
                                    </span>
                                </template>
                            </div>
                            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-end justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mulai dari</p>
                                    <template x-if="previewKos && previewKos.harga_mulai">
                                        <p class="text-base font-black text-slate-900 dark:text-white leading-tight">
                                            Rp <span x-text="previewKos.harga_mulai.toLocaleString('id-ID')"></span><span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/bln</span>
                                        </p>
                                    </template>
                                    <template x-if="previewKos && !previewKos.harga_mulai && previewKos.harga_harian_mulai">
                                        <p class="text-base font-black text-slate-900 dark:text-white leading-tight">
                                            Rp <span x-text="previewKos.harga_harian_mulai.toLocaleString('id-ID')"></span><span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">/hr</span>
                                        </p>
                                    </template>
                                    <template x-if="(!previewKos) || (!previewKos.harga_mulai && !previewKos.harga_harian_mulai)">
                                        <p class="text-xs text-slate-400 dark:text-slate-500">Harga hubungi pemilik</p>
                                    </template>
                                </div>
                                <span class="shrink-0 inline-flex items-center gap-1.5 text-xs font-bold text-white bg-primary-500 px-3.5 py-2 rounded-xl">
                                    Lihat Kos <i class="ri-arrow-right-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Partner preview --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-4" x-show="mode === 'partner'" x-cloak>
                    <div class="max-w-md mx-auto">
                        <div class="group relative block overflow-hidden rounded-2xl border border-indigo-100 dark:border-indigo-500/20 bg-gradient-to-br from-white via-indigo-50/60 to-primary-50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900">
                            <template x-if="partner.image">
                                <div class="absolute inset-y-0 right-0 w-1/3 hidden sm:block">
                                    <img :src="partner.image" alt="" class="w-full h-full object-cover" onerror="this.style.display='none'">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white dark:from-slate-900 via-white/20 dark:via-slate-900/20 to-transparent"></div>
                                </div>
                            </template>
                            <div class="relative p-6 pr-6 sm:pr-28 flex flex-col justify-center min-h-[9rem]">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg bg-indigo-500 text-white">
                                        <i class="ri-megaphone-fill text-[10px]"></i> Promoted Partner
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                        <template x-if="partner.advertiser_logo">
                                            <img :src="partner.advertiser_logo" alt="" class="w-4 h-4 rounded object-cover" onerror="this.style.display='none'">
                                        </template>
                                        <span x-text="partner.advertiser_name || 'Nama Advertiser'"></span>
                                    </span>
                                </div>
                                <h3 class="mt-2 text-base sm:text-lg font-black text-slate-900 dark:text-white leading-snug line-clamp-2">
                                    <span x-text="partner.headline || 'Headline iklan Anda'"></span>
                                </h3>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                    <span x-text="partner.description || 'Deskripsi penawaran untuk penghuni kos.'"></span>
                                </p>
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <span class="inline-flex items-center gap-1.5 bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-xl">
                                        <span x-text="partner.cta || 'Lihat Penawaran'"></span> <i class="ri-arrow-right-line text-xs"></i>
                                    </span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">Iklan · Partner KosManager</span>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                            Placement: <span class="font-semibold text-indigo-600 dark:text-indigo-400" x-text="placementLabel"></span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('owner.advertising.index') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-5 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    Batal
                </a>
                <button type="submit" {{ $packages->isEmpty() ? 'disabled' : '' }}
                        class="inline-flex items-center gap-2 text-sm font-bold text-white bg-primary-500 hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed px-6 py-2.5 rounded-xl transition shadow-sm shadow-primary-500/30">
                    <i class="ri-megaphone-line"></i> Buat Kampanye
                </button>
            </div>
        </form>
    </div>

    <script>
        window.adCampaignBuilder = function (kosData, packageData, placementSelect) {
            return {
                kosData: kosData || {},
                packageData: packageData || [],
                placements: placementSelect || {},
                mode: '{{ old("ad_type", "kos") }}',
                selectedKos: '{{ old("kos_id") }}',
                selectedPackage: '{{ old("package_id") }}',
                partner: {
                    advertiser_name: '{{ old("advertiser_name") }}',
                    advertiser_logo: '{{ old("advertiser_logo") }}',
                    headline: '{{ old("headline") }}',
                    description: '{{ old("advertiser_description") }}',
                    image: '{{ old("image") }}',
                    cta: '{{ old("cta_label", "Lihat Penawaran") }}',
                    destination: '{{ old("destination_url") }}',
                    placement: '{{ old("placement", "marketplace") }}'
                },
                setMode(m) { this.mode = m; },
                get previewPackage() {
                    var self = this;
                    return this.packageData.find(function (p) { return String(p.id) === String(self.selectedPackage); }) || null;
                },
                get previewKos() {
                    return this.kosData[this.selectedKos] || null;
                },
                get placementLabel() {
                    var p = this.partner.placement;
                    var labels = {
                        homepage: 'homepage',
                        marketplace: 'marketplace',
                        detail: 'detail',
                        native: 'native'
                    };
                    return labels[p] || p;
                }
            };
        };
    </script>
</x-app-layout>