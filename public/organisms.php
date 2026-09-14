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
                <h3>Add an Organism</h3>
                <p id="organism-form-error" class="error-message" hidden></p>

                <form id="organism-form">
                    <fieldset>
                        <legend>Basics</legend>
                        <div class="input-component">
                            <label for="tank_id">Tank:</label>
                            <select id="tank_id" required>
                                <option value="">Loading tanks...</option>
                            </select>
                        </div>
                        <div class="input-component">
                            <label for="custom_name">Organism Name:</label>
                            <input type="text" id="custom_name" required>
                        </div>
                        <div class="input-component">
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
                        </div>
                        <div class="input-component">
                            <label for="current_size_inches">Current Size (inches):</label>
                            <input type="number" step="0.01" id="current_size_inches">
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Species</legend>
                        <div class="input-component">
                            <label for="species_search">Search Species:</label>
                            <input type="text" id="species_search" placeholder="e.g. neon tetra" autocomplete="off">
                        </div>
                        <div id="species-suggestions" class="species-suggestions" hidden></div>

                        <div class="input-component">
                            <label for="scientific_name">Scientific Name:</label>
                            <input type="text" id="scientific_name" required>
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

                    <fieldset>
                        <legend>Sprite</legend>

                        <div class="input-component">
                            <label><input type="radio" name="sprite_mode" value="upload" checked> Upload a photo</label>
                            <label><input type="radio" name="sprite_mode" value="bank"> Choose from my sprites</label>
                        </div>

                        <div id="sprite-upload-fields">
                            <div class="input-component">
                                <label for="sprite_name">Sprite Name:</label>
                                <input type="text" id="sprite_name">
                            </div>
                            <div class="input-component">
                                <label for="sprite_photo">Photo:</label>
                                <input type="file" id="sprite_photo" accept="image/*">
                            </div>
                        </div>

                        <div id="sprite-bank-fields" hidden>
                            <div id="sprite-bank" class="sprite-bank">
                                <p>Loading your sprites...</p>
                            </div>
                        </div>
                    </fieldset>

                    <div class="action-component">
                        <button type="submit" class="btn-secondary">Add Organism</button>
                    </div>
                </form>
            </section>
        </main>

        <script src="js/organisms.js"></script>
    </body>
</html>
