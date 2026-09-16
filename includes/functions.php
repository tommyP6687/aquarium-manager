<?php
/**
 * functions.php
 * Shared helper functions. Currently holds the pixel art palette definition,
 * used by the hand-drawn pixel art editor to render its color swatches;
 * general app helpers (formatting, validation, etc.) can be added here as
 * they come up.
 */

/**
 * A small fixed palette for pixel art sprites, offered as one-click swatches
 * in the hand-drawn editor. Chosen to cover common fish/plant colors
 * (oranges, reds, blues, greens) plus neutrals for shading and highlights.
 * Users aren't limited to it -- the editor also offers a native color
 * picker for exact custom colors.
 *
 * @return array<int, array{0:int,1:int,2:int}> RGB triples
 */
function pixel_art_palette(): array
{
    return [
        [0, 0, 0],        // black
        [255, 255, 255],  // white
        [40, 40, 40],     // dark gray
        [120, 120, 120],  // mid gray
        [200, 200, 200],  // light gray
        [230, 60, 40],    // red
        [255, 140, 20],   // orange
        [255, 210, 40],   // yellow
        [180, 220, 40],   // yellow-green
        [60, 180, 75],    // green
        [30, 120, 90],    // dark green
        [40, 180, 200],   // teal / cyan
        [30, 100, 200],   // blue
        [20, 50, 150],    // dark blue
        [140, 80, 220],   // purple
        [220, 80, 180],   // magenta / pink
        [255, 150, 180],  // light pink
        [150, 90, 40],    // brown
        [210, 160, 100],  // tan
        [255, 225, 190],  // pale / skin
        [90, 60, 30],     // dark brown
        [10, 10, 30],     // near-black blue (shadow)
        [245, 245, 255],  // near-white (highlight)
        [255, 190, 0],    // gold
    ];
}

function rgb_to_hex(array $rgb): string
{
    return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function redirect($location)
{
    header('Location: ' . $location);
    exit;
}

function send_json(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}