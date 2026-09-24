<?php
echo "=== GIT STATUS ===\n";
echo shell_exec('git status --short 2>&1') . "\n";
echo "\n=== GIT DIFF STAT ===\n";
echo shell_exec('git diff --stat 2>&1') . "\n";
echo "\n=== GIT DIFF WELCOME ===\n";
echo shell_exec('git diff --stat -- resources/views/welcome.blade.php 2>&1') . "\n";
echo "\n=== GIT DIFF CONTROLLERS ===\n";
echo shell_exec('git diff --stat -- app/Http/Controllers/WelcomeController.php routes/web.php app/Http/Controllers/AdvertisingClickController.php 2>&1') . "\n";
echo "\n=== GIT DIFF CSS ===\n";
echo shell_exec('git diff --stat -- resources/css/app.css 2>&1') . "\n";
echo "\n=== STORAGE FILES ===\n";
echo shell_exec('dir /s /b "C:/Users/ASUS/KosManager/storage/app/public/advertising/" 2>&1') . "\n";
