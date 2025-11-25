<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

$_title = 'Agreement Management';
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

require_once 'config.php';

// Get filter parameter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$valid_statuses = ['all', 'to_accept', 'ongoing', 'completed', 'declined', 'cancelled', 'disputed'];

if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

// Build query based on user type
$conn = getDBConnection();

if ($user_type === 'client') {
    // Client view - agreements where they are the client
    $sql = "SELECT 
                a.AgreementID,
                a.FreelancerID,
                a.ClientID,
                a.ProjectTitle,
                a.PaymentAmount,
                a.Status,
                a.CreatedDate,
                a.ClientSignedDate,
                a.FreelancerSignedDate,
                a.ExpiredDate,
                CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
                f.ProfilePicture as FreelancerProfilePic,
                c.CompanyName as ClientName
            FROM agreement a
            JOIN freelancer f ON a.FreelancerID = f.FreelancerID
            JOIN client c ON a.ClientID = c.ClientID
            WHERE a.ClientID = ?";

    if ($status_filter !== 'all') {
        $sql .= " AND a.Status = ?";
    }

    $sql .= " ORDER BY a.CreatedDate DESC";
} else {
    // Freelancer view - agreements where they are the freelancer
    $sql = "SELECT 
                a.AgreementID,
                a.FreelancerID,
                a.ClientID,
                a.ProjectTitle,
                a.PaymentAmount,
                a.Status,
                a.CreatedDate,
                a.ClientSignedDate,
                a.FreelancerSignedDate,
                a.ExpiredDate,
                CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
                f.ProfilePicture as FreelancerProfilePic,
                c.CompanyName as ClientName
            FROM agreement a
            JOIN freelancer f ON a.FreelancerID = f.FreelancerID
            JOIN client c ON a.ClientID = c.ClientID
            WHERE a.FreelancerID = ?";

    if ($status_filter !== 'all') {
        $sql .= " AND a.Status = ?";
    }

    $sql .= " ORDER BY a.CreatedDate DESC";
}

$stmt = $conn->prepare($sql);

