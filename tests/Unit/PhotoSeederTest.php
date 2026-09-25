<?php

namespace Tests\Unit;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_kos_has_photo_relationship(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $kos->photos());
    }

    public function test_kamar_photo_field_is_string(): void
    {
        $kamar = Kamar::factory()->create(['photo' => 'kamar/kamar-single.jpg']);
        $this->assertIsString($kamar->photo);
    }

    public function test_kamar_photo_cover_path_is_valid(): void
    {
        $kamar = Kamar::factory()->create(['photo' => 'kamar/kamar-single.jpg']);
        $this->assertStringStartsWith('kamar/', $kamar->photo);
    }

    public function test_kos_gallery_photo_paths_method_returns_array(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'photo' => 'kos/kos-melati.jpg']);
        KosPhoto::factory()->count(3)->create(['kos_id' => $kos->id]);

        $paths = $kos->galleryPhotoPaths();
        $this->assertIsArray($paths);
        $this->assertNotEmpty($paths);
    }

    public function test_kos_cover_path_returns_photo_field_when_no_gallery(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'photo' => 'kos/kos-melati.jpg']);

        $path = $kos->coverPath();
        $this->assertEquals('kos/kos-melati.jpg', $path);
    }

    public function test_kos_photo_table_has_correct_relationship(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        KosPhoto::factory()->create(['kos_id' => $kos->id]);
        $this->assertCount(1, $kos->fresh()->photos);
    }

    public function test_kos_photo_cover_flag_works(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'photo' => 'kos/kos-melati.jpg']);
        KosPhoto::factory()->create(['kos_id' => $kos->id, 'is_cover' => true]);

        $cover = $kos->coverPhoto()->first();
        $this->assertNotNull($cover);
        $this->assertTrue($cover->is_cover);
    }
}