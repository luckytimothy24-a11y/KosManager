<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

echo "== USERS ==\n";
foreach (User::all(['id', 'name', 'email', 'role', 'is_active', 'email_verified_at']) as $u) {
    echo $u->id.' | '.$u->email.' | '.$u->role.' | active='.$u->is_active.' | verified='.($u->email_verified_at ? 'yes' : 'NO')."\n";
}

echo "\n== PWD CHECK owner@example.com / password ==\n";
$o = User::where('email', 'owner@example.com')->first();
if ($o) {
    echo 'hash_ok='.(Hash::check('password', $o->password) ? 'yes' : 'NO')."\n";
    echo 'active='.$o->is_active."\n";
} else {
    echo "owner NOT FOUND\n";
}

echo "\n== ROUTES login ==\n";
foreach (Route::getRoutes() as $r) {
    if (str_contains($r->getName() ?? '', 'login')) {
        echo implode('|', $r->methods()).' '.$r->uri().' name='.$r->getName()."\n";
    }
}
