<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Virtual Tank</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2 id="virtual-tank-title">Virtual Tank</h2>
            <p><a href="tanks.php">&larr; Back to Tanks</a></p>

            <div id="virtual-tank-stage" class="virtual-tank-stage virtual-tank-rectangular">
                <p id="virtual-tank-empty" class="chart-empty" hidden>No organisms in this tank yet.</p>
            </div>
        </main>

        <script src="js/common.js"></script>
        <script src="js/virtual_tank.js"></script>
    </body>
</html>
