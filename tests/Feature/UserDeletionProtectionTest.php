<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeletionProtectionTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    }

    public function test_super_admin_cannot_delete_user_with_payment_history(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'status' => 'paid',
        ]);
        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('super-admin.users.destroy', $tenant));

        $response->assertRedirect(route('super-admin.users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $tenant->id]);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id]);
        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id]);
        $this->assertDatabaseHas('pembayarans', ['id' => Pembayaran::firstOrFail()->id]);
    }

    public function test_super_admin_cannot_delete_user_with_booking_history(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        Booking::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('super-admin.users.destroy', $tenant));

        $response->assertRedirect(route('super-admin.users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $tenant->id]);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_childless_user_is_soft_deleted_not_hard_deleted(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($this->superAdmin)->delete(route('super-admin.users.destroy', $tenant));

        $response->assertRedirect(route('super-admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $tenant->id]);
    }

    public function test_profile_self_delete_of_tenant_with_history_preserves_financial_records(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'inactive',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'status' => 'paid',
        ]);
        Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($tenant)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');

        $this->assertSoftDeleted('users', ['id' => $tenant->id]);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id]);
        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id]);
        $this->assertDatabaseCount('pembayarans', 1);
    }

    public function test_soft_deleted_user_cannot_login(): void
    {
        $tenant = User::factory()->create([
            'role' => 'tenant',
            'password' => bcrypt('password'),
        ]);
        $tenant->delete();

        $response = $this->post('/login', [
            'email' => $tenant->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
