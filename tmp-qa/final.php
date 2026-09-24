<?php
$output = shell_exec('git status --short 2>&1');
echo "=== GIT STATUS ===\n";
echo $output;
echo "\n=== FILES ===\n";
$lines = explode("\n", trim($output));
foreach ($lines as $line) {
    if (trim($line) !== '') echo $line . "\n";
}
echo "\n=== ADVERTISING ===\n";
$dir = 'C:/Users/ASUS/KosManager/storage/app/public/advertising';
$files = glob($dir . '/*');
foreach ($files as $f) {
    echo basename($f) . ' (' . filesize($f) . ' bytes)' . "\n";
}
echo "\n=== DATABASE ===\n";
echo shell_exec('php artisan tinker --execute="App\Models\AdvertisingCampaign::whereIn(\'id\',[8,11,13])->select(\'id\',\'image\',\'advertiser_name\')->get()->toJson();" 2>&1');
