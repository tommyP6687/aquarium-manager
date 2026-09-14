<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Tanks</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>My Tanks</h2>

            <section id="tank-list" class="tank-list">
                <p>Loading tanks...</p>
            </section>

            <section class="tank-form-section">
                <h3 id="tank-form-title">Add a Tank</h3>
                <p id="tank-form-error" class="error-message" hidden></p>

                <form id="tank-form">
                    <input type="hidden" id="tank-id" value="">

                    <fieldset>
                        <legend>Basics</legend>
                        <div class="input-component">
                            <label for="custom_name">Tank Name:</label>
                            <input type="text" id="custom_name" required>
                        </div>
                        <div class="input-component">
                            <label for="tank_type">Tank Type:</label>
                            <input type="text" id="tank_type" placeholder="e.g. planted, shrimp">
                        </div>
                        <div class="input-component">
                            <label for="salinity_type">Salinity:</label>
                            <input type="text" id="salinity_type" placeholder="freshwater, saltwater, brackish">
                        </div>
                        <div class="input-component">
                            <label for="tank_shape">Shape:</label>
                            <input type="text" id="tank_shape">
                        </div>
                        <div class="input-component">
                            <label for="volume_gallons">Volume (gallons):</label>
                            <input type="number" step="0.01" id="volume_gallons">
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Equipment</legend>

                        <div class="equipment-row">
                            <label><input type="checkbox" id="has_filter"> Filter</label>
                            <input type="text" id="filter_type" placeholder="Filter type">
                        </div>

                        <div class="equipment-row">
                            <label><input type="checkbox" id="has_co2"> CO2</label>
                            <input type="text" id="co2_type" placeholder="CO2 type">
                        </div>

                        <div class="equipment-row">
                            <label><input type="checkbox" id="has_fertilizer"> Fertilizer</label>
                            <input type="text" id="fertilizer_routine" placeholder="Fertilizer routine">
                        </div>

                        <div class="equipment-row">
                            <label><input type="checkbox" id="has_lighting"> Lighting</label>
                            <input type="text" id="lighting_type" placeholder="Lighting type">
                            <input type="text" id="lighting_schedule" placeholder="Schedule">
                            <input type="text" id="lighting_intensity" placeholder="Intensity">
                        </div>

                        <div class="equipment-row">
                            <label><input type="checkbox" id="has_substrate"> Substrate</label>
                            <input type="text" id="substrate_type" placeholder="Substrate type">
                        </div>

                        <div class="equipment-row">
                            <label><input type="checkbox" id="has_heater"> Heater</label>
                            <input type="text" id="heater_type" placeholder="Heater type">
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Cycling</legend>
                        <div class="input-component">
                            <label for="cycle_start_date">Cycle Start Date:</label>
                            <input type="date" id="cycle_start_date">
                        </div>
                        <div class="input-component">
                            <label><input type="checkbox" id="is_cycled"> Fully Cycled</label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Notes</legend>
                        <textarea id="notes" rows="3"></textarea>
                    </fieldset>

                    <div class="action-component">
                        <button type="submit" id="tank-form-submit" class="btn-secondary">Add Tank</button>
                        <button type="button" id="tank-form-cancel" class="btn-secondary" hidden>Cancel</button>
                    </div>
                </form>
            </section>
        </main>

        <script src="js/tanks.js"></script>
    </body>
</html>
