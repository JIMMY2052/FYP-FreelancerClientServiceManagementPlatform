<?php
require_once 'config.php';

// Check if user is logged in as freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$freelancer_id = $_SESSION['user_id'];

// Get freelancer information
$stmt = $conn->prepare("SELECT FreelancerID, FirstName, LastName, Email, PhoneNo, Address, Experience, Education, Bio, Rating, TotalEarned, SocialMediaURL FROM freelancer WHERE FreelancerID = ?");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$freelancer = $result->fetch_assoc();
$stmt->close();

// Get freelancer skills
$stmt = $conn->prepare("
    SELECT s.SkillName, fs.ProficiencyLevel 
    FROM freelancerskill fs
    INNER JOIN skill s ON fs.SkillID = s.SkillID
    WHERE fs.FreelancerID = ?
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$skills_result = $stmt->get_result();
$skills = [];
while ($row = $skills_result->fetch_assoc()) {
    $skills[] = $row;
}
$stmt->close();

// Get total earned from Freelancer table
$total_earned = $freelancer['TotalEarned'] ?? 0.00;

// Get gigs created by this freelancer
$stmt = $conn->prepare("
    SELECT 
        GigID,
        Title,
        Category,
        MinPrice,
        MaxPrice,
        Status,
        CreatedAt
    FROM gig
    WHERE FreelancerID = ?
    ORDER BY CreatedAt DESC
    LIMIT 10
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$gigs_result = $stmt->get_result();
$gigs = [];
while ($row = $gigs_result->fetch_assoc()) {
    $gigs[] = $row;
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
        <?php include '../includes/freelancer_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include '../includes/header.php'; ?>

            <!-- Profile Section -->
            <div class="profile-card">
                <div class="profile-header-section">
                    <div class="profile-image">
                        <div class="avatar-large"><?php echo strtoupper(substr($freelancer['FirstName'] ?: 'F', 0, 1) . substr($freelancer['LastName'] ?: 'L', 0, 1)); ?></div>
                    </div>
                    <div class="profile-info">
                        <h1 class="profile-name">
                            <?php echo htmlspecialchars($freelancer['FirstName'] . ' ' . $freelancer['LastName']); ?>
                            <a href="edit_freelancer_profile.php" class="edit-profile-btn">Edit Profile</a>
                        </h1>
                        <p class="profile-tagline"><?php echo htmlspecialchars($freelancer['Bio'] ?: 'Professional freelancer'); ?></p>
                        <div class="profile-details">
                            <?php if ($freelancer['Address']): ?>
                                <span class="detail-item">📍 Location: <?php echo htmlspecialchars($freelancer['Address']); ?></span>
                            <?php endif; ?>
                            <?php if ($freelancer['PhoneNo']): ?>
                                <span class="detail-item">📞 Phone: <?php echo htmlspecialchars($freelancer['PhoneNo']); ?></span>
                            <?php endif; ?>
                            <span class="detail-item">✉️ Email: <?php echo htmlspecialchars($freelancer['Email']); ?></span>
                            <?php if ($freelancer['Rating']): ?>
                                <span class="detail-item">⭐ Rating: <?php echo number_format($freelancer['Rating'], 2); ?>/5.00</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="profile-description">
                    <p><?php echo htmlspecialchars($freelancer['Bio'] ?: 'No bio available.'); ?></p>
                </div>

                <!-- Skills Section -->
                <?php if (!empty($skills)): ?>
                    <div class="skills-section">
                        <h3>Skills</h3>
                        <div class="skills-list">
                            <?php foreach ($skills as $skill): ?>
                                <span class="skill-tag">
                                    <?php echo htmlspecialchars($skill['SkillName']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Experience & Education -->
                <div class="profile-extra-info">
                    <?php if ($freelancer['Experience']): ?>
                        <div class="info-section">
                            <h4>Experience</h4>
                            <p><?php echo nl2br(htmlspecialchars($freelancer['Experience'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($freelancer['Education']): ?>
                        <div class="info-section">
                            <h4>Education</h4>
                            <p><?php echo nl2br(htmlspecialchars($freelancer['Education'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-stats">
                    <div class="stat-box">
                        <div class="stat-value">RM<?php echo number_format($total_earned, 2); ?></div>
                        <div class="stat-label">Total Earned</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo count($gigs); ?></div>
                        <div class="stat-label">Active Gigs</div>
                    </div>
                </div>
            </div>

            <!-- Project History Section -->
            <div class="project-history-card">
                <h2 class="section-title">My Gigs</h2>
                <div class="project-list">
                    <?php if (empty($gigs)): ?>
                        <p class="no-projects">No gigs created yet. <a href="create_gig.php">Create your first gig</a>!</p>
                    <?php else: ?>
                        <?php foreach ($gigs as $gig): ?>
                            <div class="project-item">
                                <div class="project-info">
                                    <h3 class="project-title"><?php echo htmlspecialchars($gig['Title']); ?></h3>
                                    <p class="project-freelancer">Category: <?php echo htmlspecialchars($gig['Category']); ?></p>
                                    <p class="project-budget">Price Range: RM<?php echo number_format($gig['MinPrice'], 2); ?> - RM<?php echo number_format($gig['MaxPrice'], 2); ?></p>
                                </div>
                                <div class="project-status">
                                    <?php
                                    $status = $gig['Status'];
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