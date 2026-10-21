<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerParamRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwner(): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => 'owner'])->save();
        return $u;
    }

    private function hit(User $user, string $url): array
    {
        $this->app['auth']->forgetGuards();
        $r = $this->actingAs($user)->get($url);
        $e = $r->baseResponse->exception ?? null;
        return [$r->getStatusCode(), $e ? get_class($e) . ': ' . $e->getMessage() : ''];
    }

    private function check(string $label, User $owner, User $other, string $url): void
    {
        [$s, $err] = $this->hit($owner, $url);
        $this->assertSame(200, $s, "$label pemilik: status $s $err ($url)");

        [$s2, $err2] = $this->hit($other, $url);
        $this->assertContains($s2, [403, 404], "$label IDOR: owner lain mendapat $s2 $err2 ($url)");
    }

    public function test_owner_param_pages(): void
    {
        $a = $this->makeOwner();
        $b = $this->makeOwner();

        $kos   = Kos::factory()->create(['owner_id' => $a->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $booking = Booking::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id]);
        $kontrak = Kontrak::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id]);

        $this->check('kamar.edit', $a, $b, route('owner.kamar.edit', $kamar));
        $this->check('kamar.show', $a, $b, route('owner.kamar.show', $kamar));
        $this->check('booking.occupants', $a, $b, route('owner.booking-occupants.index', $booking));
        $this->check('kontrak.show', $a, $b, route('owner.kontrak.show', $kontrak));
    }
}