<?php
/**
 * Generate Placeholder Crop Images as SVG
 * No PHP extensions required — creates clean SVG placeholders
 */

$baseDir = __DIR__ . '/../uploads/crops/';
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0755, true);
}

$crops = [
    'rice'       => ['color' => '#C8A96E', 'label' => 'Rice',       'icon' => '🌾'],
    'sugarcane'  => ['color' => '#6BBF59', 'label' => 'Sugarcane',   'icon' => '🎋'],
    'groundnut'  => ['color' => '#B8860B', 'label' => 'Groundnut',   'icon' => '🥜'],
    'banana'     => ['color' => '#F4D03F', 'label' => 'Banana',      'icon' => '🍌'],
    'tomato'     => ['color' => '#E74C3C', 'label' => 'Tomato',      'icon' => '🍅'],
    'onion'      => ['color' => '#9B59B6', 'label' => 'Onion',       'icon' => '🧅'],
    'turmeric'   => ['color' => '#F39C12', 'label' => 'Turmeric',    'icon' => '🌿'],
    'cotton'     => ['color' => '#ECF0F1', 'label' => 'Cotton',      'icon' => '☁️'],
    'chilli'     => ['color' => '#C0392B', 'label' => 'Chilli',      'icon' => '🌶️'],
    'brinjal'    => ['color' => '#6C3483', 'label' => 'Brinjal',     'icon' => '🍆'],
    'maize'      => ['color' => '#F1C40F', 'label' => 'Maize',       'icon' => '🌽'],
    'ragi'       => ['color' => '#8B5A2B', 'label' => 'Ragi',        'icon' => '🌾'],
    'ginger'     => ['color' => '#DAA520', 'label' => 'Ginger',      'icon' => '🫚'],
    'millets'    => ['color' => '#9E8B6E', 'label' => 'Millets',     'icon' => '🌾'],
    'wheat'      => ['color' => '#DEB887', 'label' => 'Wheat',       'icon' => '🌾'],
    'drumstick'  => ['color' => '#2E7D32', 'label' => 'Drumstick',   'icon' => '🥬'],
    'carrot'     => ['color' => '#E67E22', 'label' => 'Carrot',      'icon' => '🥕'],
    'potato'     => ['color' => '#C4A35A', 'label' => 'Potato',      'icon' => '🥔'],
    'cabbage'    => ['color' => '#27AE60', 'label' => 'Cabbage',     'icon' => '🥬'],
    'coriander'  => ['color' => '#1B8C2F', 'label' => 'Coriander',   'icon' => '🌿'],
];

$count = 0;
foreach ($crops as $filename => $info) {
    $filepath = $baseDir . $filename . '.svg';

    // Determine text color based on background brightness
    $hex = ltrim($info['color'], '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
    $textColor = $brightness > 128 ? '#1B5E20' : '#FFFFFF';

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$info['color']};stop-opacity:0.9"/>
      <stop offset="100%" style="stop-color:{$info['color']};stop-opacity:0.6"/>
    </linearGradient>
  </defs>
  <rect width="400" height="300" rx="12" fill="url(#bg)"/>
  <text x="200" y="130" text-anchor="middle" font-size="64">{$info['icon']}</text>
  <text x="200" y="190" text-anchor="middle" font-family="Arial,sans-serif" font-size="24" font-weight="bold" fill="{$textColor}">{$info['label']}</text>
  <text x="200" y="270" text-anchor="middle" font-family="Arial,sans-serif" font-size="11" fill="{$textColor}" opacity="0.7">Agriculture Portal</text>
</svg>
SVG;

    file_put_contents($filepath, $svg);
    $count++;
    echo "  Created: uploads/crops/{$filename}.svg\n";
}

echo "\nDone! {$count} crop images generated.\n";
