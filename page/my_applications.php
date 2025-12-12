<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../index.php');
    exit();
}

// Check if user is deleted
require_once 'checkUserStatus.php';

$_title = 'My Applications - WorkSnyc';
require_once 'config.php';

// Include header
include '../includes/header.php';
// Include sidebar
include '../includes/client_sidebar.php';

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

$clientID = $_SESSION['user_id'];
$pdo = getPDOConnection();

// Debug: Log the client ID
error_log('[my_applications] Client ID: ' . $clientID);

// Get client's wallet balance
try {
    $walletSql = "SELECT Balance, LockedBalance FROM wallet WHERE UserID = :clientID";
    $walletStmt = $pdo->prepare($walletSql);
    $walletStmt->execute([':clientID' => $clientID]);
    $walletData = $walletStmt->fetch();

    if ($walletData) {
        $clientBalance = floatval($walletData['Balance']);
        $lockedBalance = floatval($walletData['LockedBalance']);
    } else {
        // Create wallet if doesn't exist
        $createWalletSql = "INSERT INTO wallet (UserID, Balance, LockedBalance) VALUES (:clientID, 0.00, 0.00)";
        $createWalletStmt = $pdo->prepare($createWalletSql);
        $createWalletStmt->execute([':clientID' => $clientID]);
        $clientBalance = 0.00;
        $lockedBalance = 0.00;
    }
    error_log('[my_applications] Client wallet balance: RM ' . $clientBalance);
} catch (PDOException $e) {
    error_log('[my_applications] Failed to fetch wallet balance: ' . $e->getMessage());
    $clientBalance = 0.00;
    $lockedBalance = 0.00;
}

// Get filter parameters
$jobFilter = isset($_GET['job_id']) ? intval($_GET['job_id']) : null;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;

// Fetch client's jobs for filter dropdown
try {
    $sqlJobs = "SELECT JobID, Title FROM job WHERE ClientID = :clientID ORDER BY PostDate DESC";
    $stmtJobs = $pdo->prepare($sqlJobs);
    $stmtJobs->execute([':clientID' => $clientID]);
    $clientJobs = $stmtJobs->fetchAll();
    error_log('[my_applications] Client has ' . count($clientJobs) . ' jobs');
} catch (PDOException $e) {
    error_log('[my_applications] Failed to fetch client jobs: ' . $e->getMessage());
    $clientJobs = [];
}

// Build query for applications grouped by job
$sql = "SELECT 
            ja.ApplicationID, ja.JobID, ja.FreelancerID, ja.CoverLetter, 
            ja.ProposedBudget, ja.EstimatedDuration, ja.Status, ja.AppliedAt,
            j.Title as JobTitle, j.Budget as JobBudget, j.Deadline, j.PostDate,
            f.FirstName, f.LastName, f.Email as FreelancerEmail, 
            f.ProfilePicture, f.Bio, f.Rating, f.TotalEarned
        FROM job_application ja
        INNER JOIN job j ON ja.JobID = j.JobID
        INNER JOIN freelancer f ON ja.FreelancerID = f.FreelancerID
        WHERE j.ClientID = :clientID";

$params = [':clientID' => $clientID];

// Apply filters
if ($jobFilter) {
    $sql .= " AND ja.JobID = :jobID";
    $params[':jobID'] = $jobFilter;
}

if ($statusFilter && in_array($statusFilter, ['pending', 'accepted', 'rejected', 'withdrawn'])) {
    $sql .= " AND ja.Status = :status";
    $params[':status'] = $statusFilter;
}

$sql .= " ORDER BY j.PostDate DESC, ja.AppliedAt DESC";

// Debug: Log the SQL query and parameters
error_log('[my_applications] SQL: ' . $sql);
error_log('[my_applications] Params: ' . print_r($params, true));

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll();

    // Debug: Log the applications fetched
    error_log('[my_applications] Total applications fetched: ' . count($applications));
    foreach ($applications as $app) {
        error_log('[my_applications] AppID: ' . $app['ApplicationID'] . ', JobID: ' . $app['JobID'] . ', FreelancerID: ' . $app['FreelancerID'] . ', Status: ' . $app['Status']);
    }
} catch (PDOException $e) {
    error_log('[my_applications] Fetch failed: ' . $e->getMessage());
    $applications = [];
}

