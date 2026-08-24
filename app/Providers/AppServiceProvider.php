<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Tagihan;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.sidebar', function ($view) {
            $user = auth()->user();

            $badges = [
                'bookingPending' => 0,
                'tagihanBelum' => 0,
            ];

            if ($user) {
                if ($user->hasRole('super_admin')) {
                    $badges['bookingPending'] = Booking::where('status', 'pending')->count();
                } elseif ($user->isOwner()) {
                    $badges['bookingPending'] = Booking::where('status', 'pending')
                        ->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))
                        ->count();
                } elseif ($user->isAdmin()) {
                    $kosIds = $user->assignedKos()->pluck('kos.id');
                    $badges['bookingPending'] = Booking::where('status', 'pending')
                        ->whereIn('kos_id', $kosIds)
                        ->count();
                } elseif ($user->isTenant()) {
                    $badges['tagihanBelum'] = Tagihan::whereIn('status', ['unpaid', 'overdue'])
                        ->whereHas('penghuni', fn ($q) => $q->where('user_id', $user->id))
                        ->count();
                }
            }

            $view->with('sidebarBadges', $badges);
        });
    }
}
