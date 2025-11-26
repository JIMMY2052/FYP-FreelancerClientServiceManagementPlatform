<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

$_title = 'Ongoing Projects';
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

require_once 'config.php';

$conn = getDBConnection();

// Fetch ongoing projects based on user type
if ($user_type === 'client') {
    // Client sees projects where they hired freelancers
    $sql = "SELECT 
                a.AgreementID,
                a.ProjectTitle,
                a.ProjectDetail,
                a.PaymentAmount,
                a.DeliveryTime,
                a.ClientSignedDate,
                a.FreelancerSignedDate,
                a.Status,
                a.FreelancerName,
                f.FreelancerID,
                f.Email as FreelancerEmail,
                f.ProfilePicture,
                f.Rating,
                e.EscrowID,
                e.Amount as EscrowAmount,
                e.Status as EscrowStatus,
                ws.SubmissionID,
                ws.Status as SubmissionStatus,
                ws.SubmittedAt
            FROM agreement a
            JOIN freelancer f ON a.FreelancerID = f.FreelancerID
            LEFT JOIN escrow e ON e.OrderID = a.AgreementID
            LEFT JOIN work_submissions ws ON ws.AgreementID = a.AgreementID AND ws.Status = 'pending_review'
            WHERE a.ClientID = ? AND (a.Status = 'ongoing' OR a.Status = 'pending_review')
            ORDER BY a.FreelancerSignedDate DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
} else {
    // Freelancer sees projects they are working on
    $sql = "SELECT 
                a.AgreementID,
                a.ProjectTitle,
                a.ProjectDetail,
                a.PaymentAmount,
                a.DeliveryTime,
                a.ClientSignedDate,
                a.FreelancerSignedDate,
                a.Status,
                a.ClientName,
                c.ClientID,
                c.Email as ClientEmail,
                c.ProfilePicture,
                e.EscrowID,
                e.Amount as EscrowAmount,
                e.Status as EscrowStatus
            FROM agreement a
            JOIN client c ON a.ClientID = c.ClientID
            LEFT JOIN escrow e ON e.OrderID = a.AgreementID
            WHERE a.FreelancerID = ? AND (a.Status = 'ongoing' OR a.Status = 'pending_review')
            ORDER BY a.FreelancerSignedDate DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$ongoing_projects = [];

while ($row = $result->fetch_assoc()) {
    $ongoing_projects[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
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
            color: #2c3e50;
        }

        .page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 1rem;
            color: #666;
        }

        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            flex: 1;
            min-width: 200px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1ab394;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .project-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .project-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-color: #1ab394;
        }

        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .project-title {
            flex: 1;
        }

        .project-title h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .project-id {
            font-size: 0.85rem;
            color: #999;
        }

        .status-badge {
            background: #1ab394;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .collaborator-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .collaborator-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #1ab394;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .collaborator-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .collaborator-details {
            flex: 1;
        }

        .collaborator-label {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .collaborator-name {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .collaborator-email {
            font-size: 0.85rem;
            color: #666;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 4px;
        }

        .rating-stars {
            color: #fbbf24;
            font-size: 0.9rem;
        }

        .rating-value {
            font-size: 0.85rem;
            color: #666;
        }

        .project-description {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .project-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .meta-item {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
        }

        .meta-label {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .meta-value {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .meta-value.amount {
            color: #1ab394;
            font-size: 1.2rem;
        }

        .escrow-info {
            background: #e8f5f1;
            border: 1px solid #1ab394;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .escrow-icon {
            font-size: 1.2rem;
        }

        .escrow-text {
            flex: 1;
        }

        .escrow-label {
            font-size: 0.75rem;
            color: #0d9c7a;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .escrow-amount {
            font-size: 0.9rem;
            color: #2c3e50;
        }

        .project-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #1ab394;
            color: white;
        }

        .btn-primary:hover {
            background: #158a74;
            box-shadow: 0 4px 12px rgba(26, 179, 148, 0.3);
        }

        .btn-secondary {
            background: #f0f1f3;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e2e8;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }

        .status-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
        }

        .status-ongoing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-pending-review {
            background: #fff3cd;
            color: #856404;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 30px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #1ab394;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            gap: 12px;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }

            .projects-grid {
                grid-template-columns: 1fr;
            }

            .project-meta {
                grid-template-columns: 1fr;
            }

            .stats-bar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php 
    if ($user_type === 'client') {
        include '../includes/client_sidebar.php';
    } else {
        include '../includes/freelancer_sidebar.php';
    }
    ?>

    <div class="page-container">
        <a href="<?= $user_type === 'client' ? 'client_dashboard.php' : 'freelancer_dashboard.php' ?>" class="back-link">
            ← Back to Dashboard
        </a>

        <div class="page-header">
            <h1>🚀 Ongoing Projects</h1>
            <p>Track and manage your active collaborations</p>
        </div>

        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-label">Active Projects</div>
                <div class="stat-value"><?= count($ongoing_projects) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Value</div>
                <div class="stat-value">RM <?= number_format(array_sum(array_column($ongoing_projects, 'PaymentAmount')), 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">In Escrow</div>
                <div class="stat-value">RM <?= number_format(array_sum(array_column($ongoing_projects, 'EscrowAmount')), 2) ?></div>
            </div>
        </div>

        <?php if (empty($ongoing_projects)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No Ongoing Projects</h3>
                <p>You don't have any active projects at the moment.</p>
                <?php if ($user_type === 'client'): ?>
                    <a href="job/browse_job.php" class="btn btn-primary" style="display: inline-block;">Browse Jobs</a>
                <?php else: ?>
                    <a href="job/browse_job.php" class="btn btn-primary" style="display: inline-block;">Find Jobs</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($ongoing_projects as $project): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <div class="project-title">
                                <h3>
                                    <?= htmlspecialchars($project['ProjectTitle']) ?>
                                    <span class="status-indicator status-<?= str_replace('_', '-', strtolower($project['Status'])) ?>">
                                        <?= $project['Status'] === 'pending_review' ? '⏳ Pending Review' : '🚀 In Progress' ?>
                                    </span>
                                </h3>
                                <div class="project-id">Agreement #<?= $project['AgreementID'] ?></div>
                            </div>
                            <div class="status-badge">Ongoing</div>
                        </div>

                        <div class="collaborator-info">
                            <div class="collaborator-avatar">
                                <?php if (!empty($project['ProfilePicture'])): ?>
                                    <img src="<?= htmlspecialchars($project['ProfilePicture']) ?>" alt="Profile">
                                <?php else: ?>
                                    <?php 
                                    $name = $user_type === 'client' ? $project['FreelancerName'] : $project['ClientName'];
                                    echo strtoupper(substr($name, 0, 1)); 
                                    ?>
                                <?php endif; ?>
                            </div>
                            <div class="collaborator-details">
                                <div class="collaborator-label">
                                    <?= $user_type === 'client' ? 'Freelancer' : 'Client' ?>
                                </div>
                                <div class="collaborator-name">
                                    <?= htmlspecialchars($user_type === 'client' ? $project['FreelancerName'] : $project['ClientName']) ?>
                                </div>
                                <div class="collaborator-email">
                                    <?= htmlspecialchars($user_type === 'client' ? $project['FreelancerEmail'] : $project['ClientEmail']) ?>
                                </div>
                                <?php if ($user_type === 'client' && !empty($project['Rating'])): ?>
                                    <div class="rating">
                                        <span class="rating-stars">⭐</span>
                                        <span class="rating-value"><?= number_format($project['Rating'], 1) ?>/5.0</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="project-description">
                            <?= htmlspecialchars($project['ProjectDetail']) ?>
                        </div>

                        <div class="project-meta">
                            <div class="meta-item">
                                <div class="meta-label">Project Value</div>
                                <div class="meta-value amount">RM <?= number_format($project['PaymentAmount'], 2) ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Delivery Time</div>
                                <div class="meta-value"><?= $project['DeliveryTime'] ?> days</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Started On</div>
                                <div class="meta-value">
                                    <?= date('M d, Y', strtotime($project['FreelancerSignedDate'])) ?>
                                </div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Days Elapsed</div>
                                <div class="meta-value">
                                    <?php
                                    $start_date = new DateTime($project['FreelancerSignedDate']);
                                    $now = new DateTime();
                                    $diff = $start_date->diff($now);
                                    echo $diff->days;
                                    ?> days
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($project['EscrowAmount'])): ?>
                            <div class="escrow-info">
                                <div class="escrow-icon">🔒</div>
                                <div class="escrow-text">
                                    <div class="escrow-label">Funds in Escrow</div>
                                    <div class="escrow-amount">
                                        RM <?= number_format($project['EscrowAmount'], 2) ?> 
                                        (<?= ucfirst($project['EscrowStatus']) ?>)
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="project-actions">
                            <a href="agreement_view.php?id=<?= $project['AgreementID'] ?>" class="btn btn-secondary">
                                📄 View Agreement
                            </a>
                            <a href="messages.php?<?= $user_type === 'client' ? 'freelancer_id=' . $project['FreelancerID'] : 'client_id=' . $project['ClientID'] ?>" class="btn btn-primary">
                                💬 Message
                            </a>
                            <?php if ($user_type === 'freelancer' && $project['Status'] === 'ongoing'): ?>
                                <a href="submit_work.php?agreement_id=<?= $project['AgreementID'] ?>" class="btn btn-success">
                                    ✅ Submit Work
                                </a>
                            <?php endif; ?>
                            <?php if ($user_type === 'client' && $project['Status'] === 'pending_review' && !empty($project['SubmissionID'])): ?>
                                <a href="review_work.php?submission_id=<?= $project['SubmissionID'] ?>" class="btn btn-warning">
                                    👀 Review Work
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add any interactive functionality here
        console.log('Ongoing Projects Page Loaded');
    </script>
</body>
</html>
