<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

// Check if user is deleted
require_once 'checkUserStatus.php';

$_title = 'Activity History';
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

require_once 'config.php';

$conn = getDBConnection();

// Fetch user's activity history based on user type
$activities = [];

if ($user_type === 'client') {
    // Fetch client activities

    // Jobs posted
    $sql = "SELECT 'job_posted' as type, JobID as id, Title as title, PostDate as date, Status as status, Budget as amount
            FROM job 
            WHERE ClientID = ?
            ORDER BY PostDate DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();

    // Agreements created
    $sql = "SELECT 'agreement_created' as type, AgreementID as id, ProjectTitle as title, ClientSignedDate as date, Status as status, PaymentAmount as amount
            FROM agreement 
            WHERE ClientID = ?
            ORDER BY ClientSignedDate DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();

    // Work submissions reviewed
    $sql = "SELECT 'work_reviewed' as type, ws.SubmissionID as id, a.ProjectTitle as title, ws.ReviewedAt as date, ws.Status as status, a.PaymentAmount as amount
            FROM work_submissions ws
            JOIN agreement a ON ws.AgreementID = a.AgreementID
            WHERE ws.ClientID = ? AND ws.Status IN ('approved', 'rejected')
            ORDER BY ws.ReviewedAt DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();

    // Wallet transactions
    $sql = "SELECT 'wallet_transaction' as type, TransactionID as id, Description as title, CreatedAt as date, Type as status, Amount as amount
            FROM wallet_transactions wt
            JOIN wallet w ON wt.WalletID = w.WalletID
            WHERE w.UserID = ?
            ORDER BY CreatedAt DESC
            LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
} else {
    // Fetch freelancer activities

    // Job applications
    $sql = "SELECT 'job_application' as type, ja.ApplicationID as id, j.Title as title, ja.AppliedAt as date, ja.Status as status, ja.ProposedBudget as amount
            FROM job_application ja
            JOIN job j ON ja.JobID = j.JobID
            WHERE ja.FreelancerID = ?
            ORDER BY ja.AppliedAt DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();

    // Agreements signed
    $sql = "SELECT 'agreement_signed' as type, AgreementID as id, ProjectTitle as title, FreelancerSignedDate as date, Status as status, PaymentAmount as amount
            FROM agreement 
            WHERE FreelancerID = ? AND FreelancerSignedDate IS NOT NULL
            ORDER BY FreelancerSignedDate DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();

    // Work submissions
    $sql = "SELECT 'work_submitted' as type, ws.SubmissionID as id, a.ProjectTitle as title, ws.SubmittedAt as date, ws.Status as status, a.PaymentAmount as amount
            FROM work_submissions ws
            JOIN agreement a ON ws.AgreementID = a.AgreementID
            WHERE ws.FreelancerID = ?
            ORDER BY ws.SubmittedAt DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();

    // Wallet transactions
    $sql = "SELECT 'wallet_transaction' as type, TransactionID as id, Description as title, CreatedAt as date, Type as status, Amount as amount
            FROM wallet_transactions wt
            JOIN wallet w ON wt.WalletID = w.WalletID
            WHERE w.UserID = ?
            ORDER BY CreatedAt DESC
            LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
}

$conn->close();

