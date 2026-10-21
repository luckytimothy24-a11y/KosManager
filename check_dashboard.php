<?php

use App\Http\Controllers\DashboardController;
use App\Models\User;
use App\Services\AdvertisingService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
foreach (['super_admin', 'admin', 'owner', 'tenant'] as $role) {
    $u = User::where('role', $role)->first();
    if (! $u) {
        echo "$role: no user\n";

        continue;
    }
    try {
        $c = new DashboardController(new AdvertisingService);
        $r = Request::create('/dashboard', 'GET');
        $r->setUserResolver(fn () => $u);
        $resp = $c($r);
        $name = method_exists($resp, 'getName') ? $resp->getName() : get_class($resp);
        $status = method_exists($resp, 'getStatusCode') ? $resp->getStatusCode() : 200;
        echo "$role id={$u->id} => $name status=$status OK\n";
    } catch (Throwable $e) {
        echo "$role FAILED ".get_class($e).': '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine()."\n";
    }
}
echo 'view composer test: ';
try {
    $u = User::first();
    auth()->setUser($u);
    $v = view('errors::500', ['exception' => new Exception('test')])->render();
    echo '500 view renders '.strlen($v)." bytes OK\n";
} catch (Throwable $e) {
    echo '500 view FAIL '.$e->getMessage()."\n";
}
