<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Verifikasi Pembayaran" description="Periksa bukti pembayaran yang dikirim penghuni." icon="ri-bank-card-line" />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter + tab --}}
        <div class="filter-card">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pembayaran..." aria-label="Cari pembayaran"
                           class="field-input pl-10 pr-3.5">
                </div>
                <button type="submit" class="btn-secondary whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>

            <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100 dark:border-white/[0.06]">
                @foreach([null => 'Semua', 'pending' => 'Menunggu Verifikasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'] as $s => $label)
                    @php $active = ($s === null && request('status') === null) || (request('status') === (string) $s); @endphp
                    <a href="{{ route($prefix.'.pembayaran.index', array_filter(['status' => $s])) }}"
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ $active ? 'bg-primary-600 text-white shadow-sm shadow-primary-600/30' : 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.1]' }}">
                        {{ $label }}
                        @if($s === 'pending')
                            <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $active ? 'bg-white/25' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/15 dark:text-yellow-300' }}">{{ $pendingCount ?? 0 }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Table --}}
        @php $pendingIds = $pembayarans->getCollection()->where('verification_status', 'pending')->pluck('id')->values()->all(); @endphp
        <div class="table-card" x-data="{ selected: [], allPending: {{ json_encode($pendingIds) }} }">
            <div x-show="selected.length > 0" x-cloak class="flex flex-wrap items-center gap-3 border-b border-green-100 bg-green-50/60 px-4 py-3 dark:border-green-500/20 dark:bg-green-500/[0.06]">
                <span class="text-xs font-bold text-green-700 dark:text-green-300"><span x-text="selected.length"></span> dipilih</span>
                <x-confirm-dialog title="Verifikasi Massal?" description="Verifikasi semua pembayaran terpilih sebagai LUNAS? Item yang tidak memenuhi syarat dilewati otomatis."
                                   confirmText="Ya, Verifikasi" confirmClass="bg-green-600 hover:bg-green-700 text-white"
                                   triggerClass="inline-flex items-center gap-1.5 rounded-xl bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs font-semibold transition"
                                   aria-label="Verifikasi massal pembayaran terpilih">
                    <x-slot name="slot"><i class="ri-check-double-line"></i> Verifikasi Terpilih</x-slot>
                    <x-slot name="actions">
                        <form method="POST" action="{{ route("$prefix.pembayaran.bulk-verify") }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                            @csrf
                            <template x-for="id in selected" :key="id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                            <button type="submit" :disabled="submitting"
                                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Ya, Verifikasi</button>
                        </form>
                    </x-slot>
                </x-confirm-dialog>
                <button type="button" @click="selected = []" class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400">Batalkan pilihan</button>
            </div>
            {{-- Mobile cards --}}
            <div class="space-y-3 p-4 md:hidden">
                @forelse($pembayarans as $p)
                    @php
                        $displayName = $p->penghuni?->user?->name ?? $p->booking?->user?->name ?? '-';
                    @endphp
                    <x-mobile-record-card
                        :title="$p->payment_number"
                        :subtitle="$displayName . ' · ' . \PaymentLabels::paymentMethod($p->payment_method)"
                        :meta="[
                            'Nominal' => 'Rp ' . number_format($p->amount, 0, ',', '.'),
                            'Tanggal' => $p->payment_date?->translatedFormat('d M Y') ?? '-',
                        ]">
                        <x-slot name="badge">
                            <x-status-badge :status="$p->verification_status" context="verification" />
                        </x-slot>
                        <x-slot name="actions">
                            @if($p->verification_status === 'pending')
                                <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-600 dark:border-white/10 dark:text-slate-300">
                                    <input type="checkbox" :value="{{ $p->id }}" :checked="selected.includes({{ $p->id }})"
                                           @change="selected = $event.target.checked ? [...selected, {{ $p->id }}] : selected.filter(id => id !== {{ $p->id }})"
                                           class="h-5 w-5 rounded border-slate-300 text-green-600 focus:ring-green-500/30">
                                    Pilih
                                </label>
                            @endif
                            <x-button href="{{ route($prefix . '.pembayaran.show', $p) }}" type="secondary" size="sm" class="min-h-11">
                                <i class="ri-eye-line"></i> Detail
                            </x-button>
                        </x-slot>
                    </x-mobile-record-card>
                @empty
                    <x-empty-state icon="ri-bank-card-line" title="Belum ada pembayaran"
                                   description="Pembayaran yang dikirim penghuni akan tampil di sini untuk diverifikasi." />
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" aria-label="Pilih semua pending"
                                       :checked="allPending.length > 0 && selected.length === allPending.length"
                                       @change="selected = $event.target.checked ? [...allPending] : []"
                                       class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500/30">
                            </th>
                            <th>No. Pembayaran</th>
                            <th class="hidden md:table-cell">Penghuni</th>
                            <th class="text-right">Nominal</th>
                            <th class="hidden lg:table-cell">Tanggal</th>
                            <th class="hidden sm:table-cell">Metode</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayarans as $p)
                            <tr>
                                <td>
                                    @if($p->verification_status === 'pending')
                                        <input type="checkbox" :value="{{ $p->id }}" :checked="selected.includes({{ $p->id }})"
                                               @change="selected = $event.target.checked ? [...selected, {{ $p->id }}] : selected.filter(id => id !== {{ $p->id }})"
                                               aria-label="Pilih pembayaran {{ $p->payment_number }}"
                                               class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500/30">
                                    @endif
                                </td>
                                <td class="font-mono font-semibold text-slate-900 dark:text-slate-50">{{ $p->payment_number }}</td>
                                <td class="hidden md:table-cell">
                                    @php
                                        $displayName = $p->penghuni?->user?->name ?? $p->booking?->user?->name ?? '-';
                                        $displayInitial = strtoupper(substr($displayName, 0, 1));
                                    @endphp
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ $displayInitial }}</span>
                                        <div class="min-w-0">
                                            <span class="text-slate-700 dark:text-slate-200 truncate max-w-[10rem] block">{{ $displayName }}</span>
                                            @if($p->isBookingPayment() && $p->booking)
                                                <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">Booking {{ $p->booking->booking_code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right font-semibold text-slate-900 dark:text-slate-50 whitespace-nowrap">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                                <td class="hidden lg:table-cell text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->payment_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="hidden sm:table-cell">
                                    <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                        <i class="{{ ['transfer_bank' => 'ri-bank-line', 'qris' => 'ri-qr-code-2-line', 'e_wallet' => 'ri-smartphone-line'][$p->payment_method] ?? 'ri-wallet-3-line' }} text-slate-400"></i>
                                        {{ \PaymentLabels::paymentMethod($p->payment_method) }}
                                        @if($p->isFromGateway())
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-300" title="Verifikasi otomatis"><i class="ri-global-line"></i> Online</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center"><x-status-badge :status="$p->verification_status" context="verification" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route("$prefix.pembayaran.show", $p) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           title="Detail" aria-label="Detail pembayaran {{ $p->payment_number }}">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        @if($p->verification_status === 'pending')
                                            <x-confirm-dialog title="Verifikasi Pembayaran?" description="Verifikasi pembayaran {{ $p->payment_number }} sebagai LUNAS?"
                                                               confirmText="Verifikasi" confirmClass="bg-green-600 hover:bg-green-700 text-white"
                                                               triggerClass="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition"
                                                               aria-label="Verifikasi pembayaran {{ $p->payment_number }}">
                                                <x-slot name="slot"><i class="ri-check-double-line"></i></x-slot>
                                                <x-slot name="actions">
                                                    <form method="POST" action="{{ route("$prefix.pembayaran.verify", $p) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        @csrf
                                                        <button type="submit" :disabled="submitting"
                                                                class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-show="!submitting">Verifikasi</span>
                                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                                        </button>
                                                    </form>
                                                </x-slot>
                                            </x-confirm-dialog>
                                            <button type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                    title="Tolak pembayaran" aria-label="Tolak pembayaran {{ $p->payment_number }}"
                                                    x-data @click="$dispatch('open-modal', 'reject-{{ $p->id }}')">
                                                <i class="ri-close-line"></i>
                                            </button>

                                            {{-- Modal tolak + alasan wajib --}}
                                            <x-modal name="reject-{{ $p->id }}" maxWidth="md" ariaLabel="Tolak pembayaran {{ $p->payment_number }}">
                                                <div x-data="{ reason: '', submitting: false }" class="space-y-4">
                                                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-50">Tolak Pembayaran</h3>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                                        Pembayaran <span class="font-mono font-semibold">{{ $p->payment_number }}</span> atas nama <strong>{{ $p->penghuni?->user?->name ?? $p->booking?->user?->name ?? '-' }}</strong> akan ditolak dan tagihan kembali ke status <strong>Belum Dibayar</strong>.
                                                    </p>
                                                    <form method="POST" action="{{ route("$prefix.pembayaran.reject", $p) }}" @submit="if (!reason.trim()) { event.preventDefault(); return; } submitting = true">
                                                        @csrf
                                                        <label for="reject-reason-{{ $p->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                                                        <textarea id="reject-reason-{{ $p->id }}" name="reason" rows="3" required x-model="reason"
                                                                  placeholder="Wajib diisi — contoh: Bukti transfer tidak sesuai nominal."
                                                                  class="field-input"></textarea>
                                                        @error('reason')<p role="alert" class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                                        <div class="mt-4 flex justify-end gap-2">
                                                            <button type="button" @click="$dispatch('close-modal', 'reject-{{ $p->id }}')"
                                                                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-white/[0.06] border border-slate-200/60 dark:border-white/[0.08] rounded-xl hover:bg-slate-200 dark:hover:bg-white/[0.1] transition">Batal</button>
                                                            <button type="submit" :disabled="submitting"
                                                                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                                <span x-show="!submitting">Tolak Pembayaran</span>
                                                                <span x-show="submitting" x-cloak>Memproses...</span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </x-modal>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-empty-state icon="ri-bank-card-line" title="Belum ada pembayaran"
                                                   description="Pembayaran yang dikirim penghuni akan tampil di sini untuk diverifikasi." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 dark:border-white/[0.06]">{{ $pembayarans->links() }}</div>
        </div>
    </div>
</x-app-layout>
