<?php
require_once __DIR__ . '/../includes/functions.php';

// The photo-to-sprite pipeline this test originally covered was removed --
// sprites are hand-drawn now (see public/pixel_art_editor.php). What's left
// to sanity check is the palette definition and its hex conversion, which
// the editor renders as swatches on every page load.

$palette = pixel_art_palette();

if (count($palette) === 0) {
    fwrite(STDERR, "FAIL: palette is empty\n");
    exit(1);
}

foreach ($palette as $index => $color) {
    if (!is_array($color) || count($color) !== 3) {
        fwrite(STDERR, "FAIL: palette entry $index is not an RGB triple\n");
        exit(1);
    }

    foreach ($color as $channel) {
        if (!is_int($channel) || $channel < 0 || $channel > 255) {
            fwrite(STDERR, "FAIL: palette entry $index has an out-of-range channel value\n");
            exit(1);
        }
    }
}

echo "Palette has " . count($palette) . " colors, all valid RGB triples.\n";

$hexCases = [
    [[0, 0, 0], '#000000'],
    [[255, 255, 255], '#ffffff'],
    [[230, 60, 40], '#e63c28'],
];

foreach ($hexCases as [$rgb, $expected]) {
    $actual = rgb_to_hex($rgb);
    if ($actual !== $expected) {
        fwrite(STDERR, "FAIL: rgb_to_hex(" . implode(',', $rgb) . ") = $actual, expected $expected\n");
        exit(1);
    }
}

echo "rgb_to_hex() produces correctly formatted hex strings.\n";

echo "\nPASS\n";
