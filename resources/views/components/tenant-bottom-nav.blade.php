@php
    $user = Auth::user();
    $sidebarData = $sidebarBadges ?? [];
    $isTenant = $user?->isTenant() ?? false;

    $items = [];

    if ($user) {
        if ($user->isSuperAdmin()) {
            $items = [
                [
                    'label' => 'Beranda',
                    'route' => 'dashboard',
                    'icon' => 'ri-home-5',
                    'active' => request()->routeIs('dashboard'),
                ],
                [
                    'label' => 'User',
                    'route' => 'super-admin.users.index',
                    'icon' => 'ri-team-line',
                    'active' => request()->routeIs('super-admin.users.*'),
                    'badge' => (int) (\App\Models\User::where('is_active', false)->count()),
                ],
                [
                    'label' => 'Moderasi',
                    'route' => 'super-admin.moderation.index',
                    'icon' => 'ri-shield-check-line',
                    'active' => request()->routeIs('super-admin.moderation.*'),
                ],
                [
                    'label' => 'Settings',
                    'route' => 'super-admin.settings.index',
                    'icon' => 'ri-settings-3-line',
                    'active' => request()->routeIs('super-admin.settings.*'),
                ],
                [
                    'label' => 'Profil',
                    'route' => 'profile.edit',
                    'icon' => 'ri-user-3',
                    'active' => request()->routeIs('profile.*'),
                ],
            ];
        } elseif ($isTenant) {
            $items = [
                [
                    'label' => 'Beranda',
                    'route' => 'dashboard',
                    'icon' => 'ri-home-5',
                    'active' => request()->routeIs('dashboard'),
                ],
                [
                    'label' => 'Cari Kos',
                    'route' => 'tenant.kos.index',
                    'icon' => 'ri-search-line',
                    'active' => request()->routeIs('tenant.kos.*'),
                ],
                [
                    'label' => 'Booking',
                    'route' => 'tenant.booking.index',
                    'icon' => 'ri-calendar-check',
                    'active' => request()->routeIs('tenant.booking.*', 'tenant.checkin.*'),
                    'badge' => (int) ($sidebarData['activeBooking'] ?? 0),
                    'subtabs' => [
                        ['label' => 'Booking', 'route' => 'tenant.booking.index', 'active' => request()->routeIs('tenant.booking.*')],
                        ['label' => 'Check-In', 'route' => 'tenant.checkin.index', 'active' => request()->routeIs('tenant.checkin.*')],
                    ],
                ],
                [
                    'label' => 'Owner',
                    'route' => 'tenant.messages.index',
                    'icon' => 'ri-chat-3',
                    'active' => request()->routeIs('tenant.messages.*'),
                    'badge' => (int) ($sidebarData['unreadMessages'] ?? 0),
                    'badgeClass' => 'bg-orange-500',
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
                    'active' => request()->routeIs('profile.*', 'tenant.favorites.*', 'tenant.dispute.*', 'tenant.damage-reports.*', 'tenant.laporan.*'),
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
                    'active' => request()->routeIs($routePrefix.'.booking.*', $routePrefix.'.checkin.*', $routePrefix.'.checkout.*'),
                    'badge' => (int) ($sidebarData['bookingPending'] ?? 0),
                    'subtabs' => [
                        ['label' => 'Booking', 'route' => $routePrefix.'.booking.index', 'active' => request()->routeIs($routePrefix.'.booking.*')],
                        ['label' => 'Check-In', 'route' => $routePrefix.'.checkin.index', 'active' => request()->routeIs($routePrefix.'.checkin.*')],
                        ['label' => 'Check-Out', 'route' => $routePrefix.'.checkout.index', 'active' => request()->routeIs($routePrefix.'.checkout.*')],
                    ],
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
                   class="relative flex min-w-0 flex-1 flex-col items-center justify-center gap-1 px-1 py-1.5 rounded-xl transition-all {{ $active ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400' : 'text-slate-500 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 active:text-slate-600' }}"
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

    {{-- Sub-tab bar for Booking (owner/admin/tenant) --}}
    @foreach($items as $item)
        @if(!empty($item['subtabs']) && $item['active'])
            <div class="lg:hidden fixed bottom-[var(--bottom-nav-h)] left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200/60 dark:border-slate-800 safe-area-bottom">
                <div class="flex items-stretch justify-around h-10 px-2">
                    @foreach($item['subtabs'] as $subtab)
                        <a href="{{ route($subtab['route']) }}"
                           class="flex-1 flex items-center justify-center text-xs font-semibold transition {{ $subtab['active'] ? 'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300' }}"
                           aria-label="{{ $subtab['label'] }}">
                            {{ $subtab['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
@endif
