<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private const TINY_JPEG_BASE64 = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD3+iiigD//2Q==';

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        Storage::fake('public');
    }

    private function fakeImage(string $name = 'kos.jpg'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::TINY_JPEG_BASE64));
    }

    public function test_owner_can_upload_kos_photo(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Foto',
            'address' => 'Jl. Foto No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => $this->fakeImage(),
        ]);

        $response->assertRedirect(route('owner.kos.index'));

        $kos = Kos::where('name', 'Kos Foto')->first();
        $this->assertNotNull($kos->photo);
        Storage::disk('public')->assertExists($kos->photo);
    }

    public function test_kos_photo_is_replaced_and_old_file_deleted_on_update(): void
    {
        $kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'photo' => 'kos/old.jpg',
        ]);
        Storage::disk('public')->put('kos/old.jpg', 'dummy');

        $response = $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name' => $kos->name,
            'address' => $kos->address,
            'phone' => $kos->phone,
            'status' => 'active',
            'photo' => $this->fakeImage('new.jpg'),
        ]);

        $response->assertRedirect(route('owner.kos.index'));

        $kos->refresh();
        Storage::disk('public')->assertMissing('kos/old.jpg');
        Storage::disk('public')->assertExists($kos->photo);
    }

    public function test_owner_can_upload_kamar_photo(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $kos->id,
            'room_number' => 'A99',
            'room_name' => 'Kamar Foto',
            'room_type' => 'putra',
            'floor' => 1,
            'area' => '3x3',
            'daily_price' => 50000,
            'monthly_price' => 1000000,
            'status' => 'available',
            'photo' => $this->fakeImage(),
        ]);

        $response->assertRedirect(route('owner.kamar.index'));

        $kamar = Kamar::where('room_number', 'A99')->first();
        $this->assertNotNull($kamar->photo);
        Storage::disk('public')->assertExists($kamar->photo);
    }

    public function test_invalid_photo_type_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Invalid',
            'address' => 'Jl. Invalid No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => UploadedFile::fake()->create('malicious.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_oversized_photo_is_rejected(): void
    {
        $oversized = base64_decode(self::TINY_JPEG_BASE64).str_repeat("\0", 3 * 1024 * 1024);

        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Big',
            'address' => 'Jl. Big No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => UploadedFile::fake()->createWithContent('big.jpg', $oversized),
        ]);

        $response->assertSessionHasErrors('photo');
    }
}
