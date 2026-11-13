<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use App\Services\BookingService;
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
        $tenant = User::factory()->create(['role' => 'pencari']);

        $response = $this->actingAs($tenant)->get(route('tenant.kos.index'));
        $response->assertOk();
        $response->assertSee('Griya Srikandi Pogung');
        $response->assertSee('Pondok Bareng Jakal');

        $filtered = $this->actingAs($tenant)->get(route('tenant.kos.index', ['tersedia_only' => 1]));
        $filtered->assertOk();
        $filtered->assertSee('Kost Eksekutif Malioboro');
    }

    public function test_shared_booking_on_single_occupancy_room_is_rejected(): void
    {
        $this->seedShowcase();
        $kamar = Kamar::where('room_number', 'S-03')->firstOrFail();
        $this->assertSame(1, (int) $kamar->capacity);
        $this->assertFalse($kamar->isShared());

        $service = new BookingService;
        $tenant = User::factory()->create(['role' => 'pencari']);

        $shared = $service->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 1,
        ], $tenant->id);

        $this->assertFalse($shared['ok']);
        $this->assertSame('billing_mode_not_allowed', $shared['error']);

        $multi = $service->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 2,
        ], $tenant->id);

        $this->assertFalse($multi['ok']);
        $this->assertSame('personal_multi_rejected', $multi['error']);
    }

    public function test_showcase_rooms_are_personal_single_occupancy(): void
    {
        $this->seedShowcase();

        $this->assertSame(0, Kamar::where('capacity', '>', 1)->count());
        $this->assertSame(0, Kamar::where('billing_mode', 'shared')->count());
        $this->assertSame(0, Kamar::whereIn('room_type', ['Sharing', 'Family'])->count());

        $kamar = Kamar::where('room_number', 'B-02')->firstOrFail();
        $this->assertSame(1, (int) $kamar->capacity);
        $this->assertSame('personal', $kamar->billing_mode);
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
        $room = Kamar::where('room_number', 'B-04')->firstOrFail();
        $this->assertSame(1, (int) $room->capacity);
        $this->assertFalse($room->isFull());

        // N+1 dibuktikan via delta pada regime section penuh: top-up dulu
        // hingga 13 kos (section discovery/budget sudah maksimal/bounded),
        // lalu tambah 10 kos + 20 kamar lagi. Query tidak boleh tumbuh
        // proporsional (+<10) karena seluruh relasi di-eager-load dan
        // badge sidebar di-memo per request.
        $tenant = User::factory()->create(['role' => 'pencari']);
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
