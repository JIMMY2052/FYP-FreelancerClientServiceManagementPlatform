<?php
require_once 'config.php';

// Check if user is logged in as client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$client_id = $_SESSION['user_id'];

// Get client information
$stmt = $conn->prepare("SELECT CompanyName, Email, Status FROM client WHERE ClientID = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WorkSnyc</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body class="profile-page">
    <div class="profile-layout">
        <?php include 'includes/client_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include 'includes/header.php'; ?>

            <div class="profile-card">
                <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($client['CompanyName'] ?: 'Client'); ?>!</h1>
                <p>Email: <?php echo htmlspecialchars($client['Email']); ?></p>
                <h2>Client Dashboard</h2>
                <p>This is your dashboard. More features coming soon!</p>
            </div>
        </main>
    </div>
</body>
</html>

