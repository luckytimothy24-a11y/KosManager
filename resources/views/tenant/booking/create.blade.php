<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/25 shrink-0">
                <i class="ri-calendar-check-line text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Buat Booking</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih kamar, atur periode & penghuni — konfirmasi instan.</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Booking Saya', 'url' => route('tenant.booking.index')],
            ['label' => 'Booking Baru'],
        ]" />
        <x-alert />

        <div class="max-w-4xl">
            <div class="card-surface p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute -top-28 -right-28 w-80 h-80 rounded-full bg-gradient-to-br from-orange-500/10 via-amber-500/10 to-transparent blur-3xl pointer-events-none"></div>
                @php $preselectedKamarId = old('kamar_id', request('kamar_id')); @endphp

                {{-- Pilih kos --}}
                <div class="relative">
                    <label for="kos-select" class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2"><i class="ri-building-2-line text-orange-500 mr-1"></i> Pilih Kos <span class="text-red-500">*</span></label>
                    <form method="GET" action="{{ route('tenant.booking.create') }}" id="kos-form">
                        <select id="kos-select" name="kos_id" required aria-label="Pilih kos"
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm py-3 focus:border-orange-500 focus:ring-orange-500 shadow-sm"
                                onchange="this.form.submit()">
                            <option value="">— Pilih Kos —</option>
                            @foreach($kos as $k)
                                <option value="{{ $k->id }}" {{ request('kos_id') == $k->id ? 'selected' : '' }}>{{ $k->name }} — {{ $k->address }}</option>
                            @endforeach
                        </select>
                    </form>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pilih kos dulu, lalu kamar akan muncul di bawah.</p>
                </div>

                <div class="mt-7 pt-7 border-t border-slate-100 dark:border-slate-800">
                    @if($kamar->count())
                        <form method="POST" action="{{ route('tenant.booking.store') }}" id="booking-form"
                              x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            <input type="hidden" name="kos_id" value="{{ request('kos_id') }}">
                            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                                <div class="lg:col-span-3 space-y-6">
                                    <div>
                                        <label for="kamar-select" class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2"><i class="ri-door-open-line text-orange-500 mr-1"></i> Kamar <span class="text-red-500">*</span></label>
                                        <select id="kamar-select" name="kamar_id" required
                                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-orange-500 focus:ring-orange-500 py-3 shadow-sm">
                                            <option value="">— Pilih Kamar —</option>
                                            @foreach($kamar as $k)
                                                <option value="{{ $k->id }}"
                                                        data-room="{{ $k->room_number }} — {{ $k->room_name }}"
                                                        data-monthly="{{ $k->monthly_price }}"
                                                        data-weekly="{{ $k->weekly_price }}"
                                                        data-daily="{{ $k->daily_price }}"
                                                        data-billing-mode="{{ $k->billing_mode ?? 'personal' }}"
                                                        data-capacity="{{ $k->capacity }}"
                                                        data-photo="{{ $k->photo ?? '' }}"
                                                        {{ (string) $preselectedKamarId === (string) $k->id ? 'selected' : '' }}>
                                                    {{ $k->room_number }} — {{ $k->room_name }} ({{ $k->room_type }}) · Rp {{ number_format($k->monthly_price, 0, ',', '.') }}/bulan
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kamar_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror

                                        {{-- Foto kamar (dynamic) --}}
                                        <div id="room-photo-wrap" class="mt-4 relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 via-orange-50 to-amber-50 dark:from-slate-800 dark:via-slate-800 dark:to-slate-700 aspect-[4/3] hidden shadow-sm border border-slate-100 dark:border-slate-700">
                                            <img id="room-photo-img" src="" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'">
                                            <span id="room-photo-letter" class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-black uppercase text-orange-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true"></span>
                                        </div>
                                    </div>

                                    <fieldset>
                                        <legend class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-3"><i class="ri-time-line text-orange-500 mr-1"></i> Tipe Sewa <span class="text-red-500">*</span></legend>
                                        <div class="grid grid-cols-3 gap-3">
                                            <label class="group flex flex-col items-center gap-2 px-3 py-4 rounded-2xl border-2 cursor-pointer transition bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-orange-300 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:dark:bg-orange-500/10 has-[:checked]:shadow-sm text-center">
                                                <input type="radio" name="rental_type" value="monthly" {{ old('rental_type', 'monthly') === 'monthly' ? 'checked' : '' }} required
                                                       class="sr-only">
                                                <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-100 dark:bg-slate-700 group-has-[:checked]:bg-orange-500 group-has-[:checked]:text-white transition"><i class="ri-calendar-line"></i></span>
                                                <span class="block text-xs font-black text-slate-900 dark:text-white">Bulanan</span>
                                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">per bulan</span>
                                            </label>
                                             <label class="group flex flex-col items-center gap-2 px-3 py-4 rounded-2xl border-2 cursor-pointer transition bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-orange-300 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:dark:bg-orange-500/10 has-[:checked]:shadow-sm text-center">
                                                 <input type="radio" name="rental_type" value="weekly" {{ old('rental_type') === 'weekly' ? 'checked' : '' }}
                                                        class="sr-only">
                                                 <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-100 dark:bg-slate-700 group-has-[:checked]:bg-orange-500 group-has-[:checked]:text-white transition"><i class="ri-calendar-check-line"></i></span>
                                                 <span class="block text-xs font-black text-slate-900 dark:text-white">Mingguan</span>
                                                 <span class="block text-[11px] text-slate-500 dark:text-slate-400">per minggu</span>
                                             </label>
                                            <label class="group flex flex-col items-center gap-2 px-3 py-4 rounded-2xl border-2 cursor-pointer transition bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-orange-300 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:dark:bg-orange-500/10 has-[:checked]:shadow-sm text-center">
                                                <input type="radio" name="rental_type" value="daily" {{ old('rental_type') === 'daily' ? 'checked' : '' }}
                                                       class="sr-only">
                                                <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-100 dark:bg-slate-700 group-has-[:checked]:bg-orange-500 group-has-[:checked]:text-white transition"><i class="ri-sun-line"></i></span>
                                                <span class="block text-xs font-black text-slate-900 dark:text-white">Harian</span>
                                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">per hari</span>
                                            </label>
                                        </div>
                                    </fieldset>

                                    {{-- Jumlah penghuni --}}
                                    <div id="occupant-field" class="rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 p-4">
                                        <label for="occupant-count" class="block text-[11px] font-black tracking-widest uppercase text-slate-600 dark:text-slate-300 mb-2"><i class="ri-team-line text-orange-500 mr-1"></i> Berapa orang yang akan tinggal? <span class="text-red-500">*</span></label>
                                        <p id="occupant-help" class="text-xs text-slate-500 dark:text-slate-400 mb-3 leading-relaxed">Kamar bisa diisi bersama — atur jumlah penghuni, tagihan bisa patungan jika kos mendukung.</p>
                                        <div class="flex items-center gap-3">
                                            <button type="button" id="occupant-minus" aria-label="Kurangi jumlah penghuni" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm"><i class="ri-subtract-line"></i></button>
                                            <input type="number" id="occupant-count" name="requested_occupants" value="{{ old('requested_occupants', 1) }}" min="1" max="10" required inputmode="numeric"
                                                   aria-describedby="occupant-help occupant-capacity occupant-invite-help"
                                                   class="w-24 text-center rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-base font-black py-3 focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                            <button type="button" id="occupant-plus" aria-label="Tambah jumlah penghuni" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20 hover:from-orange-600 hover:to-orange-700"><i class="ri-add-line"></i></button>
                                            <span id="occupant-suffix" class="text-sm font-bold text-slate-600 dark:text-slate-300">orang</span>
                                        </div>
                                        <p id="occupant-capacity" class="mt-2 text-xs font-medium text-slate-500 dark:text-slate-400"></p>
                                        <p id="occupant-invite-help" class="mt-3 hidden rounded-xl border border-orange-100 dark:border-orange-500/20 bg-orange-50 dark:bg-orange-500/10 px-3 py-2.5 text-xs leading-relaxed text-orange-700 dark:text-orange-300"><i class="ri-mail-send-line mr-1"></i> Setelah booking jadi, kamu bisa undang teman lewat link undangan.</p>

                                        @if($selectedKos && $selectedKos->phone)
                                            <div class="mt-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-4">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="ri-customer-service-2-line text-orange-500"></i>
                                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Tanya Owner?</span>
                                                </div>
                                                <a href="{{ route('tenant.messages.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300">
                                                    <i class="ri-message-3-line"></i> Hubungi Owner
                                                </a>
                                            </div>
                                        @endif
                                         @error('requested_occupants') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                                     </div>

                                     {{-- Identitas Tenant (Wajib) --}}
                                     <div class="rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 p-4">
                                         <h3 class="text-xs font-bold text-slate-900 dark:text-white mb-3"><i class="ri-shield-keyhole-line text-orange-500 mr-1"></i> Identitas Tenant (Wajib)</h3>
                                         <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 leading-relaxed">
                                             Data KTP dan WhatsApp diperlukan untuk proses verifikasi owner saat check-in. Data hanya digunakan untuk keperluan administrasi kos ini.
                                         </p>
                                         <div class="grid grid-cols-1 gap-4">
                                             <div>
                                                 <label for="identity_number" class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2"><i class="ri-pass-valid-line text-orange-500 mr-1"></i> Nomor KTP (NIK) <span class="text-red-500">*</span></label>
                                                 <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number') }}" required inputmode="numeric" maxlength="50" placeholder="16 digit — contoh 3201xxxxxxxxxxxx"
                                                        class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm px-4 py-3 focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                                 @error('identity_number') <p class="text-xs font-medium text-red-500 mt-1.5">{{ $message }}</p> @enderror
                                             </div>
                                             <div>
                                                 <label for="phone" class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2"><i class="ri-whatsapp-line text-emerald-500 mr-1"></i> WhatsApp <span class="text-red-500">*</span></label>
                                                 <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel" maxlength="20" placeholder="0812xxxxxxx"
                                                        class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm px-4 py-3 focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                                 <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500"><i class="ri-information-line mr-1"></i> Bisa +62/62 atau 08 — otomatis dinormalisasi.</p>
                                                 @error('phone') <p class="text-xs font-medium text-red-500 mt-1.5">{{ $message }}</p> @enderror
                                             </div>
                                             <div>
                                                 <label for="address" class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2"><i class="ri-map-pin-line text-orange-500 mr-1"></i> Alamat Lengkap <span class="text-red-500">*</span></label>
                                                 <textarea id="address" name="address" rows="3" required maxlength="500" placeholder="Alamat domisili saat ini — jalan, RT/RW, kelurahan"
                                                           class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm px-4 py-3 focus:border-orange-500 focus:ring-orange-500 shadow-sm">{{ old('address') }}</textarea>
                                                 @error('address') <p class="text-xs font-medium text-red-500 mt-1.5">{{ $message }}</p> @enderror
                                             </div>
                                         </div>
                                     </div>

                                     {{-- Pembayaran kamar --}}
                                    <fieldset id="billing-mode-fieldset" class="hidden">
                                        <legend class="block text-[11px] font-black tracking-widest uppercase text-slate-600 dark:text-slate-300 mb-3"><i class="ri-hand-coin-line text-orange-500 mr-1"></i> Pembayaran kamar <span class="text-red-500">*</span></legend>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <label class="group flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-orange-300 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:dark:bg-orange-500/10">
                                                <input type="radio" name="billing_mode" value="personal" {{ old('billing_mode', 'personal') === 'personal' ? 'checked' : '' }}
                                                       class="mt-1 text-orange-600 focus:ring-orange-500 border-slate-300 dark:border-slate-600">
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">Saya tanggung sendiri</span>
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400 leading-relaxed mt-0.5">Full tagihan di kamu.</span>
                                                </span>
                                            </label>
                                            <label class="group flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-orange-300 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50 has-[:checked]:dark:bg-orange-500/10">
                                                <input type="radio" name="billing_mode" value="shared" {{ old('billing_mode') === 'shared' ? 'checked' : '' }}
                                                       class="mt-1 text-orange-600 focus:ring-orange-500 border-slate-300 dark:border-slate-600">
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-bold text-slate-900 dark:text-white">Patungan</span>
                                                    <span class="block text-xs text-slate-500 dark:text-slate-400 leading-relaxed mt-0.5">Biaya dibagi jumlah penghuni.</span>
                                                </span>
                                            </label>
                                        </div>
                                        <p id="billing-mode-help" class="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed hidden"></p>
                                        @error('billing_mode') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                                    </fieldset>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start-date" class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                                            <input type="date" id="start-date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" min="{{ now()->toDateString() }}" required
                                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-orange-500 focus:ring-orange-500 py-3 shadow-sm">
                                            @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="end-date" class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2">Tanggal Selesai <span class="text-red-500">*</span></label>
                                            <input type="date" id="end-date" name="end_date" value="{{ old('end_date', now()->addMonth()->toDateString()) }}" required
                                                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-orange-500 focus:ring-orange-500 py-3 shadow-sm">
                                            @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="notes-input" class="block text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-2">Catatan (opsional)</label>
                                        <textarea id="notes-input" name="notes" rows="3" placeholder="Pesan untuk pemilik kos — mis. jam kedatangan, kebutuhan khusus"
                                                  class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">{{ old('notes') }}</textarea>
                                    </div>

                                    <div class="flex items-center justify-end gap-3 pt-2">
                                        <a href="{{ route('tenant.kos.show', ['kos' => request('kos_id')]) }}"
                                           class="px-5 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm">Batal</a>
                                        <button type="submit" :disabled="submitting"
                                                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-black text-white bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl hover:from-orange-600 hover:to-orange-700 active:scale-[0.98] transition shadow-lg shadow-orange-500/25 disabled:opacity-60 disabled:cursor-not-allowed">
                                            <template x-if="!submitting">
                                                <span class="flex items-center gap-2"><i class="ri-calendar-check-line"></i> Konfirmasi Booking</span>
                                            </template>
                                            <template x-if="submitting" x-cloak>
                                                <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Memproses...</span>
                                            </template>
                                        </button>
                                    </div>
                                </div>

                                {{-- Review / Ringkasan + Trust --}}
                                <aside class="lg:col-span-2">
                                    <div class="rounded-[24px] border border-orange-100 dark:border-orange-500/20 bg-gradient-to-br from-orange-50 via-white to-amber-50/50 dark:from-slate-800 dark:via-slate-800 dark:to-orange-950/10 p-5 lg:sticky lg:top-20 space-y-5 shadow-sm">
                                        <h2 class="flex items-center gap-2 text-[11px] font-black tracking-widest uppercase text-orange-600 dark:text-orange-400"><i class="ri-file-list-3-line"></i> Review Booking</h2>
                                        <dl class="space-y-3 text-sm">
                                            @if($selectedKos)
                                                <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 via-orange-50 to-amber-50 dark:from-slate-700 dark:via-slate-800 dark:to-slate-700 aspect-[16/9] border border-orange-100 dark:border-slate-700 shadow-sm">
                                                    @if($selectedKos->photo)
                                                        <img src="{{ asset('storage/' . $selectedKos->photo) }}" alt="Foto {{ $selectedKos->name }}"
                                                             class="absolute inset-0 w-full h-full object-cover"
                                                             loading="lazy" onerror="this.style.display='none'">
                                                    @endif
                                                    <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-black uppercase text-orange-900/[0.08] dark:text-white/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($selectedKos->name, 0, 1) }}</span>
                                                </div>

                                                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-orange-100 dark:border-orange-500/15 p-4 space-y-3 shadow-sm">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="text-slate-500 dark:text-slate-400 shrink-0 text-xs font-bold uppercase tracking-wide">Kos</dt>
                                                        <dd class="font-black text-slate-900 dark:text-white text-right text-sm">{{ $selectedKos->name }}</dd>
                                                    </div>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="text-slate-500 dark:text-slate-400 shrink-0 text-xs font-bold uppercase tracking-wide">Alamat</dt>
                                                        <dd class="font-semibold text-slate-700 dark:text-slate-200 text-right leading-snug text-xs">
                                                            {{ $selectedKos->address }}
                                                            @if($selectedKos->googleMapsDirectionsUrl())
                                                                <x-kos-directions-button :kos="$selectedKos" variant="secondary" size="sm" label="Arah ke Kos" />
                                                            @endif
                                                        </dd>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0 text-xs">Kamar</dt>
                                                <dd id="sum-kamar" class="font-bold text-slate-900 dark:text-white text-right text-sm">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0 text-xs">Penghuni</dt>
                                                <dd id="sum-occupants" class="font-bold text-slate-900 dark:text-white text-right text-sm">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0 text-xs">Pembayaran</dt>
                                                <dd id="sum-billing" class="font-bold text-slate-900 dark:text-white text-right text-sm">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0 text-xs">Tipe sewa</dt>
                                                <dd id="sum-type" class="font-bold text-slate-900 dark:text-white text-right text-sm">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-slate-500 dark:text-slate-400 shrink-0 text-xs">Periode</dt>
                                                <dd id="sum-periode" class="font-bold text-slate-900 dark:text-white text-right text-xs">—</dd>
                                            </div>
                                            <p id="sum-occupant-help" class="hidden text-[11px] text-orange-700 dark:text-orange-300 leading-relaxed rounded-xl border border-orange-100 dark:border-orange-500/20 bg-white dark:bg-slate-900 px-3 py-2"></p>
                                            <div class="pt-4 border-t border-orange-100 dark:border-orange-500/15">
                                                <div class="flex items-center justify-between gap-3">
                                                    <dt class="text-slate-600 dark:text-slate-400 font-bold text-xs uppercase tracking-wide">Total</dt>
                                                    <dd id="sum-total" class="text-xl font-black text-orange-600 dark:text-orange-400">—</dd>
                                                </div>
                                                <p id="sum-total-note" class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed hidden"></p>
                                            </div>
                                        </dl>

                                        <div class="pt-5 border-t border-orange-100 dark:border-orange-500/15">
                                            <h4 class="text-[11px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400 mb-3"><i class="ri-shield-check-line text-emerald-500 mr-1"></i> Yang Kamu Dapatkan</h4>
                                            <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                                <li class="flex items-center gap-2.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-emerald-500 text-white shrink-0"><i class="ri-check-line text-xs"></i></span> Booking langsung terkonfirmasi</li>
                                                <li class="flex items-center gap-2.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-emerald-500 text-white shrink-0"><i class="ri-check-line text-xs"></i></span> Kamar langsung diamankan</li>
                                                <li class="flex items-center gap-2.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-emerald-500 text-white shrink-0"><i class="ri-check-line text-xs"></i></span> Tagihan otomatis & transparan</li>
                                                <li class="flex items-center gap-2.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 shrink-0"><i class="ri-customer-service-2-line text-xs"></i></span> Check-in dibantu pengelola</li>
                                            </ul>
                                        </div>

                                        @if($selectedKos && $selectedKos->phone)
                                            <div class="pt-4 border-t border-orange-100 dark:border-orange-500/15">
                                                <a href="{{ route('tenant.messages.index') }}" class="inline-flex items-center justify-center gap-2 w-full text-sm font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 bg-orange-50 dark:bg-orange-500/10 px-4 py-2.5 rounded-xl transition">
                                                    <i class="ri-message-3-line"></i> Hubungi Owner
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </aside>
                            </div>
                        </form>
                    @elseif(request('kos_id'))
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 p-10 text-center">
                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-white dark:bg-slate-700 shadow-sm text-slate-400"><i class="ri-home-smile-line text-2xl"></i></span>
                            <h3 class="mt-4 font-black text-slate-900 dark:text-white">Tidak ada kamar tersedia</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kos ini belum memiliki kamar kosong saat ini.</p>
                            <a href="{{ route('tenant.kos.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-bold shadow-sm">
                                <i class="ri-search-eye-line text-orange-500"></i> Cari kos lain
                            </a>
                        </div>
                    @else
                        <div class="rounded-2xl border-2 border-dashed border-orange-200 dark:border-orange-500/20 bg-orange-50/50 dark:bg-orange-500/5 p-10 text-center">
                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/20"><i class="ri-arrow-up-line text-2xl"></i></span>
                            <h3 class="mt-4 font-black text-slate-900 dark:text-white">Pilih kos terlebih dahulu</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pilih kos di atas untuk melihat kamar yang tersedia & lanjut booking.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

     @if($kamar->count())
        <script>
            (function () {
                var fmt = function (n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); };
                var roomSel = document.getElementById('kamar-select');
                var startIn = document.getElementById('start-date');
                var endIn = document.getElementById('end-date');
                if (!roomSel) return;

                var occupantInput = document.getElementById('occupant-count');
                var occupantMinus = document.getElementById('occupant-minus');
                var occupantPlus = document.getElementById('occupant-plus');
                var occupantCapacity = document.getElementById('occupant-capacity');
                var occupantInviteHelp = document.getElementById('occupant-invite-help');
                var elKamar = document.getElementById('sum-kamar');
                var elOccupants = document.getElementById('sum-occupants');
                var elBilling = document.getElementById('sum-billing');
                var elType = document.getElementById('sum-type');
                var elPeriode = document.getElementById('sum-periode');
                var elTotal = document.getElementById('sum-total');
                var elOccupantHelp = document.getElementById('sum-occupant-help');
                var elTotalNote = document.getElementById('sum-total-note');
                var roomPhotoWrap = document.getElementById('room-photo-wrap');
                var roomPhotoImg = document.getElementById('room-photo-img');
                var roomPhotoLetter = document.getElementById('room-photo-letter');
                var billingFieldset = document.getElementById('billing-mode-fieldset');
                var billingHelp = document.getElementById('billing-mode-help');

                function daysBetween(a, b) {
                    var d1 = new Date(a + 'T00:00:00');
                    var d2 = new Date(b + 'T00:00:00');
                    return Math.round((d2 - d1) / 86400000);
                }
                function clampOccupant() {
                    var v = parseInt(occupantInput.value, 10) || 1;
                    var cap = parseInt((roomSel.options[roomSel.selectedIndex] || {}).dataset.capacity || '10', 10) || 10;
                    if (v < 1) v = 1; if (v > cap) v = cap; if (v > 10) v = 10;
                    occupantInput.value = v; return v;
                }
                function billingLabel(val) { return val === 'shared' ? 'Patungan' : 'Tanggung sendiri'; }
                function updateOccupantUI() {
                    var opt = roomSel.options[roomSel.selectedIndex];
                    var cap = opt && opt.value ? parseInt(opt.dataset.capacity || '1', 10) || 1 : 1;
                    var n = clampOccupant();
                    if (!opt || !opt.value) { occupantCapacity.textContent = ''; occupantInviteHelp.classList.add('hidden'); occupantInput.max = 10; return; }
                    occupantInput.max = Math.min(cap, 10);
                    occupantCapacity.textContent = cap > 1 ? 'Kamar ini untuk hingga ' + cap + ' orang.' : 'Kamar ini untuk 1 orang.';
                    if (n > 1 && cap > 1) occupantInviteHelp.classList.remove('hidden'); else occupantInviteHelp.classList.add('hidden');
                }

                function updateBillingMode() {
                    if (!billingFieldset) return;
                    var opt = roomSel.options[roomSel.selectedIndex];
                    if (!opt || !opt.value) {
                        billingFieldset.classList.add('hidden');
                        return;
                    }
                    var bm = (opt.dataset.billingMode || 'personal').toLowerCase();
                    if (bm === 'shared') {
                        billingFieldset.classList.remove('hidden');
                        if (billingHelp) {
                            billingHelp.textContent = 'Kos ini mendukung patungan. Pilih Patungan jika biaya ingin dibagi penghuni.';
                            billingHelp.classList.remove('hidden');
                        }
                    } else {
                        billingFieldset.classList.add('hidden');
                        var personal = billingFieldset.querySelector('input[value="personal"]');
                        if (personal) personal.checked = true;
                        if (billingHelp) billingHelp.classList.add('hidden');
                    }
                }

                function update() {
                    var opt = roomSel.options[roomSel.selectedIndex];
                    if (!opt || !opt.value) {
                        elKamar.textContent = '—';
                        if (elOccupants) elOccupants.textContent = '—';
                        if (elBilling) elBilling.textContent = '—';
                        elType.textContent = '—';
                        elPeriode.textContent = '—';
                        elTotal.textContent = '—';
                        if (elOccupantHelp) elOccupantHelp.classList.add('hidden');
                        if (elTotalNote) elTotalNote.classList.add('hidden');
                        roomPhotoWrap.classList.add('hidden');
                        updateBillingMode(); updateOccupantUI(); return;
                    }
                    var rentalType = document.querySelector('input[name="rental_type"]:checked').value;
                    var price = Number(opt.dataset[rentalType] || 0);
                    var unitLabels = { daily: '/hari', weekly: '/mgg', monthly: '/bulan' };
                    var typeLabels = { daily: 'Harian', weekly: 'Mingguan', monthly: 'Bulanan' };
                    var unit = unitLabels[rentalType] || '/bulan';

                    var n = clampOccupant();
                    var billingVal = (document.querySelector('input[name="billing_mode"]:checked') || {}).value || 'personal';
                    var cap = parseInt(opt.dataset.capacity || '1', 10) || 1;
                    elKamar.textContent = opt.dataset.room || ('Kamar #' + opt.value);
                    if (elOccupants) elOccupants.textContent = n + ' orang' + (n === 1 ? '' : ' · ' + cap + ' maks');
                    if (elBilling) elBilling.textContent = billingLabel(billingVal);
                    elType.textContent = (typeLabels[rentalType] || 'Bulanan') + ' · ' + fmt(price) + unit;

                    var s = startIn.value, e = endIn.value;
                    var total = 0;
                    if (s && e && s <= e) {
                        var inclusiveDays = daysBetween(s, e) + 1;
                        elPeriode.textContent = s + ' s/d ' + e + ' (' + inclusiveDays + ' hari)';
                        if (rentalType === 'daily') total = price * inclusiveDays;
                        else if (rentalType === 'weekly') total = price * Math.ceil(inclusiveDays / 7);
                        else { var sd = new Date(s + 'T00:00:00'), ed = new Date(e + 'T00:00:00'); total = price * ( (ed.getFullYear() - sd.getFullYear()) * 12 + (ed.getMonth() - sd.getMonth()) + 1 ); }
                    } else elPeriode.textContent = 'Tanggal tidak valid';
                    elTotal.textContent = total > 0 ? fmt(total) : (price > 0 ? fmt(price) : 'Hubungi pemilik');
                    if (elOccupantHelp) {
                        if (n > 1) { elOccupantHelp.textContent = 'Kamu bisa mengundang teman setelah booking jadi.'; elOccupantHelp.classList.remove('hidden'); }
                        else { elOccupantHelp.textContent = 'Booking untuk 1 orang.'; elOccupantHelp.classList.remove('hidden'); }
                    }
                    if (elTotalNote) {
                        if (billingVal === 'shared' && n > 1) { elTotalNote.textContent = 'Total akan dibagi sesuai jumlah penghuni.'; elTotalNote.classList.remove('hidden'); }
                        else if (billingVal === 'shared') { elTotalNote.textContent = 'Siap dibagi jika undang penghuni lain.'; elTotalNote.classList.remove('hidden'); }
                        else { elTotalNote.textContent = 'Total periode ini — dibayar sesuai tagihan.'; elTotalNote.classList.remove('hidden'); }
                    }

                    var photo = opt.dataset.photo;
                    var roomLabel = (opt.dataset.room || '').split(' — ')[0] || '';
                    if (photo) {
                        roomPhotoImg.src = '{{ asset("storage/") }}/' + photo;
                        roomPhotoImg.alt = 'Foto Kamar ' + roomLabel;
                        roomPhotoImg.style.display = '';
                        roomPhotoLetter.textContent = '';
                        roomPhotoWrap.classList.remove('hidden');
                    } else {
                        roomPhotoImg.src = '';
                        roomPhotoImg.style.display = 'none';
                        roomPhotoLetter.textContent = roomLabel.substring(0, 2);
                        roomPhotoWrap.classList.remove('hidden');
                    }
                    updateBillingMode(); updateOccupantUI();
                }

                if (occupantMinus) occupantMinus.addEventListener('click', function(){ var v=Math.max(1,(parseInt(occupantInput.value,10)||1)-1); occupantInput.value=v; updateOccupantUI(); update(); });
                if (occupantPlus) occupantPlus.addEventListener('click', function(){ var cap=parseInt((roomSel.options[roomSel.selectedIndex]||{}).dataset.capacity||'10',10)||10; var v=Math.min(cap,(parseInt(occupantInput.value,10)||1)+1); occupantInput.value=v; updateOccupantUI(); update(); });
                if (occupantInput) { occupantInput.addEventListener('change', function(){ updateOccupantUI(); update(); }); occupantInput.addEventListener('input', function(){ updateOccupantUI(); update(); }); }
                [roomSel, startIn, endIn].forEach(function (el) { el.addEventListener('change', update); el.addEventListener('input', update); });
                Array.prototype.forEach.call(document.querySelectorAll('input[name="rental_type"]'), function (el) { el.addEventListener('change', update); });
                Array.prototype.forEach.call(document.querySelectorAll('input[name="billing_mode"]'), function (el) { el.addEventListener('change', update); });
                roomSel.addEventListener('change', function(){ updateOccupantUI(); updateBillingMode(); update(); });
                update(); updateBillingMode(); updateOccupantUI();
            })();
        </script>
    @endif
</x-app-layout>
