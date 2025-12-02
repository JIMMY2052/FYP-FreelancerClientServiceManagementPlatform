<?php
session_start();

// Check if user is logged in as freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

// Get client ID from URL
$clientID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$clientID) {
    header('Location: browse_job.php');
    exit();
}

$conn = getDBConnection();

// Fetch client information
$stmt = $conn->prepare("SELECT * FROM client WHERE ClientID = ?");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();

if (!$client) {
    header('Location: browse_job.php');
    exit();
}

// Get total jobs posted by this client
$stmt = $conn->prepare("SELECT COUNT(*) as job_count FROM job WHERE ClientID = ? AND Status = 'available'");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$job_result = $stmt->get_result();
$job_count = $job_result->fetch_assoc()['job_count'];
$stmt->close();

// Get completed projects count
$stmt = $conn->prepare("SELECT COUNT(*) as completed_count FROM job WHERE ClientID = ? AND Status = 'complete'");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$completed_result = $stmt->get_result();
$completed_count = $completed_result->fetch_assoc()['completed_count'];
$stmt->close();

// Get client's recent jobs
$stmt = $conn->prepare("SELECT JobID, Title, Budget, Status, PostDate FROM job WHERE ClientID = ? ORDER BY PostDate DESC LIMIT 5");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$jobs_result = $stmt->get_result();
$recent_jobs = [];
while ($row = $jobs_result->fetch_assoc()) {
    $recent_jobs[] = $row;
}
$stmt->close();

$conn->close();

// Process profile picture path
$profilePic = $client['ProfilePicture'];
if ($profilePic && !empty($profilePic) && strpos($profilePic, 'http') !== 0) {
    if (strpos($profilePic, '/') !== 0) {
        $profilePic = '/' . $profilePic;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($client['CompanyName']) ?> - WorkSnyc</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="stylesheet" href="/assets/css/client-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/freelancer_sidebar.php'; ?>

    <div class="container">
        <!-- Back Button -->
        <div style="margin-bottom: 2rem;">
            <a href="<?= isset($_GET['source']) && $_GET['source'] === 'messages' ? 'messages.php' : (isset($_SESSION['job_id']) ? 'job/job_details.php?id=' . intval($_SESSION['job_id']) : 'job/browse_job.php') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #16a34a; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <!-- Hero Section -->
        <div class="profile-hero">
            <div class="hero-avatar-section">
                <?php if ($profilePic && !empty($profilePic)): ?>
                    <img src="<?= htmlspecialchars($profilePic) ?>" alt="<?= htmlspecialchars($client['CompanyName']) ?>" class="hero-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <?php endif; ?>
                <div class="hero-avatar-initial" style="<?= ($profilePic && !empty($profilePic)) ? 'display:none;' : 'display:flex;' ?>">
                    <?= strtoupper(substr($client['CompanyName'], 0, 1)) ?>
                </div>
            </div>

            <div class="hero-info">
                <h1 class="hero-name"><?= htmlspecialchars($client['CompanyName']) ?></h1>
                <?php if (!empty($client['Description'])): ?>
                    <p class="hero-description"><?= htmlspecialchars($client['Description']) ?></p>
                <?php endif; ?>

                <!-- Stats Section -->
                <div class="stats-container" style="display: flex; gap: 2rem; margin-top: 2rem; flex-wrap: wrap;">
                    <!-- Active Projects Stat -->
                    <div class="stat-card" style="flex: 1; min-width: 180px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 2rem; border-radius: 16px; box-shadow: 0 4px 15px rgba(22, 163, 74, 0.2); color: white; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                            <div>
                                <div style="font-size: 2.5rem; font-weight: 900; line-height: 1; margin-bottom: 0.5rem;"><?= $job_count ?></div>
                                <div style="font-size: 0.95rem; opacity: 0.9; font-weight: 500;">Active Projects</div>
                            </div>
                            <div style="font-size: 2.5rem; opacity: 0.3;">
                                <i class="fas fa-briefcase"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Projects Stat -->
                    <div class="stat-card" style="flex: 1; min-width: 180px; background: linear-gradient(135deg, #0f766e 0%, #0d5f59 100%); padding: 2rem; border-radius: 16px; box-shadow: 0 4px 15px rgba(15, 118, 110, 0.2); color: white; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                            <div>
                                <div style="font-size: 2.5rem; font-weight: 900; line-height: 1; margin-bottom: 0.5rem;"><?= $completed_count ?></div>
                                <div style="font-size: 0.95rem; opacity: 0.9; font-weight: 500;">Completed Projects</div>
                            </div>
                            <div style="font-size: 2.5rem; opacity: 0.3;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="messages.php?client_id=<?= $clientID ?>" class="action-btn btn-contact">
                        <i class="fas fa-comment-dots"></i> Send Message
                    </a>
                    <a href="job/browse_job.php" class="action-btn btn-browse">
                        <i class="fas fa-briefcase"></i> Browse Their Projects
                    </a>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact-section">
            <h2 class="section-title">Contact Information</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-label">Email</div>
                    <div class="contact-value">
                        <a href="mailto:<?= htmlspecialchars($client['Email']) ?>" class="contact-link">
                            <?= htmlspecialchars($client['Email']) ?>
                        </a>
                    </div>
                </div>
                <?php if (!empty($client['PhoneNo'])): ?>
                    <div class="contact-item">
                        <div class="contact-label">Phone</div>
                        <div class="contact-value">
                            <a href="tel:<?= htmlspecialchars($client['PhoneNo']) ?>" class="contact-link">
                                <?= htmlspecialchars($client['PhoneNo']) ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Jobs Section -->
        <?php if (!empty($recent_jobs)): ?>
            <div class="recent-jobs-section">
                <h2 class="section-title">Recent Projects</h2>
                <div class="jobs-list">
                    <?php foreach ($recent_jobs as $job): ?>
                        <div class="job-item">
                            <div class="job-title"><?= htmlspecialchars($job['Title']) ?></div>
                            <div class="job-meta">
                                <span>Posted: <?= date('M d, Y', strtotime($job['PostDate'])) ?></span>
                                <span class="job-budget">RM <?= number_format($job['Budget'], 0) ?></span>
                                <span class="job-status <?= $job['Status'] ?>"><?= ucfirst($job['Status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../_foot.php'; ?>
</body>

</html>