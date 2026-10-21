<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AgreementOverlapTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $po->id]);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $po->id]);
    }

    private function postAgreement(array $overrides = []): TestResponse
    {
        return $this->actingAs($this->owner)->post(route('owner.agreements.store'), array_merge([
            'kos_id' => $this->kos->id,
            'commission_type' => 'percent',
            'commission_value' => 10,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
        ], $overrides));
    }

    public function test_open_ended_overlap_rejected(): void
    {
        $this->postAgreement(['effective_from' => '2026-01-01', 'effective_to' => null])->assertSessionHasNoErrors();
        $this->assertSame(1, ManagementAgreement::count());
        $res = $this->postAgreement(['effective_from' => '2026-06-01', 'effective_to' => null]);
        $res->assertSessionHasErrors('effective_from');
        $this->assertSame(1, ManagementAgreement::count());
    }

    public function test_closed_then_sequential_allowed(): void
    {
        $this->postAgreement(['effective_from' => '2026-01-01', 'effective_to' => '2026-03-31'])->assertSessionHasNoErrors();
        $res = $this->postAgreement(['effective_from' => '2026-04-01', 'effective_to' => null]);
        $res->assertSessionHasNoErrors();
        $this->assertSame(2, ManagementAgreement::count());
    }

    public function test_duplicate_effective_from_rejected(): void
    {
        $this->postAgreement(['effective_from' => '2026-02-01'])->assertSessionHasNoErrors();
        $res = $this->postAgreement(['effective_from' => '2026-02-01', 'effective_to' => '2026-05-01']);
        $res->assertSessionHasErrors('effective_from');
        $this->assertSame(1, ManagementAgreement::count());
    }

    public function test_closed_overlap_rejected(): void
    {
        $this->postAgreement(['effective_from' => '2026-01-01', 'effective_to' => '2026-06-30'])->assertSessionHasNoErrors();
        $res = $this->postAgreement(['effective_from' => '2026-05-01', 'effective_to' => '2026-08-31']);
        $res->assertSessionHasErrors('effective_from');
        $this->assertSame(1, ManagementAgreement::count());
    }

    public function test_non_overlapping_closed_intervals_allowed(): void
    {
        $this->postAgreement(['effective_from' => '2026-01-01', 'effective_to' => '2026-03-31'])->assertSessionHasNoErrors();
        $this->postAgreement(['effective_from' => '2026-04-01', 'effective_to' => '2026-06-30'])->assertSessionHasNoErrors();
        $this->assertSame(2, ManagementAgreement::count());
    }
}
