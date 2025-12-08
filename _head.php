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
    <link rel="stylesheet" href="/assets/css/client.css">
    <script>
        // Global handler to prevent +, -, and e characters in numeric inputs

        // Reusable function to apply numeric input restrictions
        function restrictNumericInput(input) {
            // Prevent typing +, -, and e characters
            input.addEventListener('keydown', function(e) {
                // List of keys to block: +, -, e, E
                const invalidKeys = ['+', '-', 'e', 'E'];

                if (invalidKeys.includes(e.key)) {
                    e.preventDefault();
                    return false;
                }
            });

            // Also prevent pasting invalid characters
            input.addEventListener('paste', function(e) {
                setTimeout(function() {
                    // Remove any +, -, or e characters that were pasted
                    input.value = input.value.replace(/[+\-eE]/g, '');
                }, 0);
            });
        }

        // Apply to all existing numeric inputs on page load
        document.addEventListener('DOMContentLoaded', function() {
            const numericInputs = document.querySelectorAll('input[type="number"]');
            numericInputs.forEach(restrictNumericInput);
        });

        // Watch for dynamically added numeric inputs
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                // Check if the added node itself is a numeric input
                                if (node.tagName === 'INPUT' && node.type === 'number') {
                                    restrictNumericInput(node);
                                }
                                // Check for numeric inputs within the added node
                                if (node.querySelectorAll) {
                                    const numericInputs = node.querySelectorAll('input[type="number"]');
                                    numericInputs.forEach(restrictNumericInput);
                                }
                            }
                        });
                    }
                });
            });

            // Start observing after DOM is ready
            if (document.body) {
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
        }
    </script>
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
                    <div class="profile-dropdown">
                        <div class="profile-avatar" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #16a34a, #15803d); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; cursor: pointer; overflow: hidden; flex-shrink: 0;">
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

                                    // Prepare profile picture source
                                    $profilePicSrc = '';
                                    if (!empty($profilePicture)) {
                                        $picPath = $profilePicture;
                                        // Add leading / if missing and not an absolute URL
                                        if (strpos($picPath, '/') !== 0 && strpos($picPath, 'http') !== 0) {
                                            $picPath = '/' . $picPath;
                                        }
                                        $profilePicSrc = $picPath;
                                    }

                                    // Display profile picture if exists
                                    if (!empty($profilePicSrc)) {
                                        echo '<img src="' . htmlspecialchars($profilePicSrc) . '" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;" onerror="this.style.display=\'none\'; this.parentElement.textContent = \'' . strtoupper(substr($name1 ?: 'U', 0, 1)) . '\';">';
                                    } else {
                                        // Show initials fallback
                                        if ($user_type === 'freelancer') {
                                            $initials = strtoupper(substr($name1 ?: 'F', 0, 1));
                                        } else {
                                            $initials = strtoupper(substr($name1 ?: 'C', 0, 1));
                                        }
                                        echo $initials;
                                    }
                                } else {
                                    echo 'U';
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