// Sort all activities by date
usort($activities, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="stylesheet" href="/assets/css/<?= $user_type === 'client' ? 'client' : 'freelancer' ?>.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            border-color: #1ab394;
            color: #1ab394;
        }

        .filter-btn.active {
            background: #1ab394;
            border-color: #1ab394;
            color: white;
        }

        .history-timeline {
            position: relative;
        }

        .timeline-item {
            position: relative;
            padding-left: 50px;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e9ecef;
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            font-weight: 700;
        }

        .timeline-icon.job-posted {
            background: #3498db;
        }

        .timeline-icon.job-application {
            background: #9b59b6;
        }

        .timeline-icon.agreement-created {
            background: #e74c3c;
        }

        .timeline-icon.agreement-signed {
            background: #2ecc71;
        }

        .timeline-icon.work-submitted {
            background: #f39c12;
        }

        .timeline-icon.work-reviewed {
            background: #1abc9c;
        }

        .timeline-icon.wallet-transaction {
            background: #34495e;
        }

        .timeline-content {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .timeline-content:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .activity-title {
            flex: 1;
        }

        .activity-type {
            font-size: 0.75rem;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .activity-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .activity-date {
            font-size: 0.85rem;
            color: #666;
        }

        .activity-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1ab394;
        }

        .activity-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 10px;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-processing {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
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
            color: #721c24;
        }

        .status-ongoing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-pending_review {
            background: #e2e3e5;
            color: #383d41;
        }

        .status-to_accept {
            background: #fff3cd;
            color: #856404;
        }

        .status-payment {
            background: #f8d7da;
            color: #721c24;
        }

        .status-credit {
            background: #d4edda;
            color: #155724;
        }

        .status-withdrawn {
            background: #e2e3e5;
            color: #383d41;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .activity-header {
                flex-direction: column;
            }

            .activity-amount {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <?php
    include '../includes/header.php';
    if ($user_type === 'client') {
        include '../includes/client_sidebar.php';
    } else {
        include '../includes/freelancer_sidebar.php';
    }
    ?>
    <div class="main-content">
        <div class="main-container">
            <?php
            if ($user_type === 'client') {
                include '../includes/client_sidebar.php';
            } else {
                include '../includes/freelancer_sidebar.php';
            }
            ?>

            <div class="content">
                <div class="page-header">
                    <h1>📋 Activity History</h1>
                    <p>Track all your activities and transactions</p>
                </div>

                <div class="filter-tabs">
                    <button class="filter-btn active" onclick="filterActivities('all')">All Activities</button>
                    <?php if ($user_type === 'client'): ?>
                        <button class="filter-btn" onclick="filterActivities('job_posted')">Jobs Posted</button>
                        <button class="filter-btn" onclick="filterActivities('agreement_created')">Agreements</button>
                        <button class="filter-btn" onclick="filterActivities('work_reviewed')">Reviews</button>
                    <?php else: ?>
                        <button class="filter-btn" onclick="filterActivities('job_application')">Applications</button>
                        <button class="filter-btn" onclick="filterActivities('agreement_signed')">Agreements</button>
                        <button class="filter-btn" onclick="filterActivities('work_submitted')">Submissions</button>
                    <?php endif; ?>
                    <button class="filter-btn" onclick="filterActivities('wallet_transaction')">Wallet</button>
                </div>

                <?php if (empty($activities)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h3>No Activity Yet</h3>
                        <p>Your activity history will appear here as you use the platform.</p>
                    </div>
                <?php else: ?>
                    <div class="history-timeline">
                        <?php foreach ($activities as $activity): ?>
                            <div class="timeline-item" data-type="<?= $activity['type'] ?>">
                                <div class="timeline-icon <?= str_replace('_', '-', $activity['type']) ?>">
                                    <?php
                                    $icons = [
                                        'job_posted' => '📝',
                                        'job_application' => '📬',
                                        'agreement_created' => '📄',
                                        'agreement_signed' => '✍️',
                                        'work_submitted' => '📤',
                                        'work_reviewed' => '✅',
                                        'wallet_transaction' => '💰'
                                    ];
                                    echo $icons[$activity['type']] ?? '📌';
                                    ?>
                                </div>

                                <div class="timeline-content">
                                    <div class="activity-header">
                                        <div class="activity-title">
                                            <div class="activity-type">
                                                <?= ucwords(str_replace('_', ' ', $activity['type'])) ?>
                                            </div>
                                            <div class="activity-name"><?= htmlspecialchars($activity['title']) ?></div>
                                            <div class="activity-date">
                                                <?= date('F d, Y - g:i A', strtotime($activity['date'])) ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($activity['amount'])): ?>
                                            <div class="activity-amount">
                                                <?php if ($activity['type'] === 'wallet_transaction' && $activity['status'] === 'payment'): ?>
                                                    -RM <?= number_format($activity['amount'], 2) ?>
                                                <?php else: ?>
                                                    RM <?= number_format($activity['amount'], 2) ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <span class="activity-status status-<?= str_replace('_', '-', strtolower($activity['status'])) ?>">
                                        <?= ucwords(str_replace('_', ' ', $activity['status'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            function filterActivities(type) {
                const items = document.querySelectorAll('.timeline-item');
                const buttons = document.querySelectorAll('.filter-btn');

                // Update active button
                buttons.forEach(btn => btn.classList.remove('active'));
                event.target.classList.add('active');

                // Filter items
                items.forEach(item => {
                    if (type === 'all' || item.dataset.type === type) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }
        </script>
    </div>
</body>

</html>