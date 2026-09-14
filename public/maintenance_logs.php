<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Maintenance</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>Maintenance Logs</h2>

            <section id="maintenance-log-list" class="tank-list">
                <p>Loading logs...</p>
            </section>

            <section class="tank-form-section">
                <h3 id="maintenance-log-form-title">Log a Task</h3>
                <p id="maintenance-log-form-error" class="error-message" hidden></p>

                <form id="maintenance-log-form">
                    <input type="hidden" id="maintenance-log-id" value="">

                    <div class="input-component" id="tank-field">
                        <label for="tank_id">Tank:</label>
                        <select id="tank_id">
                            <option value="">Loading tanks...</option>
                        </select>
                    </div>

                    <div class="input-component">
                        <label for="performed_at">Date &amp; Time:</label>
                        <input type="datetime-local" id="performed_at">
                    </div>

                    <div class="input-component">
                        <label for="task_type_preset">Task Type:</label>
                        <select id="task_type_preset">
                            <option>Feeding</option>
                            <option>Water Change</option>
                            <option>Fertilizing</option>
                            <option>Filter Cleaning</option>
                            <option>Glass Cleaning</option>
                            <option>Substrate Vacuuming</option>
                            <option>Trimming Plants</option>
                            <option>Medication Dosing</option>
                            <option>Equipment Changes</option>
                            <option>CO2 Adjustments</option>
                            <option>Light Schedule Changes</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="input-component" id="custom-task-type-field" hidden>
                        <label for="task_type">Custom Task Type:</label>
                        <input type="text" id="task_type">
                    </div>

                    <div class="input-component">
                        <label for="details">Details:</label>
                        <textarea id="details" rows="3"></textarea>
                    </div>

                    <div class="input-component">
                        <label for="maintenance_notes">Notes:</label>
                        <textarea id="maintenance_notes" rows="3"></textarea>
                    </div>

                    <div class="action-component">
                        <button type="submit" id="maintenance-log-form-submit" class="btn-secondary">Log Task</button>
                        <button type="button" id="maintenance-log-form-cancel" class="btn-secondary" hidden>Cancel</button>
                    </div>
                </form>
            </section>
        </main>

        <script src="js/maintenance_logs.js"></script>
    </body>
</html>
