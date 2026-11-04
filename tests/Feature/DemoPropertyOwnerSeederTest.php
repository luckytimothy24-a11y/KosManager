<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\User;
use Database\Seeders\DemoPropertyOwnerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoPropertyOwnerSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_is_idempotent_and_links_demo_kos(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'email' => 'owner@example.com',
        ]);

        $kos = Kos::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Griya Srikandi Pogung',
            'status' => 'active',
        ]);

        $this->seed(DemoPropertyOwnerSeeder::class);
        $this->seed(DemoPropertyOwnerSeeder::class);

        $this->assertSame(1, PropertyOwner::count());
        $this->assertSame(1, PropertyOwnerBankAccount::count());
        $this->assertSame(1, ManagementAgreement::count());

        $propertyOwner = PropertyOwner::firstOrFail();
        $this->assertSame('Budi Santoso', $propertyOwner->name);

        $bankAccount = PropertyOwnerBankAccount::firstOrFail();
        $this->assertSame('BCA', $bankAccount->bank_name);
        $this->assertSame('001234567890', $bankAccount->account_number);
        $this->assertSame('Budi Santoso', $bankAccount->account_holder_name);

        $agreement = ManagementAgreement::firstOrFail();
        $this->assertSame('percent', $agreement->commission_type);
        $this->assertSame('10.00', (string) $agreement->commission_value);
        $this->assertSame($owner->id, $agreement->approved_by);

        $kos->refresh();
        $this->assertSame($propertyOwner->id, $kos->property_owner_id);
        $this->assertSame('Budi Santoso', $kos->propertyOwner?->name);
    }
}
