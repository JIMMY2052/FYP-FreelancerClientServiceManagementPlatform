<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar {
            font-family: 'Momo Trust Display', sans-serif;
        }

        .sidebar-nav,
        .nav-list,
        .nav-item,
        .nav-link {
            font-family: 'Inter', sans-serif;
        }

        .nav-label {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }

        .nav-link {
            font-weight: 500;
        }

        .nav-icon {
            font-size: 18px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .nav-link:hover .nav-icon,
        .nav-link.active .nav-icon {
            color: var(--text-primary);
        }
    </style>
    <div class="sidebar-content">
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_manage_users.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'admin_manage_users.php') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <span class="nav-label">Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_manage_disputes.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'admin_manage_disputes.php') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-gavel"></i>
                        <span class="nav-label">Manage Disputes</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="admin_settings.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'admin_settings.php') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cog"></i>
                        <span class="nav-label">Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>