<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: /index.php');
    exit();
}

// Check if user is deleted
require_once 'checkUserStatus.php';

$_title = 'My Jobs - WorkSnyc';
require_once 'config.php';

$clientID = $_SESSION['user_id'];
$newJobID = $_SESSION['new_job_id'] ?? null;
$conn = getDBConnection();

// Fetch all jobs for this client
$stmt = $conn->prepare("SELECT JobID, Title, Description, Budget, Deadline, Status, PostDate FROM job WHERE ClientID = ? AND Status != 'deleted' ORDER BY PostDate DESC");
$stmt->bind_param("i", $clientID);
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="container">
    <div class="my-jobs-header">
        <h1>My Jobs</h1>
        <a href="/job/create/createJob.php" class="btn-primary">Post New Job</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($jobs)): ?>
        <div class="no-jobs">
            <p>You haven't posted any jobs yet.</p>
            <a href="/job/create/createJob.php" class="btn-primary">Post Your First Job</a>
        </div>
    <?php else: ?>
        <div class="jobs-list">
            <?php foreach ($jobs as $job): ?>
                <div class="job-card <?php echo ($job['JobID'] == $newJobID) ? 'new-job' : ''; ?>">
                    <div class="job-card-header">
                        <div class="job-info">
                            <h2 class="job-title"><?php echo htmlspecialchars($job['Title']); ?></h2>
                            <p class="job-id">Job ID: #<?php echo $job['JobID']; ?></p>
                        </div>
                        <span class="job-status status-<?php echo strtolower($job['Status']); ?>">
                            <?php echo ucfirst($job['Status']); ?>
                        </span>
                    </div>

                    <div class="job-card-body">
                        <p class="job-description">
                            <strong>Description:</strong> <?php echo htmlspecialchars($job['Description']); ?>
                        </p>

                        <div class="job-details">
                            <div class="detail-item">
                                <span class="label">Budget:</span>
                                <span class="value">$<?php echo number_format($job['Budget'], 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Posted:</span>
                                <span class="value"><?php echo date('M d, Y H:i', strtotime($job['PostDate'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Deadline:</span>
                                <span class="value"><?php echo date('M d, Y', strtotime($job['Deadline'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="job-card-footer">
                        <a href="/page/job/client_job_details.php?id=<?php echo $job['JobID']; ?>" class="btn-small">View Details</a>
                        <a href="/page/job/editJob.php?id=<?php echo $job['JobID']; ?>" class="btn-small">Edit</a>
                        <a href="/page/job/deleteJob.php?id=<?php echo $job['JobID']; ?>" class="btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this job?');">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
include '../_foot.php';
?>

<style>
    /* My Jobs Page */
    .my-jobs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .my-jobs-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #333;
        margin: 0;
    }

    .jobs-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .job-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .job-card:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .job-card.new-job {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.02) 0%, rgba(40, 167, 69, 0.05) 100%);
        border: 2px solid #28a745;
    }

    .job-card.new-job::before {
        content: "✓ NEW";
        position: absolute;
        top: 15px;
        right: 15px;
        background: #28a745;
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .job-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 1px solid #e9ecef;
        position: relative;
    }

    .job-info {
        flex: 1;
    }

    .job-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 6px 0;
    }

    .job-id {
        font-size: 0.8rem;
        color: #adb5bd;
        margin: 0;
        font-weight: 500;
    }

    .job-status {
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-completed {
        background: #cfe2ff;
        color: #084298;
        border: 1px solid #b6d4fe;
    }

    .status-closed {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .job-card-body {
        padding: 25px;
    }

    .job-description {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0 0 20px 0;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .job-description strong {
        color: #333;
        font-weight: 600;
    }

    .job-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-item .label {
        font-size: 0.8rem;
        color: #adb5bd;
        font-weight: 600;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .detail-item .value {
        font-size: 1rem;
        color: #2c3e50;
        font-weight: 700;
    }

    .job-card-footer {
        display: flex;
        gap: 10px;
        padding: 15px 25px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        justify-content: flex-end;
    }

    .btn-small {
        background: rgb(159, 232, 112);
        color: #333;
        padding: 12px 24px;
        border-radius: 20px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: inline-block;
    }

    .btn-small:hover {
        background: rgb(140, 210, 90);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(159, 232, 112, 0.4);
    }

    .btn-small.btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-small.btn-danger:hover {
        background: #c82333;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    .no-jobs {
        text-align: center;
        padding: 80px 40px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px;
        border: 2px dashed #dee2e6;
    }

    .no-jobs p {
        font-size: 1.15rem;
        color: #666;
        margin-bottom: 25px;
        font-weight: 500;
    }

    .no-jobs .btn-primary {
        background: rgb(159, 232, 112);
        color: #333;
        padding: 14px 36px;
        font-size: 0.95rem;
        border-radius: 20px;
    }

    .no-jobs .btn-primary:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 6px 16px rgba(159, 232, 112, 0.4);
    }

    /* Alerts */
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-success::before {
        content: "✓";
        font-weight: 700;
        font-size: 1.2rem;
    }

    .alert-error {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .alert-error::before {
        content: "✕";
        font-weight: 700;
        font-size: 1.2rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .my-jobs-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .my-jobs-header h1 {
            font-size: 1.6rem;
        }

        .job-card-header {
            flex-direction: column;
            gap: 12px;
        }

        .job-status {
            align-self: flex-start;
        }

        .job-details {
            grid-template-columns: 1fr;
        }

        .job-card-footer {
            flex-wrap: wrap;
        }

        .btn-small {
            flex: 1;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .my-jobs-header h1 {
            font-size: 1.4rem;
        }

        .job-title {
            font-size: 1.1rem;
        }

        .job-card-body {
            padding: 15px;
        }

        .job-card-footer {
            padding: 10px 15px;
        }

        .btn-small {
            padding: 10px 16px;
            font-size: 0.85rem;
        }
    }
</style>