<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Reminders</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>Reminders</h2>

            <section>
                <h3>Upcoming</h3>
                <div id="upcoming-reminder-list" class="tank-list">
                    <p>Loading reminders...</p>
                </div>
            </section>

            <section>
                <h3>Completed</h3>
                <div id="completed-reminder-list" class="tank-list">
                    <p>Loading reminders...</p>
                </div>
            </section>

            <section class="tank-form-section">
                <h3 id="reminder-form-title">Add a Reminder</h3>
                <p id="reminder-form-error" class="error-message" hidden></p>

                <form id="reminder-form">
                    <input type="hidden" id="reminder-id" value="">

                    <div class="input-component" id="tank-field">
                        <label for="tank_id">Tank:</label>
                        <select id="tank_id">
                            <option value="">Loading tanks...</option>
                        </select>
                    </div>

                    <div class="input-component">
                        <label for="title">Title:</label>
                        <input type="text" id="title" required>
                    </div>

                    <div class="input-component">
                        <label for="task_type_preset">Task Type:</label>
                        <select id="task_type_preset">
                            <option>Feed fish</option>
                            <option>Dose fertilizer</option>
                            <option>Change water</option>
                            <option>Test water</option>
                            <option>Clean filter</option>
                            <option>Trim plants</option>
                            <option>Dose medication</option>
                            <option>Replace equipment</option>
                            <option>Check livestock health</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="input-component" id="custom-task-type-field" hidden>
                        <label for="task_type">Custom Task Type:</label>
                        <input type="text" id="task_type">
                    </div>

                    <div class="input-component">
                        <label for="due_at">Due:</label>
                        <input type="datetime-local" id="due_at">
                    </div>

                    <div class="input-component">
                        <label for="repeat_interval_days">Repeat every (days, optional):</label>
                        <input type="number" min="1" step="1" id="repeat_interval_days">
                    </div>

                    <div class="input-component">
                        <label for="reminder_notes">Notes:</label>
                        <textarea id="reminder_notes" rows="3"></textarea>
                    </div>

                    <div class="action-component">
                        <button type="submit" id="reminder-form-submit" class="btn-secondary">Add Reminder</button>
                        <button type="button" id="reminder-form-cancel" class="btn-secondary" hidden>Cancel</button>
                    </div>
                </form>
            </section>
        </main>

        <script src="js/reminders.js"></script>
    </body>
</html>
