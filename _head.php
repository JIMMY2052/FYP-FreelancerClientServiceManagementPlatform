<?php
// Start session if not already started
require_once __DIR__ . '/page/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=  $_title ?? 'Untitiled' ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="/index.php">
                    <img src="/images/logo.png" alt="Freelancer Platform Logo" class="logo-img">
                </a>
            </div>
            <div class="header-search">
                <input type="text" placeholder="Search for services..." class="search-input">
            </div>
            <nav class="header-nav">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])): ?>
                    <!-- Show profile and notification when logged in -->
                    <span class="notification-icon">🔔</span>
                    <div class="profile-dropdown">
                        <div class="profile-avatar">👤</div>
                        <div class="dropdown-menu">
                            <?php if ($_SESSION['user_type'] === 'freelancer'): ?>
                                <a href="/page/freelancer_profile.php" class="dropdown-item">View Profile</a>
                                <a href="/page/freelancer_dashboard.php" class="dropdown-item">Dashboard</a>
                            <?php else: ?>
                                <a href="/page/client_profile.php" class="dropdown-item">View Profile</a>
                                <a href="/page/client_dashboard.php" class="dropdown-item">Dashboard</a>
                            <?php endif; ?>
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