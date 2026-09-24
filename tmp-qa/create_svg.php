<?php

$dir = 'C:/Users/ASUS/KosManager/storage/app/public/advertising';

$campaigns = [
    [
        'file' => 'shopee-promo.svg',
        'brand' => 'Shopee',
        'color' => '#FF6D00',
        'subtitle' => 'Beli kebutuhan kos lebih hemat di Shopee',
        'text' => 'Hemat Sampai 50%'
    ],
    [
        'file' => 'dana-promo.svg',
        'brand' => 'DANA',
        'color' => '#7B1FA2',
        'subtitle' => 'Bayar tagihan kos langsung dari dompet digital DANA',
        'text' => 'Bayar Mudah & Aman'
    ],
    [
        'file' => 'gopay-promo.svg',
        'brand' => 'GoPay',
        'color' => '#00897B',
        'subtitle' => 'Isi saldo dan bayar semua kebutuhan di GoPay',
        'text' => 'Semua di GoPay'
    ]
];

foreach ($campaigns as $c) {
    $path = "$dir/{$c['file']}";
    $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450" viewBox="0 0 800 450">
  <defs>
    <linearGradient id="bg-{$c['brand']}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#FFFFFF;stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$c['color']};stop-opacity:0.08" />
    </linearGradient>
    <linearGradient id="overlay-{$c['brand']}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" style="stop-color:#000000;stop-opacity:0" />
      <stop offset="60%" style="stop-color:#000000;stop-opacity:0.1" />
      <stop offset="100%" style="stop-color:#000000;stop-opacity:0.5" />
    </linearGradient>
  </defs>
  <rect width="800" height="450" fill="url(#bg-{$c['brand']})" rx="12"/>
  <rect x="0" y="0" width="4" height="450" fill="{$c['color']}" rx="2"/>
  <text x="50" y="120" font-family="Arial,sans-serif" font-size="48" font-weight="bold" fill="{$c['color']}">{$c['brand']}</text>
  <text x="50" y="200" font-family="Arial,sans-serif" font-size="22" fill="#333333">{$c['text']}</text>
  <text x="50" y="250" font-family="Arial,sans-serif" font-size="16" fill="#666666">{$c['subtitle']}</text>
  <rect x="50" y="310" width="180" height="40" rx="20" fill="{$c['color']}"/>
  <text x="68" y="335" font-family="Arial,sans-serif" font-size="14" font-weight="bold" fill="white">LIHAT PROMO</text>
  <circle cx="730" cy="80" r="25" fill="{$c['color']}" opacity="0.15"/>
  <circle cx="700" cy="380" r="40" fill="{$c['color']}" opacity="0.1"/>
</svg>
SVG;
    file_put_contents($path, $svg);
    echo "Created: $path (" . strlen($svg) . " bytes)\n";
}

echo "\nAll advertising images created.\n";
