<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\DamageReport;
use App\Models\Favorite;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\Message;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Audit N+1: memastikan halaman utama tidak melakukan lazy loading relasi.
 *
 * Model::preventLazyLoading() membuat Eloquent melempar exception saat relasi
 * diakses tanpa eager loading, sehingga regresi N+1 langsung terdeteksi.
 */
class LazyLoadingAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $admin;

    private User $superAdmin;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private Kontrak $kontrak;

    private Tagihan $tagihan;

    private Pembayaran $pembayaran;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        Model::preventLazyLoading();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);

        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'moderation_status' => 'approved',
        ]);
        $this->admin->assignedKos()->attach($this->kos);

        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'occupied',
        ]);

        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $this->tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
            'due_date' => now()->addDays(3),
        ]);

        $this->pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'pending',
        ]);

        $this->booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_APPROVED,
        ]);

        CheckOut::create([
            'penghuni_id' => $this->penghuni->id,
            'kamar_id' => $this->kamar->id,
            'request_date' => now(),
            'status' => CheckOut::STATUS_PENDING,
        ]);

        DamageReport::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'owner_id' => $this->owner->id,
            'status' => DamageReport::STATUS_PENDING,
        ]);

        KosPhoto::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => KosPhoto::STATUS_PENDING,
        ]);

        Message::factory()->create([
            'kos_id' => $this->kos->id,
            'owner_id' => $this->owner->id,
            'sender_id' => $this->tenant->id,
            'receiver_id' => $this->owner->id,
            'read_at' => null,
        ]);

        // Favorit memaksa dashboard tenant merender section "Kos yang Kamu Simpan"
        // sehingga eager loading pada query favorit ikut teraudit.
        Favorite::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
        ]);
    }

    protected function tearDown(): void
    {
        Model::preventLazyLoading(false);

        parent::tearDown();
    }

    public function test_super_admin_pages_do_not_lazy_load(): void
    {
        $this->withoutExceptionHandling();

        $pages = [
            route('dashboard'),
            route('super-admin.admins.index'),
            route('super-admin.users.index'),
            route('super-admin.owners.index'),
            route('super-admin.owners.show', $this->owner),
            route('super-admin.moderation.index'),
            route('super-admin.moderation.kos'),
            route('super-admin.moderation.photos'),
            route('super-admin.moderation.advertising'),
            route('super-admin.moderation.damage-reports'),
            route('super-admin.fasilitas.index'),
            route('super-admin.partners.index'),
            route('super-admin.advertising.dashboard'),
            route('super-admin.advertising.campaigns.index'),
            route('super-admin.advertising.packages.index'),
            route('super-admin.advertising.revenue'),
            route('super-admin.laporan.index'),
            route('super-admin.audit-log.index'),
            route('super-admin.settings.index'),
            route('super-admin.system-health.index'),
            route('notifications.index'),
            route('profile.edit'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($this->superAdmin)->get($url)->assertOk();
        }
    }

    public function test_owner_pages_do_not_lazy_load(): void
    {
        $this->withoutExceptionHandling();

        $pages = [
            route('dashboard'),
            route('owner.kos.index'),
            route('owner.kos.show', $this->kos),
            route('owner.kamar.index'),
            route('owner.kamar.show', $this->kamar),
            route('owner.penghuni.index'),
            route('owner.penghuni.show', $this->penghuni),
            route('owner.kontrak.index'),
            route('owner.kontrak.show', $this->kontrak),
            route('owner.tagihan.index'),
            route('owner.tagihan.show', $this->tagihan),
            route('owner.pembayaran.index'),
            route('owner.pembayaran.show', $this->pembayaran),
            route('owner.booking.index'),
            route('owner.booking.show', $this->booking),
            route('owner.checkin.index'),
            route('owner.checkout.index'),
            route('owner.laporan.index'),
            route('notifications.index'),
            route('profile.edit'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($this->owner)->get($url)->assertOk();
        }
    }

    public function test_admin_pages_do_not_lazy_load(): void
    {
        $this->withoutExceptionHandling();

        $pages = [
            route('dashboard'),
            route('admin.kos.index'),
            route('admin.kos.show', $this->kos),
            route('admin.kamar.index'),
            route('admin.kamar.show', $this->kamar),
            route('admin.penghuni.index'),
            route('admin.penghuni.show', $this->penghuni),
            route('admin.kontrak.index'),
            route('admin.kontrak.show', $this->kontrak),
            route('admin.tagihan.index'),
            route('admin.tagihan.show', $this->tagihan),
            route('admin.pembayaran.index'),
            route('admin.pembayaran.show', $this->pembayaran),
            route('admin.booking.index'),
            route('admin.booking.show', $this->booking),
            route('admin.checkin.index'),
            route('admin.checkout.index'),
            route('admin.laporan.index'),
            route('admin.moderation.kos'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_tenant_pages_do_not_lazy_load(): void
    {
        $this->withoutExceptionHandling();

        $pages = [
            route('dashboard'),
            route('tenant.kos.index'),
            route('tenant.kos.show', $this->kos),
            route('tenant.booking.index'),
            route('tenant.booking.show', $this->booking),
            route('tenant.kontrak.index'),
            route('tenant.kontrak.show', $this->kontrak),
            route('tenant.tagihan.index'),
            route('tenant.tagihan.show', $this->tagihan),
            route('tenant.pembayaran.index'),
            route('notifications.index'),
            route('profile.edit'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($this->tenant)->get($url)->assertOk();
        }
    }
}
