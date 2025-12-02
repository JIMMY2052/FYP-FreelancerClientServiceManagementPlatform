<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../login.php');
    exit();
}

$_title = 'Job Details - WorkSnyc';
require_once '../config.php';

// Get job ID from URL
$jobID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clientID = $_SESSION['user_id'];

if (!$jobID) {
    $_SESSION['error'] = 'Invalid job ID.';
    header('Location: ../my_jobs.php');
    exit();
}

$conn = getDBConnection();

// Fetch job details - verify it belongs to this client
$sql = "SELECT j.*, 
               (SELECT COUNT(*) FROM job_application WHERE JobID = j.JobID) as application_count,
               (SELECT COUNT(*) FROM job_application WHERE JobID = j.JobID AND Status = 'accepted') as accepted_count
        FROM job j
        WHERE j.JobID = ? AND j.ClientID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $jobID, $clientID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Job not found or you do not have permission to view it.';
    $stmt->close();
    $conn->close();
    header('Location: ../my_jobs.php');
    exit();
}

$job = $result->fetch_assoc();
$stmt->close();

// Fetch job applications
$sql = "SELECT ja.*, 
               CONCAT(f.FirstName, ' ', f.LastName) as freelancer_name,
               f.Email as freelancer_email,
               f.ProfilePicture
        FROM job_application ja
        JOIN freelancer f ON ja.FreelancerID = f.FreelancerID
        WHERE ja.JobID = ?
        ORDER BY ja.AppliedAt DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $jobID);
$stmt->execute();
$applications_result = $stmt->get_result();
$applications = $applications_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch screening questions if any
$sql = "SELECT jq.*, 
               GROUP_CONCAT(jqo.OptionText ORDER BY jqo.DisplayOrder SEPARATOR '|||') as options
        FROM job_question jq
        LEFT JOIN job_question_option jqo ON jq.QuestionID = jqo.QuestionID
        WHERE jq.JobID = ?
        GROUP BY jq.QuestionID
        ORDER BY jq.QuestionID";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $jobID);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = $questions_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

