<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$emails = [
    'superadmin@example.com' => ['Super Admin', 'super_admin'],
    'admin@example.com' => ['Admin', 'admin'],
    'owner@example.com' => ['Owner', 'owner'],
    'tenant@example.com' => ['Tenant', 'tenant'],
];

foreach ($emails as $email => [$name, $role]) {
    $u = User::where('email', $email)->first();
    if (! $u) {
        $u = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
        ]);
        echo "CREATED $email\n";
    } else {
        $u->update([
            'email_verified_at' => $u->email_verified_at ?? now(),
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        echo "FIXED $email verified + password=password\n";
    }
}

echo 'done. users='.User::count()."\n";
