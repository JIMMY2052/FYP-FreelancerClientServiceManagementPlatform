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
    <!-- User Profile Section -->
    <div class="sidebar-profile">
        <div class="sidebar-profile-avatar" id="sidebarProfileAvatar" style="width: 56px; height: 56px; border-radius: 50%; background-color: #22c55e; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; cursor: pointer; overflow: hidden; flex-shrink: 0;">
            <?php
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
        <div class="sidebar-profile-info">
            <p class="sidebar-profile-name"><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'User'; ?></p>
            <p class="sidebar-profile-type"><?php echo isset($_SESSION['user_type']) ? ucfirst($_SESSION['user_type']) : 'User'; ?></p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="/page/ongoing_projects.php" class="nav-item <?php echo ($current_page === 'ongoing_projects.php') ? 'active' : ''; ?>">
            Ongoing Projects
        </a>
        <a href="/page/agreementListing.php" class="nav-item <?php echo ($current_page === 'agreementListing.php') ? 'active' : ''; ?>">
            Manage Agreement
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