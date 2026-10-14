<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Payout Pemilik" description="Settlement ke Pemilik Kos Asli. Transfer manual di luar aplikasi — KosManager hanya mencatat.">
            <x-button href="{{ route('owner.distribution.index') }}" type="secondary"><i class="ri-wallet-3-line"></i> Distribusi Booking</x-button>
        </x-page-header>
    </x-slot>

    <div class="space-y-6">
        <x-alert />
        @isset($settlement)
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Sewa Diterima</p>
                    <p class="text-base font-bold text-slate-900 dark:text-white">Rp {{ number_format((float) $settlement['received'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-amber-600">Komisi Owner</p>
                    <p class="text-base font-bold text-amber-600">Rp {{ number_format((float) $settlement['commission'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-emerald-600">Terverifikasi</p>
                    <p class="text-base font-bold text-emerald-600">Rp {{ number_format((float) $settlement['payout_verified'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-surface-elevated rounded-2xl border border-slate-100 dark:border-white/10 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-blue-500">Tertunda</p>
                    <p class="text-base font-bold text-blue-600">Rp {{ number_format((float) $settlement['payout_pending'], 0, ',', '.') }}</p>
                    @if((float) ($settlement['penalty_excluded'] ?? 0) > 0)
                        <p class="text-[10px] text-slate-400">Penalti excl: Rp {{ number_format((float) $settlement['penalty_excluded'], 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>
        @endisset

        {{-- Warning: kos tanpa pemilik asli --}}
        @if($kosWithoutPropertyOwner->isNotEmpty())
            <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-5">
                <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Pemilik Kos Asli belum ditentukan</p>
                <p class="text-sm text-amber-700 dark:text-amber-300/90 mt-1">
                    Tentukan pemilik asli + rekening tujuan untuk kos berikut sebelum membuat payout:
                </p>
                <ul class="mt-2 space-y-1 text-sm text-amber-700 dark:text-amber-300/90">
                    @foreach($kosWithoutPropertyOwner as $kw)
                        <li class="flex items-center gap-2">
                            <i class="ri-home-smile-line"></i> {{ $kw->name }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Buat Payout --}}
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                    <i class="ri-add-circle-line text-primary-500"></i> Buat Payout
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Nominal = sewa approved − komisi per-kos (penalti tidak masuk basis).
                </p>

                @if($kosOptions->isEmpty())
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Belum ada kos aktif.</p>
                @else
                    <form method="POST" action="{{ route('owner.payouts.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="payout-kos" class="field-label">Kos</label>
                            <select id="payout-kos" name="kos_id" required class="field-input">
                                <option value="">— Pilih Kos —</option>
                                @foreach($kosOptions as $k)
                                    <option value="{{ $k->id }}" {{ old('kos_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->name }}{{ $k->property_owner_id ? '' : ' (belum ada pemilik asli)' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kos_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="period_start" class="field-label">Mulai Periode</label>
                                <input type="date" id="period_start" name="period_start" required
                                       value="{{ old('period_start') }}" class="field-input">
                                @error('period_start') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="period_end" class="field-label">Akhir Periode</label>
                                <input type="date" id="period_end" name="period_end" required
                                       value="{{ old('period_end') }}" class="field-input">
                                @error('period_end') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary">
                                <i class="ri-money-dollar-circle-line"></i> Buat Payout
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Tentukan Pemilik Kos Asli --}}
            <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50 flex items-center gap-2">
                    <i class="ri-user-settings-line text-primary-500"></i> Tentukan Pemilik Kos Asli
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Mengikat pemilik asli + rekening tujuan ke kos yang dipilih, dalam satu proses.
                </p>

                @if($kosOptions->isEmpty())
                    <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Belum ada kos aktif.</p>
                @else
                    @php
                        $kosNeedingOwner = $kosOptions->filter(fn ($k) => ! $k->property_owner_id)->values();
                    @endphp
                    @if($kosNeedingOwner->isEmpty())
                        <p class="mt-4 text-sm text-green-700 dark:text-green-300">Semua kos aktif sudah memiliki pemilik asli.</p>
                    @else
                        <div class="mt-4 space-y-4">
                            @foreach($kosNeedingOwner as $kw)
                                <form method="POST" action="{{ route('owner.payouts.set-property-owner', $kw) }}" class="rounded-xl border border-slate-100 dark:border-white/10 p-4 space-y-3">
                                    @csrf
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $kw->name }}</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="col-span-2">
                                            <label class="field-label">Nama Pemilik <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" required maxlength="100" placeholder="Contoh: Budi Santoso" class="field-input">
                                        </div>
                                        <div>
                                            <label class="field-label">Telepon</label>
                                            <input type="text" name="phone" maxlength="30" class="field-input">
                                        </div>
                                        <div>
                                            <label class="field-label">Email</label>
                                            <input type="email" name="email" maxlength="100" class="field-input">
                                        </div>
                                        <div>
                                            <label class="field-label">Nama Bank <span class="text-red-500">*</span></label>
                                            <input type="text" name="bank_name" required maxlength="50" placeholder="BCA" class="field-input">
                                        </div>
                                        <div>
                                            <label class="field-label">Nomor Rekening <span class="text-red-500">*</span></label>
                                            <input type="text" name="account_number" required maxlength="30" inputmode="numeric" placeholder="001234567890" class="field-input font-mono">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="field-label">Atas Nama Rekening <span class="text-red-500">*</span></label>
                                            <input type="text" name="account_holder_name" required maxlength="100" class="field-input">
                                        </div>
                                    </div>
                                    @error('name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                    @error('bank_name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                    @error('account_number') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                    @error('account_holder_name') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                    <div class="flex justify-end">
                                        <button type="submit" class="btn-secondary text-xs">
                                            <i class="ri-save-line"></i> Simpan Pemilik
                                        </button>
                                    </div>
                                </form>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Daftar Payout --}}
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">Daftar Payout</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Transfer manual → unggah bukti → verifikasi Super Admin</span>
            </div>

            @if($payouts->isEmpty())
                <div class="px-6 py-12 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                        <i class="ri-hand-coin-line text-2xl text-slate-400"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada payout.</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Buat payout setelah ada pendapatan approved pada suatu periode.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-white/10">
                    @foreach($payouts as $p)
                        @php
                            $badge = match($p->status) {
                                'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
                                'transferred' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-300',
                                'verified' => 'bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-300',
                                'rejected' => 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-300',
                                default => 'bg-slate-100 text-slate-700',
                            };
                            $statusLabel = match($p->status) {
                                'pending' => 'Belum Dibayar',
                                'transferred' => 'Menunggu Verifikasi',
                                'verified' => 'Terverifikasi',
                                'rejected' => 'Ditolak',
                                default => $p->status,
                            };
                        @endphp
                        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $p->payout_number }}</span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge }}">{{ $statusLabel }}</span>
                                </div>
                                <p class="mt-1 text-sm font-bold text-slate-900 dark:text-slate-50">{{ $p->kos?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pemilik: {{ $p->propertyOwner?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Periode: {{ \Illuminate\Support\Carbon::parse($p->period_start)->translatedFormat('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($p->period_end)->translatedFormat('d M Y') }}</p>
                            </div>
                            <div class="sm:text-right shrink-0">
                                <p class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-wide font-medium">Pendapatan</p>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Rp {{ number_format((float) $p->gross_amount, 0, ',', '.') }}</p>
                                <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">Komisi: -Rp {{ number_format((float) ($p->commission_total ?? 0), 0, ',', '.') }}</p>
                                <p class="text-base font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format((float) $p->amount, 0, ',', '.') }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">Hak Pemilik Kos Asli</p>
                                <a href="{{ route('owner.payouts.show', $p) }}" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                    {{ $p->status === 'pending' || $p->status === 'rejected' ? 'Bayar Pemilik' : 'Lihat Detail' }}
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4">
                    {{ $payouts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
