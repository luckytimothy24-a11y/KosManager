@php
    $user = Auth::user();
    $sidebarData = $sidebarBadges ?? [];
    $isTenant = $user?->isTenant() ?? false;

    $items = [];

    if ($user) {
        if ($isTenant) {
            $items = [
                [
                    'label' => 'Beranda',
                    'route' => 'dashboard',
                    'icon' => 'ri-home-5',
                    'active' => request()->routeIs('dashboard'),
                ],
                [
                    'label' => 'Favorit',
                    'route' => 'tenant.favorites.index',
                    'icon' => 'ri-heart',
                    'active' => request()->routeIs('tenant.favorites.*'),
                ],
                [
                    'label' => 'Booking',
                    'route' => 'tenant.booking.index',
                    'icon' => 'ri-calendar-check',
                    'active' => request()->routeIs('tenant.booking.*'),
                    'badge' => (int) ($sidebarData['activeBooking'] ?? 0),
                ],
                [
                    'label' => 'Tagihan',
                    'route' => 'tenant.tagihan.index',
                    'icon' => 'ri-file-list-3',
                    'active' => request()->routeIs('tenant.tagihan.*', 'tenant.pembayaran.*'),
                    'badge' => (int) ($sidebarData['tagihanBelum'] ?? 0),
                    'badgeClass' => 'bg-red-500',
                ],
                [
                    'label' => 'Profil',
                    'route' => 'profile.edit',
                    'icon' => 'ri-user-3',
                    'active' => request()->routeIs('profile.*'),
                ],
            ];
        } else {
            $routePrefix = ($user->isOwner() || $user->isSuperAdmin()) ? 'owner' : 'admin';

            $items = [
                [
                    'label' => 'Beranda',
                    'route' => 'dashboard',
                    'icon' => 'ri-home-5',
                    'active' => request()->routeIs('dashboard'),
                ],
                [
                    'label' => 'Kos',
                    'route' => $routePrefix.'.kos.index',
                    'icon' => 'ri-building-2',
                    'active' => request()->routeIs($routePrefix.'.kos.*'),
                ],
                [
                    'label' => 'Booking',
                    'route' => $routePrefix.'.booking.index',
                    'icon' => 'ri-calendar-check',
                    'active' => request()->routeIs($routePrefix.'.booking.*'),
                    'badge' => (int) ($sidebarData['bookingPending'] ?? 0),
                ],
                [
                    'label' => 'Tagihan',
                    'route' => $routePrefix.'.tagihan.index',
                    'icon' => 'ri-file-list-3',
                    'active' => request()->routeIs($routePrefix.'.tagihan.*', $routePrefix.'.pembayaran.*'),
                ],
                [
                    'label' => 'Profil',
                    'route' => 'profile.edit',
                    'icon' => 'ri-user-3',
                    'active' => request()->routeIs('profile.*'),
                ],
            ];
        }
    }
@endphp

@if($user && !empty($items))
    {{-- Mobile Bottom Navigation — tampil untuk semua role --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200/60 dark:border-slate-800 safe-area-bottom" aria-label="Navigasi utama">
        <div class="flex items-stretch justify-around h-[var(--bottom-nav-h)] px-1.5">
            @foreach($items as $item)
                @php
                    $active = $item['active'];
                    $badge = (int) ($item['badge'] ?? 0);
                    $badgeClass = $item['badgeClass'] ?? 'bg-primary-500';
                @endphp

                <a href="{{ route($item['route']) }}"
                   class="relative flex min-w-0 flex-1 flex-col items-center justify-center gap-1 px-1 py-1.5 rounded-xl transition-all {{ $active ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 active:text-slate-600' }}"
                   aria-label="{{ $item['label'] }}"
                   @if($active) aria-current="page" @endif>
                    <i class="{{ $item['icon'] }}-{{ $active ? 'fill' : 'line' }} text-xl" aria-hidden="true"></i>
                    <span class="text-[11px] font-semibold leading-none whitespace-nowrap">{{ $item['label'] }}</span>

                    @if($badge > 0)
                        <span class="absolute top-0.5 right-1 min-w-[18px] h-[18px] px-1 rounded-full {{ $badgeClass }} text-white text-[10px] font-bold flex items-center justify-center">{{ $badge }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>
@endif
