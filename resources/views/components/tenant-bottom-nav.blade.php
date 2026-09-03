@php
    $currentRoute = request()->route()->getName() ?? '';
    $user = Auth::user();
    $bookingBadge = (int) ($sidebarBadges['activeBooking'] ?? 0);
    $tagihanBadge = (int) ($sidebarBadges['tagihanBelum'] ?? 0);
@endphp

@if($user->isTenant())
    {{-- Mobile Bottom Navigation --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200/60 dark:border-slate-800 safe-area-bottom" aria-label="Navigasi utama">
        <div class="flex items-center justify-around h-16 px-2">

            {{-- Beranda --}}
            <a href="{{ route('dashboard') }}"
               class="relative flex flex-col items-center justify-center gap-0.5 w-12 py-1 rounded-xl transition {{ $currentRoute === 'dashboard' ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 active:text-slate-600' }}"
               aria-label="Beranda"
               @if($currentRoute === 'dashboard') aria-current="page" @endif>
                <i class="ri-home-5-{{ $currentRoute === 'dashboard' ? 'fill' : 'line' }} text-xl"></i>
                <span class="text-[10px] font-semibold leading-none">Beranda</span>
            </a>

            {{-- Cari --}}
            <a href="{{ route('tenant.kos.index') }}"
               class="relative flex flex-col items-center justify-center gap-0.5 w-12 py-1 rounded-xl transition {{ str_starts_with($currentRoute, 'tenant.kos') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500' }}"
               aria-label="Cari Kos"
               @if(str_starts_with($currentRoute, 'tenant.kos')) aria-current="page" @endif>
                <i class="ri-search-{{ str_starts_with($currentRoute, 'tenant.kos') ? 'fill' : 'line' }} text-xl"></i>
                <span class="text-[10px] font-semibold leading-none">Cari</span>
            </a>

            {{-- Favorit --}}
            <a href="{{ route('tenant.favorites.index') }}"
               class="relative flex flex-col items-center justify-center gap-0.5 w-12 py-1 rounded-xl transition {{ str_starts_with($currentRoute, 'tenant.favorites') ? 'text-red-500 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}"
               aria-label="Favorit"
               @if(str_starts_with($currentRoute, 'tenant.favorites')) aria-current="page" @endif>
                <i class="ri-heart-{{ str_starts_with($currentRoute, 'tenant.favorites') ? 'fill' : 'line' }} text-xl"></i>
                <span class="text-[10px] font-semibold leading-none">Favorit</span>
            </a>

            {{-- Booking --}}
            <a href="{{ route('tenant.booking.index') }}"
               class="relative flex flex-col items-center justify-center gap-0.5 w-12 py-1 rounded-xl transition {{ str_starts_with($currentRoute, 'tenant.booking') ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500' }}"
               aria-label="Booking Saya"
               @if(str_starts_with($currentRoute, 'tenant.booking')) aria-current="page" @endif>
                <i class="ri-calendar-check-{{ str_starts_with($currentRoute, 'tenant.booking') ? 'fill' : 'line' }} text-xl"></i>
                <span class="text-[10px] font-semibold leading-none">Booking</span>
                @if($bookingBadge > 0)
                    <span class="absolute top-0 right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-primary-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $bookingBadge }}</span>
                @endif
            </a>

            {{-- Tagihan --}}
            <a href="{{ route('tenant.tagihan.index') }}"
               class="relative flex flex-col items-center justify-center gap-0.5 w-12 py-1 rounded-xl transition {{ str_starts_with($currentRoute, 'tenant.tagihan') || str_starts_with($currentRoute, 'tenant.pembayaran') ? 'text-red-500 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}"
               aria-label="Tagihan"
               @if(str_starts_with($currentRoute, 'tenant.tagihan') || str_starts_with($currentRoute, 'tenant.pembayaran')) aria-current="page" @endif>
                <i class="ri-bill-line text-xl"></i>
                <span class="text-[10px] font-semibold leading-none">Tagihan</span>
                @if($tagihanBadge > 0)
                    <span class="absolute top-0 right-0 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $tagihanBadge }}</span>
                @endif
            </a>

            {{-- Profil --}}
            <a href="{{ route('profile.edit') }}"
               class="relative flex flex-col items-center justify-center gap-0.5 w-12 py-1 rounded-xl transition {{ $currentRoute === 'profile.edit' ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500' }}"
               aria-label="Profil"
               @if($currentRoute === 'profile.edit') aria-current="page" @endif>
                <i class="ri-user-3-{{ $currentRoute === 'profile.edit' ? 'fill' : 'line' }} text-xl"></i>
                <span class="text-[10px] font-semibold leading-none">Profil</span>
            </a>
        </div>
    </nav>

    {{-- Bottom padding for mobile content --}}
    <style>
        @media (max-width: 63.9375rem) {
            main { padding-bottom: 5.5rem !important; }
        }
    </style>
@endif
