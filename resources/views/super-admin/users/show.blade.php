<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Detail User" description="{{ $user->email }}">
            <x-button href="{{ route('super-admin.users.index') }}" type="secondary"><i class="ri-arrow-left-line"></i> Kembali</x-button>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <x-alert />

        @if(session('new_password'))
            <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Password Baru (tampil sekali)</p>
                <p class="mt-1 font-mono text-lg font-bold text-slate-900 dark:text-slate-50">{{ session('new_password') }}</p>
                <p class="mt-1 text-xs text-amber-700/80 dark:text-amber-300/80">Catat sekarang — tidak akan ditampilkan lagi.</p>
            </div>
        @endif

        {{-- Aksi akun --}}
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-5 flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('super-admin.users.toggle-active', $user) }}" class="inline-flex" x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf
                <button type="submit" :disabled="submitting"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition disabled:opacity-50 {{ $user->is_active ? 'bg-slate-100 dark:bg-surface-elevated text-slate-600 dark:text-slate-300 hover:bg-slate-200' : 'bg-green-600 hover:bg-green-700 text-white' }}">
                    <i class="ri-{{ $user->is_active ? 'forbid' : 'check' }}-line"></i> {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
            <x-confirm-dialog title="Reset Kata Sandi?" description="Password {{ $user->name }} akan diganti acak dan ditampilkan sekali."
                               confirmText="Ya, Reset" confirmClass="bg-amber-600 hover:bg-amber-700 text-white"
                               triggerClass="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition"
                               aria-label="Reset kata sandi {{ $user->name }}">
                <x-slot name="slot"><i class="ri-key-line"></i> Reset Password</x-slot>
                <x-slot name="actions">
                    <form method="POST" action="{{ route('super-admin.users.reset-password', $user) }}" class="inline-flex">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 transition">Ya, Reset</button>
                    </form>
                </x-slot>
            </x-confirm-dialog>
            <a href="{{ route('super-admin.users.edit', $user) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline"><i class="ri-edit-line"></i> Edit</a>
        </div>

        {{-- Avatar ringkas --}}
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 flex items-center gap-4">
            <span class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold text-lg flex items-center justify-center uppercase shrink-0">
                {{ mb_substr($user->name, 0, 1) }}
            </span>
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-slate-50 truncate">{{ $user->name }}</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 truncate">{{ $user->email }}</p>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium @switch($user->role)
                        @case('super_admin') bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300 @break
                        @case('admin') bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300 @break
                        @case('owner') bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300 @break
                        @default bg-slate-100 text-slate-800 dark:bg-surface-elevated dark:text-slate-200 @endswitch">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
        </div>

        {{-- Info --}}
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6 sm:p-8 space-y-4">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Nama</p>
                    <p class="text-sm text-slate-900 dark:text-slate-50 mt-1">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Email</p>
                    <p class="text-sm text-slate-900 dark:text-slate-50 mt-1">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">Telepon</p>
                    <p class="text-sm text-slate-900 dark:text-slate-50 mt-1">{{ $user->phone ?? '-' }}</p>
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
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="font-bold text-slate-900 dark:text-slate-50 mb-3">Kos Dimiliki</h3>
            @if($ownedKos->count() > 0)
                <div class="space-y-2">
                    @foreach($ownedKos as $kos)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-surface-elevated/40">
                            <span class="text-sm text-slate-900 dark:text-slate-50">{{ $kos->name }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $kos->city ?? '-' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada kos.</p>
            @endif
        </div>

        {{-- Bookings --}}
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="font-bold text-slate-900 dark:text-slate-50 mb-3">Booking</h3>
            @if($bookings->count() > 0)
                <div class="space-y-2">
                    @foreach($bookings as $booking)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-surface-elevated/40">
                            <span class="text-sm text-slate-900 dark:text-slate-50">{{ $booking->booking_code }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->status }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-slate-500">Tidak ada booking.</p>
            @endif
        </div>

        {{-- Penghuni --}}
        <div class="bg-white dark:bg-surface-elevated rounded-2xl shadow-sm border border-slate-100 dark:border-white/10 p-6">
            <h3 class="font-bold text-slate-900 dark:text-slate-50 mb-3">Penghuni</h3>
            @if($penghunis->count() > 0)
                <div class="space-y-2">
                    @foreach($penghunis as $penghuni)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-surface-elevated/40">
                            <span class="text-sm text-slate-900 dark:text-slate-50">{{ $penghuni->user?->name ?? 'N/A' }}</span>
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
