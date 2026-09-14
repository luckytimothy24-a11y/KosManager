<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.kamar.index'); $canManage = Auth::user()->hasRole('owner', 'super_admin');
        $activePenghunis = $kamar->penghunis->where('status', 'active');
        $occupied = $activePenghunis->count();
        $capacity = (int) $kamar->capacity;
    @endphp
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
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 overflow-hidden">
                    <div class="h-28 bg-gradient-to-br from-primary-600 to-primary-800 dark:from-primary-700 dark:to-primary-900 relative">
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
                                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $kamar->area ? $kamar->area.' mÂ²' : '-' }}</dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <i class="ri-information-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Status</dt>
                                        <dd class="mt-0.5"><x-status-badge :status="$kamar->status" context="kamar" /></dd>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                        <i class="ri-group-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kapasitas</dt>
                                            <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200">{{ $capacity }} Orang</dd>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2.5">
                                        <i class="ri-scales-3-line text-slate-400 dark:text-slate-500 mt-0.5"></i>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mode Tagihan</dt>
                                            @php $bm = $kamar->billing_mode ?? 'personal'; @endphp
                                            <dd class="mt-0.5">
                                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $bm === 'shared' ? 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' : 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-slate-300' }}">
                                                    <i class="{{ $bm === 'shared' ? 'ri-team-line' : 'ri-user-line' }}"></i> {{ $bm === 'shared' ? 'Patungan Kamar' : 'Tagihan Pribadi' }}
                                                </span>
                                            </dd>
                                            <dd class="mt-1 text-xs text-slate-400 dark:text-slate-500 leading-relaxed">{{ $bm === 'shared' ? 'Biaya kamar dapat dibagi antar penghuni.' : 'Satu kamar untuk satu penghuni.' }}</dd>
                                        </div>
                                    </div>
                                </dl>
                            <x-status-badge :status="$kamar->status" context="kamar" class="sm:hidden" />
                        </div>

                        <div class="mt-5 pt-5 border-t border-slate-100 dark:border-zinc-700 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-zinc-700 px-4 py-3">
                                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"><i class="ri-calendar-line"></i> Harian</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($kamar->daily_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-zinc-700 px-4 py-3">
                                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"><i class="ri-calendar-todo-line"></i> Mingguan</span>
                                <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($kamar->weekly_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl border border-primary-100 dark:border-primary-500/20 bg-primary-50/50 dark:bg-primary-500/[0.06] px-4 py-3">
                                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400"><i class="ri-calendar-check-line"></i> Bulanan</span>
                                <span class="font-bold text-primary-700 dark:text-primary-300">Rp {{ number_format($kamar->monthly_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fasilitas --}}
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                        <i class="ri-sofa-line text-primary-500"></i> Fasilitas Kamar
                    </h3>
                    @if($kamar->fasilitas->count())
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($kamar->fasilitas as $f)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
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
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-user-star-line text-primary-500"></i> Penghuni Aktif
                        <span class="ml-auto text-xs font-bold text-slate-500 dark:text-slate-400">{{ $occupied }} / {{ $capacity }}</span>
                    </h3>

                    @if($capacity > 1)
                        <div class="mb-4">
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-primary-500 transition-all" style="width: {{ min(100, (int) round($occupied / max(1, $capacity) * 100)) }}%"></div>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Terisi {{ $occupied }} dari {{ $capacity }} kapasitas</p>
                        </div>
                    @endif

                    <div class="space-y-2.5">
                        @forelse($activePenghunis as $p)
                            @php
                                $pKontrak = $p->kontraks->where('status','active')->first();
                                $pShare = $pKontrak ? ($pKontrak->custom_share_amount ?? null) : null;
                                $pBillingMode = $pKontrak ? ($pKontrak->billing_mode ?? $kamar->billing_mode) : ($kamar->billing_mode ?? 'personal');
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-zinc-800/60">
                                @php $initial = strtoupper(substr($p->user?->name ?? '?', 0, 1)); @endphp
                                <span class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-sm font-bold shrink-0">{{ $initial }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $p->user?->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ $p->phone ?: '-' }}</p>
                                    @if($pBillingMode === 'shared')
                                        <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 mt-0.5">
                                            Rp {{ number_format($pShare ?? (int) round($kamar->monthly_price / max(1,$capacity)), 0, ',', '.') }}/bln
                                            @if($pShare) <span class="font-normal text-slate-400">· custom</span> @endif
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Rp {{ number_format($pKontrak->rental_price ?? 0, 0, ',', '.') }}/{{ $pKontrak->rental_type ?? 'bulan' }}</p>
                                    @endif
                                </div>
                                @if($canManage)
                                    <form method="POST" action="{{ route('owner.checkout.request', $p) }}" onsubmit="return confirm('Check-out {{ $p->user?->name }}? Hanya penghuni ini yang akan di-check-out.')">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-red-600 dark:text-red-400 hover:text-red-700 px-2 py-1 rounded-lg border border-red-200 dark:border-red-500/30 hover:bg-red-50 dark:hover:bg-red-500/10 transition" title="Check-out penghuni ini saja">Keluar</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="py-6 text-center">
                                <span class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                                    <i class="ri-user-search-line text-lg text-slate-300 dark:text-slate-600"></i>
                                </span>
                                <p class="mt-2.5 text-sm text-slate-400 dark:text-slate-500">Tidak ada penghuni aktif.</p>
                            </div>
                        @endforelse
                    </div>
                    @if($occupied < $capacity && $occupied > 0)
                        <p class="mt-3 text-xs text-center text-green-600 dark:text-green-400 font-medium"><i class="ri-checkbox-circle-line"></i> {{ $capacity - $occupied }} slot tersedia — bisa tambah penghuni lagi</p>
                    @endif

                    @if($canManage && $occupied < $capacity)
                        <div class="mt-4 border-t border-slate-100 pt-4 dark:border-zinc-700" x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">
                            <div class="rounded-xl border border-slate-100 dark:border-zinc-700 bg-slate-50/60 dark:bg-zinc-800/40 px-4 py-3 mb-3">
                                <p class="flex items-start gap-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300"><i class="ri-information-line text-slate-400 dark:text-slate-500 mt-0.5"></i><span>Untuk penghuni walk-in, data identitas dapat diisi oleh owner. Jika penghuni berasal dari booking, minta tenant melakukan <span class="font-semibold">Konfirmasi Identitas</span> terlebih dahulu.</span></p>
                            </div>
                            <button type="button" @click="open = !open"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-primary-300 bg-primary-50/50 px-4 py-2.5 text-sm font-semibold text-primary-600 transition hover:bg-primary-50 dark:border-primary-500/40 dark:bg-primary-500/10 dark:text-primary-300 dark:hover:bg-primary-500/20">
                                <i class="ri-user-add-line"></i> <span x-show="!open">+ Tambah Penghuni</span><span x-show="open" x-cloak>Tutup</span>
                            </button>

                            <form method="POST" action="{{ route('owner.kamar.add-penghuni', $kamar) }}" x-show="open" x-cloak class="mt-3 space-y-3">
                                @csrf
                                <div>
                                    <label for="add_user_id" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tenant</label>
                                    <select id="add_user_id" name="user_id" required
                                            class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Pilih tenant</option>
                                        @foreach($tenantCandidates as $tc)
                                            <option value="{{ $tc->id }}">{{ $tc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="add_rental_type" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tipe Sewa</label>
                                        <select id="add_rental_type" name="rental_type" class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="monthly">Bulanan</option>
                                            <option value="weekly">Mingguan</option>
                                            <option value="daily">Harian</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="add_rental_price" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Harga Sewa</label>
                                        <input id="add_rental_price" type="number" name="rental_price" min="0" step="any" required value="{{ old('rental_price', $kamar->monthly_price) }}"
                                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                    </div>
                                </div>
                                <div>
                                    <label for="add_check_in_date" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tanggal Masuk</label>
                                    <input id="add_check_in_date" type="date" name="check_in_date" required value="{{ old('check_in_date', now()->toDateString()) }}"
                                           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label for="add_identity_number" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">No. Identitas</label>
                                    <input id="add_identity_number" type="text" name="identity_number" value="{{ old('identity_number') }}"
                                           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label for="add_phone" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Telepon</label>
                                    <input id="add_phone" type="text" name="phone" value="{{ old('phone') }}"
                                           class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                @if(($kamar->billing_mode ?? 'personal') === 'shared')
                                    <div>
                                        <label for="add_custom_share" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Bagian Patungan (opsional)</label>
                                        <input id="add_custom_share" type="number" name="custom_share_amount" min="0" step="any" value="{{ old('custom_share_amount') }}"
                                               placeholder="Kosongkan = bagi rata (Rp {{ number_format((int) round($kamar->monthly_price / max(1,$capacity)),0,',','.') }})"
                                               class="w-full rounded-xl border-slate-200 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <p class="text-[11px] text-slate-400 mt-1">Jika diisi, total semua bagian harus = harga kamar.</p>
                                    </div>
                                @endif
                                @error('user_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                @error('rental_price') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                                <button type="submit"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-600">
                                    <i class="ri-user-add-line"></i> Simpan Penghuni
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-slate-100 dark:border-zinc-700 p-6">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white mb-4">
                        <i class="ri-flashlight-line text-primary-500"></i> Aksi Cepat
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route("$prefix.booking.index") }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-zinc-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-calendar-check-line text-lg"></i> Lihat Booking
                        </a>
                        <a href="{{ route("$prefix.checkin.index") }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-zinc-800/60 hover:bg-primary-50 dark:hover:bg-primary-500/10 hover:text-primary-600 dark:hover:text-primary-300 transition">
                            <i class="ri-login-box-line text-lg"></i> Proses Check-in
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
