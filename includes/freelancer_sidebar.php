<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
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
        <a href="ongoing_projects.php" class="nav-item <?php echo ($current_page === 'ongoing_projects.php') ? 'active' : ''; ?>">
            Ongoing Projects
        </a>
        <a href="agreementListing.php" class="nav-item <?php echo ($current_page === 'agreementListing.php') ? 'active' : ''; ?>">
            Manage Agreement
        </a>
        <a href="payment/wallet.php" class="nav-item <?php echo ($current_page === 'wallet.php') ? 'active' : ''; ?>">
            Wallet
        </a>
        <a href="messages.php" class="nav-item <?php echo ($current_page === 'messages.php') ? 'active' : ''; ?>">
            Messages
        </a>
        <a href="activity_history.php" class="nav-item <?php echo ($current_page === 'activity_history.php') ? 'active' : ''; ?>">
            Activity History
        </a>
        <a href="freelancer_profile.php" class="nav-item <?php echo (in_array($current_page, ['freelancer_profile.php', 'edit_freelancer_profile.php'])) ? 'active' : ''; ?>">
            Profile
        </a>
        <a href="settings.php" class="nav-item <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
            Settings
        </a>
    </nav>
</aside>
<label for="sidebarToggle" class="sidebar-overlay" id="sidebarOverlay"></label>