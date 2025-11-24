<?php
require_once 'config.php';

// Check if user is logged in as freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$freelancer_id = $_SESSION['user_id'];

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if ($file['size'] > 0 && $file['size'] <= $max_size && in_array($file['type'], $allowed_types)) {
        $upload_dir = '../uploads/profile_pictures/';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'freelancer_' . $freelancer_id . '_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Get old picture path to delete if exists
            $stmt = $conn->prepare("SELECT ProfilePicture FROM freelancer WHERE FreelancerID = ?");
            $stmt->bind_param("i", $freelancer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old_picture = $result->fetch_assoc()['ProfilePicture'];
            $stmt->close();

            // Delete old picture if exists
            if ($old_picture && file_exists('../' . $old_picture)) {
                unlink('../' . $old_picture);
            }

            // Update database with new picture path
            $relative_path = 'uploads/profile_pictures/' . $new_filename;
            $stmt = $conn->prepare("UPDATE freelancer SET ProfilePicture = ? WHERE FreelancerID = ?");
            $stmt->bind_param("si", $relative_path, $freelancer_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success'] = 'Profile picture updated successfully!';
            header('Location: freelancer_profile.php');
            exit();
        } else {
            $_SESSION['error'] = 'Failed to upload profile picture.';
        }
    } else {
        $_SESSION['error'] = 'Invalid file. Please upload an image (JPG, PNG, GIF) under 5MB.';
    }
}

// Get freelancer information
$stmt = $conn->prepare("SELECT FreelancerID, FirstName, LastName, Email, PhoneNo, Address, Experience, Education, Bio, Rating, TotalEarned, SocialMediaURL, ProfilePicture FROM freelancer WHERE FreelancerID = ?");
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
        Price,
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

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Section -->
            <div class="profile-card">
                <div class="profile-header-section">
                    <div class="profile-image-container">
                        <?php if ($freelancer['ProfilePicture'] && file_exists('../' . $freelancer['ProfilePicture'])): ?>
                            <img src="/<?php echo htmlspecialchars($freelancer['ProfilePicture']); ?>" alt="Profile Picture" class="profile-picture">
                        <?php else: ?>
                            <div class="avatar-large"><?php echo strtoupper(substr($freelancer['FirstName'] ?: 'F', 0, 1) . substr($freelancer['LastName'] ?: 'L', 0, 1)); ?></div>
                        <?php endif; ?>

                        <!-- Upload Button with Pencil Icon -->
                        <label for="profile_picture_input" class="upload-icon-button" title="Upload profile picture">
                            <svg class="pencil-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </label>
                        <input type="file" id="profile_picture_input" name="profile_picture" accept="image/*" style="display: none;">
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

    <script>
        // Auto-dismiss success/error messages after 3 seconds with animation
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('.alert');

            if (successAlert) {
                // Show message initially
                successAlert.classList.add('message-show');

                // Auto-dismiss after 3 seconds
                setTimeout(function() {
                    successAlert.classList.add('message-hide');

                    // Remove from DOM after animation completes
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 3000);
            }
        });

        // Handle profile picture upload
        document.getElementById('profile_picture_input').addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Please upload JPG, PNG, or GIF.');
                    return;
                }

                if (file.size > maxSize) {
                    alert('File is too large. Maximum size is 5MB.');
                    return;
                }

                // Create FormData and submit
                const formData = new FormData();
                formData.append('profile_picture', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'freelancer_profile.php', true);

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    } else {
                        alert('Error uploading file. Please try again.');
                    }
                };

                xhr.onerror = function() {
                    alert('Error uploading file. Please try again.');
                };

                xhr.send(formData);
            }
        });
    </script>
</body>

</html>