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
$stmt = $conn->prepare("SELECT ClientID, CompanyName, Description, Email, PhoneNo, Address, Status FROM client WHERE ClientID = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();

// Get total spent (sum of all payments for this client's projects)
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(p.Amount), 0) as total_spent 
    FROM payment p
    INNER JOIN application a ON p.ApplicationID = a.ApplicationID
    INNER JOIN job j ON a.JobID = j.JobID
    WHERE j.ClientID = ? AND p.Status = 'completed'
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$total_spent_result = $stmt->get_result();
$total_spent = $total_spent_result->fetch_assoc()['total_spent'];
$stmt->close();

// Get projects posted count
$stmt = $conn->prepare("SELECT COUNT(*) as project_count FROM job WHERE ClientID = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$project_count_result = $stmt->get_result();
$project_count = $project_count_result->fetch_assoc()['project_count'];
$stmt->close();

// Get project history with freelancer information
$stmt = $conn->prepare("
    SELECT 
        j.JobID,
        j.Title,
        j.Budget,
        j.Status as JobStatus,
        j.PostDate,
        a.ApplicationID,
        a.Status as ApplicationStatus,
        f.FirstName,
        f.LastName,
        f.FreelancerID
    FROM job j
    LEFT JOIN application a ON j.JobID = a.JobID AND a.Status = 'accepted'
    LEFT JOIN freelancer f ON a.FreelancerID = f.FreelancerID
    WHERE j.ClientID = ?
    ORDER BY j.PostDate DESC
    LIMIT 10
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$projects_result = $stmt->get_result();
$projects = [];
while ($row = $projects_result->fetch_assoc()) {
    $projects[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - WorkSnyc</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
</head>

<body class="profile-page">
    <div class="profile-layout">
        <?php include '../includes/client_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include '../includes/header.php'; ?>

            <!-- Profile Section -->
            <div class="profile-card">
                <div class="profile-header-section">
                    <div class="profile-image">
                        <div class="avatar-large"><?php echo strtoupper(substr($client['CompanyName'] ?: 'C', 0, 1)); ?></div>
                    </div>
                    <div class="profile-info">
                        <h1 class="profile-name">
                            <?php echo htmlspecialchars($client['CompanyName'] ?: 'Client'); ?>
                            <a href="edit_client_profile.php" class="edit-profile-btn">Edit Profile</a>
                        </h1>
                        <p class="profile-tagline"><?php echo htmlspecialchars($client['Description'] ?: 'No description available'); ?></p>
                        <div class="profile-details">
                            <?php if ($client['Address']): ?>
                                <span class="detail-item">📍 Location: <?php echo htmlspecialchars($client['Address']); ?></span>
                            <?php endif; ?>
                            <?php if ($client['PhoneNo']): ?>
                                <span class="detail-item">📞 Phone: <?php echo htmlspecialchars($client['PhoneNo']); ?></span>
                            <?php endif; ?>
                            <span class="detail-item">✉️ Email: <?php echo htmlspecialchars($client['Email']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="profile-description">
                    <p><?php echo htmlspecialchars($client['Description'] ?: 'No description available.'); ?></p>
                </div>

                <div class="profile-stats">
                    <div class="stat-box">
                        <div class="stat-value">RM<?php echo number_format($total_spent, 2); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $project_count; ?></div>
                        <div class="stat-label">Projects Posted</div>
                    </div>
                </div>
            </div>

            <!-- Project History Section -->
            <div class="project-history-card">
                <h2 class="section-title">Project History</h2>
                <div class="project-list">
                    <?php if (empty($projects)): ?>
                        <p class="no-projects">No projects yet. <a href="post_job.php">Post your first project</a>!</p>
                    <?php else: ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="project-item">
                                <div class="project-info">
                                    <h3 class="project-title"><?php echo htmlspecialchars($project['Title']); ?></h3>
                                    <?php if ($project['FirstName'] && $project['LastName']): ?>
                                        <p class="project-freelancer">Freelancer: <?php echo htmlspecialchars($project['FirstName'] . ' ' . $project['LastName']); ?></p>
                                    <?php else: ?>
                                        <p class="project-freelancer">Freelancer: Not assigned</p>
                                    <?php endif; ?>
                                    <p class="project-budget">Budget: RM<?php echo number_format($project['Budget'], 2); ?></p>
                                </div>
                                <div class="project-status">
                                    <?php
                                    $status = $project['ApplicationStatus'] ?: $project['JobStatus'];
                                    $status_class = strtolower(str_replace(' ', '-', $status));
                                    ?>
                                    <span class="status-badge status-<?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>