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
            font-size: 1.3rem;
            margin-bottom: 1.2rem;
            color: #1a1a1a;
            font-weight: 600;
        }

        .profile-section p {
            line-height: 1.7;
            color: #555;
            font-size: 0.95rem;
        }

        .skills-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .skill-tag {
            background: linear-gradient(135deg, #e8f5e9, #f1f8f5);
            color: #22863a;
            padding: 0.6rem 1.2rem;
            border-radius: 24px;
            font-size: 0.9rem;
            border: 1.5px solid #22863a;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .skill-tag:hover {
            background-color: #22863a;
            color: white;
            transform: translateY(-2px);
        }

        .contact-info {
            background: linear-gradient(135deg, #f9fafb, #f5f7f6);
            padding: 1.8rem;
            border-radius: 10px;
            margin-top: 1rem;
            border-left: 4px solid #22863a;
        }

        .contact-info p {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .contact-info p:last-child {
            margin-bottom: 0;
        }

        .contact-info strong {
            color: #1a1a1a;
            min-width: 100px;
            font-weight: 600;
        }

        .contact-info a {
            color: #22863a;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-info h1 {
                font-size: 1.8rem;
            }

            .profile-meta {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .meta-item {
                justify-content: center;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-card {
                padding: 1.5rem;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2rem;
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
                        <div class="profile-subtitle"><?php echo htmlspecialchars($freelancer['Bio'] ?? 'Freelancer'); ?></div>
                        <div class="profile-meta">
                            <?php if (!empty($freelancer['Address'])): ?>
                                <div class="meta-item">
                                    <span class="meta-icon">📍</span>
                                    <?php echo htmlspecialchars($freelancer['Address']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($freelancer['PhoneNo'])): ?>
                                <div class="meta-item">
                                    <span class="meta-icon">📱</span>
                                    <?php echo htmlspecialchars($freelancer['PhoneNo']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="meta-item">
                                <span class="meta-icon">✉️</span>
                                <?php echo htmlspecialchars($freelancer['Email']); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <h1><?php echo htmlspecialchars($client['CompanyName']); ?></h1>
                        <div class="profile-subtitle">Client</div>
                        <div class="profile-meta">
                            <?php if (!empty($client['Address'])): ?>
                                <div class="meta-item">
                                    <span class="meta-icon">📍</span>
                                    <?php echo htmlspecialchars($client['Address']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($client['PhoneNo'])): ?>
                                <div class="meta-item">
                                    <span class="meta-icon">📱</span>
                                    <?php echo htmlspecialchars($client['PhoneNo']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="meta-item">
                                <span class="meta-icon">✉️</span>
                                <?php echo htmlspecialchars($client['Email']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-grid">
                <?php if ($freelancer): ?>
                    <div class="profile-stat">
                        <div class="stat-number">$<?php echo number_format($total_earned, 2); ?></div>
                        <div class="stat-label">Total Earned</div>
                    </div>
                    <div class="profile-stat">
                        <div class="stat-number"><?php echo $completed_count; ?></div>
                        <div class="stat-label">Completed Projects</div>
                    </div>
                <?php else: ?>
                    <div class="profile-stat">
                        <div class="stat-number">$<?php echo number_format($total_spent, 2); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    <div class="profile-stat">
                        <div class="stat-number"><?php echo $project_count; ?></div>
                        <div class="stat-label">Projects Posted</div>
                    </div>
                <?php endif; ?>
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
        </div>
    </div>
</body>

</html>