// Fetch answers for each application
foreach ($applications as &$app) {
    try {
        $sqlAnswers = "SELECT 
                        jaa.AnswerText, jaa.SelectedOptionID,
                        jq.QuestionText, jq.QuestionType,
                        jqo.OptionText
                      FROM job_application_answer jaa
                      INNER JOIN job_question jq ON jaa.QuestionID = jq.QuestionID
                      LEFT JOIN job_question_option jqo ON jaa.SelectedOptionID = jqo.OptionID
                      WHERE jaa.JobID = :jobID AND jaa.FreelancerID = :freelancerID
                      ORDER BY jaa.AnswerID ASC";

        $stmtAnswers = $pdo->prepare($sqlAnswers);
        $stmtAnswers->execute([
            ':jobID' => $app['JobID'],
            ':freelancerID' => $app['FreelancerID']
        ]);
        $app['answers'] = $stmtAnswers->fetchAll();
    } catch (PDOException $e) {
        $app['answers'] = [];
    }
}

// Group applications by JobID
$groupedApplications = [];
foreach ($applications as $app) {
    $jobId = $app['JobID'];
    if (!isset($groupedApplications[$jobId])) {
        $groupedApplications[$jobId] = [
            'JobID' => $app['JobID'],
            'JobTitle' => $app['JobTitle'],
            'JobBudget' => $app['JobBudget'],
            'Deadline' => $app['Deadline'],
            'PostDate' => $app['PostDate'],
            'applications' => []
        ];
    }
    $groupedApplications[$jobId]['applications'][] = $app;
}

?>

