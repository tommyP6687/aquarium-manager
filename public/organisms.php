<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Organisms</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>My Organisms</h2>

            <section id="organism-list" class="tank-list">
                <p>Loading organisms...</p>
            </section>

            <section class="tank-form-section">
                <h3 id="organism-form-title">Add an Organism</h3>
                <p id="organism-form-error" class="error-message" hidden></p>

                <form id="organism-form">
                    <input type="hidden" id="organism-id" value="">

                    <fieldset>
                        <legend>Basics</legend>
                        <div class="input-component" id="tank-field">
                            <label for="tank_id">Tank:</label>
                            <select id="tank_id">
                                <option value="">Loading tanks...</option>
                            </select>
                        </div>
                        <div class="input-component">
                            <label for="custom_name">Organism Name:</label>
                            <input type="text" id="custom_name" required>
                        </div>
                        <div class="input-component" id="date-added-field">
                            <label for="date_added">Date Added:</label>
                            <input type="date" id="date_added">
                        </div>
                        <div class="input-component">
                            <label for="health_status">Health Status:</label>
                            <select id="health_status">
                                <option>Healthy</option>
                                <option>Watching</option>
                                <option>Sick</option>
                                <option>Recovering</option>
                                <option>Deceased</option>
                                <option>Removed</option>
                                <option>Unknown</option>
                            </select>
                        </div>
                        <div class="input-component">
                            <label for="growth_stage">Growth Stage:</label>
                            <select id="growth_stage">
                                <option value="">Unspecified</option>
                                <option>Juvenile</option>
                                <option>Sub-adult</option>
                                <option>Adult</option>
                            </select>
                            <button type="button" id="use-growth-estimate" hidden>Use estimate</button>
                        </div>
                        <div class="input-component">
                            <label for="current_size_inches">Current Size (inches):</label>
                            <input type="number" step="0.01" id="current_size_inches">
                        </div>
                        <div class="input-component">
                            <label for="organism_notes">Notes:</label>
                            <textarea id="organism_notes" rows="3"></textarea>
                        </div>
                    </fieldset>

                    <fieldset id="species-fieldset">
                        <legend>Species</legend>
                        <div class="input-component">
                            <label for="species_search">Search Species:</label>
                            <input type="text" id="species_search" placeholder="e.g. neon tetra" autocomplete="off">
                        </div>
                        <div id="species-suggestions" class="species-suggestions" hidden></div>

                        <div class="input-component">
                            <label for="scientific_name">Scientific Name:</label>
                            <input type="text" id="scientific_name">
                        </div>
                        <div class="input-component">
                            <label for="common_name">Common Name:</label>
                            <input type="text" id="common_name">
                        </div>
                        <div class="input-component">
                            <label for="organism_type">Organism Type:</label>
                            <select id="organism_type">
                                <option value="">Unspecified</option>
                                <option>Fish</option>
                                <option>Invertebrate</option>
                                <option>Plant</option>
                                <option>Coral</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="input-component">
                            <label for="species_salinity_type">Salinity:</label>
                            <input type="text" id="species_salinity_type" placeholder="freshwater, saltwater, brackish">
                        </div>
                        <input type="hidden" id="external_taxon_id" value="">
                    </fieldset>

                    <fieldset id="sprite-fieldset">
                        <legend>Sprite</legend>

                        <p>Need a new sprite? <a href="pixel_art_editor.php" target="_blank">Open the Pixel Art Editor</a>, then come back here.</p>

                        <div class="action-component">
                            <button type="button" id="refresh-sprites-button" class="btn-secondary">Refresh My Sprites</button>
                        </div>

                        <div id="sprite-bank" class="sprite-bank">
                            <p>Loading your sprites...</p>
                        </div>
                    </fieldset>

                    <div class="action-component">
                        <button type="submit" id="organism-form-submit" class="btn-secondary">Add Organism</button>
                        <button type="button" id="organism-form-cancel" class="btn-secondary" hidden>Cancel</button>
                    </div>
                </form>
            </section>
        </main>

        <script src="js/common.js"></script>
        <script src="js/organisms.js"></script>
    </body>
</html>
