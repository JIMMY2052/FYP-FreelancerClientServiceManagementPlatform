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
$stmt = $conn->prepare("SELECT ClientID, CompanyName, Description, Email, PhoneNo, Address FROM client WHERE ClientID = ?");
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
    <title>Edit Profile - WorkSnyc</title>
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

            <!-- Edit Profile Form -->
            <div class="profile-card">
                <div class="edit-profile-header">
                    <h1>Edit Profile</h1>
                    <a href="client_profile.php" class="back-btn">← Back to Profile</a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?php
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="edit_profile_process.php" method="POST" class="edit-profile-form">
                    <input type="hidden" name="user_type" value="client">

                    <!-- Company Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">Company Information</h3>
                        <div class="form-group">
                            <label for="company_name">Company Name *</label>
                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($client['CompanyName'] ?: ''); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description / Tagline</label>
                            <textarea
                                id="description"
                                name="description"
                                class="form-control"
                                rows="3"
                                placeholder="Brief description about your company"><?php echo htmlspecialchars($client['Description'] ?: ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">Contact Information</h3>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?php echo htmlspecialchars($client['Email']); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                class="form-control"
                                value="<?php echo htmlspecialchars($client['PhoneNo'] ?: ''); ?>"
                                placeholder="e.g., +60 12-345 6789">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea
                                id="address"
                                name="address"
                                class="form-control"
                                rows="3"
                                placeholder="Company address"><?php echo htmlspecialchars($client['Address'] ?: ''); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-signin">Save Changes</button>
                        <a href="client_profile.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>