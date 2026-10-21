<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\BookingOccupant;
use App\Models\CheckOut;
use App\Models\DamageReport;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\View\Composers\LayoutComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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

        View::composer('layouts.app', function ($view) {
            try {
                $user = auth()->user();
                if ($user) {
                    $recent = $user->notifications()->latest()->take(8)->get();
                    $view->with('recentNotifications', $recent);
                } else {
                    $view->with('recentNotifications', collect());
                }
            } catch (\Throwable $e) {
                $view->with('recentNotifications', collect());
            }
            $routeName = optional(request()->route())->getName();
            $view->with('layoutPageTitle', config('page-titles')[$routeName] ?? null);
        });

        View::composer('layouts.sidebar', LayoutComposer::class);

        View::composer('*', function ($view) {
            static $cache = [];

            $empty = [
                'bookingPending' => 0,
                'tagihanBelum' => 0,
                'unreadNotif' => 0,
                'activeBooking' => 0,
                'unreadMessages' => 0,
                'invitationPending' => 0,
                'pendingDamageReports' => 0,
                'unreadInquiries' => 0,
                'unreadAdminMessages' => 0,
                'pendingCheckouts' => 0,
            ];

            try {
                $user = auth()->user();
            } catch (\Throwable $e) {
                $view->with('sidebarBadges', $empty);

                return;
            }

            $cacheKey = $user ? (int) $user->id : 0;

            if (isset($cache[$cacheKey])) {
                $view->with('sidebarBadges', $cache[$cacheKey]);

                return;
            }

            $badges = $empty;

            if ($user) {
                try {
                    $badges['unreadNotif'] = Notification::where('user_id', $user->id)->where('is_read', false)->count();
                } catch (\Throwable $e) {
                    $badges['unreadNotif'] = 0;
                }
                try {
                    $badges['unreadMessages'] = Message::where('receiver_id', $user->id)->whereNull('read_at')->count();
                } catch (\Throwable $e) {
                    $badges['unreadMessages'] = 0;
                }
                try {
                    if ($user->isSuperAdmin()) {
                        $badges['bookingPending'] = Booking::where('status', 'approved')->withoutActivePenghuni()->count();
                    } elseif ($user->isOwner()) {
                        $badges['bookingPending'] = Booking::where('status', 'approved')->withoutActivePenghuni()->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count();
                        try {
                            $badges['pendingDamageReports'] = DamageReport::where('status', DamageReport::STATUS_PENDING)->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count();
                        } catch (\Throwable $e) {
                            $badges['pendingDamageReports'] = 0;
                        }
                        try {
                            $ownerKosIds = Kos::where('owner_id', $user->id)->pluck('id');
                            $activeIds = Penghuni::whereIn('kos_id', $ownerKosIds)->where('status', 'active')->pluck('user_id')->all();
                            $badges['unreadInquiries'] = Message::whereIn('kos_id', $ownerKosIds)->where('receiver_id', $user->id)->whereNull('read_at')->where('sender_id', '!=', $user->id)->whereNotIn('sender_id', $activeIds)->count();
                        } catch (\Throwable $e) {
                            $badges['unreadInquiries'] = 0;
                        }
                    } elseif ($user->isAdmin()) {
                        try {
                            $adminKosIds = $user->assignedKos()->pluck('kos.id');
                            $badges['unreadAdminMessages'] = Message::whereIn('kos_id', $adminKosIds)->whereNull('read_at')->count();
                            $badges['pendingDamageReports'] = DamageReport::where('status', DamageReport::STATUS_PENDING)->whereIn('kos_id', $adminKosIds)->count();
                            $badges['pendingCheckouts'] = CheckOut::where('status', CheckOut::STATUS_PENDING)->whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $adminKosIds))->count();
                        } catch (\Throwable $e) {
                            $badges['unreadAdminMessages'] = 0;
                            $badges['pendingDamageReports'] = 0;
                            $badges['pendingCheckouts'] = 0;
                        }
                        try {
                            $kosIds = $user->assignedKos()->pluck('kos.id');
                            $badges['bookingPending'] = Booking::needsCheckin()->whereIn('kos_id', $kosIds)->count();
                        } catch (\Throwable $e) {
                            $badges['bookingPending'] = 0;
                        }
                    } elseif ($user->isTenant()) {
                        try {
                            $badges['tagihanBelum'] = Tagihan::payable()->whereHas('penghuni', fn ($q) => $q->where('user_id', $user->id))->count();
                        } catch (\Throwable $e) {
                            $badges['tagihanBelum'] = 0;
                        }
                        try {
                            $badges['activeBooking'] = Booking::where('user_id', $user->id)->whereIn('status', Booking::activeStatuses())->whereDate('end_date', '>=', today()->toDateString())->count();
                        } catch (\Throwable $e) {
                            $badges['activeBooking'] = 0;
                        }
                        try {
                            if (Schema::hasTable('booking_occupants')) {
                                $badges['invitationPending'] = BookingOccupant::where(function ($q) use ($user) {
                                    $q->whereRaw('LOWER(email) = ?', [strtolower($user->email)])->orWhere('user_id', $user->id);
                                })->where('status', BookingOccupant::STATUS_INVITED)->count();
                            }
                        } catch (\Throwable $e) {
                            $badges['invitationPending'] = 0;
                        }
                    }
                } catch (\Throwable $e) {
                }
            }

            $cache[$cacheKey] = $badges;
            $view->with('sidebarBadges', $badges);
        });
    }
}
