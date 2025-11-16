<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <div class="sidebar-content">
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_manage_users.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'admin_manage_users.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">👥</span>
                        <span class="nav-label">Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">
                        <span class="nav-icon">📋</span>
                        <span class="nav-label">Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-label">Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>