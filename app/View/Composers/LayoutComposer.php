<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class LayoutComposer
{
    public function compose(View $view): void
    {
        $view->with('layoutPageTitle', $this->pageTitle());
        if ($view->getName() === 'layouts.sidebar') {
            $view->with('layoutSidebar', $this->sidebarData());
        }
    }

    private function pageTitle(): ?string
    {
        $routeName = optional(request()->route())->getName();
        $map = config('page-titles', []);

        return $map[$routeName] ?? null;
    }

    private static array $routeCache = [];

    private function resolveRoute(string $pattern): ?string
    {
        $base = rtrim($pattern, '*');
        if (array_key_exists($base, self::$routeCache)) {
            return self::$routeCache[$base];
        }
        if (Route::has($base)) {
            return self::$routeCache[$base] = $base;
        }
        $withIndex = $base.'.index';
        if (Route::has($withIndex)) {
            return self::$routeCache[$base] = $withIndex;
        }

        return self::$routeCache[$base] = null;
    }

    private function tenantGroups(): array
    {
        $definitions = [
            'Utama' => [
                ['route' => 'dashboard*', 'label' => 'Dashboard', 'icon' => 'ri-dashboard-3-line', 'exact' => true],
                ['route' => 'tenant.kos*', 'label' => 'Cari Kos', 'icon' => 'ri-search-eye-line'],
                ['route' => 'tenant.favorites*', 'label' => 'Favorit', 'icon' => 'ri-heart-line'],
            ],
            'Aktivitas' => [
                ['route' => 'tenant.booking*', 'label' => 'Booking Saya', 'icon' => 'ri-calendar-check-line'],
                ['route' => 'tenant.kontrak*', 'label' => 'Kontrak', 'icon' => 'ri-file-text-line'],
                ['route' => 'tenant.tagihan*', 'label' => 'Tagihan', 'icon' => 'ri-file-list-3-line', 'badgeKey' => 'tagihanBelum', 'badgeClass' => 'bg-red-500'],
                ['route' => 'tenant.pembayaran*', 'label' => 'Pembayaran', 'icon' => 'ri-money-dollar-circle-line'],
            ],
            'Bantuan' => [
                ['route' => 'tenant.messages*', 'label' => 'Hubungi Owner', 'icon' => 'ri-customer-service-2-line', 'badgeKey' => 'unreadMessages', 'badgeClass' => 'bg-gradient-to-br from-orange-500 to-orange-600'],
                ['route' => 'tenant.invitations*', 'label' => 'Undangan Kos', 'icon' => 'ri-mail-send-line', 'badgeKey' => 'invitationPending', 'badgeClass' => 'bg-amber-500'],
            ],
        ];

        foreach ($definitions as $group => $items) {
            foreach ($items as $idx => $item) {
                $definitions[$group][$idx]['resolvedRoute'] = $this->resolveRoute($item['route']);
            }
        }

        return $definitions;
    }

    private function sidebarData(): array
    {
        $user = auth()->user();
        if (! $user) {
            return ['roleAccent' => 'from-primary-600 to-primary-700', 'roleLabel' => 'Tenant', 'isTenant' => false, 'isActive' => fn ($r) => false, 'currentRoute' => '', 'tenantGroups' => $this->tenantGroups()];
        }
        $currentRoute = optional(request()->route())->getName() ?? '';
        $isActive = function ($route) use ($currentRoute) {
            if (str_ends_with($route, '*')) {
                return str_starts_with($currentRoute, rtrim($route, '*'));
            }

            return $currentRoute === $route;
        };
        $roleAccent = match (true) {
            $user->hasRole('super_admin') => 'from-violet-600 to-indigo-600',
            $user->isOwner() => 'from-primary-600 to-primary-700',
            $user->isAdmin() => 'from-sky-600 to-blue-600',
            default => 'from-primary-600 to-primary-700',
        };
        $roleLabel = match (true) {
            $user->hasRole('super_admin') => 'Super Admin',
            $user->isOwner() => 'Owner',
            $user->isAdmin() => 'Admin Kos',
            default => 'Tenant',
        };

        return [
            'roleAccent' => $roleAccent,
            'roleLabel' => $roleLabel,
            'isTenant' => $user->isTenant(),
            'isActive' => $isActive,
            'currentRoute' => $currentRoute,
            'tenantGroups' => $this->tenantGroups(),
        ];
    }
}
