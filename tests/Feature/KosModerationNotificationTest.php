<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\Notification;
use App\Models\User;
use App\Support\NotificationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KosModerationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_created_kos_is_pending_moderation_and_notifies_super_admin(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        $response = $this->actingAs($owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Melati',
            'address' => 'Jl. Melati No. 1',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect(route('owner.kos.index'));

        $kos = Kos::where('name', 'Kos Melati')->first();
        $this->assertNotNull($kos);
        $this->assertSame('pending', $kos->moderation_status);
        $this->assertSame($owner->id, $kos->owner_id);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $superAdmin->id,
            'type' => NotificationType::KOS_MODERATION,
            'title' => 'Kos Baru Menunggu Moderasi',
        ]);
    }

    public function test_super_admin_created_kos_is_auto_approved_without_moderation_notification(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($superAdmin)->post(route('owner.kos.store'), [
            'name' => 'Kos Anggrek',
            'address' => 'Jl. Anggrek No. 2',
            'phone' => '081234567891',
            'owner_id' => $owner->id,
        ]);

        $response->assertRedirect();

        $kos = Kos::where('name', 'Kos Anggrek')->first();
        $this->assertNotNull($kos);
        $this->assertSame('approved', $kos->moderation_status);

        $this->assertSame(0, Notification::where('type', NotificationType::KOS_MODERATION)->count());
    }

    public function test_approving_kos_notifies_owner(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create([
            'owner_id' => $owner->id,
            'moderation_status' => 'pending',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('super-admin.moderation.kos.approve', $kos));

        $response->assertRedirect();
        $this->assertSame('approved', $kos->fresh()->moderation_status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => NotificationType::KOS_MODERATION,
            'title' => 'Kos Disetujui',
        ]);
    }

    public function test_rejecting_kos_notifies_owner_with_reason(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create([
            'owner_id' => $owner->id,
            'moderation_status' => 'pending',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('super-admin.moderation.kos.reject', $kos), [
            'reason' => 'Alamat tidak lengkap',
        ]);

        $response->assertRedirect();
        $fresh = $kos->fresh();
        $this->assertSame('rejected', $fresh->moderation_status);
        $this->assertSame('Alamat tidak lengkap', $fresh->rejection_reason);

        $notification = Notification::where('user_id', $owner->id)
            ->where('type', NotificationType::KOS_MODERATION)
            ->where('title', 'Kos Ditolak')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Alamat tidak lengkap', $notification->message);
    }

    public function test_super_admin_dashboard_counts_pending_kos_by_moderation_status(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'owner']);

        Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'moderation_status' => 'pending']);
        Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'moderation_status' => 'approved']);

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSeeText('Kos Menunggu Persetujuan');
        $response->assertSeeText('1 item menunggu');
    }
}
