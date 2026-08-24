<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagihanDiscountValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kos $kos;

    private Kontrak $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => User::factory()->create(['role' => 'tenant'])->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => 1000000,
            'status' => 'active',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'rent',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
        ], $overrides);
    }

    public function test_discount_larger_than_subtotal_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), $this->payload([
            'discount' => 1500000,
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors('discount');

        $this->assertDatabaseCount('tagihans', 0);
    }

    public function test_discount_equal_to_subtotal_with_penalty_produces_positive_total(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), $this->payload([
            'discount' => 1000000,
            'penalty' => 50000,
        ]));

        $response->assertRedirect(route('owner.tagihan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tagihans', [
            'kontrak_id' => $this->kontrak->id,
            'subtotal' => 1000000,
            'discount' => 1000000,
            'penalty' => 50000,
            'total' => 50000,
        ]);
    }

    public function test_negative_total_is_never_created(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.tagihan.store'), $this->payload([
            'discount' => 999999.99,
            'penalty' => 0,
        ]));

        $response->assertRedirect(route('owner.tagihan.index'));

        $tagihan = Tagihan::where('kontrak_id', $this->kontrak->id)->firstOrFail();

        $this->assertGreaterThanOrEqual(0, (float) $tagihan->total);
    }
}
