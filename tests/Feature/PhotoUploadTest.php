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

    private const TINY_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private const TINY_WEBP_BASE64 = 'UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==';

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

    public function test_valid_png_photo_is_accepted(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos PNG',
            'address' => 'Jl. PNG No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => UploadedFile::fake()->createWithContent('kos.png', base64_decode(self::TINY_PNG_BASE64)),
        ]);

        $response->assertRedirect(route('owner.kos.index'));

        $kos = Kos::where('name', 'Kos PNG')->first();
        $this->assertNotNull($kos->photo);
        Storage::disk('public')->assertExists($kos->photo);
    }

    public function test_valid_webp_photo_is_accepted(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos WebP',
            'address' => 'Jl. WebP No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => UploadedFile::fake()->createWithContent('kos.webp', base64_decode(self::TINY_WEBP_BASE64)),
        ]);

        $response->assertRedirect(route('owner.kos.index'));

        $kos = Kos::where('name', 'Kos WebP')->first();
        $this->assertNotNull($kos->photo);
        Storage::disk('public')->assertExists($kos->photo);
    }

    public function test_svg_photo_is_rejected(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"><rect width="1" height="1" fill="#000000"/></svg>';

        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos SVG',
            'address' => 'Jl. SVG No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => UploadedFile::fake()->createWithContent('malicious.svg', $svg),
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_html_disguised_as_jpg_is_rejected(): void
    {
        $html = '<!DOCTYPE html><html><body><script>alert(1)</script></body></html>';

        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos HTML',
            'address' => 'Jl. HTML No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => UploadedFile::fake()->createWithContent('evil.jpg', $html),
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_malformed_image_is_rejected(): void
    {
        $malformed = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00".str_repeat("\0", 120);

        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Malformed',
            'address' => 'Jl. Malformed No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => UploadedFile::fake()->createWithContent('broken.jpg', $malformed),
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_uploaded_filename_cannot_escape_storage_directory(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Traversal',
            'address' => 'Jl. Traversal No. 1',
            'phone' => '08123456789',
            'status' => 'active',
            'photo' => UploadedFile::fake()->createWithContent('../../../../evil-escape.jpg', base64_decode(self::TINY_JPEG_BASE64)),
        ]);

        $response->assertRedirect(route('owner.kos.index'));

        $kos = Kos::where('name', 'Kos Traversal')->first();
        $this->assertNotNull($kos->photo);
        $this->assertStringStartsWith('kos/', $kos->photo);
        $this->assertStringNotContainsString('..', $kos->photo);
        Storage::disk('public')->assertExists($kos->photo);
    }
}
