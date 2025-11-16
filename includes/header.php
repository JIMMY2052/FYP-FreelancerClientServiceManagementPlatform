<!-- Header -->
<header class="profile-header">
    <div class="search-bar">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input type="text" placeholder="Search" class="search-input">
    </div>
    <div class="header-actions">
        <svg class="notification-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <div class="profile-dropdown">
            <div class="profile-avatar" id="profileAvatar">
                <?php
                // Display user initials from session
                if (isset($_SESSION['email'])) {
                    $email = $_SESSION['email'];
                    $name_parts = explode(' ', $email);
                    $initials = strtoupper(substr($name_parts[0], 0, 1));
                    if (isset($name_parts[1])) {
                        $initials .= strtoupper(substr($name_parts[1], 0, 1));
                    }
                    echo $initials;
                } else {
                    echo '👤';
                }
                ?>
            </div>
            <div class="dropdown-menu">
                <a href="edit_profile.php" class="dropdown-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    Edit Profile
                </a>
                <a href="logout.php" class="dropdown-item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>