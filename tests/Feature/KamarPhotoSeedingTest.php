<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\KosPhoto;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarPhotoSeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_kos_has_gallery_photos_after_seeding(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kos = Kos::where('status', 'active')->first();
        $this->assertNotNull($kos);
        $this->assertGreaterThanOrEqual(1, KosPhoto::where('kos_id', $kos->id)->count());
    }

    public function test_every_kos_has_at_least_one_photo(): void
    {
        $this->seed(DatabaseSeeder::class);

        $activeKos = Kos::where('status', 'active')->get();
        foreach ($activeKos as $kos) {
            $photos = KosPhoto::where('kos_id', $kos->id)->count();
            $this->assertGreaterThanOrEqual(1, $photos, "Kos {$kos->name} should have at least 1 photo");
        }
    }

    public function test_kamar_photos_are_set(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kamars = Kamar::where('status', 'available')->get();
        $kamarWithPhoto = $kamars->whereNotNull('photo')->first();
        $this->assertNotNull($kamarWithPhoto, 'At least one kamar should have a photo');
    }

    public function test_photo_paths_are_valid(): void
    {
        $this->seed(DatabaseSeeder::class);

        $photos = KosPhoto::all();
        foreach ($photos as $photo) {
            $this->assertStringStartsWith('kos/', $photo->path);
            $this->assertNotEmpty($photo->path);
        }
    }

    public function test_kos_cover_photo_exists(): void
    {
        $this->seed(DatabaseSeeder::class);

        $coverPhotos = KosPhoto::where('is_cover', true)->get();
        $this->assertGreaterThanOrEqual(1, $coverPhotos->count());
    }

    public function test_kamar_occupancy_awareness(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kamar = Kamar::first();
        $this->assertNotNull($kamar);
        $this->assertIsInt($kamar->capacity);
        $this->assertGreaterThanOrEqual(1, $kamar->capacity);
        $this->assertTrue(method_exists($kamar, 'isFull'));
        $this->assertTrue(method_exists($kamar, 'remainingSlots'));
        $this->assertTrue(method_exists($kamar, 'hasAvailableSlot'));
    }

    public function test_kamar_capacity_enforcement_in_model(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kamar = Kamar::factory()->create(['capacity' => 2, 'status' => 'available']);
        $this->assertEquals(2, $kamar->capacity);
        $this->assertTrue($kamar->hasAvailableSlot());

        $kamar->update(['capacity' => 1]);
        $this->assertEquals(1, $kamar->fresh()->capacity);
    }
}
