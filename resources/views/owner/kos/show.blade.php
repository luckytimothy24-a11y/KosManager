<x-app-layout>
    @php $backUrl = route('owner.kos.index'); @endphp
    <x-slot name="header">
        <x-page-header title="{{ $kos->name }}" description="Detail kos.">
            <x-button href="{{ $backUrl }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Kos</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500 dark:text-slate-400">Alamat:</span><p class="font-medium">{{ $kos->address }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Telepon:</span><p class="font-medium">{{ $kos->phone }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $kos->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-100' }}">
                            {{ ucfirst($kos->status) }}
                        </span>
                    </div>
                    <div><span class="text-gray-500 dark:text-slate-400">Pemilik:</span><p class="font-medium">{{ $kos->owner->name }}</p></div>
                </div>
                @if($kos->description)
                    <div class="mt-4"><span class="text-gray-500 dark:text-slate-400 text-sm">Deskripsi:</span><p class="text-sm mt-1">{{ $kos->description }}</p></div>
                @endif
                @if($kos->general_facilities)
                    <div class="mt-4"><span class="text-gray-500 dark:text-slate-400 text-sm">Fasilitas Umum:</span><p class="text-sm mt-1">{{ $kos->general_facilities }}</p></div>
                @endif
                @if($kos->rules)
                    <div class="mt-4"><span class="text-gray-500 dark:text-slate-400 text-sm">Peraturan:</span><p class="text-sm mt-1 whitespace-pre-line">{{ $kos->rules }}</p></div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistik</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-slate-300">Total Kamar</span>
                        <span class="text-lg font-bold text-blue-600">{{ $kos->kamar_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-slate-300">Total Penghuni</span>
                        <span class="text-lg font-bold text-green-600">{{ $kos->penghunis_count }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-slate-300">Total Booking</span>
                        <span class="text-lg font-bold text-yellow-600">{{ $kos->bookings_count }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
