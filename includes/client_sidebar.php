<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px 20px;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 5px;
    }

    .sidebar-logo a {
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .logo-img {
        width: 50px;
        height: 50px;
        object-fit: contain;
        display: block;
    }
</style>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="../client_home.php">
            <img src="../images/logo.png" alt="WorkSnyc Logo" class="logo-img">
        </a>
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
        <a href="my_applications.php" class="nav-item <?php echo ($current_page === 'my_applications.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📋</span>
            <span>Applications</span>
        </a>
        <a href="ongoing_projects.php" class="nav-item <?php echo ($current_page === 'ongoing_projects.php') ? 'active' : ''; ?>">
            <span class="nav-icon">🚀</span>
            <span>Ongoing Projects</span>
        </a>
        <a href="messages.php" class="nav-item <?php echo ($current_page === 'messages.php') ? 'active' : ''; ?>">
            <span class="nav-icon">💬</span>
            <span>Messages</span>
        </a>
        <a href="agreementListing.php" class="nav-item <?php echo ($current_page === 'agreementListing.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📄</span>
            <span>Agreements</span>
        </a>
        <a href="activity_history.php" class="nav-item <?php echo ($current_page === 'activity_history.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📋</span>
            <span>Activity History</span>
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