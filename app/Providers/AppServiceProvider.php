<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Message;
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

        View::composer('*', function ($view) {
            $user = auth()->user();

            $badges = [
                'bookingPending' => 0,
                'tagihanBelum' => 0,
                'unreadNotif' => 0,
                'activeBooking' => 0,
                'unreadMessages' => 0,
                'invitationPending' => 0,
            ];

            if ($user) {
                $badges['unreadNotif'] = Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
                // Badge pesan untuk semua role (tenant maupun owner/super_admin) — aman via try/catch
                try {
                    $badges['unreadMessages'] = Message::where('receiver_id', $user->id)->whereNull('read_at')->count();
                } catch (\Throwable $e) {
                    $badges['unreadMessages'] = 0;
                }
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
                    // Undangan pending (jika tabel booking_occupants ada)
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('booking_occupants')) {
                            $badges['invitationPending'] = \App\Models\BookingOccupant::where(function ($q) use ($user) {
                                $q->whereRaw('LOWER(email) = ?', [strtolower($user->email)])->orWhere('user_id', $user->id);
                            })->where('status', \App\Models\BookingOccupant::STATUS_INVITED)->count();
                        }
                    } catch (\Throwable $e) {
                        $badges['invitationPending'] = 0;
                    }
                }
            }

            $view->with('sidebarBadges', $badges);
        });
    }
}
