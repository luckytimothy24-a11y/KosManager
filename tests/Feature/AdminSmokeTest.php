<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ─── DASHBOARD ────────────────────────────────────────────

    public function test_admin_dashboard_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertSuccessful();
    }

    // ─── KOS PAGES ────────────────────────────────────────────

    public function test_admin_kos_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kos.index'));
        $response->assertSuccessful();
    }

    public function test_admin_kos_create_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kos.create'));
        $response->assertSuccessful();
    }

    public function test_admin_kos_show_renders(): void
    {
        $kos = \App\Models\Kos::factory()->create(['owner_id' => User::factory()->create(['role' => 'owner'])->id]);
        $this->admin->assignedKos()->attach($kos->id);

        $response = $this->actingAs($this->admin)->get(route('admin.kos.show', $kos));
        $response->assertSuccessful();
    }

    // ─── KAMAR PAGES ────────────────────────────────────────────

    public function test_admin_kamar_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kamar.index'));
        $response->assertSuccessful();
    }

    public function test_admin_kamar_create_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kamar.create'));
        $response->assertSuccessful();
    }

    // ─── BOOKING PAGES ─────────────────────────────────────────

    public function test_admin_booking_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.booking.index'));
        $response->assertSuccessful();
    }

    // ─── PENGHUNI PAGES ───────────────────────────────────────

    public function test_admin_penghuni_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.penghuni.index'));
        $response->assertSuccessful();
    }

    // ─── KONTRAK PAGES ────────────────────────────────────────

    public function test_admin_kontrak_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kontrak.index'));
        $response->assertSuccessful();
    }

    // ─── TAGIHAN PAGES ───────────────────────────────────────

    public function test_admin_tagihan_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tagihan.index'));
        $response->assertSuccessful();
    }

    public function test_admin_tagihan_create_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tagihan.create'));
        $response->assertSuccessful();
    }

    // ─── PEMBAYARAN PAGES ─────────────────────────────────────

    public function test_admin_pembayaran_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.pembayaran.index'));
        $response->assertSuccessful();
    }

    // ─── LA LAPAN PAGES ───────────────────────────────────────

    public function test_admin_laporan_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index'));
        $response->assertSuccessful();
    }

    // ─── ADVERTISING PAGES ───────────────────────────────────

    public function test_admin_advertising_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.advertising.index'));
        $response->assertSuccessful();
    }

    public function test_admin_advertising_create_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.advertising.create'));
        $response->assertSuccessful();
    }
}