<?php

namespace Tests\Feature;

use App\Mail\KosManagerMail;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SchedulerResilienceTest extends TestCase
{
    use RefreshDatabase;

    // ---- Idempotency

    public function test_overdue_command_is_idempotent(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subDay(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();
        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'overdue']);
        Mail::assertQueued(KosManagerMail::class, 1);
    }

    public function test_expire_booking_command_is_idempotent(): void
    {
        Mail::fake();

        $booking = Booking::factory()->create([
            'status' => 'approved',
            'start_date' => today()->subMonths(2),
            'end_date' => today()->subDay(),
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();
        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'expired']);
        Mail::assertQueued(KosManagerMail::class, 1);
    }

    public function test_expire_kontrak_command_is_idempotent(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
            'end_date' => today()->subDay(),
        ]);

        $this->artisan('kontrak:expire-old')->assertSuccessful();
        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('kontraks', ['id' => $kontrak->id, 'status' => 'expired']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseCount('check_outs', 1);
    }

    public function test_remind_command_is_idempotent(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->addDays(3),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:remind-due-soon', ['--days' => 3])->assertSuccessful();
        $this->artisan('tagihan:remind-due-soon', ['--days' => 3])->assertSuccessful();

        $this->assertSame(1, Notification::where('type', 'billing')
            ->where('title', 'Pengingat Jatuh Tempo')
            ->count());
    }

    // ---- Cleanup command

    public function test_cleanup_removes_old_notifications(): void
    {
        $user = User::factory()->create();

        $old = Notification::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(100),
        ]);
        $recent = Notification::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(10),
        ]);

        $this->artisan('notification:cleanup', ['--days' => 90])->assertSuccessful();

        $this->assertDatabaseMissing('notifications', ['id' => $old->id]);
        $this->assertDatabaseHas('notifications', ['id' => $recent->id]);
    }

    public function test_cleanup_respects_custom_days_option(): void
    {
        $user = User::factory()->create();

        $old = Notification::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(40),
        ]);
        $recent = Notification::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(10),
        ]);

        $this->artisan('notification:cleanup', ['--days' => 30])->assertSuccessful();

        $this->assertDatabaseMissing('notifications', ['id' => $old->id]);
        $this->assertDatabaseHas('notifications', ['id' => $recent->id]);
    }

    public function test_cleanup_returns_zero_when_nothing_to_delete(): void
    {
        $user = User::factory()->create();

        Notification::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(5),
        ]);

        $this->artisan('notification:cleanup', ['--days' => 90])->assertSuccessful();
    }

    // ---- Unique key by penghuni_id

    public function test_auto_checkout_unique_key_is_per_penghuni(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
            'end_date' => today()->subDays(30),
        ]);

        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $notification = Notification::where('user_id', $tenant->id)
            ->where('type', 'checkout')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString("checkout-auto:{$penghuni->id}", $notification->data['unique_key'] ?? '');
    }

    // ---- Notification isolation (admin does not receive owner notifications)

    public function test_admin_does_not_receive_tenant_notification_on_payment_verify(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['role' => 'owner']);
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'amount' => $tagihan->total,
            'verification_status' => 'pending',
        ]);

        $admin->assignedKos()->attach($kos->id);

        $response = $this->actingAs($admin)->post(route('admin.pembayaran.verify', $pembayaran->id));

        $response->assertRedirect();

        $tenantNotifCount = Notification::forUser($tenant)
            ->where('type', 'payment')
            ->where('title', 'Pembayaran Diverifikasi')
            ->count();

        $this->assertSame(1, $tenantNotifCount);
    }
}
