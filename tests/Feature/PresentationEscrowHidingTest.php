<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Escrow/wallet/payout masih WIP dan tidak boleh tampil di UI presentasi.
 * Route tetap ada, tetapi menu dan cross-link harus tersembunyi.
 */
class PresentationEscrowHidingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sidebar_hides_escrow_and_payout_menu(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Dana Booking Masuk');
        $response->assertDontSee('Transfer ke Pemilik Kos');
        $response->assertDontSee(route('owner.distribution.index'), false);
        $response->assertDontSee(route('owner.payouts.index'), false);
    }

    public function test_super_admin_sidebar_hides_escrow_and_payout_menu(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Dana Booking Masuk');
        $response->assertDontSee('Transfer ke Pemilik Kos');
        $response->assertDontSee(route('owner.distribution.index'), false);
        $response->assertDontSee(route('owner.payouts.index'), false);
    }

    public function test_bank_account_page_has_no_distribution_cross_link(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get(route('owner.bank-account.edit'));

        $response->assertOk();
        $response->assertDontSee('Distribusi');
        $response->assertDontSee(route('owner.distribution.index'), false);
    }

    public function test_agreements_page_has_no_payout_cross_link(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get(route('owner.agreements.index'));

        $response->assertOk();
        $response->assertDontSee(route('owner.payouts.index'), false);
    }
}
