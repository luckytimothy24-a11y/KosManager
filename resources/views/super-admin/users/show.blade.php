<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail User" description="{{ $user->email }}">
            <x-button href="{{ route('super-admin.users.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        {{-- Avatar ringkas --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
                {{ mb_substr($user->name, 0, 1) }}
            </span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-white truncate">{{ $user->name }}</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 truncate">{{ $user->email }}</p>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300' : ($user->role === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300' : ($user->role === 'owner' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100')) }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
        </div>

        {{-- Info --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sm:p-8 space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Nama</p>
                    <p class="text-sm text-slate-900 dark:text-white mt-1">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Email</p>
                    <p class="text-sm text-slate-900 dark:text-white mt-1">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Telepon</p>
                    <p class="text-sm text-slate-900 dark:text-white mt-1">{{ $user->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Status</p>
                    <p class="text-sm mt-1 {{ $user->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Owned Kos --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Kos Dimiliki</h3>
            @if($ownedKos->count() > 0)
                <div class="space-y-2">
                    @foreach($ownedKos as $kos)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                            <span class="text-sm text-slate-900 dark:text-white">{{ $kos->name }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $kos->city ?? '-' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada kos.</p>
            @endif
        </div>

        {{-- Bookings --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Booking</h3>
            @if($bookings->count() > 0)
                <div class="space-y-2">
                    @foreach($bookings as $booking)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                            <span class="text-sm text-slate-900 dark:text-white">{{ $booking->booking_code }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->status }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada booking.</p>
            @endif
        </div>

        {{-- Penghuni --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Penghuni</h3>
            @if($penghunis->count() > 0)
                <div class="space-y-2">
                    @foreach($penghunis as $penghuni)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                            <span class="text-sm text-slate-900 dark:text-white">{{ $penghuni->user?->name ?? 'N/A' }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $penghuni->status }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada penghuni.</p>
            @endif
        </div>
    </div>
</x-app-layout>
