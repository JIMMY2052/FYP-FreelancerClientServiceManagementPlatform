<?php
// Start session if not already started
require_once __DIR__ . '/page/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitiled' ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/freelancer.css">
    <link rel="stylesheet" href="/assets/css/client.css">
</head>

<body>
    <header class="main-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="<?php
                            if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
                                if ($_SESSION['user_type'] === 'freelancer') {
                                    echo '/freelancer_home.php';
                                } else {
                                    echo '/client_home.php';
                                }
                            } else {
                                echo '/index.php';
                            }
                            ?>">
                    <img src="/images/logo.png" alt="Freelancer Platform Logo" class="logo-img">
                </a>
            </div>
            <nav class="header-nav">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])): ?>
                    <!-- Show profile and notification when logged in -->
                    <span class="notification-icon">🔔</span>
                    <div class="profile-dropdown">
                        <div class="profile-avatar" style="width: 36px; height: 36px; border-radius: 50%; background-color: #22c55e; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; cursor: pointer; overflow: hidden; flex-shrink: 0;">
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
                                    // _head.php is in root, so the path is directly as stored in database
                                    if (!empty($profilePicture) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $profilePicture)) {
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
                            <?php if ($_SESSION['user_type'] === 'freelancer'): ?>
                                <a href="/page/freelancer_profile.php" class="dropdown-item">View Profile</a>
                            <?php else: ?>
                                <a href="/page/client_profile.php" class="dropdown-item">View Profile</a>
                            <?php endif; ?>
                            <a href="/page/agreementListing.php" class="dropdown-item">Manage Agreement</a>
                            <a href="/page/payment/wallet.php" class="dropdown-item">Wallet</a>
                            <a href="/page/logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Show login and signup when not logged in -->
                    <a href="/page/login.php" class="btn btn-login">Login</a>
                    <a href="/page/signup.php" class="btn btn-signup">Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>