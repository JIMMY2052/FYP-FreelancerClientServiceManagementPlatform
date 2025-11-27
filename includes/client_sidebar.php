<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Toggle -->
<input type="checkbox" id="sidebarToggle" class="sidebar-checkbox">
<label for="sidebarToggle" class="sidebar-overlay"></label>

<!-- Sidebar -->
<aside class="sidebar">
    <nav class="sidebar-nav">
        <a href="client_dashboard.php" class="nav-item <?php echo ($current_page === 'client_dashboard.php') ? 'active' : ''; ?>">
            <span>Dashboard</span>
        </a>
        <a href="post_job.php" class="nav-item <?php echo ($current_page === 'post_job.php') ? 'active' : ''; ?>">
            <span>Post a Project</span>
        </a>
        <a href="my_jobs.php" class="nav-item <?php echo ($current_page === 'my_jobs.php') ? 'active' : ''; ?>">
            <span>My Projects</span>
        </a>
        <a href="my_applications.php" class="nav-item <?php echo ($current_page === 'my_applications.php') ? 'active' : ''; ?>">
            <span>Applications</span>
        </a>
        <a href="ongoing_projects.php" class="nav-item <?php echo ($current_page === 'ongoing_projects.php') ? 'active' : ''; ?>">
            <span>Ongoing Projects</span>
        </a>
        <a href="messages.php" class="nav-item <?php echo ($current_page === 'messages.php') ? 'active' : ''; ?>">
            <span>Messages</span>
        </a>
        <a href="agreementListing.php" class="nav-item <?php echo ($current_page === 'agreementListing.php') ? 'active' : ''; ?>">
            <span>Agreements</span>
        </a>
        <a href="activity_history.php" class="nav-item <?php echo ($current_page === 'activity_history.php') ? 'active' : ''; ?>">
            <span>Activity History</span>
        </a>
        <a href="client_profile.php" class="nav-item <?php echo (in_array($current_page, ['client_profile.php', 'edit_client_profile.php'])) ? 'active' : ''; ?>">
            <span>Profile</span>
        </a>
        <a href="settings.php" class="nav-item <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>">
            <span>Settings</span>
        </a>
    </nav>
</aside>