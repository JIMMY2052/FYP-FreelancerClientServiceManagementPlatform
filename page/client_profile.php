<?php
require_once 'config.php';

// Check if user is logged in as client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: login.php');
    exit();
}

// Check if user is deleted
require_once 'checkUserStatus.php';

$conn = getDBConnection();
$client_id = $_SESSION['user_id'];

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
        $new_filename = 'client_' . $client_id . '_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Get old picture path to delete if exists
            $stmt = $conn->prepare("SELECT ProfilePicture FROM client WHERE ClientID = ?");
            $stmt->bind_param("i", $client_id);
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
            $stmt = $conn->prepare("UPDATE client SET ProfilePicture = ? WHERE ClientID = ?");
            $stmt->bind_param("si", $relative_path, $client_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = 'Profile picture updated successfully!';
                $stmt->close();
                $conn->close();
                header('Location: client_profile.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to update database: ' . $stmt->error;
                $stmt->close();
            }
        } else {
            $_SESSION['error'] = 'Failed to upload profile picture.';
        }
    } else {
        $_SESSION['error'] = 'Invalid file. Please upload an image (JPG, PNG, GIF) under 5MB.';
    }
}

// Get client information
$stmt = $conn->prepare("SELECT ClientID, CompanyName, Description, Email, PhoneNo, Address, Status, ProfilePicture FROM client WHERE ClientID = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();

// Get total budget from jobs posted by this client
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(Budget), 0) as total_budget 
    FROM job
    WHERE ClientID = ? AND Status = 'complete'
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$total_budget_result = $stmt->get_result();
$total_budget = $total_budget_result->fetch_assoc()['total_budget'];
$stmt->close();

// Get projects posted count
$stmt = $conn->prepare("SELECT COUNT(*) as project_count FROM job WHERE ClientID = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$project_count_result = $stmt->get_result();
$project_count = $project_count_result->fetch_assoc()['project_count'];
$stmt->close();

// Get project history
$stmt = $conn->prepare("
    SELECT 
        JobID,
        Title,
        Budget,
        Status,
        PostDate,
        Description
    FROM job
    WHERE ClientID = ?
    ORDER BY PostDate DESC
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
    <link rel="icon" type="image/png" href="/images/tabLogo.png">
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
                        <?php if ($client['ProfilePicture'] && file_exists('../' . $client['ProfilePicture'])): ?>
                            <img src="/<?php echo htmlspecialchars($client['ProfilePicture']); ?>" alt="Profile Picture" class="profile-picture">
                        <?php else: ?>
                            <div class="avatar-large"><?php echo strtoupper(substr($client['CompanyName'] ?: 'C', 0, 1)); ?></div>
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
                        <div class="stat-value">RM<?php echo number_format($total_budget, 2); ?></div>
                        <div class="stat-label">Total Budget Posted</div>
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
                        <p class="no-projects">No projects yet. <a href="createJob.php">Post your first project</a>!</p>
                    <?php else: ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="project-item">
                                <div class="project-info">
                                    <h3 class="project-title"><?php echo htmlspecialchars($project['Title']); ?></h3>
                                    <p class="project-description"><?php echo htmlspecialchars(substr($project['Description'], 0, 100)) . (strlen($project['Description']) > 100 ? '...' : ''); ?></p>
                                    <p class="project-budget">Budget: RM<?php echo number_format($project['Budget'], 2); ?></p>
                                </div>
                                <div class="project-status">
                                    <?php
                                    $status = $project['Status'];
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
        document.getElementById('profile_picture_input').addEventListener('change', function(e) {
            if (this.files[0]) {
                const formData = new FormData();
                formData.append('profile_picture', this.files[0]);

                const xhr = new XMLHttpRequest();
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    } else {
                        alert('Error uploading profile picture');
                    }
                };
                xhr.onerror = function() {
                    alert('Error uploading profile picture');
                };
                xhr.open('POST', 'client_profile.php', true);
                xhr.send(formData);
            }
        });

        // Auto-dismiss messages with animation
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('.alert');
            if (successAlert) {
                successAlert.classList.add('message-show');
                setTimeout(function() {
                    successAlert.classList.add('message-hide');
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 3000);
            }
        });
    </script>
</body>

</html>