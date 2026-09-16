<?php

const SPRITE_RENDER_CELL_PX = 8;

// Renders a pixel-art grid (rows of "#rrggbb" strings, as saved by pixel_art.php)
// into a flat-colored PNG, one square per cell -- mirrors what renderSpriteThumbnail()
// draws client-side, just rasterized for storage/display outside the app itself.
function renderSpriteToPng(array $pixelData, int $gridSize): string
{
    $sizePx = $gridSize * SPRITE_RENDER_CELL_PX;
    $image = imagecreatetruecolor($sizePx, $sizePx);

    foreach ($pixelData as $rowIndex => $row) {
        foreach ($row as $colIndex => $hex) {
            $hex = ltrim($hex, '#');
            $color = imagecolorallocate(
                $image,
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            );

            imagefilledrectangle(
                $image,
                $colIndex * SPRITE_RENDER_CELL_PX,
                $rowIndex * SPRITE_RENDER_CELL_PX,
                ($colIndex + 1) * SPRITE_RENDER_CELL_PX - 1,
                ($rowIndex + 1) * SPRITE_RENDER_CELL_PX - 1,
                $color
            );
        }
    }

    ob_start();
    imagepng($image);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return $bytes;
}
