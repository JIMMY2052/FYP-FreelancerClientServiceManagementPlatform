<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../index.php');
    exit();
}

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

// Build query for applications
$sql = "SELECT 
            ja.ApplicationID, ja.JobID, ja.FreelancerID, ja.CoverLetter, 
            ja.ProposedBudget, ja.EstimatedDuration, ja.Status, ja.AppliedAt,
            j.Title as JobTitle, j.Budget as JobBudget, j.Deadline,
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

$sql .= " ORDER BY ja.AppliedAt DESC";

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

?>

<div class="container">
    <div class="page-header">
        <h1>Job Applications</h1>
        <a href="my_jobs.php" class="btn-secondary">
            <i class="fas fa-briefcase"></i> My Jobs
        </a>
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
        <?= count($applications) ?> application(s) found
    </p>

    <?php if (empty($applications)): ?>
        <div class="no-applications">
            <i class="fas fa-inbox"></i>
            <p>No applications found.</p>
            <p class="subtitle">Applications from freelancers will appear here.</p>
        </div>
    <?php else: ?>
        <div class="applications-list">
            <?php foreach ($applications as $app): ?>
                <div class="application-card">
                    <div class="application-header">
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
                                    <?= htmlspecialchars($app['FirstName'] . ' ' . $app['LastName']) ?>
                                    <small style="font-size: 0.7rem; color: #999; font-weight: normal;">(App #<?= $app['ApplicationID'] ?>)</small>
                                </h3>
                                <p class="freelancer-email"><?= htmlspecialchars($app['FreelancerEmail']) ?></p>
                                <div class="freelancer-stats">
                                    <span class="stat-item">
                                        <i class="fas fa-star"></i>
                                        <?= $app['Rating'] ? number_format($app['Rating'], 1) : 'N/A' ?>
                                    </span>
                                    <span class="stat-item">
                                        <i class="fas fa-dollar-sign"></i>
                                        RM <?= $app['TotalEarned'] ? number_format($app['TotalEarned'], 0) : '0' ?> earned
                                    </span>
                                </div>
                            </div>
                        </div>
                        <span class="application-status status-<?= strtolower($app['Status']) ?>">
                            <?= ucfirst($app['Status']) ?>
                        </span>
                    </div>

                    <div class="application-body">
                        <div class="job-reference">
                            <h4>
                                <i class="fas fa-briefcase"></i>
                                Applied for: <?= htmlspecialchars($app['JobTitle']) ?>
                            </h4>
                            <div class="job-meta">
                                <span>Budget: RM <?= number_format($app['JobBudget'], 2) ?></span>
                                <span>•</span>
                                <span>Deadline: <?= date('M d, Y', strtotime($app['Deadline'])) ?></span>
                            </div>
                        </div>

                        <?php if (!empty($app['CoverLetter'])): ?>
                            <div class="section">
                                <h5>Cover Letter</h5>
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

                            <div class="detail-item">
                                <span class="label">Applied On:</span>
                                <span class="value"><?= date('M d, Y H:i', strtotime($app['AppliedAt'])) ?></span>
                            </div>
                        </div>

                        <?php if (!empty($app['answers'])): ?>
                            <div class="section">
                                <h5>
                                    <i class="fas fa-question-circle"></i>
                                    Screening Questions Answers
                                </h5>
                                <div class="answers-list">
                                    <?php foreach ($app['answers'] as $index => $answer): ?>
                                        <div class="answer-item">
                                            <div class="question-text">
                                                <strong>Q<?= $index + 1 ?>:</strong> <?= htmlspecialchars($answer['QuestionText']) ?>
                                            </div>
                                            <div class="answer-text">
                                                <i class="fas fa-check-circle"></i>
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
                    </div>

                    <div class="application-footer">
                        <a href="view_freelancer_profile.php?id=<?= $app['FreelancerID'] ?>" class="btn-small">
                            <i class="fas fa-user"></i> View Profile
                        </a>
                        <a href="messages.php?freelancer_id=<?= $app['FreelancerID'] ?>&job_id=<?= $app['JobID'] ?>" class="btn-small">
                            <i class="fas fa-comment"></i> Message
                        </a>

                        <?php if ($app['Status'] === 'pending'): ?>
                            <button class="btn-small btn-success" onclick="showAcceptConfirmation(<?= $app['ApplicationID'] ?>, <?= $app['JobBudget'] ?>, '<?= htmlspecialchars($app['JobTitle']) ?>')">
                                <i class="fas fa-check"></i> Accept
                            </button>
                            <button class="btn-small btn-danger" onclick="updateApplicationStatus(<?= $app['ApplicationID'] ?>, 'rejected')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        <?php elseif ($app['Status'] === 'accepted'): ?>
                            <span class="acceptance-note">
                                <i class="fas fa-check-circle"></i> Application accepted - Agreement in progress
                            </span>
                        <?php endif; ?>
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
        gap: 20px;
    }

    .application-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .application-card:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .application-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        background: #f8fafc;
        border-bottom: 1px solid #e9ecef;
    }

    .freelancer-info {
        display: flex;
        gap: 15px;
        align-items: center;
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

    .freelancer-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 4px 0;
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

    .stat-item i {
        color: rgb(159, 232, 112);
        margin-right: 4px;
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

    .application-body {
        padding: 25px;
    }

    .job-reference {
        padding: 15px;
        background: #f8fafc;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .job-reference h4 {
        font-size: 1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .job-reference h4 i {
        color: rgb(159, 232, 112);
    }

    .job-meta {
        font-size: 0.85rem;
        color: #666;
        display: flex;
        gap: 8px;
    }

    .section {
        margin-bottom: 20px;
    }

    .section h5 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section h5 i {
        color: rgb(159, 232, 112);
    }

    .cover-letter {
        color: #555;
        line-height: 1.7;
        font-size: 0.95rem;
        margin: 0;
    }

    .proposal-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 12px;
        margin-bottom: 20px;
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
        gap: 12px;
    }

    .answer-item {
        padding: 15px;
        background: #f8fafc;
        border-radius: 12px;
        border-left: 4px solid rgb(159, 232, 112);
    }

    .question-text {
        color: #2c3e50;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .answer-text {
        color: #555;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .answer-text i {
        color: rgb(159, 232, 112);
    }

    .application-footer {
        display: flex;
        gap: 10px;
        padding: 15px 25px;
        background: #f8fafc;
        border-top: 1px solid #e9ecef;
        flex-wrap: wrap;
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
</style>

<script>
    function updateApplicationStatus(applicationId, newStatus) {
        if (!confirm(`Are you sure you want to ${newStatus} this application?`)) {
            return;
        }

        // TODO: Implement AJAX call to update application status
        fetch('update_application_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `application_id=${applicationId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating application status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating application status');
            });
    }

    // Acceptance confirmation modal functions
    const clientBalance = <?= $clientBalance ?>;

    function showAcceptConfirmation(applicationId, jobBudget, jobTitle) {
        // Check if client has sufficient balance
        if (clientBalance < jobBudget) {
            // Show insufficient balance modal
            showInsufficientBalanceModal(jobBudget);
            return;
        }

        // Store the data
        pendingAcceptanceData = {
            applicationId: applicationId,
            jobBudget: jobBudget,
            jobTitle: jobTitle
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
        const modal = document.getElementById('acceptConfirmationModal');
        if (e.target === modal) {
            closeAcceptConfirmation();
        }
    });
</script>

<?php
include '../_foot.php';
?>