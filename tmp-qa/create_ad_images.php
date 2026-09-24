<?php

$dir = 'C:/Users/ASUS/KosManager/storage/app/public/advertising';

$campaigns = [
    ['shopee-promo.webp', 'Shopee', '#FF6D00', '#FFFFFF', 'Beli kebutuhan kos lebih hemat di Shopee'],
    ['dana-promo.webp', 'DANA', '#7B1FA2', '#FFFFFF', 'Bayar tagihan kos langsung dari dompet digital DANA'],
    ['gopay-promo.webp', 'GoPay', '#00897B', '#FFFFFF', 'Isi saldo dan bayar semua kebutuhan di GoPay'],
];

foreach ($campaigns as $item) {
    list($filename, $brand, $primaryColor, $textColor, $subtitle) = $item;
    $path = "$dir/$filename";
    $img = imagecreatetruecolor(800, 450);

    $bgColor = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bgColor);

    $primaryRGB = hexToArray($primaryColor);
    $primary = imagecolorallocate($img, $primaryRGB[0], $primaryRGB[1], $primaryRGB[2]);
    $textRGB = hexToArray($textColor);
    $textColorAlloc = imagecolorallocate($img, $textRGB[0], $textRGB[1], $textRGB[2]);

    $gradient = imagecreatetruecolor(800, 450);
    imagefilter($gradient, IMG_FILTER_COLORIZE, [
        'red' => (int)($primaryRGB[0] * 0.9),
        'green' => (int)($primaryRGB[1] * 0.85),
        'blue' => (int)($primaryRGB[2] * 0.8),
        'opacity' => 70
    ]);
    imagecopymerge($img, $gradient, 0, 0, 0, 0, 800, 450, 40);

    $white = imagecolorallocate($img, 255, 255, 255);
    $whiteSemi = imagecolorallocatealpha($img, 255, 255, 255, 40);
    imagefilledrectangle($img, 0, 300, 800, 450, $whiteSemi);

    imagettftext($img, 28, 0, 50, 150, $textColorAlloc, 'C:/Windows/Fonts/arial.ttf', $brand);
    imagettftext($img, 16, 0, 50, 340, $textColorAlloc, 'C:/Windows/Fonts/arial.ttf', $subtitle);

    $badgeColor = imagecolorallocate($img, 255, 109, 0);
    $badgeText = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 50, 370, 200, 400, $badgeColor);
    imagettftext($img, 12, 0, 60, 388, $badgeText, 'C:/Windows/Fonts/arial.ttf', 'PROMO');

    imagefilledrectangle($img, 650, 370, 750, 400, $textColorAlloc);
    imagettftext($img, 10, 0, 660, 388, $white, 'C:/Windows/Fonts/arial.ttf', 'LIHAT');

    imagepng($img, str_replace('.webp', '.png', $path));
    imagedestroy($img);
    echo "Created: $path.png\n";
}

function hexToArray($hex) {
    $hex = ltrim($hex, '#');
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

echo "All images created successfully.\n";