<div class="container">
    <div class="page-header">
        <h1>Job Applications</h1>
    </div>

    <!-- Filters -->
    <form method="get" class="filters-section">
        <div class="filter-group">
            <label for="job_id">Filter by Job:</label>
            <select name="job_id" id="job_id" class="filter-select">
                <option value="">All Jobs</option>
                <?php foreach ($clientJobs as $job): ?>
                    <option value="<?= $job['JobID'] ?>" <?= ($jobFilter == $job['JobID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($job['Title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="status">Filter by Status:</label>
            <select name="status" id="status" class="filter-select">
                <option value="">All Status</option>
                <option value="pending" <?= ($statusFilter === 'pending') ? 'selected' : '' ?>>Pending</option>
                <option value="accepted" <?= ($statusFilter === 'accepted') ? 'selected' : '' ?>>Accepted</option>
                <option value="rejected" <?= ($statusFilter === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                <option value="withdrawn" <?= ($statusFilter === 'withdrawn') ? 'selected' : '' ?>>Withdrawn</option>
            </select>
        </div>

        <button type="submit" class="filter-btn">
            <i class="fas fa-filter"></i> Apply Filters
        </button>
        <a href="my_applications.php" class="reset-btn">
            <i class="fas fa-redo"></i> Reset
        </a>
    </form>

    <!-- Results Count -->
    <p class="results-count">
        <?= count($groupedApplications) ?> job(s) with <?= count($applications) ?> application(s) found
    </p>

    <?php if (empty($groupedApplications)): ?>
        <div class="no-applications">
            <i class="fas fa-inbox"></i>
            <p>No applications found.</p>
            <p class="subtitle">Applications from freelancers will appear here.</p>
        </div>
    <?php else: ?>
        <div class="applications-list">
            <?php foreach ($groupedApplications as $jobGroup): ?>
                <div class="job-group-card">
                    <!-- Job Header -->
                    <div class="job-group-header">
                        <div class="job-info">
                            <h2 class="job-title">
                                <i class="fas fa-briefcase"></i>
                                <?= htmlspecialchars($jobGroup['JobTitle']) ?>
                            </h2>
                            <div class="job-meta">
                                <span class="meta-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    Budget: RM <?= number_format($jobGroup['JobBudget'], 2) ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    Deadline: <?= date('M d, Y', strtotime($jobGroup['Deadline'])) ?>
                                </span>
                                <span class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <?= count($jobGroup['applications']) ?> Applicant(s)
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Freelancers List -->
                    <div class="freelancers-list">
                        <?php foreach ($jobGroup['applications'] as $app): ?>
                            <div class="freelancer-application">
                                <div class="freelancer-main">
                                    <div class="freelancer-info">
                                        <div class="freelancer-avatar">
                                            <?php if (!empty($app['ProfilePicture'])): ?>
                                                <img src="/<?= htmlspecialchars($app['ProfilePicture']) ?>"
                                                    alt="<?= htmlspecialchars($app['FirstName']) ?>">
                                            <?php else: ?>
                                                <div class="avatar-placeholder">
                                                    <?= strtoupper(substr($app['FirstName'], 0, 1) . substr($app['LastName'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="freelancer-details">
                                            <h3 class="freelancer-name">
                                                <a href="view_freelancer_profile.php?id=<?= $app['FreelancerID'] ?>" class="freelancer-name-link">
                                                    <?= htmlspecialchars($app['FirstName'] . ' ' . $app['LastName']) ?>
                                                </a>
                                            </h3>
                                            <p class="freelancer-email"><?= htmlspecialchars($app['FreelancerEmail']) ?></p>
                                            <div class="freelancer-stats">
                                                <span class="stat-item">
                                                    <i class="fas fa-star"></i>
                                                    <?= $app['Rating'] ? number_format($app['Rating'], 1) : 'N/A' ?>
                                                </span>
                                                <span class="stat-item">
                                                    <i class="fas fa-clock"></i>
                                                    Applied: <?= date('M d, Y', strtotime($app['AppliedAt'])) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="application-status-actions">
                                        <span class="application-status status-<?= strtolower($app['Status']) ?>">
                                            <?= ucfirst($app['Status']) ?>
                                        </span>
                                        <?php if ($app['Status'] === 'pending'): ?>
                                            <div class="action-buttons">
                                                <button class="btn-small btn-success" 
                                                    onclick="showAcceptConfirmation(<?= $app['ApplicationID'] ?>, <?= $jobGroup['JobBudget'] ?>, '<?= htmlspecialchars(addslashes($jobGroup['JobTitle'])) ?>', <?= $app['JobID'] ?>)">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                                <button class="btn-small btn-danger" 
                                                    onclick="updateApplicationStatus(<?= $app['ApplicationID'] ?>, 'rejected')">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        <?php elseif ($app['Status'] === 'accepted'): ?>
                                            <span class="acceptance-note">
                                                <i class="fas fa-check-circle"></i> Accepted
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Expandable Details -->
                                <div class="freelancer-expandable">
                                    <button class="expand-toggle" onclick="toggleDetails(this)">
                                        <i class="fas fa-chevron-down"></i> Show Details
                                    </button>
                                    <div class="freelancer-details-content" style="display: none;">
                                        <?php if (!empty($app['CoverLetter'])): ?>
                                            <div class="detail-section">
                                                <h5><i class="fas fa-envelope"></i> Cover Letter</h5>
                                                <p class="cover-letter"><?= nl2br(htmlspecialchars($app['CoverLetter'])) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="proposal-details">
                                            <?php if (!empty($app['ProposedBudget'])): ?>
                                                <div class="detail-item">
                                                    <span class="label">Proposed Budget:</span>
                                                    <span class="value">RM <?= number_format($app['ProposedBudget'], 2) ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($app['EstimatedDuration'])): ?>
                                                <div class="detail-item">
                                                    <span class="label">Estimated Duration:</span>
                                                    <span class="value"><?= htmlspecialchars($app['EstimatedDuration']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($app['answers'])): ?>
                                            <div class="detail-section">
                                                <h5><i class="fas fa-question-circle"></i> Screening Questions</h5>
                                                <div class="answers-list">
                                                    <?php foreach ($app['answers'] as $index => $answer): ?>
                                                        <div class="answer-item">
                                                            <div class="question-text">
                                                                <strong>Q<?= $index + 1 ?>:</strong> <?= htmlspecialchars($answer['QuestionText']) ?>
                                                            </div>
                                                            <div class="answer-text">
                                                                <?php if ($answer['QuestionType'] === 'yes_no'): ?>
                                                                    <strong><?= ucfirst($answer['AnswerText']) ?></strong>
                                                                <?php else: ?>
                                                                    <?= htmlspecialchars($answer['OptionText']) ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="detail-actions">
                                            <a href="view_freelancer_profile.php?id=<?= $app['FreelancerID'] ?>" class="btn-small">
                                                <i class="fas fa-user"></i> View Profile
                                            </a>
                                            <a href="messages.php?freelancer_id=<?= $app['FreelancerID'] ?>&job_id=<?= $app['JobID'] ?>" class="btn-small">
                                                <i class="fas fa-comment"></i> Message
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Acceptance Confirmation Modal -->
    <div id="acceptConfirmationModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Accept Application & Sign Agreement</h2>
                <button class="modal-close" onclick="closeAcceptConfirmation()">&times;</button>
            </div>
            <div class="modal-body">
                <ol>
                    <li><strong>Review and sign the agreement</strong></li>
                    <li><strong>Hold the project amount</strong> of <span id="modalJobBudget" class="amount"></span></li>
                </ol>
                <p class="warning-text">Amount will be held until project completion or cancelled.</p>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-secondary" onclick="closeAcceptConfirmation()">Cancel</button>
                <button class="btn-modal-primary" onclick="proceedWithAcceptance()">Proceed to Agreement</button>
            </div>
        </div>
    </div>

    <!-- Insufficient Balance Modal -->
    <div id="insufficientBalanceModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Insufficient Wallet Balance</h2>
                <button class="modal-close" onclick="closeInsufficientBalanceModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px 0;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #ffc107; margin-bottom: 20px;"></i>
                    <p style="font-size: 1.1rem; color: #2c3e50; margin-bottom: 15px;">
                        You don't have enough balance in your wallet to accept this application.
                    </p>
                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: #666;">Current Balance:</span>
                            <span style="font-weight: 700; color: #2c3e50;" id="modalCurrentBalance">RM 0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="color: #666;">Required Amount:</span>
                            <span style="font-weight: 700; color: #dc3545;" id="modalRequiredAmount">RM 0.00</span>
                        </div>
                        <div style="border-top: 2px solid #e9ecef; margin: 10px 0; padding-top: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666; font-weight: 600;">Amount Needed:</span>
                                <span style="font-weight: 700; color: #ffc107; font-size: 1.1rem;" id="modalAmountNeeded">RM 0.00</span>
                            </div>
                        </div>
                    </div>
                    <p style="font-size: 0.9rem; color: #666;">
                        Please top up your wallet to proceed with accepting this application.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-secondary" onclick="closeInsufficientBalanceModal()">Cancel</button>
                <button class="btn-modal-primary" onclick="goToTopUp()" style="background: #28a745;">
                    <i class="fas fa-wallet"></i> Top Up Wallet
                </button>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div id="rejectConfirmationModal" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Reject Application</h2>
                <button class="modal-close" onclick="closeRejectConfirmation()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="padding: 20px 0;">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #dc3545; margin-bottom: 20px; display: block; text-align: center;"></i>
                    <p style="font-size: 1.1rem; color: #2c3e50; margin-bottom: 15px; text-align: center;">
                        Are you sure you want to reject this application?
                    </p>
                    <p style="font-size: 0.9rem; color: #666; text-align: center; margin-bottom: 25px;">
                        The freelancer will be notified with your feedback.
                    </p>
                    
                    <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border-left: 4px solid #dc3545;">
                        <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">
                            <i class="fas fa-comment-alt"></i> Rejection Reason (Optional)
                        </label>
                        <textarea id="rejectionReason" 
                            placeholder="Share constructive feedback with the freelancer about why their application wasn't selected. This will help them improve their future applications."
                            style="width: 100%; padding: 12px; border: 1.5px solid #e9ecef; border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 0.9rem; resize: vertical; min-height: 120px; transition: all 0.3s ease;"
                            onfocus="this.style.borderColor='rgb(159, 232, 112)'; this.style.boxShadow='0 0 0 3px rgba(159, 232, 112, 0.1)';"
                            onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none';"></textarea>
                        <p style="font-size: 0.75rem; color: #999; margin-top: 8px;">
                            <i class="fas fa-info-circle"></i> The freelancer will receive this message once you reject the application.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-secondary" onclick="closeRejectConfirmation()">Cancel</button>
                <button class="btn-modal-primary" onclick="confirmReject()" style="background: #dc3545;">
                    <i class="fas fa-times"></i> Reject Application
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/assets/css/app.css">
<link rel="stylesheet" href="/assets/css/profile.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Header Styles */
    .profile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 30px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-left {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .menu-toggle {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }

    .menu-toggle:hover {
        background-color: #f0f0f0;
    }

    .menu-icon {
        width: 24px;
        height: 24px;
    }

    .header-logo {
        flex: 1;
        display: flex;
        justify-content: center;
    }

    .logo-img {
        height: 40px;
        width: auto;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 20px;
        flex: 1;
        justify-content: flex-end;
    }

    .notification-icon {
        cursor: pointer;
        color: #333;
        transition: color 0.3s ease;
    }

    .notification-icon:hover {
        color: #22c55e;
    }

    .profile-dropdown {
        position: relative;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        min-width: 180px;
        padding: 8px 0;
        z-index: 1000;
    }

    .profile-dropdown:hover .dropdown-menu {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-item svg {
        width: 16px;
        height: 16px;
    }

    .container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px 60px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .btn-secondary {
        padding: 12px 20px;
        background: #6c757d;
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-secondary:hover {
        background: #5a6268;
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        transform: translateY(-2px);
    }

    /* Filters */
    .filters-section {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1;
        min-width: 200px;
    }

    .filter-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #555;
    }

    .filter-select {
        padding: 10px 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .filter-select:focus {
        outline: none;
        border-color: rgb(159, 232, 112);
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
    }

    .filter-btn,
    .reset-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        align-self: flex-end;
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .filter-btn {
        background: rgb(159, 232, 112);
        color: #2c3e50;
    }

    .filter-btn:hover {
        background: rgb(140, 210, 90);
    }

    .reset-btn {
        background: #f8f9fa;
        color: #6c757d;
        border: 1px solid #ddd;
    }

    .reset-btn:hover {
        background: #e9ecef;
    }

    .results-count {
        color: #666;
        margin-bottom: 20px;
        font-weight: 500;
    }

    /* Applications List */
    .applications-list {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    /* Job Group Card */
    .job-group-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .job-group-card:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .job-group-header {
        padding: 25px;
        background: linear-gradient(135deg, #f8fafc 0%, #e9ecef 100%);
        border-bottom: 2px solid rgb(159, 232, 112);
    }

    .job-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .job-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .job-title i {
        color: rgb(159, 232, 112);
    }

    .job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        font-size: 0.9rem;
        color: #666;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .meta-item i {
        color: rgb(159, 232, 112);
    }

    /* Freelancers List */
    .freelancers-list {
        display: flex;
        flex-direction: column;
    }

    .freelancer-application {
        border-bottom: 1px solid #e9ecef;
    }

    .freelancer-application:last-child {
        border-bottom: none;
    }

    .freelancer-main {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: white;
        transition: background 0.3s ease;
    }

    .freelancer-application:hover .freelancer-main {
        background: #f8fafc;
    }

    .freelancer-info {
        display: flex;
        gap: 15px;
        align-items: center;
        flex: 1;
    }

    .freelancer-avatar {
        flex-shrink: 0;
    }

    .freelancer-avatar img,
    .avatar-placeholder {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    }

    .avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgb(159, 232, 112);
        color: white;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .freelancer-details {
        flex: 1;
    }

    .freelancer-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 4px 0;
    }

    .freelancer-name-link {
        color: #2c3e50;
        text-decoration: none;
        transition: color 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .freelancer-name-link:hover {
        color: rgb(159, 232, 112);
        text-decoration: underline;
    }

    .freelancer-email {
        font-size: 0.85rem;
        color: #666;
        margin: 0 0 8px 0;
    }

    .freelancer-stats {
        display: flex;
        gap: 15px;
        font-size: 0.85rem;
        color: #555;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-item i {
        color: rgb(159, 232, 112);
    }

    .application-status-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .application-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-accepted {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #842029;
    }

    .status-withdrawn {
        background: #e2e3e5;
        color: #383d41;
    }

    .acceptance-note {
        font-size: 0.85rem;
        color: #155724;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Expandable Details */
    .freelancer-expandable {
        padding: 0 25px 15px 25px;
    }

    .expand-toggle {
        width: 100%;
        padding: 12px 16px;
        background: #f8fafc;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #2c3e50;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .expand-toggle:hover {
        background: #e9ecef;
    }

    .expand-toggle i {
        transition: transform 0.3s ease;
    }

    .expand-toggle.expanded i {
        transform: rotate(180deg);
    }

    .freelancer-details-content {
        margin-top: 15px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 12px;
    }

    .detail-section {
        margin-bottom: 20px;
    }

    .detail-section:last-child {
        margin-bottom: 0;
    }

    .detail-section h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .detail-section h5 i {
        color: rgb(159, 232, 112);
    }

    .cover-letter {
        color: #555;
        line-height: 1.7;
        font-size: 0.95rem;
        margin: 0;
        padding: 15px;
        background: white;
        border-radius: 8px;
    }

    .proposal-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        padding: 15px;
        background: white;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .detail-item .label {
        font-size: 0.75rem;
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
    }

    .detail-item .value {
        font-size: 0.95rem;
        color: #2c3e50;
        font-weight: 700;
    }

    .answers-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .answer-item {
        padding: 12px 15px;
        background: white;
        border-radius: 8px;
        border-left: 3px solid rgb(159, 232, 112);
    }

    .question-text {
        color: #2c3e50;
        font-size: 0.9rem;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .answer-text {
        color: #555;
        font-size: 0.9rem;
        padding-left: 10px;
    }

    .detail-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .btn-small {
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgb(159, 232, 112);
        color: #2c3e50;
    }

    .btn-small:hover {
        background: rgb(140, 210, 90);
        transform: translateY(-1px);
    }

    .btn-small.btn-success {
        background: #28a745;
        color: white;
    }

    .btn-small.btn-success:hover {
        background: #218838;
    }

    .btn-small.btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-small.btn-danger:hover {
        background: #c82333;
    }

    .acceptance-note {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: #d4edda;
        color: #155724;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .acceptance-note i {
        color: #28a745;
    }

    /* No Applications */
    .no-applications {
        text-align: center;
        padding: 80px 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .no-applications i {
        font-size: 4rem;
        color: rgb(159, 232, 112);
        margin-bottom: 20px;
    }

    .no-applications p {
        font-size: 1.1rem;
        color: #666;
        margin: 0 0 10px 0;
    }

    .no-applications .subtitle {
        font-size: 0.9rem;
        color: #999;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .filters-section {
            flex-direction: column;
        }

        .filter-group {
            min-width: 100%;
        }

        .application-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .freelancer-info {
            flex-direction: column;
            text-align: center;
        }

        .application-footer {
            flex-direction: column;
        }

        .btn-small {
            width: 100%;
            justify-content: center;
        }
    }

    /* Acceptance Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 500px;
        width: 90%;
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 25px;
        background: #f8fafc;
        border-bottom: 1px solid #e9ecef;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.3rem;
        color: #2c3e50;
        font-weight: 700;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.8rem;
        color: #999;
        cursor: pointer;
        transition: color 0.3s ease;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        color: #2c3e50;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-body ol {
        margin: 0 0 20px 0;
        padding-left: 20px;
        color: #2c3e50;
        line-height: 1.8;
    }

    .modal-body ol li {
        margin-bottom: 12px;
        font-weight: 500;
    }

    .modal-body li strong {
        color: rgb(159, 232, 112);
    }

    .warning-text {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px 15px;
        border-radius: 6px;
        color: #856404;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    .amount {
        font-weight: 700;
        color: rgb(159, 232, 112);
    }

    .modal-footer {
        display: flex;
        gap: 12px;
        padding: 15px 25px;
        background: #f8fafc;
        border-top: 1px solid #e9ecef;
        justify-content: flex-end;
    }

    .btn-modal-primary,
    .btn-modal-secondary {
        padding: 12px 24px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .btn-modal-primary {
        background: rgb(159, 232, 112);
        color: #2c3e50;
    }

    .btn-modal-primary:hover {
        background: rgb(140, 210, 90);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    }

    .btn-modal-secondary {
        background: #e9ecef;
        color: #555;
    }

    .btn-modal-secondary:hover {
        background: #ddd;
    }

    /* Rejection Reason Textarea */
    #rejectionReason {
        font-family: 'Inter', sans-serif;
    }

    #rejectionReason::placeholder {
        color: #bbb;
    }

    #rejectionReason:focus {
        outline: none;
    }
</style>

<script>
    let pendingRejection = null;
    let pendingAcceptanceData = null;

    function toggleDetails(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            button.classList.add('expanded');
            button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Details';
        } else {
            content.style.display = 'none';
            button.classList.remove('expanded');
            button.innerHTML = '<i class="fas fa-chevron-down"></i> Show Details';
        }
    }

    function updateApplicationStatus(applicationId, newStatus) {
        if (newStatus === 'rejected') {
            // Show reject confirmation modal
            pendingRejection = { applicationId, newStatus };
            document.getElementById('rejectConfirmationModal').style.display = 'flex';
            document.getElementById('rejectionReason').value = ''; // Clear previous input
            return;
        }

        // For other statuses, proceed directly
        processStatusUpdate(applicationId, newStatus);
    }

    function processStatusUpdate(applicationId, newStatus, rejectionReason = null) {
        // Build form data
        const formData = new URLSearchParams();
        formData.append('application_id', applicationId);
        formData.append('status', newStatus);
        
        if (rejectionReason) {
            formData.append('rejection_reason', rejectionReason);
        }

        // Implement AJAX call to update application status
        fetch('update_application_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Could not update application status'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating application status');
            });
    }

    function closeRejectConfirmation() {
        document.getElementById('rejectConfirmationModal').style.display = 'none';
        pendingRejection = null;
    }

    function confirmReject() {
        if (pendingRejection) {
            const { applicationId, newStatus } = pendingRejection;
            const rejectionReason = document.getElementById('rejectionReason').value.trim();
            closeRejectConfirmation();
            processStatusUpdate(applicationId, newStatus, rejectionReason);
        }
    }

    // Acceptance confirmation modal functions
    const clientBalance = <?= $clientBalance ?>;

    function showAcceptConfirmation(applicationId, jobBudget, jobTitle, jobId) {
        // Check if client has sufficient balance
        if (clientBalance < jobBudget) {
            // Show insufficient balance modal
            showInsufficientBalanceModal(jobBudget);
            return;
        }

        // Store the data including jobId for rejecting other applications
        pendingAcceptanceData = {
            applicationId: applicationId,
            jobBudget: jobBudget,
            jobTitle: jobTitle,
            jobId: jobId
        };

        document.getElementById('modalJobBudget').textContent = 'RM ' + parseFloat(jobBudget).toLocaleString('en-MY', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        document.getElementById('acceptConfirmationModal').style.display = 'flex';
    }

    function showInsufficientBalanceModal(requiredAmount) {
        const amountNeeded = requiredAmount - clientBalance;

        document.getElementById('modalCurrentBalance').textContent = 'RM ' + parseFloat(clientBalance).toLocaleString('en-MY', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('modalRequiredAmount').textContent = 'RM ' + parseFloat(requiredAmount).toLocaleString('en-MY', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('modalAmountNeeded').textContent = 'RM ' + parseFloat(amountNeeded).toLocaleString('en-MY', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('insufficientBalanceModal').style.display = 'flex';
    }

    function closeInsufficientBalanceModal() {
        document.getElementById('insufficientBalanceModal').style.display = 'none';
    }

    function goToTopUp() {
        window.location.href = 'payment/wallet.php';
    }

    function closeAcceptConfirmation() {
        document.getElementById('acceptConfirmationModal').style.display = 'none';
    }

    function proceedWithAcceptance() {
        const {
            applicationId
        } = pendingAcceptanceData;
        // Create a form and submit via POST to agreementClient.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'agreementClient.php';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'application_id';
        input.value = applicationId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        const acceptModal = document.getElementById('acceptConfirmationModal');
        const rejectModal = document.getElementById('rejectConfirmationModal');
        
        if (e.target === acceptModal) {
            closeAcceptConfirmation();
        }
        if (e.target === rejectModal) {
            closeRejectConfirmation();
        }
    });
</script>

<?php
include '../_foot.php';
?>