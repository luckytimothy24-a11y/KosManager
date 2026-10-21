<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Message;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantInquiryTest extends TestCase
{
    use RefreshDatabase;

    private function makeKos(User $owner): array
    {
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        return [$kos, $kamar];
    }

    public function test_seeker_without_penghuni_can_open_ask_form(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($seeker)->get(route('tenant.kos.ask', $kos))->assertOk();
    }

    public function test_ask_form_renders_clickable_topic_pills(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($seeker)->get(route('tenant.kos.ask', $kos));
        $response->assertOk();
        $response->assertSee('Umum');
        $response->assertSee('Fasilitas');
        $response->assertSee('type="radio" name="category"', escape: false);
    }

    public function test_seeker_can_send_inquiry_to_kos_owner(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
            'message' => 'Apakah ada kamar kosong bulan depan?',
            'category' => 'kamar',
        ]);

        $response->assertRedirect(route('tenant.inquiries.index'));
        $this->assertDatabaseHas('messages', [
            'kos_id' => $kos->id,
            'sender_id' => $seeker->id,
            'receiver_id' => $owner->id,
            'message' => 'Apakah ada kamar kosong bulan depan?',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'message',
        ]);
    }

    public function test_inquiry_to_inactive_kos_is_not_found(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'inactive']);
        $seeker = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($seeker)->get(route('tenant.kos.ask', $kos))->assertNotFound();
        $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
            'message' => 'Halo',
        ])->assertNotFound();
    }

    public function test_owner_sees_seeker_in_inquiries_tab(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $seeker->id,
            'receiver_id' => $owner->id,
            'message' => 'Berapa harga termurah?',
        ]);

        $response = $this->actingAs($owner)->get(route('owner.messages.inquiries'));
        $response->assertOk();
        $response->assertSee('Pertanyaan Masuk');
        $response->assertSee($seeker->name);
        $response->assertSee('Calon Penghuni');
        $response->assertSee('Berapa harga termurah?');
    }

    public function test_active_penghuni_thread_not_in_inquiries_tab(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos, $kamar] = $this->makeKos($owner);
        $tenant = User::factory()->create(['role' => 'tenant']);
        Penghuni::factory()->create([
            'user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active',
        ]);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'message' => 'AC rusak',
        ]);

        $response = $this->actingAs($owner)->get(route('owner.messages.inquiries'));
        $response->assertOk();
        $response->assertDontSee('AC rusak');
    }

    public function test_other_owner_cannot_see_inquiry(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $seeker->id,
            'receiver_id' => $owner->id,
            'message' => 'Rahasia calon penghuni',
        ]);

        $ownerB = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($ownerB)->get(route('owner.messages.inquiries'));
        $response->assertOk();
        $response->assertDontSee('Rahasia calon penghuni');
        $this->actingAs($ownerB)->get(route('owner.messages.show', $seeker))->assertForbidden();
    }

    public function test_owner_can_reply_to_seeker(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $seeker->id,
            'receiver_id' => $owner->id,
            'message' => 'Ada kamar kosong?',
        ]);

        $response = $this->actingAs($owner)->post(route('owner.messages.reply', $seeker), [
            'message' => 'Ada, silakan booking.',
        ]);

        $response->assertRedirect(route('owner.messages.show', ['tenant' => $seeker, 'kos' => $kos->id]));
        $this->assertDatabaseHas('messages', [
            'kos_id' => $kos->id,
            'sender_id' => $owner->id,
            'receiver_id' => $seeker->id,
            'message' => 'Ada, silakan booking.',
        ]);
    }

    public function test_seeker_sees_own_inquiries_only(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seekerA = User::factory()->create(['role' => 'tenant']);
        $seekerB = User::factory()->create(['role' => 'tenant']);
        Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $seekerA->id, 'receiver_id' => $owner->id, 'message' => 'Tanya A rahasia',
        ]);
        Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $seekerB->id, 'receiver_id' => $owner->id, 'message' => 'Tanya B',
        ]);

        $response = $this->actingAs($seekerA)->get(route('tenant.inquiries.index'));
        $response->assertOk();
        $response->assertSee('Tanya A rahasia');
        $response->assertDontSee('Tanya B');
    }

    public function test_inquiry_store_is_throttled(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
                'message' => "Spam $i",
            ])->assertRedirect();
        }

        $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
            'message' => 'Spam 6',
        ])->assertStatus(429);
    }

    public function test_seeker_can_open_thread_and_reply(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $seeker->id,
            'receiver_id' => $owner->id,
            'message' => 'Ada kamar kosong?',
        ]);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $owner->id,
            'receiver_id' => $seeker->id,
            'message' => 'Ada, silakan booking.',
        ]);

        $response = $this->actingAs($seeker)->get(route('tenant.inquiries.show', $kos));
        $response->assertOk();
        $response->assertSee('Ada kamar kosong?');
        $response->assertSee('Ada, silakan booking.');

        $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
            'message' => 'Oke, saya booking minggu depan.',
        ])->assertRedirect(route('tenant.inquiries.index'));
    }

    public function test_thread_is_isolated_per_seeker_and_kos(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos, $kamar] = $this->makeKos($owner);
        $kosB = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $seekerA = User::factory()->create(['role' => 'tenant']);
        $seekerB = User::factory()->create(['role' => 'tenant']);
        Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $seekerA->id, 'receiver_id' => $owner->id, 'message' => 'Rahasia A',
        ]);
        Message::factory()->create([
            'kos_id' => $kosB->id, 'sender_id' => $seekerA->id, 'receiver_id' => $owner->id, 'message' => 'Tanya kos B',
        ]);
        Message::factory()->create([
            'kos_id' => $kos->id, 'sender_id' => $seekerB->id, 'receiver_id' => $owner->id, 'message' => 'Rahasia B',
        ]);

        // Thread kos A milik seeker A: panel hanya berisi pesan kos A,
        // milik seeker A (daftar kiri wajar memuat thread miliknya sendiri).
        $response = $this->actingAs($seekerA)->get(route('tenant.inquiries.show', $kos));
        $response->assertOk();
        $response->assertSee('Rahasia A');
        $response->assertDontSee('Rahasia B');

        // Seeker B tidak bisa membuka thread kos milik A ... (ia punya thread sendiri di kos yang sama)
        $responseB = $this->actingAs($seekerB)->get(route('tenant.inquiries.show', $kos));
        $responseB->assertOk();
        $responseB->assertSee('Rahasia B');
        $responseB->assertDontSee('Rahasia A');

        // Tanpa pesan sama sekali → 404.
        $loner = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($loner)->get(route('tenant.inquiries.show', $kos))->assertNotFound();
    }

    public function test_chat_pages_render_shell_columns(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos, $kamar] = $this->makeKos($owner);
        $tenant = User::factory()->create(['role' => 'tenant']);
        Penghuni::factory()->create([
            'user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active',
        ]);
        Message::factory()->create([
            'kos_id' => $kos->id,
            'sender_id' => $tenant->id,
            'receiver_id' => $owner->id,
            'message' => 'Halo owner',
        ]);

        $this->actingAs($owner)->get(route('owner.messages.index'))->assertOk()->assertSee('Pilih percakapan');
        $this->actingAs($owner)->get(route('owner.messages.show', $tenant))->assertOk()->assertSee('Halo owner');
        $this->actingAs($tenant)->get(route('tenant.messages.index'))->assertOk()->assertSee('Halo owner');
    }

    public function test_topic_is_saved_labeled_and_filterable(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);

        // Topik dari form tersimpan dan berlabel di thread.
        $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
            'message' => 'Berapa harga kamar?',
            'category' => 'kamar',
        ])->assertRedirect();
        $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
            'message' => 'Ada WiFi?',
            'category' => 'fasilitas',
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'kos_id' => $kos->id,
            'sender_id' => $seeker->id,
            'category' => 'kamar',
        ]);

        // Bilah filter topik tampil di thread + label kategori tampil.
        $response = $this->actingAs($seeker)->get(route('tenant.inquiries.show', $kos));
        $response->assertOk();
        $response->assertSee('Filter topik');
        $response->assertSee('Kamar');
        $response->assertSee('Fasilitas');

        // Owner melihat bilah filter yang sama.
        $ownerResponse = $this->actingAs($owner)->get(route('owner.messages.show', $seeker));
        $ownerResponse->assertOk();
        $ownerResponse->assertSee('Filter topik');
    }

    public function test_overdue_tenant_can_still_ask(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        [$kos, $kamar] = $this->makeKos($owner);
        $seeker = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $seeker->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active',
        ]);
        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => Tagihan::STATUS_OVERDUE,
        ]);

        // Rute non-billing lain tetap diblokir ...
        $this->actingAs($seeker)->get(route('tenant.booking.index'))
            ->assertRedirect(route('tenant.tagihan.index'));
        // ... tetapi bertanya tetap diizinkan.
        $this->actingAs($seeker)->get(route('tenant.kos.ask', $kos))->assertOk();
        $this->actingAs($seeker)->post(route('tenant.kos.ask.store', $kos), [
            'message' => 'Saya mau tanya dulu.',
        ])->assertRedirect(route('tenant.inquiries.index'));
    }
}
