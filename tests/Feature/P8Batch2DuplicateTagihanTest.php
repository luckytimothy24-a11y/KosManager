<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * P8 Batch 2 — Duplicate Tagihan Prevention (DB-level integrity).
 */
class P8Batch2DuplicateTagihanTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kontrak $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => User::factory()->create(['role' => 'tenant'])->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => 1000000,
            'status' => 'active',
        ]);
    }

    private function store(string $start, string $end): mixed
    {
        return $this->actingAs($this->owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $this->kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => $start,
            'period_end' => $end,
            'discount' => 0,
            'penalty' => 0,
            'due_date' => $end,
        ]);
    }

    public function test_integrity_index_exists(): void
    {
        $this->assertTrue(Schema::hasColumn('tagihans', 'active_billing_key'));

        $indexes = collect(Schema::getIndexes('tagihans'));

        $this->assertTrue(
            $indexes->contains(fn ($i) => in_array('active_billing_key', $i['columns']))
        );
    }

    public function test_normal_creation_sets_deterministic_key(): void
    {
        $this->store('2024-01-01', '2024-01-31')->assertRedirect(route('owner.tagihan.index'));

        $this->assertDatabaseHas('tagihans', [
            'active_billing_key' => $this->kontrak->id.':2024-01-01:2024-01-31',
        ]);
    }

    public function test_duplicate_period_is_rejected(): void
    {
        $this->store('2024-01-01', '2024-01-31')->assertRedirect(route('owner.tagihan.index'));

        $response = $this->store('2024-01-01', '2024-01-31');
        $response->assertSessionHasErrors('kontrak_id');

        $this->assertDatabaseCount('tagihans', 1);
    }

    public function test_different_period_for_same_contract_is_allowed(): void
    {
        $this->store('2024-01-01', '2024-01-31')->assertRedirect(route('owner.tagihan.index'));
        $this->store('2024-02-01', '2024-02-29')->assertRedirect(route('owner.tagihan.index'));

        $this->assertDatabaseCount('tagihans', 2);
    }

    public function test_db_constraint_blocks_duplicate_key(): void
    {
        Tagihan::factory()->create([
            'kontrak_id' => $this->kontrak->id,
            'active_billing_key' => $this->kontrak->id.':2024-01-01:2024-01-31',
        ]);

        $this->expectException(QueryException::class);
        Tagihan::factory()->create([
            'kontrak_id' => $this->kontrak->id,
            'active_billing_key' => $this->kontrak->id.':2024-01-01:2024-01-31',
        ]);
    }

    public function test_soft_deleted_tagihan_releases_key_slot(): void
    {
        $tagihan = Tagihan::factory()->create([
            'kontrak_id' => $this->kontrak->id,
            'active_billing_key' => $this->kontrak->id.':2024-01-01:2024-01-31',
        ]);

        $tagihan->delete();

        // Aktifkan kembali periode yang sama setelah soft-delete -> boleh.
        $this->store('2024-01-01', '2024-01-31')->assertRedirect(route('owner.tagihan.index'));

        $this->assertDatabaseCount('tagihans', 2);
    }

    public function test_historical_records_remain_valid_after_migration_strategy(): void
    {
        // Duplikat historis (kontrak+periode sama) tetap tersimpan; baris pertama
        // memegang kunci, baris duplikat NULL. Ini mensimulasikan strategi
        // backfill yang aman (tidak menghapus data lama).
        Tagihan::factory()->create([
            'kontrak_id' => $this->kontrak->id,
            'active_billing_key' => $this->kontrak->id.':2024-03-01:2024-03-31',
        ]);
        Tagihan::factory()->create([
            'kontrak_id' => $this->kontrak->id,
            'active_billing_key' => null,
        ]);

        $this->assertDatabaseCount('tagihans', 2);

        $keyHolder = Tagihan::whereNotNull('active_billing_key')->first();
        $this->assertNotNull($keyHolder);
        // Pembuatan periode berbeda masih diperbolehkan tanpa konflik.
        $this->store('2024-04-01', '2024-04-30')->assertRedirect(route('owner.tagihan.index'));
        $this->assertDatabaseCount('tagihans', 3);
    }
}