if ($status_filter !== 'all') {
    $stmt->bind_param('is', $user_id, $status_filter);
} else {
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$agreements = [];

while ($row = $result->fetch_assoc()) {
    $agreements[] = $row;
}

$stmt->close();
$conn->close();

// Function to get status badge class
function getStatusClass($status)
{
    switch ($status) {
        case 'to_accept':
            return 'status-pending';
        case 'ongoing':
            return 'status-ongoing';
        case 'completed':
            return 'status-completed';
        case 'declined':
            return 'status-declined';
        case 'cancelled':
            return 'status-cancelled';
        case 'disputed':
            return 'status-disputed';
        default:
            return 'status-unknown';
    }
}

// Function to get status display label
function getStatusLabel($status)
{
    switch ($status) {
        case 'to_accept':
            return 'To Accept';
        case 'ongoing':
            return 'On-going';
        case 'completed':
            return 'Completed';
        case 'declined':
            return 'Declined';
        case 'cancelled':
            return 'Cancelled';
        case 'disputed':
            return 'Disputed';
        default:
            return 'Unknown';
    }
}

// Count agreements by status
$counts = ['all' => 0, 'to_accept' => 0, 'ongoing' => 0, 'completed' => 0, 'declined' => 0, 'cancelled' => 0, 'disputed' => 0];
foreach ($agreements as $agreement) {
    $counts['all']++;
    if (isset($counts[$agreement['Status']])) {
        $counts[$agreement['Status']]++;
    }
}

// Include head (this will output the header and start of body)
include '../_head.php';
?>

<style>
    .header-search {
        display: none !important;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .page-subtitle {
        font-size: 14px;
        color: #7f8c8d;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        margin: 30px 0;
        flex-wrap: wrap;
        border-bottom: 2px solid #e0e6ed;
        padding-bottom: 0;
    }

    .filter-tab {
        padding: 12px 20px;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #7f8c8d;
        position: relative;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
    }

    .filter-tab:hover {
        color: #2c3e50;
    }

    .filter-tab.active {
        color: #1ab394;
        border-bottom-color: #1ab394;
    }

    .tab-count {
        background: #e9ecef;
        color: #2c3e50;
        border-radius: 12px;
        padding: 2px 8px;
        margin-left: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    .filter-tab.active .tab-count {
        background: #1ab394;
        color: white;
    }

    .agreements-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .agreement-card {
        background: white;
        border: 1px solid #e0e6ed;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .agreement-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .card-title {
        font-size: 16px;
        font-weight: 700;
        color: #2c3e50;
        flex: 1;
        margin-bottom: 5px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-ongoing {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    .status-declined {
        background: #f8d7da;
        color: #721c24;
    }

    .status-cancelled {
        background: #e2e3e5;
        color: #383d41;
    }

    .status-disputed {
        background: #f5c6cb;
        color: #721c24;
    }

    .status-unknown {
        background: #e9ecef;
        color: #383d41;
    }

    .card-body {
        margin-bottom: 15px;
    }

    .party-info {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e0e6ed;
    }

    .party-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        color: #2c3e50;
        overflow: hidden;
        flex-shrink: 0;
    }

    .party-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .party-details {
        flex: 1;
    }

    .party-label {
        font-size: 11px;
        color: #7f8c8d;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .party-name {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
    }

    .card-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 15px;
    }

    .detail-item {
        background: #f5f7fa;
        padding: 10px;
        border-radius: 6px;
    }

    .detail-label {
        font-size: 11px;
        color: #7f8c8d;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .detail-value {
        font-size: 14px;
        font-weight: 700;
        color: #2c3e50;
    }

    .detail-value.amount {
        color: #1ab394;
    }

    .detail-value.date {
        font-size: 13px;
        font-weight: 600;
    }

    .card-actions {
        display: flex;
        gap: 8px;
    }

    .btn {
        flex: 1;
        padding: 10px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-view {
        background: #1ab394;
        color: white;
    }

    .btn-view:hover {
        background: #158a74;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(26, 179, 148, 0.3);
    }

    .btn-sign {
        background: #3498db;
        color: white;
    }

    .btn-sign:hover {
        background: #2980b9;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
    }

    .btn-secondary {
        background: #e9ecef;
        color: #2c3e50;
    }

    .btn-secondary:hover {
        background: #dee2e6;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .empty-state-title {
        font-size: 20px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .empty-state-text {
        font-size: 14px;
        color: #7f8c8d;
        margin-bottom: 30px;
    }

    .empty-state-action {
        display: inline-block;
        padding: 10px 20px;
        background: #1ab394;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .empty-state-action:hover {
        background: #158a74;
    }

    .expiration-warning {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
        font-size: 12px;
        color: #856404;
    }

    @media (max-width: 768px) {
        .agreements-grid {
            grid-template-columns: 1fr;
        }

        .filter-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .filter-tab {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .card-details {
            grid-template-columns: 1fr;
        }

        .card-actions {
            flex-direction: column;
        }
    }
</style>

<!-- Main Content -->
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">📋 Agreement Management</h1>
        <p class="page-subtitle">
            <?php echo $user_type === 'client' ? 'Manage your agreements with freelancers' : 'View and manage your project agreements'; ?>
        </p>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button class="filter-tab <?= $status_filter === 'all' ? 'active' : '' ?>" onclick="window.location.href='?status=all'">
            All
            <span class="tab-count"><?= $counts['all'] ?></span>
        </button>
        <button class="filter-tab <?= $status_filter === 'to_accept' ? 'active' : '' ?>" onclick="window.location.href='?status=to_accept'">
            <i class="fas fa-hourglass-start"></i> To Accept
            <span class="tab-count"><?= $counts['to_accept'] ?></span>
        </button>
        <button class="filter-tab <?= $status_filter === 'ongoing' ? 'active' : '' ?>" onclick="window.location.href='?status=ongoing'">
            <i class="fas fa-spinner"></i> On-going
            <span class="tab-count"><?= $counts['ongoing'] ?></span>
        </button>
        <button class="filter-tab <?= $status_filter === 'completed' ? 'active' : '' ?>" onclick="window.location.href='?status=completed'">
            <i class="fas fa-check-circle"></i> Completed
            <span class="tab-count"><?= $counts['completed'] ?></span>
        </button>
        <button class="filter-tab <?= $status_filter === 'declined' ? 'active' : '' ?>" onclick="window.location.href='?status=declined'">
            <i class="fas fa-times-circle"></i> Declined
            <span class="tab-count"><?= $counts['declined'] ?></span>
        </button>
        <button class="filter-tab <?= $status_filter === 'cancelled' ? 'active' : '' ?>" onclick="window.location.href='?status=cancelled'">
            <i class="fas fa-ban"></i> Cancelled
            <span class="tab-count"><?= $counts['cancelled'] ?></span>
        </button>
        <button class="filter-tab <?= $status_filter === 'disputed' ? 'active' : '' ?>" onclick="window.location.href='?status=disputed'">
            <i class="fas fa-exclamation-circle"></i> Disputed
            <span class="tab-count"><?= $counts['disputed'] ?></span>
        </button>
    </div>

    <!-- Agreements Grid -->
    <?php if (count($agreements) > 0): ?>
        <div class="agreements-grid">
            <?php foreach ($agreements as $agreement): ?>
                <div class="agreement-card">
                    <div class="card-header">
                        <div class="card-title"><?= htmlspecialchars($agreement['ProjectTitle']) ?></div>
                        <div class="status-badge <?= getStatusClass($agreement['Status']) ?>">
                            <?= getStatusLabel($agreement['Status']) ?>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Party Info -->
                        <div class="party-info">
                            <div class="party-avatar">
                                <?php
                                if ($user_type === 'client') {
                                    echo strtoupper(substr($agreement['FreelancerName'], 0, 1));
                                } else {
                                    echo strtoupper(substr($agreement['ClientName'], 0, 1));
                                }
                                ?>
                            </div>
                            <div class="party-details">
                                <div class="party-label">
                                    <?= $user_type === 'client' ? 'Freelancer' : 'Client' ?>
                                </div>
                                <div class="party-name">
                                    <?= $user_type === 'client' ? htmlspecialchars($agreement['FreelancerName']) : htmlspecialchars($agreement['ClientName']) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="card-details">
                            <div class="detail-item">
                                <div class="detail-label">Amount</div>
                                <div class="detail-value amount">RM <?= number_format($agreement['PaymentAmount'], 2) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Created</div>
                                <div class="detail-value date"><?= date('M d, Y', strtotime($agreement['CreatedDate'])) ?></div>
                            </div>
                            <?php if ($agreement['ClientSignedDate']): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Client Signed</div>
                                    <div class="detail-value date"><?= date('M d, Y', strtotime($agreement['ClientSignedDate'])) ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($agreement['FreelancerSignedDate']): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Freelancer Signed</div>
                                    <div class="detail-value date"><?= date('M d, Y', strtotime($agreement['FreelancerSignedDate'])) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Expiration Warning -->
                        <?php
                        if ($agreement['ExpiredDate']) {
                            $now = new DateTime();
                            $expiration = new DateTime($agreement['ExpiredDate']);
                            $interval = $now->diff($expiration);
                            $daysUntilExpiry = $interval->days;

                            if ($now > $expiration) {
                                echo '<div class="expiration-warning">⚠️ Agreement has expired</div>';
                            } elseif ($daysUntilExpiry <= 2) {
                                echo '<div class="expiration-warning">⏰ Expires in ' . $daysUntilExpiry . ' day(s)</div>';
                            }
                        }
                        ?>
                    </div>

                    <!-- Actions -->
                    <div class="card-actions">
                        <a href="agreement_view.php?agreement_id=<?= $agreement['AgreementID'] ?>" class="btn btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <?php
                        // Show action buttons based on status and user type
                        if ($agreement['Status'] === 'to_accept' && $user_type === 'freelancer') {
                            echo '<a href="freelancer_agreement_approval.php?agreement_id=' . $agreement['AgreementID'] . '" class="btn btn-sign">';
                            echo '<i class="fas fa-pen"></i> Review & Sign';
                            echo '</a>';
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3 class="empty-state-title">No agreements yet</h3>
            <p class="empty-state-text">
                <?php
                if ($status_filter === 'to_accept') {
                    echo $user_type === 'client' ? 'No pending agreements to send' : 'No agreements awaiting your signature';
                } elseif ($status_filter === 'ongoing') {
                    echo 'No active agreements at the moment';
                } elseif ($status_filter === 'completed') {
                    echo 'No completed agreements yet';
                } elseif ($status_filter === 'declined') {
                    echo 'No declined agreements';
                } elseif ($status_filter === 'cancelled') {
                    echo 'No cancelled agreements';
                } elseif ($status_filter === 'disputed') {
                    echo 'No disputed agreements';
                } else {
                    echo 'Start creating agreements to manage your projects';
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>
</body>

</html>