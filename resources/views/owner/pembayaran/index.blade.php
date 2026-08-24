<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; @endphp
    <x-slot name="header">
        <x-page-header title="Verifikasi Pembayaran" description="Periksa bukti pembayaran yang dikirim penghuni." />
    </x-slot>
    <x-alert />

    {{-- Filter tab --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pembayaran..." class="flex-1 rounded-lg border-gray-300 dark:border-slate-600 text-sm">
            <select name="status" class="rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                <option value="">Semua Status</option>
                @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ \PaymentLabels::verificationLabel($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 rounded-lg text-sm font-medium">Filter</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach([null => 'Semua', 'pending' => 'Menunggu Verifikasi', 'approved' => 'Terverifikasi', 'rejected' => 'Ditolak'] as $s => $label)
                @php $active = ($s === null && request('status') === null) || (request('status') === (string) $s); @endphp
                <a href="{{ route($prefix.'.pembayaran.index', array_filter(['status' => $s])) }}"
                   class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition {{ $active ? 'bg-primary-500 text-white shadow-sm shadow-primary-500/30' : 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 hover:bg-gray-200' }}">
                    {{ $label }}
                    @if($s === 'pending')
                        <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ $active ? 'bg-white/25' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/15 dark:text-yellow-300' }}">{{ $pendingCount ?? \App\Models\Pembayaran::where('verification_status', 'pending')->count() }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-gray-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">No. Pembayaran</th>
                        <th class="px-4 py-3 text-left font-medium">Penghuni</th>
                        <th class="px-4 py-3 text-right font-medium">Nominal</th>
                        <th class="px-4 py-3 text-left font-medium">Tanggal Bayar</th>
                        <th class="px-4 py-3 text-left font-medium">Metode</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($pembayarans as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->payment_number }}</td>
                            <td class="px-4 py-3">{{ $p->penghuni->user->name }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $p->payment_date->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">{{ \PaymentLabels::paymentMethod($p->payment_method) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::verificationBadge($p->verification_status) }}">{{ \PaymentLabels::verificationLabel($p->verification_status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route("$prefix.pembayaran.show", $p) }}" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a>
                                @if($p->verification_status === 'pending')
                                    <form method="POST" action="{{ route("$prefix.pembayaran.verify", $p) }}" class="inline ml-2"
                                          onsubmit="return confirm('Verifikasi pembayaran ini sebagai LUNAS?')">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-semibold">Verifikasi</button>
                                    </form>
                                    <button type="button" class="text-red-600 hover:text-red-800 text-xs font-semibold ml-2"
                                            x-data @click="$dispatch('open-modal', 'reject-{{ $p->id }}')">Tolak</button>

                                    {{-- Modal tolak + alasan wajib --}}
                                    <x-modal name="reject-{{ $p->id }}" maxWidth="md">
                                        <div x-data="{ reason: '' }" class="space-y-4">
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tolak Pembayaran</h3>
                                            <p class="text-sm text-gray-500 dark:text-slate-400">
                                                Pembayaran <span class="font-mono">{{ $p->payment_number }}</span> atas nama <strong>{{ $p->penghuni->user->name }}</strong> akan ditolak dan tagihan kembali ke status <strong>Belum Dibayar</strong>.
                                            </p>
                                            <form method="POST" action="{{ route("$prefix.pembayaran.reject", $p) }}" @submit="if (!reason.trim()) { alert('Alasan penolakan wajib diisi.'); event.preventDefault(); }">
                                                @csrf
                                                <textarea name="reason" rows="3" required x-model="reason"
                                                          placeholder="Wajib diisi — contoh: Bukti transfer tidak sesuai nominal."
                                                          class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm"></textarea>
                                                <div class="mt-4 flex justify-end gap-2">
                                                    <button type="button" @click="$dispatch('close-modal', 'reject-{{ $p->id }}')"
                                                            class="px-4 py-2 text-sm font-medium bg-gray-100 dark:bg-slate-800 rounded-lg hover:bg-gray-200">Batal</button>
                                                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700">Tolak Pembayaran</button>
                                                </div>
                                            </form>
                                        </div>
                                    </x-modal>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-3"><x-empty-state icon="ri-bank-card-line" title="Belum ada pembayaran." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-slate-700">{{ $pembayarans->links() }}</div>
    </div>
</x-app-layout>
