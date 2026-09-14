<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Graphs</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>Graphs</h2>

            <div class="input-component">
                <label for="graph_tank_id">Tank:</label>
                <select id="graph_tank_id">
                    <option value="">Loading tanks...</option>
                </select>
            </div>

            <section>
                <h3>Water Parameter Trends</h3>

                <div class="input-component">
                    <label for="graph_parameter">Parameter:</label>
                    <select id="graph_parameter">
                        <option value="">No data yet</option>
                    </select>
                </div>

                <div class="chart-container">
                    <canvas id="parameter-chart"></canvas>
                </div>
                <p id="parameter-chart-empty" class="chart-empty" hidden>No water test readings for this tank yet.</p>
            </section>

            <section>
                <h3>Livestock Count Over Time</h3>

                <div class="chart-container">
                    <canvas id="livestock-chart"></canvas>
                </div>
                <p id="livestock-chart-empty" class="chart-empty" hidden>No organisms in this tank yet.</p>
            </section>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script src="js/graphs.js"></script>
    </body>
</html>
