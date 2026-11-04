@php
    $user = Auth::user();
    $currentRoute = request()->route()->getName() ?? '';
    $sidebarData = $sidebarBadges ?? [];

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

    // Role accent untuk header icon - biar premium tiap role tetap terasa
    $roleAccent = match(true) {
        $user->hasRole('super_admin') => 'from-violet-600 to-indigo-600',
        $user->isOwner() => 'from-orange-500 to-orange-600',
        $user->isAdmin() => 'from-sky-600 to-blue-600',
        default => 'from-orange-500 to-orange-600',
    };
    $roleLabel = match(true) {
        $user->hasRole('super_admin') => 'Super Admin',
        $user->isOwner() => 'Owner',
        $user->isAdmin() => 'Admin Kos',
        default => 'Tenant',
    };

    // Extract frequently used badge values with defaults
    $bookingPending = $sidebarData['bookingPending'] ?? 0;
    $tagihanBelum = $sidebarData['tagihanBelum'] ?? 0;
    $unreadMessages = $sidebarData['unreadMessages'] ?? 0;
    $invitationPending = $sidebarData['invitationPending'] ?? 0;
    $unreadNotif = $sidebarData['unreadNotif'] ?? 0;
    $unreadAdminMessages = $sidebarData['unreadAdminMessages'] ?? 0;
    $pendingDamageReports = $sidebarData['pendingDamageReports'] ?? 0;
    $pendingCheckouts = $sidebarData['pendingCheckouts'] ?? 0;
@endphp

 {{-- Sidebar: light = putih premium, dark = slate-950 â€” berlaku untuk SEMUA role --}}
<aside id="main-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-dvh flex flex-col border-r transition-all duration-300 lg:translate-x-0 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100"
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full lg:translate-x-0': !sidebarOpen,
        'lg:w-20': sidebarCollapsed,
        'lg:w-64': !sidebarCollapsed
    }">

<div class="flex items-center gap-3 px-5 h-16 border-b shrink-0 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 transition-all"
     :class="{ 'lg:px-2 lg:gap-1': sidebarCollapsed }">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $roleAccent }} flex items-center justify-center shadow-lg shadow-black/5 dark:shadow-black/20 shrink-0">
            <span class="text-white font-black text-xs">K</span>
        </div>
        <div class="min-w-0" :class="{ 'lg:hidden': sidebarCollapsed }">
            <span class="text-[13px] font-black tracking-tight text-slate-900 dark:text-white leading-none">KosManager</span>
            <p class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400 leading-none mt-1">{{ $roleLabel }} Â· Panel</p>
        </div>
        <button type="button" @click="sidebarOpen = false"
                class="lg:hidden absolute right-2.5 top-2.5 z-10 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-slate-200 text-slate-600 shadow-sm transition hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
                aria-label="Tutup menu" :aria-expanded="sidebarOpen.toString()" id="close-btn"
                x-show="sidebarOpen">
            <i class="ri-close-line text-lg"></i>
        </button>
        <button type="button"
                @click="toggleSidebar()"
                id="sidebar-collapse"
                class="ml-auto hidden lg:grid h-7 w-7 place-items-center rounded-lg bg-slate-100 dark:bg-slate-800/10 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500"
                :class="{ 'lg:h-6 lg:w-6': sidebarCollapsed }"
                :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
                :aria-expanded="(!sidebarCollapsed).toString()"
                aria-controls="main-sidebar"
                :title="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'">
            <i class="ri-layout-left-line text-sm transition-transform" :class="{ 'rotate-180': sidebarCollapsed }"></i>
        </button>
</div>

<nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5 scrollbar-thin" aria-label="Navigasi sidebar">

        @if(!$user->isTenant())
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all
                      {{ $isActive('dashboard') ? 'bg-slate-800 text-white dark:bg-slate-700 dark:text-slate-100 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}"
               :class="{ 'lg:justify-center lg:px-0': sidebarCollapsed }"
               title="Dashboard"
               aria-label="Dashboard"
               @if($isActive('dashboard')) aria-current="page" @endif>
                <span class="relative flex items-center justify-center w-5 shrink-0">
                    @if($isActive('dashboard'))
                        <span class="hidden dark:block absolute -left-3 w-[3px] h-5 bg-slate-100 rounded-full" :class="{ 'lg:hidden': sidebarCollapsed }"></span>
                        <span class="dark:hidden absolute -left-3 w-[3px] h-5 bg-slate-900 rounded-full" :class="{ 'lg:hidden': sidebarCollapsed }"></span>
                    @endif
                    <i class="ri-dashboard-3-line text-[18px]"></i>
                </span>
                <span :class="{ 'lg:hidden': sidebarCollapsed }">Dashboard</span>
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
                        $navItem('owner.booking.index', 'Booking', 'ri-calendar-check-line', $bookingPending),
                        $navItem('owner.penghuni.index', 'Penghuni', 'ri-user-star-line'),
                        $navItem('owner.kontrak.index', 'Kontrak', 'ri-file-text-line'),
                        $navItem('owner.checkin.index', 'Check-in', 'ri-login-box-line'),
                        $navItem('owner.checkout.index', 'Check-out', 'ri-logout-box-line'),
                    ];
                    $groups['Keuangan'] = [
                        $navItem('owner.tagihan.index', 'Tagihan', 'ri-file-list-3-line'),
                        $navItem('owner.pembayaran.index', 'Pembayaran', 'ri-money-dollar-circle-line'),
                        $navItem('owner.distribution.index', 'Dana Booking Masuk', 'ri-wallet-3-line'),
                        $navItem('owner.payouts.index', 'Transfer ke Pemilik Kos', 'ri-send-plane-line'),
                    ];
                    $groups['Sistem'] = [
                        $navItem('super-admin.laporan.index', 'Laporan', 'ri-bar-chart-grouped-line'),
                        $navItem('super-admin.audit-log.index', 'Activity Log', 'ri-history-line'),
                    ];
                    $groups['Monetisasi'] = [
                        $navItem('super-admin.partners.index', 'Partner', 'ri-handshake-line'),
                        $navItem('super-admin.advertising.dashboard', 'Advertising', 'ri-megaphone-line'),
                        $navItem('super-admin.advertising.packages.index', 'Paket Iklan', 'ri-price-tag-3-line'),
                        $navItem('super-admin.advertising.campaigns.index', 'Kampanye', 'ri-bill-line'),
                        $navItem('super-admin.advertising.revenue', 'Revenue', 'ri-line-chart-line'),
                    ];
                } elseif ($user->isOwner()) {
                    $groups['Manajemen'] = [
                        $navItem('owner.kos.index', 'Kelola Kos', 'ri-building-2-line'),
                        $navItem('owner.kamar.index', 'Kelola Kamar', 'ri-door-open-line'),
                        $navItem('tenant.marketplace.index', 'Marketplace', 'ri-price-tag-line'),
                    ];
                    $groups['Transaksi'] = [
                        $navItem('owner.booking.index', 'Booking', 'ri-calendar-check-line', $bookingPending),
                        $navItem('owner.penghuni.index', 'Penghuni', 'ri-user-star-line'),
                        $navItem('owner.kontrak.index', 'Kontrak', 'ri-file-text-line'),
                        $navItem('owner.checkin.index', 'Check-in', 'ri-login-box-line'),
                        $navItem('owner.checkout.index', 'Check-out', 'ri-logout-box-line'),
                    ];
                    $groups['Keuangan'] = [
                        $navItem('owner.tagihan.index', 'Tagihan', 'ri-file-list-3-line'),
                        $navItem('owner.pembayaran.index', 'Pembayaran', 'ri-money-dollar-circle-line'),
                        $navItem('owner.distribution.index', 'Dana Booking Masuk', 'ri-wallet-3-line'),
                        $navItem('owner.payouts.index', 'Transfer ke Pemilik Kos', 'ri-send-plane-line'),
                    ];
                    $groups['Komunikasi'] = [
                        $navItem('owner.messages.index', 'Pesan Tenant', 'ri-customer-service-2-line', $unreadMessages),
                    ];
                    $groups['Lainnya'] = [
                        $navItem('owner.laporan.index', 'Laporan', 'ri-file-chart-line'),
                        $navItem('owner.advertising.dashboard', 'Advertising', 'ri-megaphone-line'),
                    ];
                } else {
                    $groups['Manajemen'] = [
                        $navItem('admin.kos.index', 'Kelola Kos', 'ri-building-2-line'),
                        $navItem('admin.kamar.index', 'Kelola Kamar', 'ri-door-open-line'),
                    ];
                    $groups['Transaksi'] = [
                        $navItem('admin.booking.index', 'Booking', 'ri-calendar-check-line', $bookingPending),
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
                        $navItem('admin.advertising.index', 'Advertising', 'ri-megaphone-line'),
                    ];
                }
            @endphp

            @foreach($groups as $groupLabel => $items)
                @if(!empty($items))
                    <div class="pt-6 pb-2 px-3" :class="{ 'lg:hidden': sidebarCollapsed }">
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.18em]">{{ $groupLabel }}</p>
                    </div>
                @endif

               @foreach($items as $item)
                    @php
                        $active = $isActive($item['route'].'*') || $isActive($item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all
                              {{ $active ? 'bg-slate-800 text-white dark:bg-slate-700 dark:text-slate-100 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}"
                       :class="{ 'lg:justify-center lg:px-0': sidebarCollapsed }"
                       title="{{ $item['label'] }}"
                       aria-label="{{ $item['label'] }}"
                       @if($active) aria-current="page" @endif>
                        <span class="relative flex items-center justify-center w-5 shrink-0">
                            @if($active)
                                <span class="hidden dark:block absolute -left-3 w-[3px] h-5 bg-slate-100 rounded-full" :class="{ 'lg:hidden': sidebarCollapsed }"></span>
                                <span class="dark:hidden absolute -left-3 w-[3px] h-5 bg-slate-900 rounded-full" :class="{ 'lg:hidden': sidebarCollapsed }"></span>
                            @endif
                            <i class="{{ $item['icon'] }} text-[18px]"></i>
                        </span>
                        <span :class="{ 'lg:hidden': sidebarCollapsed }">{{ $item['label'] }}</span>
                        @if(!empty($item['badge']) && $item['badge'] > 0)
                            <span class="ml-auto text-[10px] font-black text-white bg-orange-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center shadow-sm"
                                  :class="{ 'lg:absolute lg:top-0 lg:right-0 lg:ml-0': sidebarCollapsed }">{{ $item['badge'] }}</span>
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
                        ['route' => 'tenant.marketplace*', 'label' => 'Marketplace', 'icon' => 'ri-price-tag-line'],
                    ],
                    'Aktivitas' => [
                        ['route' => 'tenant.booking*', 'label' => 'Booking Saya', 'icon' => 'ri-calendar-check-line'],
                        ['route' => 'tenant.kontrak*', 'label' => 'Kontrak', 'icon' => 'ri-file-text-line', 'badge' => $tagihanBelum, 'badgeClass' => 'bg-red-500'],
                        ['route' => 'tenant.pembayaran*', 'label' => 'Pembayaran', 'icon' => 'ri-money-dollar-circle-line'],
                    ],
                    'Bantuan' => [
                        ['route' => 'tenant.messages*', 'label' => 'Hubungi Owner', 'icon' => 'ri-customer-service-2-line', 'badge' => $unreadMessages, 'badgeClass' => 'bg-gradient-to-br from-orange-500 to-orange-600'],
                        ['route' => 'tenant.invitations*', 'label' => 'Undangan Kos', 'icon' => 'ri-mail-send-line', 'badge' => $invitationPending, 'badgeClass' => 'bg-amber-500'],
                    ],
                ];
            @endphp

            @foreach($groups as $groupLabel => $items)
                <div class="pt-6 pb-2 px-3" :class="{ 'lg:hidden': sidebarCollapsed }">
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.18em]">{{ $groupLabel }}</p>
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
                       class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all
                              {{ $active ? 'bg-slate-800 text-white dark:bg-slate-700 dark:text-slate-100 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]' }}"
                       :class="{ 'lg:justify-center lg:px-0': sidebarCollapsed }"
                       title="{{ $item['label'] }}"
                       aria-label="{{ $item['label'] }}"
                       @if($active) aria-current="page" @endif>
                        <span class="relative flex items-center justify-center w-5 shrink-0">
                            @if($active)
                                <span class="hidden dark:block absolute -left-3 w-[3px] h-5 bg-slate-100 rounded-full" :class="{ 'lg:hidden': sidebarCollapsed }"></span>
                                <span class="dark:hidden absolute -left-3 w-[3px] h-5 bg-slate-900 rounded-full" :class="{ 'lg:hidden': sidebarCollapsed }"></span>
                            @endif
                            <i class="{{ $item['icon'] }} text-[18px]"></i>
                        </span>
                        <span :class="{ 'lg:hidden': sidebarCollapsed }">{{ $item['label'] }}</span>
                        @if(!empty($item['badge']) && $item['badge'] > 0)
                            <span class="ml-auto text-[10px] font-black text-white {{ $item['badgeClass'] ?? 'bg-orange-500' }} px-1.5 py-0.5 rounded-full min-w-[20px] text-center shadow-sm"
                                  :class="{ 'lg:absolute lg:top-0 lg:right-0 lg:ml-0': sidebarCollapsed }">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach

            @if(($unreadNotif) > 0)
                <div class="pt-6 pb-2 px-3" :class="{ 'lg:hidden': sidebarCollapsed }">
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.18em]">Notifikasi</p>
                </div>
                <a href="{{ route('notifications.index') }}"
                   class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]"
                   :class="{ 'lg:justify-center lg:px-0': sidebarCollapsed }"
                   title="Notifikasi"
                   aria-label="Notifikasi">
                    <span class="relative flex items-center justify-center w-5 shrink-0">
                        <i class="ri-notification-3-line text-[18px]"></i>
                    </span>
                    <span :class="{ 'lg:hidden': sidebarCollapsed }">Notifikasi</span>
                    <span class="ml-auto text-[10px] font-black text-white bg-orange-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center shadow-sm"
                          :class="{ 'lg:absolute lg:top-0 lg:right-0 lg:ml-0': sidebarCollapsed }">{{ $unreadNotif }}</span>
                </a>
            @endif
        @endif
    </nav>

    <div class="px-3 py-4 border-t shrink-0 bg-slate-100 dark:bg-slate-800 border-slate-100 dark:border-white/5"
         :class="{ 'lg:px-2': sidebarCollapsed }">
    <button type="button" x-data
                @click="
                    document.documentElement.classList.toggle('dark');
                    localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                "
                class="group flex w-full min-h-[44px] items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-all"
                :class="{ 'lg:justify-center lg:px-0': sidebarCollapsed }"
                aria-label="Ganti mode tampilan">
            <span class="grid h-8 w-8 place-items-center rounded-xl bg-slate-100 dark:bg-slate-100/10 text-slate-600 dark:text-slate-300 transition">
                <i class="ri-moon-line text-[18px] dark:hidden"></i>
                <i class="ri-sun-line text-[18px] hidden dark:block"></i>
            </span>
            <span class="dark:hidden" :class="{ 'lg:hidden': sidebarCollapsed }">Mode Gelap</span>
            <span class="hidden dark:inline" :class="{ 'lg:hidden': sidebarCollapsed }">Mode Terang</span>
        </button>
        <a href="{{ route('profile.edit') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-all"
           :class="{ 'lg:justify-center lg:px-0': sidebarCollapsed }"
           title="Pengaturan"
           aria-label="Pengaturan">
            <span class="grid h-8 w-8 place-items-center rounded-xl bg-slate-100 dark:bg-slate-100/10 text-slate-600 dark:text-slate-300 group-hover:bg-slate-900 group-hover:text-white dark:group-hover:bg-slate-100 dark:group-hover:text-slate-900 transition">
                <i class="ri-settings-3-line text-[18px]"></i>
            </span>
            <span :class="{ 'lg:hidden': sidebarCollapsed }">Pengaturan</span>
        </a>
        <p class="mt-2 px-3 text-[10px] font-medium text-slate-400 dark:text-slate-500" :class="{ 'lg:hidden': sidebarCollapsed }">Â© {{ date('Y') }} KosManager</p>
    </div>
</aside>

<div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-transition:leave-start="opacity-0"
     class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 backdrop-blur-sm z-30 lg:hidden"></div>
