<?php

namespace Tests\Feature;

use App\Models\DamageReport;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerParamRoutes2Test extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role])->save();
        return $u;
    }

    private function hit(User $user, string $url)
    {
        $this->app['auth']->forgetGuards();
        return $this->actingAs($user)->get($url);
    }

    private function err($r): string
    {
        $e = $r->baseResponse->exception ?? null;
        return $e ? get_class($e) . ': ' . $e->getMessage() : '';
    }

    private function check(string $label, User $owner, User $other, string $url): void
    {
        $r = $this->hit($owner, $url);
        $this->assertSame(200, $r->getStatusCode(), "$label pemilik: " . $r->getStatusCode() . ' ' . $this->err($r) . " ($url)");

        $r2 = $this->hit($other, $url);
        $this->assertContains($r2->getStatusCode(), [403, 404], "$label IDOR: owner lain mendapat " . $r2->getStatusCode() . ' ' . $this->err($r2) . " ($url)");
    }

    public function test_kontrak_tagihan_and_damage(): void
    {
        $a = $this->makeUser('owner');
        $b = $this->makeUser('owner');
        $tenant = $this->makeUser('tenant');

        $kos     = Kos::factory()->create(['owner_id' => $a->id]);
        $kamar   = Kamar::factory()->create(['kos_id' => $kos->id]);
        $kontrak = Kontrak::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id]);
        $tagihan = Tagihan::factory()->create([
            'kontrak_id' => $kontrak->id,
            'penghuni_id' => $kontrak->penghuni_id,
            'kamar_id' => $kamar->id,
        ]);

        // detail kontrak harus memuat tautan ke detail tagihan (baris 94 yang diperbaiki)
        $tagihanUrl = route('owner.tagihan.show', $tagihan);
        $this->check('kontrak.show (dengan tagihan)', $a, $b, route('owner.kontrak.show', $kontrak));
        $this->hit($a, route('owner.kontrak.show', $kontrak))->assertSee($tagihanUrl, false);

        $this->check('tagihan.show', $a, $b, $tagihanUrl);

        $report = DamageReport::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
        ]);
        $this->check('damage-reports.show', $a, $b, route('owner.damage-reports.show', $report));
        // daftar: owner A melihat laporannya, owner B tidak
        $showUrl = route('owner.damage-reports.show', $report);
        $this->hit($a, route('owner.damage-reports.index'))->assertOk()->assertSee($showUrl, false);
        $this->hit($b, route('owner.damage-reports.index'))->assertOk()->assertDontSee($showUrl, false);
    }
}