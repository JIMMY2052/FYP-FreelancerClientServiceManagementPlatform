<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .html {
        font-family: 'Momo Trust Display', sans-serif;
    }

    .logo-img {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }
</style>
<!-- Sidebar -->
<input type="checkbox" id="sidebarToggle" class="sidebar-checkbox">
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <a href="/page/ongoing_projects.php" class="nav-item <?php echo ($current_page === 'ongoing_projects.php') ? 'active' : ''; ?>">
            Ongoing Projects
        </a>
        <a href="/page/agreementListing.php" class="nav-item <?php echo ($current_page === 'agreementListing.php') ? 'active' : ''; ?>">
            Manage Agreement
        </a>
        <a href="/page/my_applications.php" class="nav-item <?php echo ($current_page === 'my_applications.php') ? 'active' : ''; ?>">
            My Applications
        </a>
        <a href="/page/payment/wallet.php" class="nav-item <?php echo ($current_page === 'wallet.php') ? 'active' : ''; ?>">
            Wallet
        </a>
        <a href="/page/messages.php" class="nav-item <?php echo ($current_page === 'messages.php') ? 'active' : ''; ?>">
            Messages
        </a>
        <a href="/page/activity_history.php" class="nav-item <?php echo ($current_page === 'activity_history.php') ? 'active' : ''; ?>">
            Activity History
        </a>
        <a href="/page/freelancer_profile.php" class="nav-item <?php echo (in_array($current_page, ['freelancer_profile.php', 'edit_freelancer_profile.php'])) ? 'active' : ''; ?>">
            Profile
        </a>
        <a href="settings.php" class="nav-item <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
            Settings
        </a>
    </nav>
</aside>
<label for="sidebarToggle" class="sidebar-overlay" id="sidebarOverlay"></label>