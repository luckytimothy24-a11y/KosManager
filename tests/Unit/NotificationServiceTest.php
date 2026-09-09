<?php

namespace Tests\Unit;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_notification(): void
    {
        $user = User::factory()->create();

        NotificationService::create($user->id, 'test', 'Title', 'Message');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Title',
            'message' => 'Message',
        ]);
    }

    public function test_booking_new_notification(): void
    {
        $owner = User::factory()->create();

        NotificationService::bookingNew($owner->id, 'John', '101');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'booking',
            'title' => 'Booking Baru',
        ]);
    }

    public function test_booking_instant_confirmation_notification(): void
    {
        $tenant = User::factory()->create();

        NotificationService::bookingInstantConfirmation($tenant->id, 'BK123', '101');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $tenant->id,
            'type' => 'booking',
            'title' => 'Booking Berhasil',
        ]);
    }

    public function test_payment_submitted_notification(): void
    {
        $owner = User::factory()->create();

        NotificationService::paymentSubmitted($owner->id, 'Jane', 'TB001');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'payment',
            'title' => 'Pembayaran Baru',
        ]);
    }

    public function test_payment_verified_notification(): void
    {
        $tenant = User::factory()->create();

        NotificationService::paymentVerified($tenant->id, 'TB002');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $tenant->id,
            'type' => 'payment',
            'title' => 'Pembayaran Diverifikasi',
        ]);
    }

    public function test_bill_created_notification(): void
    {
        $tenant = User::factory()->create();

        NotificationService::billCreated($tenant->id, 'TB003', '31/01/2026');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $tenant->id,
            'type' => 'billing',
            'title' => 'Tagihan Baru',
        ]);
    }

    public function test_checkin_notification(): void
    {
        $tenant = User::factory()->create();

        NotificationService::checkin($tenant->id, '101');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $tenant->id,
            'type' => 'checkin',
            'title' => 'Check-In Berhasil',
        ]);
    }

    public function test_checkout_approved_notification(): void
    {
        $tenant = User::factory()->create();

        NotificationService::checkoutApproved($tenant->id, '101');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $tenant->id,
            'type' => 'checkout',
            'title' => 'Check-Out Disetujui',
        ]);
    }

    public function test_notification_with_data(): void
    {
        $user = User::factory()->create();

        NotificationService::create($user->id, 'test', 'Title', 'Message', ['key' => 'value']);

        $notif = Notification::where('user_id', $user->id)->first();
        $this->assertEquals(['key' => 'value'], $notif->data);
    }
}
