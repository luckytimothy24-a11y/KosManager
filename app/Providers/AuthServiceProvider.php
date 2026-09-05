<?php

namespace App\Providers;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Policies\AdvertisingCampaignPolicy;
use App\Policies\AdvertisingPackagePolicy;
use App\Policies\BookingPolicy;
use App\Policies\CheckInPolicy;
use App\Policies\CheckOutPolicy;
use App\Policies\KamarPolicy;
use App\Policies\KontrakPolicy;
use App\Policies\KosPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\PembayaranPolicy;
use App\Policies\PenghuniPolicy;
use App\Policies\TagihanPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Kos::class => KosPolicy::class,
        Kamar::class => KamarPolicy::class,
        Booking::class => BookingPolicy::class,
        Penghuni::class => PenghuniPolicy::class,
        Kontrak::class => KontrakPolicy::class,
        Tagihan::class => TagihanPolicy::class,
        Pembayaran::class => PembayaranPolicy::class,
        CheckIn::class => CheckInPolicy::class,
        CheckOut::class => CheckOutPolicy::class,
        Notification::class => NotificationPolicy::class,
        AdvertisingPackage::class => AdvertisingPackagePolicy::class,
        AdvertisingCampaign::class => AdvertisingCampaignPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
