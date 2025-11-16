<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🔧</div>
        <h1 class="logo-text">WorkSnyc</h1>
    </div>
    <nav class="sidebar-nav">
        <a href="freelancer_dashboard.php" class="nav-item <?php echo ($current_page === 'freelancer_dashboard.php') ? 'active' : ''; ?>">
            <span class="nav-icon">🏠</span>
            <span>Dashboard</span>
        </a>
        <a href="browse_jobs.php" class="nav-item <?php echo ($current_page === 'browse_jobs.php') ? 'active' : ''; ?>">
            <span class="nav-icon">🔍</span>
            <span>Browse Jobs</span>
        </a>
        <a href="my_applications.php" class="nav-item <?php echo ($current_page === 'my_applications.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📁</span>
            <span>My Applications</span>
        </a>
        <a href="messages.php" class="nav-item <?php echo ($current_page === 'messages.php') ? 'active' : ''; ?>">
            <span class="nav-icon">💬</span>
            <span>Messages</span>
        </a>
        <a href="agreements.php" class="nav-item <?php echo ($current_page === 'agreement.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📄</span>
            <span>Agreements</span>
        </a>
        <a href="freelancer_profile.php" class="nav-item <?php echo (in_array($current_page, ['freelancer_profile.php', 'edit_freelancer_profile.php'])) ? 'active' : ''; ?>">
            <span class="nav-icon">👤</span>
            <span>Profile</span>
        </a>
        <a href="settings.php" class="nav-item <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
            <span class="nav-icon">⚙️</span>
            <span>Settings</span>
        </a>
    </nav>
</aside>