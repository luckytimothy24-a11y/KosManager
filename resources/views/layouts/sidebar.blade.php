@php
    $user = Auth::user();
    $currentRoute = request()->route()->getName() ?? '';
    $badges = $sidebarBadges ?? [];

    $navItem = fn ($route, $label, $icon, $badge = null) => [
        'route' => $route,
        'label' => $label,
        'icon' => $icon,
        'badge' => $badge,
    ];

    $isActive = function ($route) use ($currentRoute) {
        if (str_ends_with($route, '*')) {
            return str_starts_with($currentRoute, rtrim($route, '*'));
        }
        return $currentRoute === $route;
    };
@endphp

<aside class="fixed top-0 left-0 z-40 w-64 h-screen bg-slate-950 text-slate-300 transition-transform lg:translate-x-0 flex flex-col"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <div class="flex items-center gap-3 px-5 h-16 border-b border-white/5 shrink-0">
        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
            <span class="text-white font-bold text-xs">K</span>
        </div>
        <div>
            <span class="text-sm font-bold text-white tracking-tight">KosManager</span>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium -mt-0.5 tracking-wider uppercase">Panel</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5" aria-label="Navigasi sidebar">

        @if(!$user->isTenant())
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                      {{ $isActive('dashboard') ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}"
               @if($isActive('dashboard')) aria-current="page" @endif>
                <span class="relative flex items-center justify-center w-5">
                    @if($isActive('dashboard'))
                        <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                    @endif
                    <i class="ri-dashboard-3-line text-lg"></i>
                </span>
                Dashboard
            </a>
        @endif

        @php
            $tenantSections = $user->isTenant();
        @endphp

        @if(!$tenantSections)
            @php
                $useOwnerPrefix = $user->isSuperAdmin() || $user->isOwner();
                $prefix = $useOwnerPrefix ? 'owner' : 'admin';

                $groups = [];

                if ($user->isSuperAdmin()) {
                    $groups['Manajemen'] = [
                        $navItem('super-admin.users.index', 'Manajemen User', 'ri-team-line'),
                        $navItem('owner.kos.index', 'Kelola Kos', 'ri-building-2-line'),
                        $navItem('owner.kamar.index', 'Kelola Kamar', 'ri-door-open-line'),
                        $navItem('super-admin.fasilitas.index', 'Fasilitas', 'ri-archive-drawer-line'),
                    ];
                    $groups['Transaksi'] = [
                        $navItem('owner.booking.index', 'Booking', 'ri-calendar-check-line', $badges['bookingPending'] ?? 0),
                        $navItem('owner.penghuni.index', 'Penghuni', 'ri-user-star-line'),
                        $navItem('owner.kontrak.index', 'Kontrak', 'ri-file-text-line'),
                        $navItem('owner.checkin.index', 'Check-in', 'ri-login-box-line'),
                        $navItem('owner.checkout.index', 'Check-out', 'ri-logout-box-line'),
                    ];
                    $groups['Keuangan'] = [
                        $navItem('owner.tagihan.index', 'Tagihan', 'ri-file-list-3-line'),
                        $navItem('owner.pembayaran.index', 'Pembayaran', 'ri-money-dollar-circle-line'),
                    ];
                    $groups['Sistem'] = [
                        $navItem('super-admin.laporan.index', 'Laporan', 'ri-bar-chart-grouped-line'),
                        $navItem('super-admin.audit-log.index', 'Activity Log', 'ri-history-line'),
                    ];
                } elseif ($user->isOwner()) {
                    $groups['Manajemen'] = [
                        $navItem('owner.kos.index', 'Kelola Kos', 'ri-building-2-line'),
                        $navItem('owner.kamar.index', 'Kelola Kamar', 'ri-door-open-line'),
                    ];
                    $groups['Transaksi'] = [
                        $navItem('owner.booking.index', 'Booking', 'ri-calendar-check-line', $badges['bookingPending'] ?? 0),
                        $navItem('owner.penghuni.index', 'Penghuni', 'ri-user-star-line'),
                        $navItem('owner.kontrak.index', 'Kontrak', 'ri-file-text-line'),
                        $navItem('owner.checkin.index', 'Check-in', 'ri-login-box-line'),
                        $navItem('owner.checkout.index', 'Check-out', 'ri-logout-box-line'),
                    ];
                    $groups['Keuangan'] = [
                        $navItem('owner.tagihan.index', 'Tagihan', 'ri-file-list-3-line'),
                        $navItem('owner.pembayaran.index', 'Pembayaran', 'ri-money-dollar-circle-line'),
                    ];
                    $groups['Lainnya'] = [
                        $navItem('owner.laporan.index', 'Laporan', 'ri-file-chart-line'),
                    ];
                } else {
                    $groups['Manajemen'] = [
                        $navItem('admin.kamar.index', 'Kamar', 'ri-door-open-line'),
                    ];
                    $groups['Transaksi'] = [
                        $navItem('admin.booking.index', 'Booking', 'ri-calendar-check-line', $badges['bookingPending'] ?? 0),
                        $navItem('admin.penghuni.index', 'Penghuni', 'ri-user-star-line'),
                        $navItem('admin.kontrak.index', 'Kontrak', 'ri-file-text-line'),
                        $navItem('admin.checkin.index', 'Check-in', 'ri-login-box-line'),
                        $navItem('admin.checkout.index', 'Check-out', 'ri-logout-box-line'),
                    ];
                    $groups['Keuangan'] = [
                        $navItem('admin.tagihan.index', 'Tagihan', 'ri-file-list-3-line'),
                        $navItem('admin.pembayaran.index', 'Pembayaran', 'ri-money-dollar-circle-line'),
                    ];
                    $groups['Lainnya'] = [
                        $navItem('admin.laporan.index', 'Laporan', 'ri-bar-chart-grouped-line'),
                    ];
                }
            @endphp

            @foreach($groups as $groupLabel => $items)
                @if(!empty($items))
                    <div class="pt-6 pb-2 px-3">
                        <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">{{ $groupLabel }}</p>
                    </div>
                @endif

                @foreach($items as $item)
                    @php
                        $active = $isActive($item['route'].'*') || $isActive($item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                              {{ $active ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}"
                       @if($active) aria-current="page" @endif>
                        <span class="relative flex items-center justify-center w-5">
                            @if($active)
                                <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                            @endif
                            <i class="{{ $item['icon'] }} text-lg"></i>
                        </span>
                        {{ $item['label'] }}
                        @if(!empty($item['badge']) && $item['badge'] > 0)
                            <span class="ml-auto text-[10px] font-bold text-white bg-primary-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        @else
            {{-- ============ TENANT ============ --}}
            @php
                $groups = [
                    'Utama' => [
                        ['route' => 'dashboard*', 'label' => 'Dashboard', 'icon' => 'ri-dashboard-3-line', 'exact' => true],
                        ['route' => 'tenant.kos*', 'label' => 'Cari Kos', 'icon' => 'ri-search-eye-line'],
                        ['route' => 'tenant.favorites*', 'label' => 'Favorit', 'icon' => 'ri-heart-line'],
                    ],
                    'Aktivitas' => [
                        ['route' => 'tenant.booking*', 'label' => 'Booking Saya', 'icon' => 'ri-calendar-check-line'],
                        ['route' => 'tenant.kontrak*', 'label' => 'Kontrak', 'icon' => 'ri-file-text-line'],
                        ['route' => 'tenant.tagihan*', 'label' => 'Tagihan', 'icon' => 'ri-file-list-3-line', 'badge' => $badges['tagihanBelum'] ?? 0, 'badgeClass' => 'bg-red-500'],
                        ['route' => 'tenant.pembayaran*', 'label' => 'Pembayaran', 'icon' => 'ri-money-dollar-circle-line'],
                    ],
                ];
            @endphp

            @foreach($groups as $groupLabel => $items)
                <div class="pt-6 pb-2 px-3">
                    <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">{{ $groupLabel }}</p>
                </div>
                @foreach($items as $item)
                    @php
                        $routeWithoutStar = rtrim($item['route'], '*');
                        $active = $item['exact'] ?? false
                            ? $currentRoute === $routeWithoutStar
                            : str_starts_with($currentRoute, $routeWithoutStar);
                        $routeName = $routeWithoutStar === 'dashboard' ? 'dashboard' : $routeWithoutStar.'.index';
                    @endphp
                    <a href="{{ route($routeName) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                              {{ $active ? 'bg-white/5 text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]' }}"
                       @if($active) aria-current="page" @endif>
                        <span class="relative flex items-center justify-center w-5">
                            @if($active)
                                <span class="absolute -left-3 w-[3px] h-4 bg-primary-500 rounded-full"></span>
                            @endif
                            <i class="{{ $item['icon'] }} text-lg"></i>
                        </span>
                        {{ $item['label'] }}
                        @if(!empty($item['badge']) && $item['badge'] > 0)
                            <span class="ml-auto text-[10px] font-bold text-white {{ $item['badgeClass'] ?? 'bg-primary-500' }} px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach

            {{-- Bantuan / Notifikasi --}}
            @if(($badges['unreadNotif'] ?? 0) > 0)
                <div class="pt-6 pb-2 px-3">
                    <p class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Bantuan</p>
                </div>
                <a href="{{ route('notifications.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03]">
                    <span class="relative flex items-center justify-center w-5">
                        <i class="ri-notification-3-line text-lg"></i>
                    </span>
                    Notifikasi
                    <span class="ml-auto text-[10px] font-bold text-white bg-primary-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center">{{ $badges['unreadNotif'] }}</span>
                </a>
            @endif
        @endif
    </nav>

    <div class="px-3 py-4 border-t border-white/5 shrink-0">
        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-300 hover:bg-white/[0.03] transition-all">
            <i class="ri-settings-3-line text-lg"></i>
            Pengaturan
        </a>
    </div>
</aside>

<div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden"></div>
