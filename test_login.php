<?php

use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Check users
$user = User::where('email', 'owner@example.com')->first();
if ($u = $user) {
    echo 'User found: active='.$u->is_active.', password_hash='.substr($u->password, 0, 30)."\n";
    echo "Password 'password' matches: ".(Hash::check('password', $u->password) ? 'yes' : 'no')."\n";
} else {
    echo "User not found\n";

    // List all users
    foreach (User::all() as $u) {
        echo 'User: '.$u->email.' active='.$u->is_active."\n";
    }
}
