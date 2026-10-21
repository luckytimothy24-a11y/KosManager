<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use App\Services\BookingService;
use App\Services\SharedBillingService;
use Database\Seeders\ShowcaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowcaseFeasibilityTest extends TestCase
{
    use RefreshDatabase;

    private function seedShowcase(): void
    {
        User::factory()->create(['role' => 'owner', 'email' => 'owner@example.com']);
        User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);
        $this->seed(ShowcaseSeeder::class);
    }

    public function test_seed_runs_and_is_idempotent(): void
    {
        $this->seedShowcase();
        $this->seed(ShowcaseSeeder::class);

        $this->assertSame(3, Kos::whereIn('name', [
            'Griya Srikandi Pogung', 'Pondok Bareng Jakal', 'Kost Eksekutif Malioboro',
        ])->count());
        $this->assertSame(14, Kamar::whereHas('kos', fn ($q) => $q->where('name', 'like', '%Pogung%')
            ->orWhere('name', 'like', '%Jakal%')
            ->orWhere('name', 'like', '%Malioboro%'))->count());
    }

    public function test_showcase_kos_visible_in_marketplace(): void
    {
        $this->seedShowcase();
        $tenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.index'));
        $response->assertOk();
        $response->assertSee('Griya Srikandi Pogung');
        $response->assertSee('Pondok Bareng Jakal');

        $filtered = $this->actingAs($tenant)->get(route('tenant.kos.index', ['tersedia_only' => 1]));
        $filtered->assertOk();
        $filtered->assertSee('Kost Eksekutif Malioboro');
    }

    public function test_shared_room_booking_respects_capacity(): void
    {
        $this->seedShowcase();
        $kamar = Kamar::where('room_number', 'S-03')->firstOrFail();
        $this->assertSame(2, (int) $kamar->capacity);
        $this->assertTrue($kamar->isShared());

        $service = new BookingService;
        $payload = fn () => [
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 1,
        ];

        $t1 = User::factory()->create(['role' => 'tenant']);
        $t2 = User::factory()->create(['role' => 'tenant']);
        $t3 = User::factory()->create(['role' => 'tenant']);

        $this->assertTrue($service->create($payload(), $t1->id)['ok']);
        $this->assertTrue($service->create($payload(), $t2->id)['ok']);

        $third = $service->create($payload(), $t3->id);
        $this->assertFalse($third['ok']);
        $this->assertSame('kamar_penuh', $third['error']);
    }

    public function test_shared_room_tagihan_split_per_occupant(): void
    {
        $this->seedShowcase();
        $kamar = Kamar::where('room_number', 'B-02')->firstOrFail();
        $this->assertSame(3, (int) $kamar->capacity);

        foreach (range(1, 3) as $i) {
            $penghuni = Penghuni::factory()->create([
                'kos_id' => $kamar->kos_id,
                'kamar_id' => $kamar->id,
                'status' => 'active',
            ]);
            Kontrak::factory()->create([
                'penghuni_id' => $penghuni->id,
                'kos_id' => $kamar->kos_id,
                'kamar_id' => $kamar->id,
                'rental_price' => 600000,
                'rental_type' => 'monthly',
                'billing_mode' => null,
                'status' => 'active',
            ]);
        }

        $tagihans = (new SharedBillingService)->createSharedTagihans(
            $kamar, 'rent', '2026-10-01', '2026-10-31', '2026-11-05',
        );

        $this->assertCount(3, $tagihans);
        foreach ($tagihans as $tagihan) {
            $this->assertEquals(200000.0, (float) $tagihan->subtotal);
        }
    }

    public function test_showcase_photos_resolve_to_real_files(): void
    {
        $this->seedShowcase();

        $kosList = Kos::whereIn('name', [
            'Griya Srikandi Pogung', 'Pondok Bareng Jakal', 'Kost Eksekutif Malioboro',
        ])->with('kamar')->get();

        $this->assertCount(3, $kosList);
        foreach ($kosList as $kos) {
            $this->assertNotNull($kos->photo);
            $this->assertTrue(
                Storage::disk('public')->exists($kos->photo),
                "Cover {$kos->photo} tidak ada di disk public."
            );
            $this->assertGreaterThanOrEqual(1, $kos->photos()->count());

            foreach ($kos->kamar as $kamar) {
                $this->assertNotNull($kamar->photo, "Kamar {$kamar->room_number} tanpa foto.");
                $this->assertTrue(
                    Storage::disk('public')->exists($kamar->photo),
                    "Foto kamar {$kamar->photo} tidak ada di disk public."
                );
            }
        }
    }

    public function test_capacity_guards_and_no_n_plus_one(): void
    {
        $this->seedShowcase();
        $family = Kamar::where('room_number', 'B-04')->firstOrFail();
        $this->assertSame(4, (int) $family->capacity);
        $this->assertFalse($family->isFull());

        // N+1 dibuktikan via delta pada regime section penuh: top-up dulu
        // hingga 13 kos (section discovery/budget sudah maksimal/bounded),
        // lalu tambah 10 kos + 20 kamar lagi. Query tidak boleh tumbuh
        // proporsional (+<10) karena seluruh relasi di-eager-load dan
        // badge sidebar di-memo per request.
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::where('email', 'owner@example.com')->first();
        $topUp = function (int $n) use ($owner) {
            foreach (range(1, $n) as $i) {
                $extra = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
                Kamar::factory()->count(2)->create(['kos_id' => $extra->id, 'status' => 'available']);
            }
        };
        $topUp(10);

        // Catatan: enableQueryLog() tidak me-flush log, jadi wajib
        // flushQueryLog() agar hitungan antar request tidak menumpuk.
        DB::enableQueryLog();
        $this->actingAs($tenant)->get(route('tenant.kos.index'))->assertOk();
        $before = count(DB::getQueryLog());
        DB::flushQueryLog();
        DB::disableQueryLog();

        $topUp(10);

        DB::enableQueryLog();
        $this->actingAs($tenant)->get(route('tenant.kos.index'))->assertOk();
        $after = count(DB::getQueryLog());
        DB::flushQueryLog();
        DB::disableQueryLog();

        $this->assertLessThan(10, $after - $before, "Query tumbuh {$before} → {$after}, indikasi N+1");
    }

    public function test_seeder_fails_closed_in_production(): void
    {
        $this->app->instance('env', 'production');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/production/i');
        (new ShowcaseSeeder)->run();
    }
}
