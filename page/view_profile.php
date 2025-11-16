<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$type = $_GET['type'] ?? null;
$id = $_GET['id'] ?? null;

if (!$type || !$id) {
    header('Location: messages.php');
    exit();
}

$freelancer = null;
$client = null;
$skills = [];
$total_earned = 0;
$completed_count = 0;
$total_spent = 0;
$project_count = 0;
$project_history = [];

if ($type === 'freelancer') {
    // Get freelancer information
    $stmt = $conn->prepare("SELECT FreelancerID, FirstName, LastName, Email, PhoneNo, Address, Experience, Education, Bio, RatingAverage, SocialMediaURL FROM freelancer WHERE FreelancerID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $freelancer = $result->fetch_assoc();
    $stmt->close();

    if (!$freelancer) {
        header('Location: messages.php');
        exit();
    }

    // Get freelancer skills
    $stmt = $conn->prepare("
        SELECT s.SkillName, fs.ProficiencyLevel 
        FROM freelancerskill fs
        INNER JOIN skill s ON fs.SkillID = s.SkillID
        WHERE fs.FreelancerID = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $skills_result = $stmt->get_result();
    while ($row = $skills_result->fetch_assoc()) {
        $skills[] = $row;
    }
    $stmt->close();

    // Get total earned
    $stmt = $conn->prepare("SELECT TotalEarned FROM freelancer WHERE FreelancerID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $total_earned_result = $stmt->get_result();
    $total_earned = $total_earned_result->fetch_assoc()['TotalEarned'] ?? 0.00;
    $stmt->close();

    // Get completed projects count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as completed_count 
        FROM application 
        WHERE FreelancerID = ? AND Status = 'completed'
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $completed_result = $stmt->get_result();
    $completed_count = $completed_result->fetch_assoc()['completed_count'] ?? 0;
    $stmt->close();
} elseif ($type === 'client') {
    // Get client information
    $stmt = $conn->prepare("SELECT ClientID, CompanyName, Description, Email, PhoneNo, Address, Status FROM client WHERE ClientID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();
    $stmt->close();

    if (!$client) {
        header('Location: messages.php');
        exit();
    }

    // Get total spent
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(p.Amount), 0) as total_spent 
        FROM payment p
        INNER JOIN application a ON p.ApplicationID = a.ApplicationID
        INNER JOIN job j ON a.JobID = j.JobID
        WHERE j.ClientID = ? AND p.Status = 'completed'
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $total_spent_result = $stmt->get_result();
    $total_spent = $total_spent_result->fetch_assoc()['total_spent'];
    $stmt->close();

    // Get projects posted count
    $stmt = $conn->prepare("SELECT COUNT(*) as project_count FROM job WHERE ClientID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $project_count_result = $stmt->get_result();
    $project_count = $project_count_result->fetch_assoc()['project_count'];
    $stmt->close();
} else {
    header('Location: messages.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $freelancer ? htmlspecialchars($freelancer['FirstName'] . ' ' . $freelancer['LastName']) : htmlspecialchars($client['CompanyName']); ?> - Profile</title>
    <link rel="stylesheet" href="../css/app.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .header-bar {
            background-color: white;
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .header-bar a {
            color: #22863a;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }

        .header-bar a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-card {
            background-color: white;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .profile-header {
            display: flex;
            align-items: flex-start;
            gap: 2.5rem;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e8e8e8;
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22863a, #1e6b30);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(34, 134, 58, 0.2);
        }

        .profile-info {
            flex: 1;
        }

        .profile-info h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: #1a1a1a;
            font-weight: 700;
        }

        .profile-subtitle {
            font-size: 1.1rem;
            color: #22863a;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .profile-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
            color: #666;
        }

        .meta-icon {
            color: #e74c3c;
            font-size: 1.2rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e8e8e8;
        }

        .profile-stat {
            text-align: center;
            padding: 1.5rem;
            background-color: #f9fafb;
            border-radius: 10px;
        }

        .profile-section {
            margin-bottom: 2rem;
        }

        .profile-section h2 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #333;
            border-bottom: 2px solid #22863a;
            padding-bottom: 0.5rem;
        }

        .profile-section p {
            line-height: 1.6;
            color: #666;
        }

        .skills-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .skill-tag {
            background-color: #f0f6f0;
            color: #22863a;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            border: 1px solid #22863a;
        }

        .skill-tag span {
            margin-left: 0.5rem;
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .contact-info {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 6px;
            margin-top: 1rem;
        }

        .contact-info p {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .contact-info strong {
            color: #333;
            min-width: 100px;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: #22863a;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1e6b30;
        }

        .btn-secondary {
            background-color: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #d0d0d0;
        }

        @media (max-width: 600px) {
            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-info h1 {
                font-size: 1.5rem;
            }

            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .profile-card {
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="header-bar">
        <a href="messages.php">← Back to Chat</a>
    </div>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php
                    if ($freelancer) {
                        $initials = strtoupper(substr($freelancer['FirstName'], 0, 1) . substr($freelancer['LastName'], 0, 1));
                    } else {
                        $initials = strtoupper(substr($client['CompanyName'], 0, 2));
                    }
                    echo $initials;
                    ?>
                </div>
                <div class="profile-info">
                    <?php if ($freelancer): ?>
                        <h1><?php echo htmlspecialchars($freelancer['FirstName'] . ' ' . $freelancer['LastName']); ?></h1>
                        <p>Freelancer</p>
                        <?php if (!empty($freelancer['RatingAverage'])): ?>
                            <p>⭐ <?php echo number_format($freelancer['RatingAverage'], 1); ?>/5 Rating</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <h1><?php echo htmlspecialchars($client['CompanyName']); ?></h1>
                        <p>Client</p>
                        <?php if (!empty($client['Status'])): ?>
                            <p>Status: <?php echo htmlspecialchars($client['Status']); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="profile-stats">
                        <?php if ($freelancer): ?>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $completed_count; ?></div>
                                <div class="stat-label">Completed</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">$<?php echo number_format($total_earned, 2); ?></div>
                                <div class="stat-label">Total Earned</div>
                            </div>
                        <?php else: ?>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $project_count; ?></div>
                                <div class="stat-label">Projects Posted</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">$<?php echo number_format($total_spent, 2); ?></div>
                                <div class="stat-label">Total Spent</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($freelancer): ?>
                <?php if (!empty($freelancer['Bio'])): ?>
                    <div class="profile-section">
                        <h2>About</h2>
                        <p><?php echo htmlspecialchars($freelancer['Bio']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($skills)): ?>
                    <div class="profile-section">
                        <h2>Skills</h2>
                        <div class="skills-grid">
                            <?php foreach ($skills as $skill): ?>
                                <div class="skill-tag">
                                    <?php echo htmlspecialchars($skill['SkillName']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($freelancer['Experience'])): ?>
                    <div class="profile-section">
                        <h2>Experience</h2>
                        <p><?php echo htmlspecialchars($freelancer['Experience']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($freelancer['Education'])): ?>
                    <div class="profile-section">
                        <h2>Education</h2>
                        <p><?php echo htmlspecialchars($freelancer['Education']); ?></p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if (!empty($client['Description'])): ?>
                    <div class="profile-section">
                        <h2>About</h2>
                        <p><?php echo htmlspecialchars($client['Description']); ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="profile-section">
                <h2>Contact Information</h2>
                <div class="contact-info">
                    <?php if ($freelancer): ?>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($freelancer['Email']); ?></p>
                        <?php if (!empty($freelancer['PhoneNo'])): ?>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($freelancer['PhoneNo']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($freelancer['Address'])): ?>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($freelancer['Address']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($freelancer['SocialMediaURL'])): ?>
                            <p><strong>Social Media:</strong> <a href="<?php echo htmlspecialchars($freelancer['SocialMediaURL']); ?>" target="_blank"><?php echo htmlspecialchars($freelancer['SocialMediaURL']); ?></a></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($client['Email']); ?></p>
                        <?php if (!empty($client['PhoneNo'])): ?>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($client['PhoneNo']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($client['Address'])): ?>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($client['Address']); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>