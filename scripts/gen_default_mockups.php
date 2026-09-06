<?php

/**
 * Generate default mockup SVG frames (4 kategori × 3 sudut).
 * Run: php scripts/gen_default_mockups.php
 */

$dir = dirname(__DIR__) . '/public/assets/mockup';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

function svgDepan(string $title): string
{
    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#EEF2F7"/>
      <stop offset="100%" stop-color="#D8E0EC"/>
    </linearGradient>
    <filter id="sh" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="12" stdDeviation="18" flood-color="#051747" flood-opacity="0.18"/>
    </filter>
  </defs>
  <rect width="800" height="600" fill="url(#bg)"/>
  <rect x="180" y="70" width="440" height="440" rx="18" fill="#051747" filter="url(#sh)"/>
  <rect x="210" y="100" width="380" height="360" rx="8" fill="#F8FAFC"/>
  <text x="400" y="560" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" fill="#64748B">{$title} · Depan</text>
</svg>
SVG;
}

function svgSamping(string $title): string
{
    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#EEF2F7"/>
      <stop offset="100%" stop-color="#D8E0EC"/>
    </linearGradient>
    <filter id="sh" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="12" stdDeviation="18" flood-color="#051747" flood-opacity="0.18"/>
    </filter>
  </defs>
  <rect width="800" height="600" fill="url(#bg)"/>
  <polygon points="520,90 620,130 620,490 520,530" fill="#0a2860" filter="url(#sh)"/>
  <rect x="260" y="90" width="260" height="440" rx="10" fill="#051747"/>
  <rect x="285" y="120" width="210" height="360" rx="6" fill="#F8FAFC"/>
  <text x="400" y="570" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" fill="#64748B">{$title} · Samping</text>
</svg>
SVG;
}

function svgAtas(string $title): string
{
    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#EEF2F7"/>
      <stop offset="100%" stop-color="#D8E0EC"/>
    </linearGradient>
    <filter id="sh" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="10" stdDeviation="14" flood-color="#051747" flood-opacity="0.16"/>
    </filter>
  </defs>
  <rect width="800" height="600" fill="url(#bg)"/>
  <ellipse cx="400" cy="480" rx="220" ry="28" fill="#B8C4D6" opacity="0.55"/>
  <g transform="rotate(-8 400 290)">
    <rect x="200" y="140" width="400" height="300" rx="16" fill="#051747" filter="url(#sh)"/>
    <rect x="230" y="170" width="340" height="240" rx="8" fill="#F8FAFC"/>
  </g>
  <text x="400" y="560" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" fill="#64748B">{$title} · Atas</text>
</svg>
SVG;
}

$cats = [
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
];

foreach ($cats as $key => $label) {
    file_put_contents("{$dir}/{$key}_depan.svg", svgDepan($label));
    file_put_contents("{$dir}/{$key}_samping.svg", svgSamping($label));
    file_put_contents("{$dir}/{$key}_atas.svg", svgAtas($label));
}

echo 'OK: ' . count(glob($dir . '/*.svg')) . " files in {$dir}\n";
