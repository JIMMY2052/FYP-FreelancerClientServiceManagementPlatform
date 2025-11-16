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
$stmt = $conn->prepare("SELECT FreelancerID, FirstName, LastName, Email, PhoneNo, Address, Experience, Education, Bio, SocialMediaURL FROM freelancer WHERE FreelancerID = ?");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$freelancer = $result->fetch_assoc();
$stmt->close();

// Get all available skills
$stmt = $conn->prepare("SELECT SkillID, SkillName FROM skill ORDER BY SkillName");
$stmt->execute();
$all_skills_result = $stmt->get_result();
$all_skills = [];
while ($row = $all_skills_result->fetch_assoc()) {
    $all_skills[] = $row;
}
$stmt->close();

// Get freelancer's current skills
$stmt = $conn->prepare("
    SELECT s.SkillID, s.SkillName, fs.ProficiencyLevel 
    FROM freelancerskill fs
    INNER JOIN skill s ON fs.SkillID = s.SkillID
    WHERE fs.FreelancerID = ?
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$current_skills_result = $stmt->get_result();
$current_skill_ids = [];
$current_skills_data = [];
while ($row = $current_skills_result->fetch_assoc()) {
    $current_skill_ids[] = $row['SkillID'];
    $current_skills_data[$row['SkillID']] = $row;
}
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
    <script src="assets/js/add_skill.js" defer></script>
</head>

<body class="profile-page">
    <div class="profile-layout">
        <?php include '../includes/freelancer_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php include '../includes/header.php'; ?>

            <!-- Edit Profile Form -->
            <div class="profile-card">
                <div class="edit-profile-header">
                    <h1>Edit Profile</h1>
                    <a href="freelancer_profile.php" class="back-btn">← Back to Profile</a>
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
                    <input type="hidden" name="user_type" value="freelancer">

                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">Personal Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($freelancer['FirstName'] ?: ''); ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($freelancer['LastName'] ?: ''); ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?php echo htmlspecialchars($freelancer['Email']); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                class="form-control"
                                value="<?php echo htmlspecialchars($freelancer['PhoneNo'] ?: ''); ?>"
                                placeholder="e.g., +60 12-345 6789">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea
                                id="address"
                                name="address"
                                class="form-control"
                                rows="3"
                                placeholder="Your address"><?php echo htmlspecialchars($freelancer['Address'] ?: ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Professional Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">Professional Information</h3>
                        <div class="form-group">
                            <label for="bio">Bio / Professional Summary</label>
                            <textarea
                                id="bio"
                                name="bio"
                                class="form-control"
                                rows="4"
                                placeholder="Tell clients about yourself and your expertise"><?php echo htmlspecialchars($freelancer['Bio'] ?: ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="experience">Experience</label>
                                <textarea
                                    id="experience"
                                    name="experience"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Describe your work experience, previous projects, etc."><?php echo htmlspecialchars($freelancer['Experience'] ?: ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="education">Education</label>
                                <textarea
                                    id="education"
                                    name="education"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Your educational background, certifications, etc."><?php echo htmlspecialchars($freelancer['Education'] ?: ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="social_media">Social Media URL</label>
                            <input
                                type="url"
                                id="social_media"
                                name="social_media"
                                class="form-control"
                                value="<?php echo htmlspecialchars($freelancer['SocialMediaURL'] ?: ''); ?>"
                                placeholder="https://linkedin.com/in/yourprofile">
                        </div>
                    </div>

                    <!-- Skills Section -->
                    <div class="form-section">
                        <h3 class="section-title">Skills</h3>
                        <div class="form-group" style="margin-bottom: 0;">
                            <div class="current-skills-container" id="current-skills-container">
                                <?php if (!empty($current_skills_data)): ?>
                                    <?php foreach ($current_skills_data as $skill): ?>
                                        <div class="skill-tag-item" data-skill-id="<?php echo $skill['SkillID']; ?>">
                                            <span class="skill-tag-name"><?php echo htmlspecialchars($skill['SkillName']); ?></span>
                                            <button type="button" class="skill-remove-btn" onclick="removeSkill(<?php echo $skill['SkillID']; ?>)">×</button>
                                            <input type="hidden" name="skills[]" value="<?php echo $skill['SkillID']; ?>">
                                            <input type="hidden" name="proficiency[<?php echo $skill['SkillID']; ?>]" value="<?php echo htmlspecialchars($skill['ProficiencyLevel']); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn-add-skill" onclick="openAddSkillModal()">+ Add skill</button>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-signin">Save Changes</button>
                        <a href="freelancer_profile.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Add Skill Modal -->
    <div class="modal-overlay" id="add-skill-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add skill</h2>
                <button type="button" class="modal-close" onclick="closeAddSkillModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="skill-search">Skill*</label>
                    <div class="skill-search-container">
                        <span class="search-icon">🔍</span>
                        <input
                            type="text"
                            id="skill-search"
                            class="form-control skill-search-input"
                            placeholder="Skill (ex: Project Management)"
                            autocomplete="off">
                    </div>
                    <div id="skill-search-results" class="skill-search-results"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-save" onclick="saveSkill()">Save</button>
            </div>
        </div>
    </div>

    <!-- Hidden data for JavaScript -->
    <script>
        const allSkills = <?php echo json_encode($all_skills); ?>;
        const currentSkillIds = <?php echo json_encode($current_skill_ids); ?>;
    </script>
</body>

</html>