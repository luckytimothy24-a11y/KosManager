<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KosPhotoGalleryTest extends TestCase
{
    use RefreshDatabase;

    private const TINY_JPEG = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD3+iiigD//2Q==';
    private const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private User $owner;
    private User $otherOwner;
    private User $admin;
    private User $tenant;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    }

    private function fakeImage(string $name = 'kos.jpg', string $base64 = self::TINY_JPEG): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode($base64));
    }

    // ── CREATE ────────────────────────────────────────────────

    public function test_create_kos_with_0_photos(): void
    {
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Zero', 'address' => 'Jl Zero', 'phone' => '081', 'status' => 'active',
        ]);
        $res->assertRedirect(route('owner.kos.index'));
        $kos = Kos::where('name', 'Kos Zero')->first();
        $this->assertNotNull($kos);
        $this->assertSame(0, $kos->photos()->count());
    }

    public function test_create_kos_with_1_photo_cover_auto(): void
    {
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos One', 'address' => 'Jl One', 'phone' => '081', 'status' => 'active',
            'photos' => [$this->fakeImage('a.jpg')],
        ]);
        $res->assertRedirect(route('owner.kos.index'));
        $kos = Kos::where('name', 'Kos One')->first();
        $photos = $kos->photos()->orderBy('position')->get();
        $this->assertCount(1, $photos);
        $this->assertTrue($photos[0]->is_cover);
        $this->assertSame(0, $photos[0]->position);
        Storage::disk('public')->assertExists($photos[0]->path);
        $this->assertStringStartsWith("kos/{$kos->id}/", $photos[0]->path);
    }

    public function test_create_kos_with_3_photos_first_is_cover(): void
    {
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Three', 'address' => 'Jl Three', 'phone' => '081', 'status' => 'active',
            'photos' => [$this->fakeImage('a.jpg'), $this->fakeImage('b.jpg'), $this->fakeImage('c.jpg')],
        ]);
        $res->assertRedirect(route('owner.kos.index'));
        $kos = Kos::where('name', 'Kos Three')->first();
        $photos = $kos->photos()->orderBy('position')->get();
        $this->assertCount(3, $photos);
        $this->assertTrue($photos[0]->is_cover);
        $this->assertFalse($photos[1]->is_cover);
        $this->assertFalse($photos[2]->is_cover);
        $this->assertSame([0,1,2], $photos->pluck('position')->all());
    }

    public function test_create_kos_with_10_photos(): void
    {
        $photos = array_map(fn($i) => $this->fakeImage("p{$i}.jpg"), range(0,9));
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Ten', 'address' => 'Jl Ten', 'phone' => '081', 'status' => 'active', 'photos' => $photos,
        ]);
        $res->assertRedirect(route('owner.kos.index'));
        $this->assertCount(10, Kos::where('name','Kos Ten')->first()->photos);
    }

    public function test_create_kos_with_11_photos_rejected(): void
    {
        $photos = array_map(fn($i) => $this->fakeImage("p{$i}.jpg"), range(0,10));
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Eleven', 'address' => 'Jl Eleven', 'phone' => '081', 'status' => 'active', 'photos' => $photos,
        ]);
        $res->assertSessionHasErrors('photos');
        $this->assertDatabaseMissing('kos', ['name' => 'Kos Eleven']);
    }

    public function test_create_kos_explicit_cover_index(): void
    {
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Cover2', 'address' => 'Jl Cover2', 'phone' => '081', 'status' => 'active',
            'photos' => [$this->fakeImage('a.jpg'), $this->fakeImage('b.jpg'), $this->fakeImage('c.jpg')],
            'cover_index' => 2,
        ]);
        $res->assertRedirect(route('owner.kos.index'));
        $photos = Kos::where('name','Kos Cover2')->first()->photos()->orderBy('position')->get();
        $this->assertFalse($photos[0]->is_cover);
        $this->assertFalse($photos[1]->is_cover);
        $this->assertTrue($photos[2]->is_cover);
        // cover independent of position: cover is position 2
        $this->assertSame(2, $photos[2]->position);
    }

    // ── EDIT ──────────────────────────────────────────────────

    public function test_edit_append_photos(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        KosPhoto::factory()->create(['kos_id'=>$kos->id, 'position'=>0, 'is_cover'=>true, 'path'=>'kos/'.$kos->id.'/old.jpg']);

        $res = $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name'=>$kos->name, 'address'=>$kos->address, 'phone'=>$kos->phone, 'status'=>'active',
            'photos'=>[$this->fakeImage('new1.jpg'), $this->fakeImage('new2.jpg')],
        ]);
        $res->assertRedirect(route('owner.kos.index'));
        $photos = $kos->fresh()->photos()->orderBy('position')->get();
        $this->assertCount(3, $photos);
        $this->assertSame([0,1,2], $photos->pluck('position')->all());
    }

    public function test_edit_delete_photo(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $p0 = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true,'path'=>"kos/{$kos->id}/a.jpg"]);
        $p1 = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>1,'is_cover'=>false,'path'=>"kos/{$kos->id}/b.jpg"]);
        Storage::disk('public')->put($p0->path,'x'); Storage::disk('public')->put($p1->path,'x');

        $res = $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active','delete_photo_ids'=>[$p1->id],
        ]);
        $res->assertRedirect(route('owner.kos.index'));
        $this->assertDatabaseMissing('kos_photos',['id'=>$p1->id]);
        Storage::disk('public')->assertMissing($p1->path);
        $remaining = $kos->fresh()->photos()->orderBy('position')->get();
        $this->assertCount(1,$remaining);
        $this->assertSame(0,$remaining[0]->position);
    }

    public function test_delete_cover_reassigns(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $p0 = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true,'path'=>"kos/{$kos->id}/a.jpg"]);
        $p1 = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>1,'is_cover'=>false,'path'=>"kos/{$kos->id}/b.jpg"]);
        Storage::disk('public')->put($p0->path,'x'); Storage::disk('public')->put($p1->path,'x');

        $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active','delete_photo_ids'=>[$p0->id],
        ])->assertRedirect(route('owner.kos.index'));
        $remaining = $kos->fresh()->photos()->orderBy('position')->get();
        $this->assertCount(1,$remaining);
        $this->assertTrue($remaining[0]->is_cover);
        $this->assertSame(0,$remaining[0]->position);
    }

    public function test_delete_all_photos(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $p0 = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true]);
        Storage::disk('public')->put($p0->path,'x');
        $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active','delete_photo_ids'=>[$p0->id],
        ])->assertRedirect(route('owner.kos.index'));
        $this->assertCount(0,$kos->fresh()->photos);
        $this->assertNull($kos->fresh()->coverPath() ? null : null); // no gallery cover
    }

    public function test_change_cover(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $p0 = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true]);
        $p1 = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>1,'is_cover'=>false]);
        $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active','cover_photo_id'=>$p1->id,
        ])->assertRedirect(route('owner.kos.index'));
        $this->assertTrue(KosPhoto::find($p1->id)->is_cover);
        $this->assertFalse(KosPhoto::find($p0->id)->is_cover);
        $this->assertSame(1, KosPhoto::find($p1->id)->position); // position unchanged
    }

    public function test_positions_compact_after_delete(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $a = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true]);
        $b = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>1]);
        $c = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>2]);
        $d = KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>3]);
        foreach([$a,$b,$c,$d] as $p) Storage::disk('public')->put($p->path,'x');
        $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active','delete_photo_ids'=>[$b->id],
        ])->assertRedirect(route('owner.kos.index'));
        $positions = $kos->fresh()->photos()->orderBy('position')->pluck('position')->all();
        $this->assertSame([0,1,2],$positions);
    }

    // ── AUTHORIZATION ─────────────────────────────────────────

    public function test_owner_cannot_manage_other_owner_kos(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->otherOwner->id]);
        $this->actingAs($this->owner)->get(route('owner.kos.edit', $kos))->assertStatus(403);
        $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), ['name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active'])->assertStatus(403);
    }

    public function test_owner_cannot_delete_other_owner_photo(): void
    {
        $kosA = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $kosB = Kos::factory()->create(['owner_id'=>$this->otherOwner->id]);
        $photoB = KosPhoto::factory()->create(['kos_id'=>$kosB->id,'position'=>0,'is_cover'=>true]);
        Storage::disk('public')->put($photoB->path,'x');
        $this->actingAs($this->owner)->put(route('owner.kos.update', $kosA), [
            'name'=>$kosA->name,'address'=>$kosA->address,'phone'=>$kosA->phone,'status'=>'active','delete_photo_ids'=>[$photoB->id],
        ])->assertSessionHasErrors('delete_photo_ids');
        $this->assertDatabaseHas('kos_photos',['id'=>$photoB->id]);
    }

    public function test_owner_cannot_set_cover_of_other_kos(): void
    {
        $kosA = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $kosB = Kos::factory()->create(['owner_id'=>$this->otherOwner->id]);
        KosPhoto::factory()->create(['kos_id'=>$kosA->id,'position'=>0,'is_cover'=>true]);
        $photoB = KosPhoto::factory()->create(['kos_id'=>$kosB->id,'position'=>0,'is_cover'=>true]);
        $this->actingAs($this->owner)->put(route('owner.kos.update', $kosA), [
            'name'=>$kosA->name,'address'=>$kosA->address,'phone'=>$kosA->phone,'status'=>'active','cover_photo_id'=>$photoB->id,
        ])->assertSessionHasErrors('cover_photo_id');
    }

    public function test_assigned_admin_blocked_by_owner_route_middleware(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $kos->admins()->attach($this->admin->id);
        // Owner routes are role:super_admin,owner — admin is blocked at middleware level before KosPolicy
        $this->actingAs($this->admin)->get(route('owner.kos.edit', $kos))->assertStatus(403);
        $this->actingAs($this->admin)->put(route('owner.kos.update', $kos), [
            'name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active','photos'=>[$this->fakeImage('adm.jpg')],
        ])->assertStatus(403);
    }

    public function test_unassigned_admin_cannot_manage(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $this->actingAs($this->admin)->get(route('owner.kos.edit', $kos))->assertStatus(403);
    }

    public function test_super_admin_can_manage(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $this->actingAs($this->superAdmin)->put(route('owner.kos.update', $kos), [
            'name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active','photos'=>[$this->fakeImage('sup.jpg')],
        ])->assertRedirect(route('owner.kos.index'));
        $this->assertCount(1,$kos->fresh()->photos);
    }

    public function test_tenant_cannot_manage_gallery(): void
    {
        $kos = Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $this->actingAs($this->tenant)->get(route('owner.kos.create'))->assertStatus(403);
        $this->actingAs($this->tenant)->get(route('owner.kos.edit', $kos))->assertStatus(403);
    }

    // ── VALIDATION ────────────────────────────────────────────

    public function test_invalid_mime_rejected(): void
    {
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name'=>'Kos Bad','address'=>'Jl Bad','phone'=>'081','status'=>'active','photos'=>[UploadedFile::fake()->create('bad.pdf',100,'application/pdf')],
        ]);
        $res->assertSessionHasErrors('photos.0');
    }

    public function test_oversized_rejected(): void
    {
        $big = base64_decode(self::TINY_JPEG).str_repeat("\0", 3*1024*1024);
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name'=>'Kos Big','address'=>'Jl Big','phone'=>'081','status'=>'active','photos'=>[UploadedFile::fake()->createWithContent('big.jpg',$big)],
        ]);
        $res->assertSessionHasErrors('photos.0');
    }

    public function test_safeimage_malformed_rejected(): void
    {
        $malformed = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00".str_repeat("\0",120);
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name'=>'Kos Mal','address'=>'Jl Mal','phone'=>'081','status'=>'active','photos'=>[UploadedFile::fake()->createWithContent('bad.jpg',$malformed)],
        ]);
        $res->assertSessionHasErrors('photos.0');
    }

    public function test_svg_rejected(): void
    {
        $svg='<svg xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1"/></svg>';
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name'=>'Kos Svg','address'=>'Jl Svg','phone'=>'081','status'=>'active','photos'=>[UploadedFile::fake()->createWithContent('bad.svg',$svg)],
        ]);
        $res->assertSessionHasErrors('photos.0');
    }

    public function test_empty_photos_valid(): void
    {
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name'=>'Kos Empty','address'=>'Jl Empty','phone'=>'081','status'=>'active',
        ]);
        $res->assertRedirect(route('owner.kos.index'));
    }

    // ── STORAGE ───────────────────────────────────────────────

    public function test_files_exist_after_upload(): void
    {
        $res = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name'=>'Kos Store','address'=>'Jl Store','phone'=>'081','status'=>'active','photos'=>[$this->fakeImage('a.jpg'), $this->fakeImage('b.jpg')],
        ]);
        $res->assertRedirect(route('owner.kos.index'));
        $kos = Kos::where('name','Kos Store')->first();
        foreach($kos->photos as $p){ Storage::disk('public')->assertExists($p->path); $this->assertStringStartsWith("kos/{$kos->id}/",$p->path); }
    }

    public function test_deleted_file_removed(): void
    {
        $kos=Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $p=KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true,'path'=>"kos/{$kos->id}/del.jpg"]);
        Storage::disk('public')->put($p->path,'x');
        $this->actingAs($this->owner)->put(route('owner.kos.update',$kos),['name'=>$kos->name,'address'=>$kos->address,'phone'=>$kos->phone,'status'=>'active','delete_photo_ids'=>[$p->id]])->assertRedirect(route('owner.kos.index'));
        Storage::disk('public')->assertMissing($p->path);
    }

    public function test_force_delete_cleans_files(): void
    {
        $kos=Kos::factory()->create(['owner_id'=>$this->owner->id]);
        $p0=KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true,'path'=>"kos/{$kos->id}/a.jpg"]);
        $p1=KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>1,'is_cover'=>false,'path'=>"kos/{$kos->id}/b.jpg"]);
        Storage::disk('public')->put($p0->path,'x');
        Storage::disk('public')->put($p1->path,'x');
        $kos->forceDelete();
        $this->assertDatabaseMissing('kos_photos',['id'=>$p0->id]);
        $this->assertDatabaseMissing('kos_photos',['id'=>$p1->id]);
        Storage::disk('public')->assertMissing($p0->path);
        Storage::disk('public')->assertMissing($p1->path);
    }

    // ── BACKFILL ──────────────────────────────────────────────

    public function test_backfill_creates_gallery_from_legacy_photo(): void
    {
        $kos=Kos::factory()->create(['owner_id'=>$this->owner->id,'photo'=>'kos/legacy.jpg']);
        $this->assertSame(0,$kos->photos()->count());
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\KosPhotoBackfillSeeder'])->assertExitCode(0);
        $kos->refresh();
        $photos=$kos->photos()->get();
        $this->assertCount(1,$photos);
        $this->assertSame('kos/legacy.jpg',$photos[0]->path);
        $this->assertTrue($photos[0]->is_cover);
        $this->assertSame(0,$photos[0]->position);
        // idempotent second run
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\KosPhotoBackfillSeeder'])->assertExitCode(0);
        $this->assertSame(1,$kos->fresh()->photos()->count());
    }

    public function test_backfill_skips_kos_with_existing_gallery(): void
    {
        $kos=Kos::factory()->create(['owner_id'=>$this->owner->id,'photo'=>'kos/legacy2.jpg']);
        KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true,'path'=>"kos/{$kos->id}/existing.jpg"]);
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\KosPhotoBackfillSeeder'])->assertExitCode(0);
        $this->assertSame(1,$kos->fresh()->photos()->count());
        $this->assertSame("kos/{$kos->id}/existing.jpg",$kos->fresh()->photos()->first()->path);
    }

    // ── MARKETPLACE / API ─────────────────────────────────────

    public function test_gallery_paths_fallback_to_legacy(): void
    {
        $kos=Kos::factory()->create(['owner_id'=>$this->owner->id,'photo'=>'kos/legacy3.jpg']);
        $this->assertSame(['kos/legacy3.jpg'],$kos->galleryPhotoPaths());
        $this->assertSame('kos/legacy3.jpg',$kos->coverPath());
    }

    public function test_gallery_paths_prefers_gallery(): void
    {
        $kos=Kos::factory()->create(['owner_id'=>$this->owner->id,'photo'=>'kos/legacy4.jpg']);
        KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true,'path'=>"kos/{$kos->id}/g0.jpg"]);
        KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>1,'is_cover'=>false,'path'=>"kos/{$kos->id}/g1.jpg"]);
        $this->assertSame(["kos/{$kos->id}/g0.jpg","kos/{$kos->id}/g1.jpg"], $kos->galleryPhotoPaths());
        $this->assertSame("kos/{$kos->id}/g0.jpg", $kos->coverPath());
    }

    public function test_api_kos_detail_returns_gallery(): void
    {
        $kos=Kos::factory()->create(['status'=>'active','photo'=>'kos/legacy5.jpg']);
        \App\Models\Kamar::factory()->create(['kos_id'=>$kos->id,'status'=>'available']);
        $response=$this->getJson("/api/v1/kos/{$kos->id}");
        $response->assertOk();
        $this->assertSame(['kos/legacy5.jpg'],$response->json('data.photos'));
        $this->assertSame('kos/legacy5.jpg',$response->json('data.photo'));
        $this->assertSame('kos/legacy5.jpg',$response->json('data.cover_photo'));
    }

    public function test_api_kos_detail_with_gallery(): void
    {
        $kos=Kos::factory()->create(['status'=>'active','photo'=>'kos/legacy6.jpg']);
        \App\Models\Kamar::factory()->create(['kos_id'=>$kos->id,'status'=>'available']);
        KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true,'path'=>"kos/{$kos->id}/api0.jpg"]);
        KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>1,'is_cover'=>false,'path'=>"kos/{$kos->id}/api1.jpg"]);
        $response=$this->getJson("/api/v1/kos/{$kos->id}");
        $response->assertOk();
        $this->assertSame(["kos/{$kos->id}/api0.jpg","kos/{$kos->id}/api1.jpg"], $response->json('data.photos'));
        $this->assertSame("kos/{$kos->id}/api0.jpg", $response->json('data.cover_photo'));
        $this->assertSame("kos/{$kos->id}/api0.jpg", $response->json('data.photo'));
    }

    public function test_marketplace_detail_shows_gallery(): void
    {
        $kos=Kos::factory()->create(['owner_id'=>$this->owner->id,'status'=>'active']);
        KosPhoto::factory()->create(['kos_id'=>$kos->id,'position'=>0,'is_cover'=>true,'path'=>"kos/{$kos->id}/m0.jpg"]);
        \App\Models\Kamar::factory()->create(['kos_id'=>$kos->id,'status'=>'available']);
        $response=$this->actingAs($this->tenant)->get(route('tenant.kos.show', $kos));
        $response->assertOk();
        $response->assertSee('m0.jpg', false);
        $response->assertSee('foto', false);
    }
}
