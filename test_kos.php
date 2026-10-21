<?php

use App\Models\Kos;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$kosCount = Kos::count();
echo 'Kos count: '.$kosCount."\n";

if ($kosCount > 0) {
    $kosList = Kos::all();
    $kosCountActual = 0;
    foreach ($kosList as $k) {
        echo ' - '.$k->name.' (owner_id: '.$k->owner_id.') - status: '.$k->status."\n";
        $kosCountActual++;
    }
    echo 'Total kos: '.$kosCountActual."\n";
}
