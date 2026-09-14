<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Water Tests</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>Water Tests</h2>

            <section id="water-test-list" class="tank-list">
                <p>Loading readings...</p>
            </section>

            <section class="tank-form-section">
                <h3 id="water-test-form-title">Log a Reading</h3>
                <p id="water-test-form-error" class="error-message" hidden></p>

                <form id="water-test-form">
                    <input type="hidden" id="water-test-id" value="">

                    <div class="input-component" id="tank-field">
                        <label for="tank_id">Tank:</label>
                        <select id="tank_id">
                            <option value="">Loading tanks...</option>
                        </select>
                    </div>

                    <div class="input-component">
                        <label for="tested_at">Date &amp; Time:</label>
                        <input type="datetime-local" id="tested_at">
                    </div>

                    <div class="input-component">
                        <label for="parameter_preset">Parameter:</label>
                        <select id="parameter_preset">
                            <option value="">Select a tank first</option>
                        </select>
                    </div>

                    <div class="input-component" id="custom-parameter-field" hidden>
                        <label for="parameter_name">Custom Parameter Name:</label>
                        <input type="text" id="parameter_name">
                    </div>

                    <div class="equipment-row">
                        <div class="input-component">
                            <label for="value">Value:</label>
                            <input type="number" step="0.0001" id="value">
                        </div>
                        <div class="input-component">
                            <label for="unit">Unit:</label>
                            <input type="text" id="unit">
                        </div>
                    </div>

                    <div class="input-component">
                        <label for="water_test_notes">Notes:</label>
                        <textarea id="water_test_notes" rows="3"></textarea>
                    </div>

                    <div class="action-component">
                        <button type="submit" id="water-test-form-submit" class="btn-secondary">Log Reading</button>
                        <button type="button" id="water-test-form-cancel" class="btn-secondary" hidden>Cancel</button>
                    </div>
                </form>
            </section>
        </main>

        <script src="js/water_tests.js"></script>
    </body>
</html>
