<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\Tagihan;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.custom-simple');

        View::composer(['layouts.app', 'layouts.sidebar', 'components.tenant-bottom-nav'], function ($view) {
            $user = auth()->user();

            $badges = [
                'bookingPending' => 0,
                'tagihanBelum' => 0,
                'unreadNotif' => 0,
                'activeBooking' => 0,
            ];

            if ($user) {
                $badges['unreadNotif'] = Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                if ($user->hasRole('super_admin')) {
                    $badges['bookingPending'] = Booking::where('status', 'approved')
                        ->withoutActivePenghuni()
                        ->count();
                } elseif ($user->isOwner()) {
                    $badges['bookingPending'] = Booking::where('status', 'approved')
                        ->withoutActivePenghuni()
                        ->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))
                        ->count();
                } elseif ($user->isAdmin()) {
                    $kosIds = $user->assignedKos()->pluck('kos.id');
                    $badges['bookingPending'] = Booking::needsCheckin()
                        ->whereIn('kos_id', $kosIds)
                        ->count();
                } elseif ($user->isTenant()) {
                    $badges['tagihanBelum'] = Tagihan::payable()
                        ->whereHas('penghuni', fn ($q) => $q->where('user_id', $user->id))
                        ->count();
                    $badges['activeBooking'] = Booking::where('user_id', $user->id)
                        ->whereIn('status', Booking::activeStatuses())
                        ->whereDate('end_date', '>=', today()->toDateString())
                        ->count();
                }
            }

            $view->with('sidebarBadges', $badges);
        });
    }
}
