<?php
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aquarium Manager - Dashboard</title>
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php include __DIR__ . '/../includes/nav.php'; ?>

        <main class="tanks-page">
            <h2>Dashboard</h2>

            <div class="action-component">
                <a href="tanks.php" class="btn-secondary">Add a Tank</a>
                <a href="organisms.php" class="btn-secondary">Add an Organism</a>
                <a href="reminders.php" class="btn-secondary">Add a Reminder</a>
            </div>

            <section>
                <h3>Upcoming Reminders</h3>
                <div id="upcoming-reminders-list" class="tank-list">
                    <p>Loading...</p>
                </div>
            </section>

            <section>
                <h3>Needs Attention</h3>
                <div id="needs-attention-list" class="tank-list">
                    <p>Loading...</p>
                </div>
            </section>

            <section>
                <h3>My Tanks</h3>
                <div id="dashboard-tank-list" class="tank-list">
                    <p>Loading tanks...</p>
                </div>
            </section>
        </main>

        <script src="js/common.js"></script>
        <script src="js/dashboard.js"></script>
    </body>
</html>
