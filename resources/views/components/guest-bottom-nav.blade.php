@php
    $currentRoute = optional(request()->route())->getName() ?? '';
@endphp

<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-surface-card/95 dark:bg-surface-dark/95 backdrop-blur-xl border-t border-slate-200/60 dark:border-white/[0.08] safe-area-bottom" aria-label="Navigasi utama">
    <div class="flex items-center justify-around h-[72px] px-1">
        <a href="/"
           class="relative flex flex-col items-center justify-center gap-0.5 min-w-0 flex-1 min-h-[64px] px-1 py-1.5 rounded-xl transition {{ $currentRoute === 'dashboard' ? 'bg-primary-50 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400' }}"
           aria-label="Beranda" @if($currentRoute === 'dashboard') aria-current="page" @endif>
            <i class="ri-home-fill text-lg" aria-hidden="true"></i>
            <span class="text-[11px] font-semibold leading-none">Beranda</span>
        </a>

        <a href="{{ route('tenant.favorites.index') }}"
           class="relative flex flex-col items-center justify-center gap-0.5 min-w-0 flex-1 min-h-[64px] px-1 py-1.5 rounded-xl transition {{ str_starts_with($currentRoute, 'tenant.favorites') ? 'bg-primary-50 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400' }}"
           aria-label="Favorit" @if(str_starts_with($currentRoute, 'tenant.favorites')) aria-current="page" @endif>
            <i class="ri-heart-line text-lg" aria-hidden="true"></i>
            <span class="text-[11px] font-semibold leading-none">Favorit</span>
        </a>

        <a href="{{ route('tenant.booking.index') }}"
           class="relative flex flex-col items-center justify-center gap-0.5 min-w-0 flex-1 min-h-[64px] px-1 py-1.5 rounded-xl transition {{ str_starts_with($currentRoute, 'tenant.booking') ? 'bg-primary-50 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400' }}"
           aria-label="Booking" @if(str_starts_with($currentRoute, 'tenant.booking')) aria-current="page" @endif>
            <i class="ri-calendar-check-line text-lg" aria-hidden="true"></i>
            <span class="text-[11px] font-semibold leading-none">Booking</span>
        </a>

        <a href="{{ route('tenant.tagihan.index') }}"
           class="relative flex flex-col items-center justify-center gap-0.5 min-w-0 flex-1 min-h-[64px] px-1 py-1.5 rounded-xl transition {{ str_starts_with($currentRoute, 'tenant.tagihan') || str_starts_with($currentRoute, 'tenant.pembayaran') ? 'bg-primary-50 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400' }}"
           aria-label="Tagihan" @if(str_starts_with($currentRoute, 'tenant.tagihan') || str_starts_with($currentRoute, 'tenant.pembayaran')) aria-current="page" @endif>
            <i class="ri-bill-line text-lg" aria-hidden="true"></i>
            <span class="text-[11px] font-semibold leading-none">Tagihan</span>
        </a>

        <a href="{{ auth()->check() ? route('profile.edit') : route('login') }}"
           class="relative flex flex-col items-center justify-center gap-0.5 min-w-0 flex-1 min-h-[64px] px-1 py-1.5 rounded-xl transition {{ $currentRoute === 'login' || $currentRoute === 'profile.edit' ? 'bg-primary-50 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400' }}"
           aria-label="Profil" @if($currentRoute === 'login' || $currentRoute === 'profile.edit') aria-current="page" @endif>
            <i class="ri-user-line text-lg" aria-hidden="true"></i>
            <span class="text-[11px] font-semibold leading-none">Profil</span>
        </a>
    </div>
</nav>
