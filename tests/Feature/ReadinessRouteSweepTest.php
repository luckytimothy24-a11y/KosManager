<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ReadinessRouteSweepTest extends TestCase
{
    use RefreshDatabase;

    public function test_sweep_all_roles(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())->filter(fn ($r) =>
            in_array('GET', $r->methods())
            && !str_contains($r->uri(), '{')
            && !preg_match('#^(_|storage|sanctum|up$)#', $r->uri())
        )->values();

        @mkdir('C:\temp\audit', 0777, true);
        $rows = ['role,uri,status,error'];
        $problems = [];

        foreach (['guest', 'tenant', 'owner', 'admin', 'super_admin'] as $role) {
            $user = null;
            if ($role !== 'guest') {
                $user = User::factory()->create();
                $user->forceFill(['role' => $role])->save();
            }

            foreach ($routes as $r) {
                $this->app['auth']->forgetGuards();
                $url = '/' . ltrim($r->uri(), '/');
                $res = $user ? $this->actingAs($user)->get($url) : $this->get($url);

                $status = $res->getStatusCode();
                $err = '';
                if ($status >= 500) {
                    $e = $res->baseResponse->exception ?? null;
                    $err = $e ? str_replace(["\r", "\n", ','], ' ', get_class($e) . ': ' . $e->getMessage()) : 'unknown';
                    $problems[] = "500 [$role] $url $err";
                }
                if ($role === 'tenant' && $status === 200 && preg_match('#^/(owner|admin|super)#', $url)) {
                    $problems[] = "AKSES SILANG [tenant] $url";
                }
                if ($role === 'guest' && $status === 200 && preg_match('#^/(dashboard|owner|admin|tenant|profile)#', $url)) {
                    $problems[] = "GUEST LOLOS [guest] $url";
                }
                $rows[] = "$role,$url,$status,$err";
            }
        }

        file_put_contents('C:\temp\audit\smoke.csv', implode(PHP_EOL, $rows));
        $this->assertEmpty($problems, implode(PHP_EOL, $problems));
    }
}