<x-app-layout>
    @php $prefix = Auth::user()->hasRole('owner', 'super_admin') ? 'owner' : 'admin'; $backUrl = route($prefix.'.kamar.index'); @endphp
    <x-slot name="header">
        <x-page-header title="Kamar {{ $kamar->room_number }}" description="{{ $kamar->room_name }}">
            <x-button href="{{ $backUrl }}" type="secondary">Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Kamar</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500 dark:text-slate-400">Kos:</span><p class="font-medium">{{ $kamar->kos->name }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Nomor:</span><p class="font-medium">{{ $kamar->room_number }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Tipe:</span><p class="font-medium">{{ $kamar->room_type }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Lantai:</span><p class="font-medium">{{ $kamar->floor ?? '-' }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Harga Harian:</span><p class="font-medium">Rp {{ number_format($kamar->daily_price, 0, ',', '.') }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Harga Bulanan:</span><p class="font-medium">Rp {{ number_format($kamar->monthly_price, 0, ',', '.') }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Luas:</span><p class="font-medium">{{ $kamar->area ? $kamar->area . ' m²' : '-' }}</p></div>
                    <div><span class="text-gray-500 dark:text-slate-400">Status:</span>
                        @php $sc = ['available'=>'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300','booked'=>'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300','occupied'=>'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300','maintenance'=>'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300']; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sc[$kamar->status] ?? '' }}">{{ ucfirst($kamar->status) }}</span>
                    </div>
                </div>
                @if($kamar->fasilitas->count())
                    <div class="mt-4"><span class="text-gray-500 dark:text-slate-400 text-sm">Fasilitas:</span>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach($kamar->fasilitas as $f)
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">{{ $f->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Penghuni</h3>
                @forelse($kamar->penghunis->where('status', 'active') as $p)
                    <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-50 dark:bg-slate-800/60 mb-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-medium">{{ substr($p->user->name, 0, 1) }}</div>
                        <div><p class="text-sm font-medium">{{ $p->user->name }}</p><p class="text-xs text-gray-500 dark:text-slate-400">{{ $p->phone }}</p></div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-slate-400">Tidak ada penghuni aktif.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
