<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route(Auth::user()->isTenant() ? 'tenant.tagihan.index' : $prefix.'.tagihan.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Detail Tagihan {{ $tagihan->bill_number }}">
            <x-button href="{{ $backUrl }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Tagihan</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500 dark:text-slate-400">Nomor:</span><p class="font-mono font-medium">{{ $tagihan->bill_number }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::tagihanBadge($tagihan->status) }}">{{ \PaymentLabels::tagihanLabel($tagihan->status) }}</span>
                </div>
                <div><span class="text-gray-500 dark:text-slate-400">Penghuni:</span><p class="font-medium">{{ $tagihan->penghuni->user->name }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Kamar:</span><p class="font-medium">{{ $tagihan->kamar->room_number }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Jenis:</span><p class="font-medium">{{ \PaymentLabels::billType($tagihan->bill_type) }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Periode:</span><p class="font-medium">{{ $tagihan->period_start->translatedFormat('d M Y') }} s/d {{ $tagihan->period_end->translatedFormat('d M Y') }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Subtotal:</span><p class="font-medium">Rp {{ number_format($tagihan->subtotal, 0, ',', '.') }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Diskon:</span><p class="font-medium">Rp {{ number_format($tagihan->discount, 0, ',', '.') }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Denda:</span><p class="font-medium">Rp {{ number_format($tagihan->penalty, 0, ',', '.') }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Jatuh Tempo:</span><p class="font-medium">{{ $tagihan->due_date->translatedFormat('d M Y') }}</p></div>
                <div class="col-span-2"><span class="text-gray-500 dark:text-slate-400">Total:</span><p class="font-bold text-xl text-blue-600">Rp {{ number_format($tagihan->total, 0, ',', '.') }}</p></div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Riwayat Pembayaran</h3>
            @forelse($tagihan->pembayarans as $p)
                <div class="p-3 border rounded-lg mb-2 text-sm">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-mono text-xs">{{ $p->payment_number }}</p>
                            <p class="text-gray-600 dark:text-slate-300">{{ $p->payment_date->translatedFormat('d M Y') }} - {{ \PaymentLabels::paymentMethod($p->payment_method) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium">Rp {{ number_format($p->amount, 0, ',', '.') }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::verificationBadge($p->verification_status) }}">{{ \PaymentLabels::verificationLabel($p->verification_status) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada pembayaran.</p>
            @endforelse

            @if(Auth::user()->isTenant() && $tagihan->status !== 'paid')
                <div class="mt-6 p-4 bg-gray-50 dark:bg-slate-800/60 rounded-lg">
                    <h4 class="font-medium text-sm mb-3">Upload Bukti Pembayaran</h4>
                    <form method="POST" action="{{ route('tenant.pembayaran.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">
                        <div class="space-y-3">
                            <input type="hidden" name="amount" value="{{ $tagihan->total }}">
                            <input type="text" value="Rp {{ number_format($tagihan->total, 0, ',', '.') }}" readonly disabled class="w-full rounded-lg border-gray-200 dark:border-slate-700 bg-gray-100 dark:bg-slate-900/60 text-sm font-bold cursor-not-allowed">
                            <select name="payment_method" required class="w-full rounded-lg border-gray-300 dark:border-slate-600 text-sm">
                                <option value="transfer_bank">Transfer Bank</option>
                                <option value="e_wallet">QRIS / E-Wallet</option>
                                <option value="cash">Tunai</option>
                            </select>
                            <input type="file" name="proof_file" accept="image/jpeg,image/png,application/pdf" required class="w-full text-sm text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700">
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 active:bg-primary-700 transition-colors shadow-sm shadow-primary-500/30">Kirim &amp; Tunggu Verifikasi</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
