<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Verifikasi Pembayaran" description="Periksa bukti pembayaran yang dikirim penghuni." />
    </x-slot>

    <div class="space-y-6">
        <x-alert />

        {{-- Filter + tab --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4 sm:p-5 space-y-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3" role="search">
                <div class="relative flex-1 min-w-0">
                    <i class="ri-search-line absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor pembayaran..." aria-label="Cari pembayaran"
                           class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition whitespace-nowrap">
                    <i class="ri-filter-3-line"></i> Terapkan
                </button>
            </form>

            <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                @foreach([null => 'Semua', 'pending' => 'Menunggu Verifikasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'] as $s => $label)
                    @php $active = ($s === null && request('status') === null) || (request('status') === (string) $s); @endphp
                    <a href="{{ route($prefix.'.pembayaran.index', array_filter(['status' => $s])) }}"
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ $active ? 'bg-primary-500 text-white shadow-sm shadow-primary-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                        @if($s === 'pending')
                            <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $active ? 'bg-white/25' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/15 dark:text-yellow-300' }}">{{ $pendingCount ?? 0 }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold">No. Pembayaran</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden md:table-cell">Penghuni</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Nominal</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden lg:table-cell">Tanggal</th>
                            <th scope="col" class="px-4 py-3.5 text-left font-semibold hidden sm:table-cell">Metode</th>
                            <th scope="col" class="px-4 py-3.5 text-center font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($pembayarans as $p)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-semibold text-slate-900 dark:text-white">{{ $p->payment_number }}</td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($p->penghuni->user->name, 0, 1)) }}</span>
                                        <span class="text-slate-700 dark:text-slate-200 truncate max-w-[10rem]">{{ $p->penghuni->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold text-slate-900 dark:text-white whitespace-nowrap">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 hidden lg:table-cell text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->payment_date->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3.5 hidden sm:table-cell">
                                    <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                        <i class="{{ ['transfer_bank' => 'ri-bank-line', 'cash' => 'ri-cash-line', 'e_wallet' => 'ri-smartphone-line'][$p->payment_method] ?? 'ri-wallet-3-line' }} text-slate-400"></i>
                                        {{ \PaymentLabels::paymentMethod($p->payment_method) }}
                                        @if($p->isFromGateway())
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-300" title="Verifikasi otomatis"><i class="ri-global-line"></i> Online</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center"><x-status-badge :status="$p->verification_status" context="verification" /></td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route("$prefix.pembayaran.show", $p) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-500/10 transition"
                                           title="Detail" aria-label="Detail pembayaran {{ $p->payment_number }}">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        @if($p->verification_status === 'pending')
                                            <x-confirm-dialog title="Verifikasi Pembayaran?" description="Verifikasi pembayaran {{ $p->payment_number }} sebagai LUNAS?"
                                                               confirmText="Verifikasi" confirmClass="bg-green-600 hover:bg-green-700 text-white"
                                                               triggerClass="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition"
                                                               aria-label="Verifikasi pembayaran {{ $p->payment_number }}">
                                                <x-slot name="slot"><i class="ri-check-double-line"></i></x-slot>
                                                <x-slot name="actions">
                                                    <form method="POST" action="{{ route("$prefix.pembayaran.verify", $p) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                                        @csrf
                                                        <button type="submit" :disabled="submitting"
                                                                class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-green-600 hover:bg-green-700 transition shadow-sm shadow-green-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                                                            <span x-show="!submitting">Verifikasi</span>
                                                            <span x-show="submitting" x-cloak>Memproses...</span>
                                                        </button>
                                                    </form>
                                                </x-slot>
                                            </x-confirm-dialog>
                                            <button type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition"
                                                    title="Tolak pembayaran" aria-label="Tolak pembayaran {{ $p->payment_number }}"
                                                    x-data @click="$dispatch('open-modal', 'reject-{{ $p->id }}')">
                                                <i class="ri-close-line"></i>
                                            </button>

                                            {{-- Modal tolak + alasan wajib --}}
                                            <x-modal name="reject-{{ $p->id }}" maxWidth="md" ariaLabel="Tolak pembayaran {{ $p->payment_number }}">
                                                <div x-data="{ reason: '', submitting: false }" class="space-y-4">
                                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tolak Pembayaran</h3>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                                        Pembayaran <span class="font-mono font-semibold">{{ $p->payment_number }}</span> atas nama <strong>{{ $p->penghuni->user->name }}</strong> akan ditolak dan tagihan kembali ke status <strong>Belum Dibayar</strong>.
                                                    </p>
                                                    <form method="POST" action="{{ route("$prefix.pembayaran.reject", $p) }}" @submit="if (!reason.trim()) { event.preventDefault(); return; } submitting = true">
                                                        @csrf
                                                        <label for="reject-reason-{{ $p->id }}" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                                                        <textarea id="reject-reason-{{ $p->id }}" name="reason" rows="3" required x-model="reason"
                                                                  placeholder="Wajib diisi — contoh: Bukti transfer tidak sesuai nominal."
                                                                  class="w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                                        @error('reason')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                                        <div class="mt-4 flex justify-end gap-2">
                                                            <button type="button" @click="$dispatch('close-modal', 'reject-{{ $p->id }}')"
                                                                    class="px-4 py-2 text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</button>
                                                            <button type="submit" :disabled="submitting"
                                                                    class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 transition shadow-sm shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
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
                                <td colspan="7">
                                    <x-empty-state icon="ri-bank-card-line" title="Belum ada pembayaran"
                                                   description="Pembayaran yang dikirim penghuni akan tampil di sini untuk diverifikasi." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pembayarans->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $pembayarans->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
