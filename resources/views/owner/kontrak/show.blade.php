<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Kontrak {{ $kontrak->contract_number }}">
            <x-button href="{{ route('owner.kontrak.index') }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Informasi Kontrak</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500 dark:text-slate-400">Nomor:</span><p class="font-mono font-medium">{{ $kontrak->contract_number }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $kontrak->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100' }}">{{ ucfirst($kontrak->status) }}</span>
                </div>
                <div><span class="text-gray-500 dark:text-slate-400">Penghuni:</span><p class="font-medium">{{ $kontrak->penghuni->user->name }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Kamar:</span><p class="font-medium">{{ $kontrak->kamar->room_number }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Kos:</span><p class="font-medium">{{ $kontrak->kos->name }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Tipe Sewa:</span><p class="font-medium">{{ ucfirst($kontrak->rental_type) }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Mulai:</span><p class="font-medium">{{ $kontrak->start_date }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Berakhir:</span><p class="font-medium">{{ $kontrak->end_date }}</p></div>
                <div class="col-span-2"><span class="text-gray-500 dark:text-slate-400">Harga Sewa:</span><p class="font-medium text-lg">Rp {{ number_format($kontrak->rental_price, 0, ',', '.') }}</p></div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Tagihan</h3>
            @forelse($kontrak->tagihans as $t)
                <div class="flex justify-between items-center p-3 border rounded-lg mb-2 text-sm">
                    <div>
                        <p class="font-mono text-xs">{{ $t->bill_number }}</p>
                        <p class="text-gray-600 dark:text-slate-300">{{ $t->period_start }} s/d {{ $t->period_end }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ \PaymentLabels::tagihanBadge($t->status) }}">{{ \PaymentLabels::tagihanLabel($t->status) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada tagihan.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
