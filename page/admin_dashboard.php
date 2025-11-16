<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit();
}

$conn = getDBConnection();

// Get total statistics
$freelancer_count = $conn->query("SELECT COUNT(*) as count FROM freelancer")->fetch_assoc()['count'];
$client_count = $conn->query("SELECT COUNT(*) as count FROM client")->fetch_assoc()['count'];
$total_users = $freelancer_count + $client_count;

// Get active users
$active_freelancers = $conn->query("SELECT COUNT(*) as count FROM freelancer WHERE Status = 'active'")->fetch_assoc()['count'];
$active_clients = $conn->query("SELECT COUNT(*) as count FROM client WHERE Status = 'active'")->fetch_assoc()['count'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WorkSnyc</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="admin-layout">
    <div class="admin-sidebar">
        <?php include '../includes/admin_sidebar.php'; ?>
    </div>

    <div class="admin-layout-wrapper">
        <?php include '../includes/admin_header.php'; ?>

        <main class="admin-main-content">
            <div class="dashboard-container">
                <div class="dashboard-header">
                    <h1>Dashboard</h1>
                    <p>Welcome back, Admin! Here's an overview of your platform.</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Users</h3>
                            <span class="stat-card-icon">👥</span>
                        </div>
                        <p class="stat-card-value"><?php echo $total_users; ?></p>
                        <div class="stat-card-change positive">
                            ↑ Freelancers: <?php echo $freelancer_count; ?> | Clients: <?php echo $client_count; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Active Users</h3>
                            <span class="stat-card-icon">✅</span>
                        </div>
                        <p class="stat-card-value"><?php echo $active_freelancers + $active_clients; ?></p>
                        <div class="stat-card-change positive">
                            ↑ Freelancers: <?php echo $active_freelancers; ?> | Clients: <?php echo $active_clients; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Inactive Users</h3>
                            <span class="stat-card-icon">❌</span>
                        </div>
                        <p class="stat-card-value"><?php echo ($freelancer_count - $active_freelancers) + ($client_count - $active_clients); ?></p>
                        <div class="stat-card-change">
                            Freelancers: <?php echo $freelancer_count - $active_freelancers; ?> | Clients: <?php echo $client_count - $active_clients; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Platform Health</h3>
                            <span class="stat-card-icon">📈</span>
                        </div>
                        <p class="stat-card-value"><?php echo $total_users > 0 ? round((($active_freelancers + $active_clients) / $total_users) * 100) : 0; ?>%</p>
                        <div class="stat-card-change positive">
                            Active user rate
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2>Recent Users</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px;">
                                    <p style="color: #9ca3af;">Go to <a href="admin_manage_users.php" style="color: #7c3aed; text-decoration: none;">Manage Users</a> to view and manage all users</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>