<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail Penghuni">
            <x-button href="{{ route('owner.penghuni.index') }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Data Diri</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500 dark:text-slate-400">Nama:</span><p class="font-medium">{{ $penghuni->user->name }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Email:</span><p class="font-medium">{{ $penghuni->user->email }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Telepon:</span><p class="font-medium">{{ $penghuni->phone }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">No. Identitas:</span><p class="font-medium">{{ $penghuni->identity_number }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Kamar:</span><p class="font-medium">{{ $penghuni->kamar->room_number }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Kos:</span><p class="font-medium">{{ $penghuni->kos->name }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Tanggal Masuk:</span><p class="font-medium">{{ $penghuni->check_in_date }}</p></div>
                <div><span class="text-gray-500 dark:text-slate-400">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $penghuni->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100' }}">{{ ucfirst($penghuni->status) }}</span>
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-semibold mb-4">Kontrak</h3>
                @forelse($penghuni->kontraks as $k)
                    <div class="p-3 border rounded-lg mb-2 text-sm">
                        <p class="font-mono text-xs">{{ $k->contract_number }}</p>
                        <p>{{ $k->start_date }} s/d {{ $k->end_date }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $k->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100' }}">{{ ucfirst($k->status) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada kontrak.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
