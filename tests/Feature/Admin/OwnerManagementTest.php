<?php

namespace Tests\Feature\Admin;

use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\PropertyOwner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OwnerManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeOwner(string $name = 'Owner'): User
    {
        return User::factory()->create(['role' => 'owner', 'name' => $name]);
    }

    public function test_admin_can_open_pengelola_owner_menu(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get(route('admin.owners.index'))->assertStatus(200);
    }

    public function test_super_admin_can_open_pengelola_owner(): void
    {
        $sa = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($sa)->get(route('admin.owners.index'))->assertStatus(200);
    }

    public function test_owner_scope_only_assigned_kos(): void
    {
        $admin = $this->makeAdmin();
        $ownerIn = $this->makeOwner('Owner In');
        $ownerOut = $this->makeOwner('Owner Out');
        $kosIn = Kos::factory()->create(['owner_id' => $ownerIn->id]);
        $kosOut = Kos::factory()->create(['owner_id' => $ownerOut->id]);
        $admin->assignedKos()->attach($kosIn->id);

        $res = $this->actingAs($admin)->get(route('admin.owners.index'));
        $res->assertStatus(200);
        $res->assertSee($ownerIn->name);
        $res->assertDontSee($ownerOut->name);
    }

    public function test_owner_without_assigned_kos_not_visible(): void
    {
        $admin = $this->makeAdmin();
        $ownerNoKos = $this->makeOwner('Owner NoKos');

        $res = $this->actingAs($admin)->get(route('admin.owners.index'));
        $res->assertStatus(200);
        $res->assertDontSee($ownerNoKos->name);
    }

    public function test_detail_shows_only_owned_kos(): void
    {
        $admin = $this->makeAdmin();
        $ownerA = $this->makeOwner('Owner A');
        $ownerB = $this->makeOwner('Owner B');
        $kosA = Kos::factory()->create(['owner_id' => $ownerA->id, 'name' => 'Kos A']);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id, 'name' => 'Kos B']);
        $admin->assignedKos()->attach([$kosA->id, $kosB->id]);

        $this->actingAs($admin)->get(route('admin.owners.show', $ownerA))->assertStatus(200)->assertSee('Kos A')->assertDontSee('Kos B');
        $this->actingAs($admin)->get(route('admin.owners.show', $ownerB))->assertStatus(200)->assertSee('Kos B')->assertDontSee('Kos A');
    }

    public function test_property_owner_id_not_confused_with_owner_id(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner('Owner Prop');
        $propertyOwner = PropertyOwner::factory()->create();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'property_owner_id' => $propertyOwner->id, 'name' => 'Kos Prop']);
        $admin->assignedKos()->attach($kos->id);

        $res = $this->actingAs($admin)->get(route('admin.owners.show', $owner));
        $res->assertStatus(200)->assertSee('Kos Prop');

        $otherOwner = $this->makeOwner('Other');
        $res2 = $this->actingAs($admin)->get(route('admin.owners.show', $otherOwner));
        $res2->assertStatus(403);
    }

    public function test_unauthorized_roles_rejected(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->makeOwner();
        $this->actingAs($tenant)->get(route('admin.owners.index'))->assertStatus(403);
        $this->actingAs($owner)->get(route('admin.owners.index'))->assertStatus(403);

        $admin = $this->makeAdmin();
        $targetOwner = $this->makeOwner();
        $kos = Kos::factory()->create(['owner_id' => $targetOwner->id]);
        $admin->assignedKos()->attach($kos->id);

        $tenant2 = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($tenant2)->get(route('admin.owners.show', $targetOwner))->assertStatus(403);
    }

    public function test_invalid_owner_returns_404(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get(route('admin.owners.show', 999999))->assertStatus(404);
    }

    public function test_search_pagination_empty_state(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner('Searchable Owner');
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $admin->assignedKos()->attach($kos->id);

        $this->actingAs($admin)->get(route('admin.owners.index', ['search' => 'Searchable']))->assertStatus(200)->assertSee('Searchable Owner');
        $this->actingAs($admin)->get(route('admin.owners.index', ['search' => 'NonExistentXYZ']))->assertStatus(200)->assertSee('Tidak ada pengelola ditemukan');

        $ownerEmpty = $this->makeOwner('Empty Owner');
        $ownerEmpty->load(['ownedKos']);
        $this->actingAs($admin)->get(route('admin.owners.show', $ownerEmpty))->assertStatus(403);

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $emptyOwnerForSA = $this->makeOwner('Empty SA');
        $this->actingAs($superAdmin)->get(route('admin.owners.show', $emptyOwnerForSA))->assertStatus(200)->assertSee('Belum ada kos yang dapat ditampilkan untuk pengelola ini.');
    }

    public function test_detail_idor_blocked_outside_scope(): void
    {
        $admin = $this->makeAdmin();
        $ownerIn = $this->makeOwner('In Scope');
        $ownerOut = $this->makeOwner('Out Scope');
        $kosIn = Kos::factory()->create(['owner_id' => $ownerIn->id]);
        $kosOut = Kos::factory()->create(['owner_id' => $ownerOut->id]);
        $admin->assignedKos()->attach($kosIn->id);

        $this->actingAs($admin)->get(route('admin.owners.show', $ownerOut))->assertStatus(403);
        $this->actingAs($admin)->get(route('admin.owners.show', $ownerIn))->assertStatus(200);
    }

    public function test_no_financial_data_leaked(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $admin->assignedKos()->attach($kos->id);

        $res = $this->actingAs($admin)->get(route('admin.owners.index'));
        $res->assertDontSee('totalRevenue');
        $res->assertDontSee('rekening');

        $res2 = $this->actingAs($admin)->get(route('admin.owners.show', $owner));
        $res2->assertDontSee('totalRevenue');
        $res2->assertDontSee('rekening');
        $res2->assertDontSee('Total Pendapatan');
    }

    public function test_route_not_found_exception_not_thrown(): void
    {
        $this->assertTrue(Route::has('admin.owners.index'));
        $this->assertTrue(Route::has('admin.owners.show'));
    }

    public function test_sidebar_contains_pengelola_owner_for_admin(): void
    {
        $admin = $this->makeAdmin();
        $res = $this->actingAs($admin)->get(route('admin.kos.index'));
        $res->assertStatus(200);
        $res->assertSee('Pengelola Owner');
        $res->assertSee(route('admin.owners.index'));
    }

    public function test_query_efficient_no_n_plus_one(): void
    {
        $admin = $this->makeAdmin();
        $owners = User::factory()->count(5)->create(['role' => 'owner']);
        foreach ($owners as $o) {
            $k = Kos::factory()->create(['owner_id' => $o->id]);
            $admin->assignedKos()->attach($k->id);
        }

        \DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.owners.index'))->assertStatus(200);
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        $this->assertLessThan(15, count($queries), 'Too many queries, likely N+1');
    }

    public function test_show_pagination_nine_per_page(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner('Paginate Owner');
        $kosIds = [];
        for ($i = 1; $i <= 12; $i++) {
            $kosIds[] = Kos::factory()->create(['owner_id' => $owner->id, 'name' => "Kos Paginate $i"])->id;
        }
        $admin->assignedKos()->attach($kosIds);

        $res = $this->actingAs($admin)->get(route('admin.owners.show', $owner));
        $res->assertStatus(200);
        $paginator = $res->viewData('kosPaginator');
        $this->assertNotNull($paginator);
        $this->assertEquals(9, $paginator->perPage());
        $this->assertEquals(12, $paginator->total());
        $this->assertEquals(9, $paginator->count());

        $res2 = $this->actingAs($admin)->get(route('admin.owners.show', ['owner' => $owner->id, 'page' => 2]));
        $res2->assertStatus(200);
        $p2 = $res2->viewData('kosPaginator');
        $this->assertEquals(2, $p2->currentPage());
        $this->assertEquals(3, $p2->count());
    }

    public function test_show_with_query_string_preserved(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner('QS Owner');
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $admin->assignedKos()->attach($kos->id);

        $res = $this->actingAs($admin)->get(route('admin.owners.show', ['owner' => $owner->id, 'page' => 1, 'foo' => 'bar']));
        $res->assertStatus(200);
        $paginator = $res->viewData('kosPaginator');
        $this->assertStringContainsString('foo=bar', $paginator->withQueryString()->url(1));
    }

    public function test_show_foto_cover_fallback_and_placeholder(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner('Foto Owner');
        $kosWithCover = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'Kos Cover', 'photo' => null]);
        KosPhoto::factory()->cover()->create(['kos_id' => $kosWithCover->id, 'path' => 'kos/cover-test.jpg', 'position' => 0]);
        $kosLegacy = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'Kos Legacy', 'photo' => 'kos/legacy.jpg']);
        $kosNoPhoto = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'Kos NoPhoto', 'photo' => null]);
        $admin->assignedKos()->attach([$kosWithCover->id, $kosLegacy->id, $kosNoPhoto->id]);

        $res = $this->actingAs($admin)->get(route('admin.owners.show', $owner));
        $res->assertStatus(200);
        $res->assertSee('kos/cover-test.jpg');
        $res->assertSee('kos/legacy.jpg');
        $res->assertSee('Foto belum tersedia');
        $res->assertSee('Kos Cover');
        $res->assertSee('Kos Legacy');
        $res->assertSee('Kos NoPhoto');
    }

    public function test_show_no_n_plus_one_with_photos_and_counts(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner('NPlus Owner');
        $ids = [];
        for ($i = 0; $i < 5; $i++) {
            $k = Kos::factory()->create(['owner_id' => $owner->id]);
            KosPhoto::factory()->create(['kos_id' => $k->id, 'path' => "kos/nplus-$i.jpg"]);
            $ids[] = $k->id;
        }
        $admin->assignedKos()->attach($ids);

        \DB::enableQueryLog();
        $this->actingAs($admin)->get(route('admin.owners.show', $owner))->assertStatus(200);
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();
        $this->assertLessThan(25, count($queries), 'N+1 on show with photos/counts');
    }

    public function test_show_kos_link_only_when_can_access_kos(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner('Link Owner');
        $kosIn = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'Kos In Link']);
        $admin->assignedKos()->attach($kosIn->id);

        $res = $this->actingAs($admin)->get(route('admin.owners.show', $owner));
        $res->assertStatus(200);
        $res->assertSee(route('admin.kos.show', $kosIn));
        $res->assertSee('Lihat Detail Kos');

        $admin2 = $this->makeAdmin();
        $this->actingAs($admin2)->get(route('admin.owners.show', $owner))->assertStatus(403);
    }

    public function test_super_admin_show_sees_all_kos_without_assignment(): void
    {
        $sa = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->makeOwner('SA See All');
        $kos1 = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'SA Kos 1']);
        $kos2 = Kos::factory()->create(['owner_id' => $owner->id, 'name' => 'SA Kos 2']);

        $res = $this->actingAs($sa)->get(route('admin.owners.show', $owner));
        $res->assertStatus(200);
        $res->assertSee('SA Kos 1');
        $res->assertSee('SA Kos 2');
        $paginator = $res->viewData('kosPaginator');
        $this->assertEquals(2, $paginator->total());
    }

    public function test_super_admin_empty_state_exact_text(): void
    {
        $sa = User::factory()->create(['role' => 'super_admin']);
        $emptyOwner = $this->makeOwner('SA Empty Exact');
        $res = $this->actingAs($sa)->get(route('admin.owners.show', $emptyOwner));
        $res->assertStatus(200);
        $res->assertSee('Belum ada kos yang dapat ditampilkan untuk pengelola ini.', false);
    }

    public function test_admin_index_kos_count_scoped(): void
    {
        $admin = $this->makeAdmin();
        $owner = $this->makeOwner('Count Owner');
        $k1 = Kos::factory()->create(['owner_id' => $owner->id]);
        $k2 = Kos::factory()->create(['owner_id' => $owner->id]);
        $k3 = Kos::factory()->create(['owner_id' => $owner->id]);
        $admin->assignedKos()->attach([$k1->id, $k2->id]);

        $res = $this->actingAs($admin)->get(route('admin.owners.index'));
        $res->assertStatus(200);
        $owners = $res->viewData('owners');
        $found = $owners->firstWhere('id', $owner->id);
        $this->assertNotNull($found);
        $this->assertEquals(2, $found->owned_kos_count);
    }
}
