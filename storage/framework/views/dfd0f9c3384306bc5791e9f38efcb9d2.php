<?php
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

    // Role accent untuk header icon — biar premium tiap role tetap terasa
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
?>


<aside class="fixed top-0 left-0 z-40 w-64 h-screen flex flex-col border-r transition-transform lg:translate-x-0 bg-white dark:bg-slate-950 border-slate-200/70 dark:border-white/5 text-slate-700 dark:text-slate-300"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <div class="flex items-center gap-3 px-5 h-16 border-b shrink-0 bg-white dark:bg-slate-950 border-slate-100 dark:border-white/5">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br <?php echo e($roleAccent); ?> flex items-center justify-center shadow-lg shadow-black/5 dark:shadow-black/20 shrink-0">
            <span class="text-white font-black text-xs">K</span>
        </div>
        <div class="min-w-0">
            <span class="text-[13px] font-black tracking-tight text-slate-900 dark:text-white leading-none">KosManager</span>
            <p class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400 leading-none mt-1"><?php echo e($roleLabel); ?> · Panel</p>
        </div>
        <span class="ml-auto hidden lg:grid h-7 w-7 place-items-center rounded-lg bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300">
            <i class="ri-layout-left-line text-sm"></i>
        </span>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5 scrollbar-thin" aria-label="Navigasi sidebar">

        <?php if(!$user->isTenant()): ?>
            <a href="<?php echo e(route('dashboard')); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all
                      <?php echo e($isActive('dashboard') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]'); ?>"
               <?php if($isActive('dashboard')): ?> aria-current="page" <?php endif; ?>>
                <span class="relative flex items-center justify-center w-5 shrink-0">
                    <?php if($isActive('dashboard')): ?>
                        <span class="hidden dark:block absolute -left-3 w-[3px] h-5 bg-white rounded-full"></span>
                        <span class="dark:hidden absolute -left-3 w-[3px] h-5 bg-slate-900 rounded-full"></span>
                    <?php endif; ?>
                    <i class="ri-dashboard-3-line text-[18px]"></i>
                </span>
                Dashboard
            </a>
        <?php endif; ?>

        <?php
            $tenantSections = $user->isTenant();
        ?>

        <?php if(!$tenantSections): ?>
            <?php
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
                    $groups['Komunikasi'] = [
                        $navItem('owner.messages.index', 'Pesan Tenant', 'ri-customer-service-2-line', $badges['unreadMessages'] ?? 0),
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
                        $navItem('admin.advertising.index', 'Advertising', 'ri-megaphone-line'),
                    ];
                }
            ?>

            <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupLabel => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!empty($items)): ?>
                    <div class="pt-6 pb-2 px-3">
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.18em]"><?php echo e($groupLabel); ?></p>
                    </div>
                <?php endif; ?>

                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $active = $isActive($item['route'].'*') || $isActive($item['route']);
                    ?>
                    <a href="<?php echo e(route($item['route'])); ?>"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all
                              <?php echo e($active ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]'); ?>"
                       <?php if($active): ?> aria-current="page" <?php endif; ?>>
                        <span class="relative flex items-center justify-center w-5 shrink-0">
                            <?php if($active): ?>
                                <span class="hidden dark:block absolute -left-3 w-[3px] h-5 bg-white rounded-full"></span>
                                <span class="dark:hidden absolute -left-3 w-[3px] h-5 bg-slate-900 rounded-full"></span>
                            <?php endif; ?>
                            <i class="<?php echo e($item['icon']); ?> text-[18px]"></i>
                        </span>
                        <?php echo e($item['label']); ?>

                        <?php if(!empty($item['badge']) && $item['badge'] > 0): ?>
                            <span class="ml-auto text-[10px] font-black text-white bg-orange-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center shadow-sm"><?php echo e($item['badge']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            
            <?php
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
                    'Bantuan' => [
                        ['route' => 'tenant.messages*', 'label' => 'Hubungi Owner', 'icon' => 'ri-customer-service-2-line', 'badge' => $badges['unreadMessages'] ?? 0, 'badgeClass' => 'bg-gradient-to-br from-orange-500 to-orange-600'],
                        ['route' => 'tenant.invitations*', 'label' => 'Undangan Kos', 'icon' => 'ri-mail-send-line', 'badge' => $badges['invitationPending'] ?? 0, 'badgeClass' => 'bg-amber-500'],
                    ],
                ];
            ?>

            <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupLabel => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="pt-6 pb-2 px-3">
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.18em]"><?php echo e($groupLabel); ?></p>
                </div>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $routeWithoutStar = rtrim($item['route'], '*');
                        $active = $item['exact'] ?? false
                            ? $currentRoute === $routeWithoutStar
                            : str_starts_with($currentRoute, $routeWithoutStar);
                        $routeName = $routeWithoutStar === 'dashboard' ? 'dashboard' : $routeWithoutStar.'.index';
                    ?>
                    <a href="<?php echo e(route($routeName)); ?>"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all
                              <?php echo e($active ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]'); ?>"
                       <?php if($active): ?> aria-current="page" <?php endif; ?>>
                        <span class="relative flex items-center justify-center w-5 shrink-0">
                            <?php if($active): ?>
                                <span class="hidden dark:block absolute -left-3 w-[3px] h-5 bg-white rounded-full"></span>
                                <span class="dark:hidden absolute -left-3 w-[3px] h-5 bg-slate-900 rounded-full"></span>
                            <?php endif; ?>
                            <i class="<?php echo e($item['icon']); ?> text-[18px]"></i>
                        </span>
                        <?php echo e($item['label']); ?>

                        <?php if(!empty($item['badge']) && $item['badge'] > 0): ?>
                            <span class="ml-auto text-[10px] font-black text-white <?php echo e($item['badgeClass'] ?? 'bg-orange-500'); ?> px-1.5 py-0.5 rounded-full min-w-[20px] text-center shadow-sm"><?php echo e($item['badge']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if(($badges['unreadNotif'] ?? 0) > 0): ?>
                <div class="pt-6 pb-2 px-3">
                    <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.18em]">Notifikasi</p>
                </div>
                <a href="<?php echo e(route('notifications.index')); ?>"
                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06]">
                    <span class="relative flex items-center justify-center w-5 shrink-0">
                        <i class="ri-notification-3-line text-[18px]"></i>
                    </span>
                    Notifikasi
                    <span class="ml-auto text-[10px] font-black text-white bg-orange-500 px-1.5 py-0.5 rounded-full min-w-[20px] text-center shadow-sm"><?php echo e($badges['unreadNotif']); ?></span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="px-3 py-4 border-t shrink-0 bg-white dark:bg-slate-950 border-slate-100 dark:border-white/5">
        <a href="<?php echo e(route('profile.edit')); ?>"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-all">
            <span class="grid h-8 w-8 place-items-center rounded-xl bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 group-hover:bg-slate-900 group-hover:text-white dark:group-hover:bg-white dark:group-hover:text-slate-900 transition">
                <i class="ri-settings-3-line text-[18px]"></i>
            </span>
            Pengaturan
        </a>
        <p class="mt-2 px-3 text-[10px] font-medium text-slate-400 dark:text-slate-500">© <?php echo e(date('Y')); ?> KosManager</p>
    </div>
</aside>

<div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/50 dark:bg-black/60 backdrop-blur-sm z-30 lg:hidden"></div>
<?php /**PATH C:\Users\ASUS\KosManager\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>