<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$palette = array_map('rgb_to_hex', pixel_art_palette());
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Pixel Art Editor</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>Pixel Art Editor</h2>

            <section id="grid-size-section" class="tank-form-section">
                <h3>Start a New Sprite</h3>
                <div class="input-component">
                    <label for="new_grid_size">Grid Size:</label>
                    <select id="new_grid_size">
                        <option value="16">16 x 16</option>
                        <option value="24" selected>24 x 24</option>
                        <option value="32">32 x 32</option>
                    </select>
                </div>
                <div class="action-component">
                    <button type="button" id="start-drawing-button" class="btn-secondary">Start Drawing</button>
                </div>
            </section>

            <section id="editor-section" class="tank-form-section" hidden>
                <h3>Draw Your Sprite</h3>
                <p id="editor-error" class="error-message" hidden></p>

                <div class="action-component">
                    <button type="button" id="tool-paint" class="btn-secondary editor-tool-active">Paint</button>
                    <button type="button" id="tool-erase" class="btn-secondary">Erase</button>
                    <button type="button" id="tool-fill" class="btn-secondary">Fill</button>
                </div>

                <div id="palette-swatches" class="palette-swatches"></div>

                <div class="input-component">
                    <label for="custom_color">Custom Color:</label>
                    <input type="color" id="custom_color" value="#230a2a">
                </div>

                <div id="editor-grid" class="editor-grid"></div>

                <div class="input-component">
                    <label for="editor_sprite_name">Sprite Name:</label>
                    <input type="text" id="editor_sprite_name" required>
                </div>

                <div class="action-component">
                    <button type="button" id="save-sprite-button" class="btn-secondary">Save Sprite</button>
                    <a href="organisms.php" class="btn-secondary">Back to Organisms</a>
                </div>
            </section>
        </main>

        <script>
            const PIXEL_ART_PALETTE = <?php echo json_encode($palette); ?>;
        </script>
        <script src="js/pixel_art_editor.js"></script>
    </body>
</html>
