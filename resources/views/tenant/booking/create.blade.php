<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Buat Booking" description="Pilih kamar & atur periode — bayar untuk konfirmasi." icon="ri-calendar-check-line" />
    </x-slot>

    <div class="space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Booking Saya', 'url' => route('tenant.booking.index')],
            ['label' => 'Booking Baru'],
        ]" />
        <x-alert />

        <div class="max-w-4xl">
            <div class="card-surface p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute -top-28 -right-28 w-80 h-80 rounded-full bg-gradient-to-br from-primary-500/10 via-primary-500/10 to-transparent blur-3xl pointer-events-none"></div>
                @php $preselectedKamarId = old('kamar_id', request('kamar_id')); @endphp

                {{-- Pilih kos --}}
                <div class="relative">
                    <label for="kos-select" class="field-label"><i class="ri-building-2-line text-primary-500 mr-1"></i> Pilih Kos <span class="text-red-500">*</span></label>
                    <form method="GET" action="{{ route('tenant.booking.create') }}" id="kos-form">
                        <select id="kos-select" name="kos_id" required aria-label="Pilih kos"
                                class="field-input"
                                onchange="this.form.submit()">
                            <option value="">— Pilih Kos —</option>
                            @foreach($kos as $k)
                                <option value="{{ $k->id }}" {{ request('kos_id') == $k->id ? 'selected' : '' }}>{{ $k->name }} — {{ $k->address }}</option>
                            @endforeach
                        </select>
                    </form>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Pilih kos dulu, lalu kamar akan muncul di bawah.</p>
                </div>

                <div class="mt-7 pt-7 border-t border-slate-100 dark:border-white/10">
                    @if($kamar->count())
                        <form method="POST" action="{{ route('tenant.booking.store') }}" id="booking-form"
                              x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            <input type="hidden" name="kos_id" value="{{ request('kos_id') }}">
                            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                                <div class="lg:col-span-3 space-y-6">
                                    <div>
                                        <label for="kamar-select" class="field-label"><i class="ri-door-open-line text-primary-500 mr-1"></i> Kamar <span class="text-red-500">*</span></label>
                                        <select id="kamar-select" name="kamar_id" required
                                                class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm focus:border-primary-500 focus:ring-primary-500 py-3 shadow-sm">
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
                                        @error('kamar_id') <p class="text-red-500 text-xs mt-2" role="alert">{{ $message }}</p> @enderror

                                        {{-- Foto kamar (dynamic) --}}
                                        <div id="room-photo-wrap" class="mt-4 relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-primary-50 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated aspect-[4/3] hidden shadow-sm border border-slate-100 dark:border-white/[0.08]">
                                            <img id="room-photo-img" src="" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'">
                                            <span id="room-photo-letter" class="absolute inset-0 flex items-center justify-center text-[4rem] leading-none font-bold uppercase text-primary-900/[0.08] dark:text-slate-50/5 select-none pointer-events-none" aria-hidden="true"></span>
                                        </div>
                                    </div>

                                    <fieldset>
                                        <legend class="field-label mb-3"><i class="ri-time-line text-primary-500 mr-1"></i> Tipe Sewa <span class="text-red-500">*</span></legend>
                                        <div class="grid grid-cols-3 gap-3">
                                            <label class="group flex flex-col items-center gap-2 px-3 py-4 rounded-2xl border-2 cursor-pointer transition bg-white dark:bg-surface-elevated border-slate-200 dark:border-white/[0.08] hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 has-[:checked]:shadow-sm text-center">
                                                <input type="radio" name="rental_type" value="monthly" {{ old('rental_type', 'monthly') === 'monthly' ? 'checked' : '' }} required
                                                       class="sr-only">
                                                <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-100 dark:bg-surface-elevated group-has-[:checked]:bg-primary-500 group-has-[:checked]:text-white transition"><i class="ri-calendar-line"></i></span>
                                                <span class="block text-xs font-bold text-slate-900 dark:text-slate-50">Bulanan</span>
                                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">per bulan</span>
                                            </label>
                                             <label class="group flex flex-col items-center gap-2 px-3 py-4 rounded-2xl border-2 cursor-pointer transition bg-white dark:bg-surface-elevated border-slate-200 dark:border-white/[0.08] hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 has-[:checked]:shadow-sm text-center">
                                                 <input type="radio" name="rental_type" value="weekly" {{ old('rental_type') === 'weekly' ? 'checked' : '' }}
                                                        class="sr-only">
                                                 <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-100 dark:bg-surface-elevated group-has-[:checked]:bg-primary-500 group-has-[:checked]:text-white transition"><i class="ri-calendar-check-line"></i></span>
                                                 <span class="block text-xs font-bold text-slate-900 dark:text-slate-50">Mingguan</span>
                                                 <span class="block text-[11px] text-slate-500 dark:text-slate-400">per minggu</span>
                                             </label>
                                            <label class="group flex flex-col items-center gap-2 px-3 py-4 rounded-2xl border-2 cursor-pointer transition bg-white dark:bg-surface-elevated border-slate-200 dark:border-white/[0.08] hover:border-primary-300 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:dark:bg-primary-500/10 has-[:checked]:shadow-sm text-center">
                                                <input type="radio" name="rental_type" value="daily" {{ old('rental_type') === 'daily' ? 'checked' : '' }}
                                                       class="sr-only">
                                                <span class="grid w-9 h-9 place-items-center rounded-xl bg-slate-100 dark:bg-surface-elevated group-has-[:checked]:bg-primary-500 group-has-[:checked]:text-white transition"><i class="ri-sun-line"></i></span>
                                                <span class="block text-xs font-bold text-slate-900 dark:text-slate-50">Harian</span>
                                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">per hari</span>
                                            </label>
                                        </div>
                                    </fieldset>

                                    {{-- Satu kamar untuk satu penghuni — patungan dihapus --}}
                                    <div class="rounded-2xl border border-slate-100 dark:border-white/[0.08] bg-slate-50/50 dark:bg-surface-elevated/50 p-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="ri-user-line text-primary-500 mr-1"></i>
                                            <span class="text-xs font-semibold text-slate-900 dark:text-slate-50">Jumlah Penghuni</span>
                                        </div>
                                        <p class="text-sm text-slate-700 dark:text-slate-300">Satu kamar untuk <strong>satu penghuni</strong>. Patungan tidak lagi didukung.</p>
                                        <input type="hidden" name="requested_occupants" value="1">
                                        <input type="hidden" name="billing_mode" value="personal">

                                        @if($selectedKos && $selectedKos->phone)
                                            <div class="mt-4 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-surface-elevated p-4">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="ri-customer-service-2-line text-primary-500"></i>
                                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Tanya Owner?</span>
                                                </div>
                                                <a href="{{ route('tenant.kos.ask', $selectedKos) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300">
                                                    <i class="ri-message-3-line"></i> Hubungi Owner
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                     {{-- Identitas Tenant (Wajib) --}}
                                     <div class="rounded-2xl border border-slate-100 dark:border-white/[0.08] bg-slate-50/50 dark:bg-surface-elevated/50 p-4">
                                         <h3 class="text-xs font-bold text-slate-900 dark:text-slate-50 mb-3"><i class="ri-shield-keyhole-line text-primary-500 mr-1"></i> Identitas Tenant (Wajib)</h3>
                                         <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 leading-relaxed">
                                             Data KTP dan WhatsApp diperlukan untuk proses verifikasi owner saat check-in. Data hanya digunakan untuk keperluan administrasi kos ini.
                                         </p>
                                         <div class="grid grid-cols-1 gap-4">
                                             <div>
                                                 <label for="identity_number" class="field-label"><i class="ri-pass-valid-line text-primary-500 mr-1"></i> Nomor KTP (NIK) <span class="text-red-500">*</span></label>
                                                 <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number') }}" required inputmode="numeric" maxlength="50" placeholder="16 digit — contoh 3201xxxxxxxxxxxx"
                                                        class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                                                 @error('identity_number') <p class="text-xs font-medium text-red-500 mt-1.5" role="alert">{{ $message }}</p> @enderror
                                             </div>
                                             <div>
                                                 <label for="phone" class="field-label"><i class="ri-whatsapp-line text-emerald-500 mr-1"></i> WhatsApp <span class="text-red-500">*</span></label>
                                                 <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel" maxlength="20" placeholder="0812xxxxxxx"
                                                        class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                                                 <p class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400"><i class="ri-information-line mr-1"></i> Bisa +62/62 atau 08 — otomatis dinormalisasi.</p>
                                                 @error('phone') <p class="text-xs font-medium text-red-500 mt-1.5" role="alert">{{ $message }}</p> @enderror
                                             </div>
                                             <div>
                                                 <label for="address" class="field-label"><i class="ri-map-pin-line text-primary-500 mr-1"></i> Alamat Lengkap <span class="text-red-500">*</span></label>
                                                 <textarea id="address" name="address" rows="3" required maxlength="500" placeholder="Alamat domisili saat ini — jalan, RT/RW, kelurahan"
                                                           class="w-full rounded-xl border-slate-200 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-50 text-sm px-4 py-3 focus:border-primary-500 focus:ring-primary-500 shadow-sm">{{ old('address') }}</textarea>
                                                 @error('address') <p class="text-xs font-medium text-red-500 mt-1.5" role="alert">{{ $message }}</p> @enderror
                                             </div>
                                         </div>
                                     </div>

                                     {{-- Pembayaran kamar — selalu tagihan pribadi --}}
                                     <div class="rounded-2xl border border-slate-100 dark:border-white/[0.08] bg-slate-50/50 dark:bg-surface-elevated/50 p-4">
                                         <div class="flex items-center gap-2 mb-2">
                                             <i class="ri-hand-coin-line text-primary-500 mr-1"></i>
                                             <span class="text-xs font-semibold text-slate-900 dark:text-slate-50">Pembayaran</span>
                                         </div>
                                         <p class="text-sm text-slate-700 dark:text-slate-300">Seluruh biaya kamar menjadi tanggunganmu (tagihan pribadi).</p>
                                         <input type="hidden" name="billing_mode" value="personal">
                                     </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start-date" class="field-label">Tanggal Mulai <span class="text-red-500">*</span></label>
                                            {{-- Default hanya nilai awal — tenant dapat mengubah tanggal mulai --}}
                                            <input type="date" id="start-date" name="start_date"
                                                   value="{{ old('start_date', $defaultStartDate ?? now()->toDateString()) }}"
                                                   min="{{ now()->toDateString() }}" required
                                                   class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-800 dark:text-slate-100 text-sm py-3 shadow-sm focus:border-primary-500 focus:ring-primary-500 focus:ring-0"
                                                   data-default-start="{{ old('start_date', $defaultStartDate ?? now()->toDateString()) }}">
                                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Pilih tanggal mulai sewa. Tanggal selesai otomatis mengikuti tanggal mulai sesuai tipe sewa.</p>
                                            @error('start_date') <p class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="end-date" class="field-label">Tanggal Selesai <span class="text-red-500">*</span></label>
                                            <input type="date" id="end-date" name="end_date"
                                                   value="{{ old('end_date', $defaultEndDate ?? now()->addMonthNoOverflow()->toDateString()) }}"
                                                   min="{{ old('start_date', $defaultStartDate ?? now()->toDateString()) }}"
                                                   required
                                                   class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-surface-elevated text-slate-800 dark:text-slate-100 text-sm py-3 shadow-sm focus:border-primary-500 focus:ring-primary-500 focus:ring-0"
                                                   data-default-end="{{ old('end_date', $defaultEndDate ?? now()->addMonthNoOverflow()->toDateString()) }}">
                                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Mengikuti tanggal mulai. Tidak boleh sebelum tanggal mulai.</p>
                                            @error('end_date') <p class="text-red-500 text-xs mt-1" role="alert">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="notes-input" class="field-label">Catatan (opsional)</label>
                                        <textarea id="notes-input" name="notes" rows="3" placeholder="Pesan untuk pemilik kos — mis. jam kedatangan, kebutuhan khusus"
                                                  class="field-input resize-none">{{ old('notes') }}</textarea>
                                    </div>
                                </div>

                                {{-- Review / Ringkasan + Trust --}}
                                <aside class="lg:col-span-2">
                                    <div class="rounded-2xl border border-primary-100 dark:border-primary-500/20 bg-gradient-to-br from-primary-50 via-white to-primary-50/50 dark:from-slate-700 dark:via-slate-700 dark:to-primary-950/10 p-5 lg:sticky lg:top-20 space-y-5 shadow-sm">
                                                <h2 class="flex items-center gap-2 text-label text-primary-600 dark:text-primary-400"><i class="ri-file-list-3-line"></i> Review Booking</h2>
                                        <dl class="space-y-3 text-sm">
                                            @if($selectedKos)
                                                <div class="rounded-2xl bg-white dark:bg-surface-elevated border border-primary-100 dark:border-primary-500/15 p-4 space-y-3 shadow-sm">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <dt class="text-label font-semibold uppercase tracking-wide">Kos</dt>
                                                        <dd class="font-bold text-slate-900 dark:text-slate-50 text-right text-sm">{{ $selectedKos->name }}</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="text-label font-semibold uppercase tracking-wide">Alamat</dt>
                                                        <dd class="mt-1 text-right">
                                                            <span class="block font-semibold text-slate-700 dark:text-slate-200 leading-snug text-xs">{{ $selectedKos->address }}</span>
                                                            @if($selectedKos->googleMapsDirectionsUrl())
                                                                <div class="mt-2 flex justify-end">
                                                                    <x-kos-directions-button :kos="$selectedKos" variant="secondary" size="sm" label="Arah ke Kos" />
                                                                </div>
                                                            @endif
                                                        </dd>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-label shrink-0">Kamar</dt>
                                                <dd id="sum-kamar" class="font-bold text-slate-900 dark:text-slate-50 text-right text-sm">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-label shrink-0">Penghuni</dt>
                                                <dd id="sum-occupants" class="font-bold text-slate-900 dark:text-slate-50 text-right text-sm">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-label shrink-0">Pembayaran</dt>
                                                <dd id="sum-billing" class="font-bold text-slate-900 dark:text-slate-50 text-right text-sm">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-label shrink-0">Tipe sewa</dt>
                                                <dd id="sum-type" class="font-bold text-slate-900 dark:text-slate-50 text-right text-sm">—</dd>
                                            </div>
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-label shrink-0">Periode</dt>
                                                <dd id="sum-periode" class="font-bold text-slate-900 dark:text-slate-50 text-right text-xs">—</dd>
                                            </div>
                                            <p id="sum-occupant-help" class="hidden text-[11px] text-primary-700 dark:text-primary-300 leading-relaxed rounded-xl border border-primary-100 dark:border-primary-500/20 bg-white dark:bg-surface-elevated px-3 py-2"></p>
                                            <div class="pt-4 border-t border-primary-100 dark:border-primary-500/15">
                                                <div class="flex items-center justify-between gap-3">
                                                    <dt class="text-label font-semibold uppercase tracking-wide">Biaya sewa</dt>
                                                    <dd id="sum-total" class="text-xl font-semibold text-primary-600 dark:text-primary-400">—</dd>
                                                </div>
                                                <p id="sum-total-note" class="mt-2 text-meta leading-relaxed hidden"></p>
                                            </div>
                                        </dl>

                                        @if($selectedKos)
                                            <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 via-primary-50 to-primary-50 dark:from-surface-elevated dark:via-surface-elevated dark:to-surface-elevated aspect-[16/9] border border-primary-100 dark:border-white/[0.08] shadow-sm">
                                                @if($selectedKos->photo)
                                                    <img src="{{ asset('storage/' . $selectedKos->photo) }}" alt="Foto {{ $selectedKos->name }}"
                                                         class="absolute inset-0 w-full h-full object-cover"
                                                         loading="lazy" onerror="this.style.display='none'">
                                                @endif
                                                <span class="absolute inset-0 flex items-center justify-center text-[5rem] leading-none font-bold uppercase text-primary-900/[0.08] dark:text-slate-50/5 select-none pointer-events-none" aria-hidden="true">{{ mb_substr($selectedKos->name, 0, 1) }}</span>
                                            </div>
                                        @endif

                                        <div class="pt-5 border-t border-primary-100 dark:border-primary-500/15">
                                            <h4 class="text-label mb-3"><i class="ri-shield-check-line text-emerald-500 mr-1"></i> Yang Kamu Dapatkan</h4>
                                            <ul class="space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                                                <li class="flex items-center gap-2.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-emerald-500 text-white shrink-0"><i class="ri-check-line text-xs"></i></span> Booking menunggu pembayaran dan konfirmasi pengelola</li>
                                                <li class="flex items-center gap-2.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-emerald-500 text-white shrink-0"><i class="ri-check-line text-xs"></i></span> Kamar langsung diamankan (reservasi)</li>
                                                <li class="flex items-center gap-2.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-emerald-500 text-white shrink-0"><i class="ri-check-line text-xs"></i></span> Pembayaran dilakukan setelah booking dibuat</li>
                                                <li class="flex items-center gap-2.5"><span class="grid w-6 h-6 place-items-center rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 shrink-0"><i class="ri-customer-service-2-line text-xs"></i></span> Check-in diproses pengelola saat kedatangan</li>
                                            </ul>
                                        </div>

                                        @if($selectedKos && $selectedKos->phone)
                                            <div class="pt-4 border-t border-primary-100 dark:border-primary-500/15">
                                                <a href="{{ route('tenant.kos.ask', $selectedKos) }}" class="inline-flex items-center justify-center gap-2 w-full text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 bg-primary-50 dark:bg-primary-500/10 px-4 py-2.5 rounded-xl transition">
                                                    <i class="ri-message-3-line"></i> Hubungi Owner
                                                </a>
                                            </div>
                                        @endif

                                        <div class="pt-4 border-t border-primary-100 dark:border-primary-500/15 space-y-3">
                                            <button type="submit" :disabled="submitting"
                                                    class="btn-primary w-full py-3">
                                                <template x-if="!submitting">
                                                    <span class="flex items-center gap-2"><i class="ri-calendar-check-line"></i> Konfirmasi Booking</span>
                                                </template>
                                                <template x-if="submitting" x-cloak>
                                                    <span class="flex items-center gap-2"><i class="ri-loader-4-line animate-spin"></i> Memproses...</span>
                                                </template>
                                            </button>
                                            <a href="{{ route('tenant.kos.show', ['kos' => request('kos_id')]) }}"
                                               class="btn-secondary w-full">Batal</a>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </form>
                    @elseif(request('kos_id'))
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-surface-elevated/30 p-10 text-center">
                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-white dark:bg-surface-elevated shadow-sm text-slate-400"><i class="ri-home-smile-line text-2xl"></i></span>
                            <h3 class="mt-4 font-bold text-slate-900 dark:text-slate-50">Tidak ada kamar tersedia</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kos ini belum memiliki kamar kosong saat ini.</p>
                            <a href="{{ route('tenant.kos.index') }}" class="btn-secondary mt-4">
                                <i class="ri-search-eye-line text-primary-500"></i> Cari kos lain
                            </a>
                        </div>
                    @else
                        <div class="rounded-2xl border-2 border-dashed border-primary-200 dark:border-primary-500/20 bg-primary-50/50 dark:bg-primary-500/5 p-10 text-center">
                            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20"><i class="ri-arrow-up-line text-2xl"></i></span>
                            <h3 class="mt-4 font-bold text-slate-900 dark:text-slate-50">Pilih kos terlebih dahulu</h3>
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

                function parseYmd(ymd) { var p = ymd.split('-'); return { y: parseInt(p[0], 10), m: parseInt(p[1], 10), d: parseInt(p[2], 10) }; }
                function ymdFromParts(y, m, d) { return y + '-' + (m < 10 ? '0' : '') + m + '-' + (d < 10 ? '0' : '') + d; }
                function ymdFromUTCDate(dt) { return ymdFromParts(dt.getUTCFullYear(), dt.getUTCMonth() + 1, dt.getUTCDate()); }
                function todayJakartaYmd() { return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date()); }
                function addDaysYmd(ymd, days) { var p = parseYmd(ymd); var dt = new Date(Date.UTC(p.y, p.m - 1, p.d + days)); return ymdFromUTCDate(dt); }
                function addMonthNoOverflowYmd(ymd) { var p = parseYmd(ymd); var totalMonths = p.y * 12 + (p.m - 1) + 1; var ty = Math.floor(totalMonths / 12); var tm = totalMonths % 12; var last = new Date(Date.UTC(ty, tm + 1, 0)).getUTCDate(); return ymdFromParts(ty, tm + 1, Math.min(p.d, last)); }
                function daysBetween(a, b) { var pa = parseYmd(a); var pb = parseYmd(b); return Math.round((Date.UTC(pb.y, pb.m - 1, pb.d) - Date.UTC(pa.y, pa.m - 1, pa.d)) / 86400000); }
                function billingLabel(val) { return val === 'shared' ? 'Patungan' : 'Tanggung sendiri'; }

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
                        return;
                    }
                    var rentalType = document.querySelector('input[name="rental_type"]:checked').value;
                    var price = Number(opt.dataset[rentalType] || 0);
                    var unitLabels = { daily: '/hari', weekly: '/mgg', monthly: '/bulan' };
                    var typeLabels = { daily: 'Harian', weekly: 'Mingguan', monthly: 'Bulanan' };
                    var unit = unitLabels[rentalType] || '/bulan';

                    var n = 1;
                    var billingVal = 'personal';
                    var cap = 1;
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
                        else { var sp = parseYmd(s), ep = parseYmd(e); total = price * ((ep.y - sp.y) * 12 + (ep.m - sp.m) + 1); }
                    } else elPeriode.textContent = 'Tanggal tidak valid';
                    elTotal.textContent = total > 0 ? fmt(total) : (price > 0 ? fmt(price) : 'Hubungi pemilik');
                    if (elOccupantHelp) {
                        elOccupantHelp.textContent = 'Booking untuk 1 orang.';
                        elOccupantHelp.classList.remove('hidden');
                    }
                    if (elTotalNote) {
                        elTotalNote.textContent = 'Total periode ini — dibayar sesuai tagihan.';
                        elTotalNote.classList.remove('hidden');
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
                }

                var startTouched = false;
                var endTouched = false;

                function defaultStartFor(type){
                    var todayYmd = todayJakartaYmd();
                    if(type==='weekly') return addDaysYmd(todayYmd, 7);
                    else if(type==='daily') return todayYmd;
                    else return addMonthNoOverflowYmd(todayYmd);
                }
                function defaultEndFor(startYmd, type){
                    if(!startYmd) startYmd = todayJakartaYmd();
                    if(type==='weekly') return addDaysYmd(startYmd, 7);
                    if(type==='daily') return addDaysYmd(startYmd, 1);
                    return addMonthNoOverflowYmd(startYmd);
                }
                function currentRentalType(){ return (document.querySelector('input[name="rental_type"]:checked')||{}).value || 'monthly'; }
                function syncEndMinFromStart(){
                    if(startIn.value){ endIn.setAttribute('min', startIn.value); }
                }
                function applyEndFollowingStart(){
                    var startVal = startIn.value || todayJakartaYmd();
                    var type = currentRentalType();
                    var nextEnd = defaultEndFor(startVal, type);
                    endIn.value = nextEnd;
                    endIn.setAttribute('data-default-end', nextEnd);
                    endIn.setAttribute('min', startVal);
                    if(endIn.value <= startVal && type !== 'daily'){
                        endIn.value = type==='weekly' ? addDaysYmd(startVal, 7) : addMonthNoOverflowYmd(startVal);
                    }
                }
                function applyDefaultsIfUntouched(type){
                    var prevStart = startIn.getAttribute('data-default-start') || '';
                    var prevEnd = endIn.getAttribute('data-default-end') || '';
                    var nextStart = defaultStartFor(type);
                    var nextEnd = defaultEndFor(nextStart, type);
                    var startIsDefault = !startIn.value || startIn.value === prevStart;
                    if (!startTouched && startIsDefault) {
                        startIn.value = nextStart;
                    }
                    var endIsDefault = !endIn.value || endIn.value === prevEnd;
                    var endIsInvalid = endIn.value <= (startIn.value || nextStart);
                    if ((!endTouched && (endIsDefault || endIsInvalid)) || (endTouched && endIsInvalid)) {
                        endIn.value = defaultEndFor(startIn.value || nextStart, type);
                    }
                    startIn.setAttribute('data-default-start', startIn.value || nextStart);
                    endIn.setAttribute('data-default-end', endIn.value || nextEnd);
                    syncEndMinFromStart();
                }
                [roomSel, startIn, endIn].forEach(function (el) { el.addEventListener('change', update); el.addEventListener('input', update); });
                if (startIn) {
                    startIn.addEventListener('change', function(){ startTouched = true; applyEndFollowingStart(); update(); });
                    startIn.addEventListener('input', function(){ startTouched = true; applyEndFollowingStart(); update(); });
                }
                if (endIn) {
                    endIn.addEventListener('change', function(){ endTouched = true; syncEndMinFromStart(); update(); });
                    endIn.addEventListener('input', function(){ endTouched = true; syncEndMinFromStart(); update(); });
                }
                Array.prototype.forEach.call(document.querySelectorAll('input[name="rental_type"]'), function (el) {
                    el.addEventListener('change', function(){ applyDefaultsIfUntouched(el.value); update(); });
                });
                roomSel.addEventListener('change', update);
                syncEndMinFromStart();
                update();
            })();
        </script>
    @endif
</x-app-layout>
