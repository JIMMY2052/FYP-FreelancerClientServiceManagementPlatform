<!-- Header -->
<header class="profile-header">

    <div class="header-actions">
        <svg class="notification-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <div class="profile-dropdown">
            <div class="profile-avatar" id="profileAvatar" style="width: 36px; height: 36px; border-radius: 50%; background-color: #22c55e; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; cursor: pointer; overflow: hidden; flex-shrink: 0;">
                <?php
                // Display user profile picture from database
                if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
                    $user_id = $_SESSION['user_id'];
                    $user_type = $_SESSION['user_type'];

                    // Get database connection
                    $conn = getDBConnection();

                    // Determine table and columns based on user type
                    if ($user_type === 'freelancer') {
                        $table = 'freelancer';
                        $id_column = 'FreelancerID';
                        $name_col1 = 'FirstName';
                        $name_col2 = 'LastName';
                    } else {
                        $table = 'client';
                        $id_column = 'ClientID';
                        $name_col1 = 'CompanyName';
                        $name_col2 = null;
                    }

                    $query = "SELECT ProfilePicture, $name_col1" . ($name_col2 ? ", $name_col2" : "") . " FROM $table WHERE $id_column = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        $profilePicture = $user['ProfilePicture'] ?? null;
                        $name1 = $user[$name_col1] ?? '';
                        $name2 = $name_col2 ? ($user[$name_col2] ?? '') : '';

                        // Display profile picture if exists
                        if (!empty($profilePicture) && file_exists('../' . $profilePicture)) {
                            echo '<img src="/' . htmlspecialchars($profilePicture) . '" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;">';
                        } else {
                            // Show initials fallback
                            if ($user_type === 'freelancer') {
                                $initials = strtoupper(substr($name1 ?: 'F', 0, 1) . substr($name2 ?: 'L', 0, 1));
                            } else {
                                $initials = strtoupper(substr($name1 ?: 'C', 0, 1));
                            }
                            echo $initials;
                        }
                    } else {
                        echo '👤';
                    }

                    $stmt->close();
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