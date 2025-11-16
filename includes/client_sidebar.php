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
        <a href="client_dashboard.php" class="nav-item <?php echo ($current_page === 'client_dashboard.php') ? 'active' : ''; ?>">
            <span class="nav-icon">🏠</span>
            <span>Dashboard</span>
        </a>
        <a href="post_job.php" class="nav-item <?php echo ($current_page === 'post_job.php') ? 'active' : ''; ?>">
            <span class="nav-icon">➕</span>
            <span>Post a Project</span>
        </a>
        <a href="my_jobs.php" class="nav-item <?php echo ($current_page === 'my_jobs.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📁</span>
            <span>My Projects</span>
        </a>
        <a href="messages.php" class="nav-item <?php echo ($current_page === 'messages.php') ? 'active' : ''; ?>">
            <span class="nav-icon">💬</span>
            <span>Messages</span>
        </a>
        <a href="agreement.php" class="nav-item <?php echo ($current_page === 'agreement.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📄</span>
            <span>Agreements</span>
        </a>
        <a href="client_profile.php" class="nav-item <?php echo (in_array($current_page, ['client_profile.php', 'edit_client_profile.php'])) ? 'active' : ''; ?>">
            <span class="nav-icon">👤</span>
            <span>Profile</span>
        </a>
        <a href="settings.php" class="nav-item <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
            <span class="nav-icon">⚙️</span>
            <span>Settings</span>
        </a>
    </nav>
</aside>