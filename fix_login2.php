<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$now = now()->toDateTimeString();
foreach (['superadmin@example.com', 'admin@example.com', 'owner@example.com', 'tenant@example.com'] as $email) {
    $u = User::where('email', $email)->first();
    if (! $u) {
        echo "MISSING $email\n";

        continue;
    }
    DB::table('users')->where('id', $u->id)->update([
        'email_verified_at' => $now,
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);
    echo "DB-FIXED $email\n";
}
foreach (User::all(['email', 'email_verified_at', 'is_active']) as $u) {
    echo $u->email.' verified='.($u->email_verified_at ? 'yes' : 'NO').' active='.$u->is_active."\n";
}
$o = User::where('email', 'owner@example.com')->first();
echo 'pwd_check='.(Hash::check('password', $o->password) ? 'yes' : 'NO')."\n";
