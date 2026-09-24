<?php

$dir = 'C:/Users/ASUS/KosManager/storage/app/public/advertising';

$campaigns = [
    [
        'file' => 'shopee-promo.svg',
        'brand' => 'Shopee',
        'primary' => '#EE282B',
        'accent' => '#FF6D00',
        'light1' => '#FFF3E0',
        'light2' => '#FFE0B2',
        'text' => '#212121',
        'muted' => '#757575',
        'headline' => 'Nikmati Promo Shopee',
        'cta' => 'LIHAT PROMO',
        'shapeColor' => '#FF6D00',
    ],
    [
        'file' => 'dana-promo.svg',
        'brand' => 'DANA',
        'primary' => '#2962FF',
        'accent' => '#004BEB',
        'light1' => '#E8EAF6',
        'light2' => '#C5CAE9',
        'text' => '#212121',
        'muted' => '#616161',
        'headline' => 'Praktis dengan DANA',
        'cta' => 'LIHAT PROMO',
        'shapeColor' => '#2962FF',
    ],
    [
        'file' => 'gopay-promo.svg',
        'brand' => 'GoPay',
        'primary' => '#00C853',
        'accent' => '#009624',
        'light1' => '#E8F5E9',
        'light2' => '#C8E6C9',
        'text' => '#212121',
        'muted' => '#616161',
        'headline' => 'Mudah Bayar dengan GoPay',
        'cta' => 'LIHAT PROMO',
        'shapeColor' => '#00C853',
    ],
];

foreach ($campaigns as $c) {
    $path = "$dir/{$c['file']}";
    $svg = createBanner($c);
    file_put_contents($path, $svg);
    echo "Created: {$c['file']} (" . strlen($svg) . " bytes)\n";
}

function createBanner($c) {
    $brand = $c['brand'];
    $primary = $c['primary'];
    $accent = $c['accent'];
    $light1 = $c['light1'];
    $light2 = $c['light2'];
    $text = $c['text'];
    $muted = $c['muted'];
    $headline = $c['headline'];
    $cta = $c['cta'];
    $shapeColor = $c['shapeColor'];

    return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="450" viewBox="0 0 800 450">
  <defs>
    <linearGradient id="bgGrad-{$brand}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="60%" stop-color="#FAFAFA"/>
      <stop offset="100%" stop-color="{$light1}"/>
    </linearGradient>
    <linearGradient id="shapeGrad-{$brand}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$shapeColor}" stop-opacity="0.12"/>
      <stop offset="100%" stop-color="{$shapeColor}" stop-opacity="0.04"/>
    </linearGradient>
    <linearGradient id="accentGrad-{$brand}" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="{$primary}" stop-opacity="0.9"/>
      <stop offset="100%" stop-color="{$primary}" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="ctaGrad-{$brand}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$primary}"/>
      <stop offset="100%" stop-color="{$accent}"/>
    </linearGradient>
    <filter id="shadow-{$brand}" x="-5%" y="-5%" width="110%" height="110%">
      <feDropShadow dx="0" dy="2" stdDeviation="4" flood-color="{$primary}" flood-opacity="0.15"/>
    </filter>
    <filter id="softShadow-{$brand}" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="4" stdDeviation="8" flood-color="#000" flood-opacity="0.08"/>
    </filter>
    <clipPath id="rounded-{$brand}">
      <rect x="0" y="0" width="800" height="450" rx="16"/>
    </clipPath>
  </defs>

  <g clip-path="url(#rounded-{$brand})">
    <rect width="800" height="450" fill="url(#bgGrad-{$brand})"/>

    <g opacity="0.06">
      <circle cx="650" cy="80" r="120" fill="{$shapeColor}"/>
      <circle cx="720" cy="350" r="80" fill="{$shapeColor}"/>
      <circle cx="120" cy="400" r="60" fill="{$shapeColor}"/>
    </g>

    <rect x="-50" y="0" width="200" height="450" fill="url(#shapeGrad-{$brand})" opacity="0.5"/>

    <g transform="translate(60, 50)">
      <circle cx="40" cy="40" r="35" fill="url(#shapeGrad-{$brand})" filter="url(#softShadow-{$brand})"/>
      <circle cx="40" cy="40" r="20" fill="{$primary}" opacity="0.2"/>
      <rect x="28" y="30" width="24" height="20" rx="4" fill="{$primary}" opacity="0.9"/>
      <rect x="32" y="34" width="16" height="12" rx="2" fill="#FFFFFF" opacity="0.8"/>
    </g>

    <g transform="translate(680, 370) rotate(-15)">
      <rect width="100" height="50" rx="10" fill="url(#shapeGrad-{$brand})" filter="url(#softShadow-{$brand})"/>
    </g>

    <g opacity="0.08">
      <rect x="50" y="150" width="700" height="1" fill="{$shapeColor}"/>
      <rect x="50" y="300" width="700" height="1" fill="{$shapeColor}"/>
    </g>

    <rect x="0" y="370" width="800" height="80" fill="#FFFFFF" opacity="0.9"/>
    <rect x="0" y="370" width="800" height="2" fill="{$light2}"/>

    <g transform="translate(60, 70)">
      <text x="0" y="0" font-family="'Segoe UI', 'Helvetica Neue', Arial, sans-serif" font-size="52" font-weight="800" fill="{$primary}">{$brand}</text>
      <rect x="0" y="18" width="140" height="4" rx="2" fill="{$primary}" opacity="0.3"/>
    </g>

    <g transform="translate(60, 220)">
      <text x="0" y="0" font-family="'Segoe UI', 'Helvetica Neue', Arial, sans-serif" font-size="36" font-weight="700" fill="{$text}">{$headline}</text>
      <text x="0" y="28" font-family="'Segoe UI', 'Helvetica Neue', Arial, sans-serif" font-size="16" fill="{$muted}">Nikmati kemudahan pembayaran dan promo terbaik hari ini</text>
    </g>

    <g transform="translate(60, 335)">
      <rect x="0" y="0" width="180" height="42" rx="21" fill="url(#ctaGrad-{$brand})" filter="url(#shadow-{$brand})"/>
      <text x="48" y="28" font-family="'Segoe UI', 'Helvetica Neue', Arial, sans-serif" font-size="14" font-weight="700" fill="#FFFFFF">{$cta}</text>
      <line x1="170" y1="21" x2="170" y2="29" stroke="#FFFFFF" stroke-width="1.5" opacity="0.6"/>
    </g>

    <g transform="translate(680, 345)">
      <circle cx="12" cy="12" r="8" fill="{$primary}" opacity="0.15"/>
      <circle cx="12" cy="12" r="4" fill="{$primary}" opacity="0.3"/>
    </g>

    <g opacity="0.15">
      <circle cx="720" cy="60" r="6" fill="{$primary}"/>
      <circle cx="740" cy="45" r="4" fill="{$primary}"/>
      <circle cx="700" cy="40" r="3" fill="{$primary}"/>
    </g>
  </g>
</svg>
SVG;
}

echo "\nAll 3 advertising banners created successfully.\n";
echo "Format: SVG 800x450 (16:9)\n";
echo "Location: {$dir}/\n";
