<?php

namespace App\Providers;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\BookingOccupant;
use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\DamageReport;
use App\Models\Fasilitas;
use App\Models\Favorite;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\Message;
use App\Models\Notification;
use App\Models\OwnerBankAccount;
use App\Models\Partner;
use App\Models\PaymentDistribution;
use App\Models\Payout;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\Tagihan;
use App\Models\User;
use App\Policies\AdminPolicy;
use App\Policies\AdvertisingCampaignPolicy;
use App\Policies\AdvertisingOrderPolicy;
use App\Policies\AdvertisingPackagePolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BookingCancellationRequestPolicy;
use App\Policies\BookingOccupantPolicy;
use App\Policies\BookingPolicy;
use App\Policies\CheckInPolicy;
use App\Policies\CheckOutPolicy;
use App\Policies\DamageReportPolicy;
use App\Policies\FasilitasPolicy;
use App\Policies\FavoritePolicy;
use App\Policies\KamarPolicy;
use App\Policies\KontrakPolicy;
use App\Policies\KosPhotoPolicy;
use App\Policies\KosPolicy;
use App\Policies\MessagePolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OwnerBankAccountPolicy;
use App\Policies\PartnerPolicy;
use App\Policies\PaymentDistributionPolicy;
use App\Policies\PayoutPolicy;
use App\Policies\PembayaranPolicy;
use App\Policies\PenghuniPolicy;
use App\Policies\PropertyOwnerPolicy;
use App\Policies\TagihanPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Kos::class => KosPolicy::class,
        KosPhoto::class => KosPhotoPolicy::class,
        Kamar::class => KamarPolicy::class,
        Booking::class => BookingPolicy::class,
        BookingCancellationRequest::class => BookingCancellationRequestPolicy::class,
        OwnerBankAccount::class => OwnerBankAccountPolicy::class,
        PaymentDistribution::class => PaymentDistributionPolicy::class,
        Payout::class => PayoutPolicy::class,
        PropertyOwner::class => PropertyOwnerPolicy::class,
        Penghuni::class => PenghuniPolicy::class,
        Kontrak::class => KontrakPolicy::class,
        Tagihan::class => TagihanPolicy::class,
        Pembayaran::class => PembayaranPolicy::class,
        CheckIn::class => CheckInPolicy::class,
        CheckOut::class => CheckOutPolicy::class,
        DamageReport::class => DamageReportPolicy::class,
        Favorite::class => FavoritePolicy::class,
        Message::class => MessagePolicy::class,
        BookingOccupant::class => BookingOccupantPolicy::class,
        Fasilitas::class => FasilitasPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Partner::class => PartnerPolicy::class,
        Notification::class => NotificationPolicy::class,
        AdvertisingPackage::class => AdvertisingPackagePolicy::class,
        AdvertisingCampaign::class => AdvertisingCampaignPolicy::class,
        AdvertisingOrder::class => AdvertisingOrderPolicy::class,
        User::class => AdminPolicy::class,
    ];

    /**
     * AdminPolicy is registered for explicit use in SuperAdmin controllers
     * via Gate::forUser()->allows() or direct policy invocation.
     * It handles admin-specific authorization boundaries that are distinct
     * from general user management (UserPolicy).
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
