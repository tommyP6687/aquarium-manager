<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Species</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>Species Care Data</h2>
            <p>Fill in care details for your species to power compatibility warnings on the dashboard.</p>

            <section id="species-list" class="tank-list">
                <p>Loading species...</p>
            </section>

            <section class="tank-form-section" id="species-form-section" hidden>
                <h3 id="species-form-title">Edit Species</h3>
                <p id="species-form-error" class="error-message" hidden></p>

                <form id="species-form">
                    <input type="hidden" id="species-id" value="">

                    <fieldset>
                        <legend>Environment</legend>
                        <div class="equipment-row">
                            <div class="input-component">
                                <label for="min_temp_f">Min Temp (&deg;F):</label>
                                <input type="number" step="0.1" id="min_temp_f">
                            </div>
                            <div class="input-component">
                                <label for="max_temp_f">Max Temp (&deg;F):</label>
                                <input type="number" step="0.1" id="max_temp_f">
                            </div>
                        </div>
                        <div class="equipment-row">
                            <div class="input-component">
                                <label for="min_ph">Min pH:</label>
                                <input type="number" step="0.1" id="min_ph">
                            </div>
                            <div class="input-component">
                                <label for="max_ph">Max pH:</label>
                                <input type="number" step="0.1" id="max_ph">
                            </div>
                        </div>
                        <div class="input-component">
                            <label for="min_tank_size_gallons">Minimum Tank Size (gallons):</label>
                            <input type="number" step="0.1" id="min_tank_size_gallons">
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Care</legend>
                        <div class="input-component">
                            <label for="adult_size_inches">Adult Size (inches):</label>
                            <input type="number" step="0.01" id="adult_size_inches">
                        </div>
                        <div class="input-component">
                            <label for="temperament">Temperament:</label>
                            <select id="temperament">
                                <option value="">Unspecified</option>
                                <option>Peaceful</option>
                                <option>Semi-aggressive</option>
                                <option>Aggressive</option>
                            </select>
                        </div>
                        <div class="input-component">
                            <label for="min_group_size">Minimum Group Size:</label>
                            <input type="number" step="1" min="1" id="min_group_size">
                        </div>
                        <div class="input-component">
                            <label for="care_level">Care Level:</label>
                            <select id="care_level">
                                <option value="">Unspecified</option>
                                <option>Beginner</option>
                                <option>Intermediate</option>
                                <option>Advanced</option>
                            </select>
                        </div>
                        <div class="input-component">
                            <label for="is_plant_safe">Plant-Safe:</label>
                            <select id="is_plant_safe">
                                <option value="">Unknown</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="input-component">
                            <label for="is_shrimp_safe">Shrimp-Safe:</label>
                            <select id="is_shrimp_safe">
                                <option value="">Unknown</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="input-component">
                            <label for="care_notes">Care Notes:</label>
                            <textarea id="care_notes" rows="3"></textarea>
                        </div>
                    </fieldset>

                    <div class="action-component">
                        <button type="submit" class="btn-secondary">Save</button>
                        <button type="button" id="species-form-cancel" class="btn-secondary">Cancel</button>
                    </div>
                </form>
            </section>
        </main>

        <script src="js/species.js"></script>
    </body>
</html>