require_once '../../_head.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="my_jobs.php">← Back to My Jobs</a>
    </div>

    <div class="job-details-layout">
        <!-- Main Content -->
        <div class="job-details-main">
            <!-- Job Header -->
            <div class="job-header-card">
                <div class="job-header-top">
                    <div class="job-title-section">
                        <h1 class="job-title"><?= htmlspecialchars($job['Title']) ?></h1>
                        <span class="job-status status-<?= strtolower($job['Status']) ?>">
                            <?= ucfirst($job['Status']) ?>
                        </span>
                    </div>
                    <div class="job-actions">
                        <a href="editJob.php?id=<?= $job['JobID'] ?>" class="btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="deleteJob.php?id=<?= $job['JobID'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this job?');">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>

                <div class="job-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Posted: <?= date('M d, Y', strtotime($job['PostDate'])) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span>Deadline: <?= date('M d, Y', strtotime($job['Deadline'])) ?></span>
                    </div>
                    <?php if (!empty($job['DeliveryTime'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-hourglass-half"></i>
                        <span>Delivery: <?= $job['DeliveryTime'] ?> days</span>
                    </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Budget: RM <?= number_format($job['Budget'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Job Description -->
            <div class="detail-card">
                <h2 class="section-title">Job Description</h2>
                <p class="job-description"><?= nl2br(htmlspecialchars($job['Description'])) ?></p>
            </div>

            <?php if (!empty($questions)): ?>
            <!-- Screening Questions -->
            <div class="detail-card">
                <h2 class="section-title">Screening Questions</h2>
                <div class="questions-list">
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="question-item">
                        <div class="question-header">
                            <span class="question-number">Q<?= $index + 1 ?></span>
                            <span class="question-text"><?= htmlspecialchars($question['QuestionText']) ?></span>
                            <?php if ($question['IsRequired']): ?>
                            <span class="required-badge">Required</span>
                            <?php endif; ?>
                        </div>
                        <div class="question-type">Type: <?= $question['QuestionType'] === 'yes_no' ? 'Yes/No' : 'Multiple Choice' ?></div>
                        <?php if ($question['QuestionType'] === 'multiple_choice' && !empty($question['options'])): ?>
                        <ul class="question-options">
                            <?php foreach (explode('|||', $question['options']) as $option): ?>
                            <li><?= htmlspecialchars($option) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Applications Section -->
            <div class="detail-card">
                <h2 class="section-title">
                    Applications (<?= count($applications) ?>)
                </h2>
                
                <?php if (empty($applications)): ?>
                <div class="no-applications">
                    <i class="fas fa-inbox"></i>
                    <p>No applications received yet.</p>
                </div>
                <?php else: ?>
                <div class="applications-list">
                    <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <div class="applicant-header">
                            <div class="applicant-info">
                                <?php if (!empty($app['ProfilePicture'])): ?>
                                <img src="<?= htmlspecialchars($app['ProfilePicture']) ?>" alt="<?= htmlspecialchars($app['freelancer_name']) ?>" class="applicant-avatar">
                                <?php else: ?>
                                <div class="applicant-avatar-placeholder">
                                    <?= strtoupper(substr($app['freelancer_name'], 0, 2)) ?>
                                </div>
                                <?php endif; ?>
                                <div class="applicant-details">
                                    <h3><?= htmlspecialchars($app['freelancer_name']) ?></h3>
                                    <p class="applicant-email"><?= htmlspecialchars($app['freelancer_email']) ?></p>
                                </div>
                            </div>
                            <div class="application-status">
                                <span class="status-badge status-<?= strtolower($app['Status']) ?>">
                                    <?= ucfirst($app['Status']) ?>
                                </span>
                                <div class="application-date">
                                    <?= date('M d, Y', strtotime($app['AppliedAt'])) ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($app['ProposedBudget'])): ?>
                        <div class="application-proposal">
                            <div class="proposal-item">
                                <span class="label">Proposed Budget:</span>
                                <span class="value">RM <?= number_format($app['ProposedBudget'], 2) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($app['CoverLetter'])): ?>
                        <div class="application-cover-letter">
                            <strong>Cover Letter:</strong>
                            <p><?= nl2br(htmlspecialchars($app['CoverLetter'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="application-actions">
                            <a href="../view_profile.php?freelancer_id=<?= $app['FreelancerID'] ?>" class="btn-view-profile">
                                View Profile
                            </a>
                            <?php if ($app['Status'] === 'pending'): ?>
                            <a href="../applications.php?application_id=<?= $app['ApplicationID'] ?>" class="btn-review">
                                Review Application
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="job-sidebar">
            <!-- Stats Card -->
            <div class="stats-card">
                <h3>Job Statistics</h3>
                <div class="stat-item">
                    <div class="stat-value"><?= $job['application_count'] ?></div>
                    <div class="stat-label">Total Applications</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $job['accepted_count'] ?></div>
                    <div class="stat-label">Accepted</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">RM <?= number_format($job['Budget'], 2) ?></div>
                    <div class="stat-label">Budget</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="actions-card">
                <h3>Quick Actions</h3>
                <a href="../applications.php?job_id=<?= $job['JobID'] ?>" class="action-link">
                    <i class="fas fa-list"></i>
                    View All Applications
                </a>
                <a href="../messages.php?job_id=<?= $job['JobID'] ?>" class="action-link">
                    <i class="fas fa-envelope"></i>
                    Messages
                </a>
                <a href="../ongoing_projects.php" class="action-link">
                    <i class="fas fa-tasks"></i>
                    Ongoing Projects
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.breadcrumb {
    margin-bottom: 20px;
}

.breadcrumb a {
    color: #666;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: color 0.3s;
}

.breadcrumb a:hover {
    color: rgb(159, 232, 112);
}

.job-details-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 25px;
}

.job-details-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.job-header-card,
.detail-card,
.stats-card,
.actions-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Job Header */
.job-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.job-title-section {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
}

.job-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.job-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
    background: #cfe2ff;
    color: #084298;
}

.status-closed {
    background: #f8d7da;
    color: #842029;
}

.job-actions {
    display: flex;
    gap: 10px;
}

.btn-edit,
.btn-delete {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-edit {
    background: rgb(159, 232, 112);
    color: #2c3e50;
    border: 2px solid rgb(159, 232, 112);
}

.btn-edit:hover {
    background: rgb(140, 210, 90);
    border-color: rgb(140, 210, 90);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-delete {
    background: #dc3545;
    color: white;
    border: 2px solid #dc3545;
}

.btn-delete:hover {
    background: #c82333;
    border-color: #c82333;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.job-meta {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}

.meta-item i {
    color: rgb(159, 232, 112);
}

/* Section */
.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

.job-description {
    color: #555;
    line-height: 1.7;
    font-size: 0.95rem;
    white-space: pre-wrap;
}

/* Questions */
.questions-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.question-item {
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.question-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.question-number {
    background: rgb(159, 232, 112);
    color: #2c3e50;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.85rem;
}

.question-text {
    flex: 1;
    font-weight: 600;
    color: #2c3e50;
}

.required-badge {
    background: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.question-type {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 8px;
}

.question-options {
    margin: 8px 0 0 20px;
    color: #555;
    font-size: 0.9rem;
}

/* Applications */
.no-applications {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.no-applications i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.applications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.application-card {
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.applicant-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.applicant-info {
    display: flex;
    gap: 15px;
    flex: 1;
}

.applicant-avatar,
.applicant-avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.applicant-avatar-placeholder {
    background: rgb(159, 232, 112);
    color: #2c3e50;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.applicant-details h3 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 1.1rem;
}

.applicant-email {
    color: #666;
    font-size: 0.85rem;
    margin: 0 0 10px 0;
}

.applicant-skills {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.skill-badge {
    background: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    color: #666;
    border: 1px solid #dee2e6;
}

.application-status {
    text-align: right;
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 5px;
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

.application-date {
    font-size: 0.8rem;
    color: #999;
}

.application-proposal {
    margin: 15px 0;
    padding: 12px;
    background: white;
    border-radius: 6px;
}

.proposal-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.proposal-item .label {
    color: #666;
    font-size: 0.9rem;
}

.proposal-item .value {
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.1rem;
}

.application-cover-letter {
    margin: 15px 0;
    padding: 12px;
    background: white;
    border-radius: 6px;
    font-size: 0.9rem;
}

.application-cover-letter strong {
    color: #2c3e50;
    display: block;
    margin-bottom: 8px;
}

.application-cover-letter p {
    color: #555;
    line-height: 1.6;
    margin: 0;
}

.application-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-view-profile,
.btn-review {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-view-profile {
    background: white;
    color: #2c3e50;
    border: 1px solid #dee2e6;
}

.btn-view-profile:hover {
    border-color: rgb(159, 232, 112);
    background: #f8f9fa;
}

.btn-review {
    background: rgb(159, 232, 112);
    color: #2c3e50;
}

.btn-review:hover {
    background: rgb(140, 210, 90);
}

/* Sidebar */
.job-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.stats-card h3,
.actions-card h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 12px;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.8rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.action-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    color: #2c3e50;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 8px;
    transition: all 0.3s;
    font-weight: 500;
}

.action-link:hover {
    background: #f8fafc;
    color: rgb(159, 232, 112);
}

.action-link i {
    width: 20px;
    text-align: center;
    color: rgb(159, 232, 112);
}

@media (max-width: 1024px) {
    .job-details-layout {
        grid-template-columns: 1fr;
    }

    .job-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .job-header-top {
        flex-direction: column;
        gap: 15px;
    }

    .job-title-section {
        flex-direction: column;
        align-items: flex-start;
    }

    .job-meta {
        flex-direction: column;
        gap: 10px;
    }

    .applicant-header {
        flex-direction: column;
        gap: 15px;
    }

    .application-status {
        text-align: left;
    }
}
</style>

<?php require_once '../../_foot.php'; ?>
