<?php
echo "=== STORAGE AUDIT ===\n";
echo "public/storage path: " . realpath('C:/Users/ASUS/KosManager/public/storage') . "\n";
echo "is_link: " . (is_link('C:/Users/ASUS/KosManager/public/storage') ? 'YES' : 'NO') . "\n";
echo "is_dir: " . (is_dir('C:/Users/ASUS/KosManager/public/storage') ? 'YES' : 'NO') . "\n";
echo "is_file: " . (is_file('C:/Users/ASUS/KosManager/public/storage') ? 'YES' : 'NO') . "\n";
echo "file_exists: " . (file_exists('C:/Users/ASUS/KosManager/public/storage') ? 'YES' : 'NO') . "\n";
echo "is_readable: " . (is_readable('C:/Users/ASUS/KosManager/public/storage') ? 'YES' : 'NO') . "\n";

$storagePath = 'C:/Users/ASUS/KosManager/storage/app/public';
echo "\nstorage/app/public: " . (is_dir($storagePath) ? 'EXISTS' : 'NOT_FOUND') . "\n";
echo "advertising dir: " . (is_dir($storagePath . '/advertising') ? 'EXISTS' : 'NOT_FOUND') . "\n";

$files = @scandir('C:/Users/ASUS/KosManager/public/storage');
if ($files) {
    echo "public/storage contents: ";
    foreach (array_slice($files, 2) as $f) {
        echo $f . ", ";
    }
    echo "\n";
} else {
    echo "public/storage: cannot scandir\n";
}

$allFiles = glob('C:/Users/ASUS/KosManager/storage/app/public/*', GLOB_ONLYDIR);
echo "\nstorage/app/public subdirs: " . count($allFiles) . "\n";
foreach ($allFiles as $d) {
    echo "  - " . basename($d) . "\n";
}
