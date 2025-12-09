<?php
require_once 'config.php';

// Check if user is logged in as freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}

// Check if user is deleted
require_once 'checkUserStatus.php';

$conn = getDBConnection();
$freelancer_id = $_SESSION['user_id'];

// Get freelancer information
$stmt = $conn->prepare("SELECT FirstName, LastName, Email, Status, Rating FROM freelancer WHERE FreelancerID = ?");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$freelancer = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WorkSnyc</title>
    <link rel="icon" type="image/png" href="/images/tabLogo.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
</head>

<body class="profile-page">
    <div class="profile-layout">
        <?php include '../includes/freelancer_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include '../includes/header.php'; ?>

            <div class="profile-card">
                <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($freelancer['FirstName'] . ' ' . $freelancer['LastName']); ?>!</h1>
                <p>Email: <?php echo htmlspecialchars($freelancer['Email']); ?></p>
                <?php if ($freelancer['Rating']): ?>
                    <p>Rating: <?php echo number_format($freelancer['Rating'], 2); ?>/5.00</p>
                <?php endif; ?>
                <h2>Freelancer Dashboard</h2>
                <p>This is your dashboard. More features coming soon!</p>
            </div>
        </main>
    </div>
</body>

</html>