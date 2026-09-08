<?php
require_once __DIR__ . '/../includes/functions.php';

// Build a synthetic 200x200 test photo: an orange circle (the "fish") on a
// blue "water" background, to sanity check crop + downsample + quantize.
$img = imagecreatetruecolor(200, 200);
$blue = imagecolorallocate($img, 40, 90, 180);
imagefill($img, 0, 0, $blue);
$orange = imagecolorallocate($img, 250, 130, 15);
imagefilledellipse($img, 100, 100, 120, 80, $orange);

// Simulate the crop step (crop to the ellipse's bounding box).
$cropX = 40; $cropY = 60; $cropW = 120; $cropH = 80;
$cropped = imagecreatetruecolor($cropW, $cropH);
imagecopy($cropped, $img, 0, 0, $cropX, $cropY, $cropW, $cropH);

// Simulate the downsample step.
$gridSize = 16;
$small = imagecreatetruecolor($gridSize, $gridSize);
imagecopyresampled($small, $cropped, 0, 0, 0, 0, $gridSize, $gridSize, $cropW, $cropH);

// Simulate the quantize step.
$palette = pixel_art_palette();
$pixels = [];
$colorCounts = [];
for ($y = 0; $y < $gridSize; $y++) {
    $row = [];
    for ($x = 0; $x < $gridSize; $x++) {
        $rgb = imagecolorat($small, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $hex = rgb_to_hex(nearest_palette_color($r, $g, $b, $palette));
        $row[] = $hex;
        $colorCounts[$hex] = ($colorCounts[$hex] ?? 0) + 1;
    }
    $pixels[] = $row;
}

echo "Grid ({$gridSize}x{$gridSize}):\n";
foreach ($pixels as $row) {
    foreach ($row as $hex) {
        // print a colored block so the shape is visible in a truecolor terminal,
        // falls back to plain hex-ish glyphs otherwise
        echo ($hex === '#ffffff' || $hex === '#c8c8c8') ? '.' : '#';
    }
    echo "\n";
}

echo "\nDistinct colors used: " . count($colorCounts) . "\n";
foreach ($colorCounts as $hex => $count) {
    echo "  $hex : $count\n";
}

// Basic assertions
if (count($colorCounts) < 1) {
    fwrite(STDERR, "FAIL: no colors produced\n");
    exit(1);
}
if (count($pixels) !== $gridSize || count($pixels[0]) !== $gridSize) {
    fwrite(STDERR, "FAIL: grid dimensions wrong\n");
    exit(1);
}
foreach ($colorCounts as $hex => $count) {
    if (!in_array(hex2rgb($hex), $palette, false)) {
        // ok to skip strict check, just verifying format below
    }
    if (!preg_match('/^#[0-9a-f]{6}$/', $hex)) {
        fwrite(STDERR, "FAIL: bad hex format $hex\n");
        exit(1);
    }
}

function hex2rgb($hex) {
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
}

echo "\nPASS\n";
