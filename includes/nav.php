<?php if (isUserLoggedIn()): ?>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<nav class="site-nav">
    <span class="site-nav-brand">Aquarium Manager</span>

    <div class="site-nav-links">
        <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Dashboard</a>

        <span class="site-nav-divider"></span>

        <a href="tanks.php" class="<?php echo in_array($currentPage, ['tanks.php', 'virtual_tank.php'], true) ? 'active' : ''; ?>">Tanks</a>
        <a href="organisms.php" class="<?php echo $currentPage === 'organisms.php' ? 'active' : ''; ?>">Organisms</a>
        <a href="species.php" class="<?php echo $currentPage === 'species.php' ? 'active' : ''; ?>">Species</a>
        <a href="pixel_art_editor.php" class="<?php echo $currentPage === 'pixel_art_editor.php' ? 'active' : ''; ?>">Pixel Art</a>

        <span class="site-nav-divider"></span>

        <a href="water_tests.php" class="<?php echo $currentPage === 'water_tests.php' ? 'active' : ''; ?>">Water Tests</a>
        <a href="maintenance_logs.php" class="<?php echo $currentPage === 'maintenance_logs.php' ? 'active' : ''; ?>">Maintenance</a>
        <a href="reminders.php" class="<?php echo $currentPage === 'reminders.php' ? 'active' : ''; ?>">Reminders</a>
        <a href="graphs.php" class="<?php echo $currentPage === 'graphs.php' ? 'active' : ''; ?>">Graphs</a>

        <span class="site-nav-divider"></span>

        <a href="wishlist.php" class="<?php echo $currentPage === 'wishlist.php' ? 'active' : ''; ?>">Wishlist</a>
    </div>

    <a href="logout.php" class="site-nav-logout">Logout</a>
</nav>
<?php endif; ?>
