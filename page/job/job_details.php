<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only freelancers can view job details
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: ../login.php');
    exit();
}

$_title = 'Job Details - WorkSnyc Platform';
require_once '../config.php';

if (!function_exists('getPDOConnection')) {
    function getPDOConnection(): PDO
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            return new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection error: ' . $e->getMessage());
        }
    }
}

// Get job ID from URL
$jobID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$jobID) {
    header('Location: browse_job.php');
    exit();
}

$pdo = getPDOConnection();

// Fetch job details with client information
try {
    $sql = "SELECT j.*, 
                   c.ClientID, c.CompanyName, c.Email as ClientEmail, 
                   c.ProfilePicture, c.Description as ClientDescription
            FROM job j
            INNER JOIN client c ON j.ClientID = c.ClientID
            WHERE j.JobID = :jobID AND j.Status = 'available'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':jobID' => $jobID]);
    $job = $stmt->fetch();

    if (!$job) {
        header('Location: browse_job.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('[job_details] Fetch failed: ' . $e->getMessage());
    die('Database error: ' . $e->getMessage());
}

?>
<?php require_once '../../_head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['Title']) ?> - WorkSnyc</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fa;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px 60px;
        }

        .job-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            align-items: start;
        }

        /* Left Column - Job Details */
        .job-main-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .job-header {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .job-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 15px 0;
            line-height: 1.3;
        }

        .job-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #666;
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .job-meta-item i {
            color: rgb(159, 232, 112);
        }

        /* Description Section */
        .job-section {
            margin-bottom: 30px;
        }

        .job-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .job-description {
            color: #555;
            line-height: 1.7;
            font-size: 0.95rem;
            white-space: pre-wrap;
        }

        .job-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .job-info-item {
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .job-info-label {
            font-size: 0.75rem;
            color: #999;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .job-info-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        /* Right Column - Sidebar */
        .job-sidebar {
            position: sticky;
            top: 100px;
        }

        .budget-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .budget-amount {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            line-height: 1.2;
        }

        .apply-btn {
            width: 100%;
            padding: 14px 20px;
            background: rgb(159, 232, 112);
            color: #2c3e50;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
            text-decoration: none;
        }

        .apply-btn:hover {
            background: rgb(140, 210, 90);
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
            transform: translateY(-2px);
        }

        .contact-btn {
            width: 100%;
            padding: 14px 20px;
            background: white;
            color: #2c3e50;
            border: 2px solid rgb(159, 232, 112);
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .contact-btn:hover {
            background: #f8f9fa;
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
            transform: translateY(-2px);
        }

        /* Client Card */
        .client-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .client-header {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .client-header a {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 8px;
            margin: -8px;
        }

        .client-header a:hover .client-info h3 {
            color: rgb(159, 232, 112);
        }

        .client-header a:hover {
            background: #f8f9fa;
        }

        .client-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            background: #f0f0f0;
        }

        .client-info h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 4px 0;
        }

        .client-email {
            font-size: 0.85rem;
            color: #666;
        }

        .client-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .stat-item {
            text-align: center;
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .stat-label {
            font-size: 0.7rem;
            color: #999;
            text-transform: uppercase;
            margin-top: 3px;
            font-weight: 600;
        }

        .client-description {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        @media (max-width: 1024px) {
            .job-layout {
                grid-template-columns: 1fr;
            }

            .job-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .job-main-content {
                padding: 20px;
            }

            .job-title {
                font-size: 1.4rem;
            }

            .job-info-grid {
                grid-template-columns: 1fr;
            }

            .budget-amount {
                font-size: 1.5rem;
            }

            .client-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Back Button -->
        <div style="margin-bottom: 2rem;">
            <a href="browse_job.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: rgb(159, 232, 112); text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                <i class="fas fa-arrow-left"></i>
                Back to Browse
            </a>
        </div>

        <div class="job-layout">
            <!-- Left Column: Job Details -->
            <div class="job-main-content">
                <!-- Job Header -->
                <div class="job-header">
                    <h1 class="job-title"><?= htmlspecialchars($job['Title']) ?></h1>
                    <div class="job-meta">
                        <div class="job-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Posted: <?= date('M d, Y', strtotime($job['PostDate'])) ?></span>
                        </div>
                        <div class="job-meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Deadline: <?= date('M d, Y', strtotime($job['Deadline'])) ?></span>
                        </div>
                        <div class="job-meta-item">
                            <i class="fas fa-briefcase"></i>
                            <span><?= htmlspecialchars($job['Status']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="job-section">
                    <h2 class="job-section-title">Job Description</h2>
                    <p class="job-description"><?= nl2br(htmlspecialchars($job['Description'])) ?></p>
                </div>

                <!-- Job Information -->
                <div class="job-section">
                    <h2 class="job-section-title">Job Details</h2>
                    <div class="job-info-grid">
                        <div class="job-info-item">
                            <div class="job-info-label">Budget</div>
                            <div class="job-info-value">RM <?= number_format($job['Budget'], 2) ?></div>
                        </div>
                        <div class="job-info-item">
                            <div class="job-info-label">Deadline</div>
                            <div class="job-info-value"><?= date('M d, Y', strtotime($job['Deadline'])) ?></div>
                        </div>
                        <div class="job-info-item">
                            <div class="job-info-label">Posted Date</div>
                            <div class="job-info-value"><?= date('M d, Y', strtotime($job['PostDate'])) ?></div>
                        </div>
                        <div class="job-info-item">
                            <div class="job-info-label">Status</div>
                            <div class="job-info-value"><?= ucfirst(htmlspecialchars($job['Status'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Budget & Client -->
            <div class="job-sidebar">
                <!-- Budget Card -->
                <div class="budget-card">
                    <div class="budget-amount">
                        RM<?= number_format($job['Budget'], 2) ?>
                    </div>
                    <a href="answer_questions.php?job_id=<?= $job['JobID'] ?>" class="apply-btn">
                        <i class="fas fa-paper-plane"></i>
                        Apply Now
                    </a>
                    <a href="../messages.php?client_id=<?= $job['ClientID'] ?>&job_id=<?= $job['JobID'] ?>" class="contact-btn">
                        <i class="fas fa-comment-dots"></i>
                        Contact Client
                    </a>
                </div>

                <!-- Client Card -->
                <div class="client-card">
                    <div class="client-header">
                        <?php $_SESSION['job_id'] = $job['JobID']; ?>
                        <a href="../view_client_profile.php?id=<?= $job['ClientID'] ?>" style="display: flex; gap: 15px; align-items: center; text-decoration: none; flex: 1;">
                            <?php
                            $profilePic = $job['ProfilePicture'];

                            // Add leading slash if missing
                            if ($profilePic && !empty($profilePic) && strpos($profilePic, 'http') !== 0) {
                                if (strpos($profilePic, '/') !== 0) {
                                    $profilePic = '/' . $profilePic;
                                }
                            }

                            // Check if picture exists and display it or fallback to initial
                            if ($profilePic && !empty($profilePic)):
                            ?>
                                <img src="<?= htmlspecialchars($profilePic) ?>"
                                    alt="<?= htmlspecialchars($job['CompanyName']) ?>"
                                    class="client-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <?php endif; ?>
                            <div class="client-avatar" style="<?= ($profilePic && !empty($profilePic)) ? 'display:none;' : 'display:flex;' ?> align-items: center; justify-content: center; background: linear-gradient(135deg, #16a34a, #15803d); color: white; font-weight: 800; font-size: 24px; border-radius: 50%;">
                                <?= strtoupper(substr($job['CompanyName'], 0, 1)) ?>
                            </div>
                            <div class="client-info">
                                <h3><?= htmlspecialchars($job['CompanyName']) ?></h3>
                                <div class="client-email"><?= htmlspecialchars($job['ClientEmail']) ?></div>
                            </div>
                        </a>
                    </div>

                    <div class="client-stats">
                        <div class="stat-item">
                            <div class="stat-value">0</div>
                            <div class="stat-label">Jobs Posted</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">0.0</div>
                            <div class="stat-label">Rating</div>
                        </div>
                    </div>

                    <?php if (!empty($job['ClientDescription'])): ?>
                        <div class="client-description">
                            <?= htmlspecialchars(substr($job['ClientDescription'], 0, 150)) ?><?= strlen($job['ClientDescription']) > 150 ? '...' : '' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../_foot.php'; ?>

</body>

</html>