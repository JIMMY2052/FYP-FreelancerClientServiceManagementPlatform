<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../index.php');
    exit();
}

$_title = 'Review Submitted Work';
$client_id = $_SESSION['user_id'];
$submission_id = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : null;

require_once 'config.php';

if (!$submission_id) {
    $_SESSION['error'] = 'Invalid submission ID.';
    header('Location: ongoing_projects.php');
    exit();
}

$conn = getDBConnection();

// Fetch submission details with all related information
$sql = "SELECT 
            ws.SubmissionID,
            ws.AgreementID,
            ws.SubmissionTitle,
            ws.SubmissionNotes,
            ws.Status,
            ws.ReviewNotes,
            ws.SubmittedAt,
            ws.ReviewedAt,
            a.ProjectTitle,
            a.ProjectDetail,
            a.PaymentAmount,
            a.Status as AgreementStatus,
            CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
            f.Email as FreelancerEmail,
            f.FreelancerID
        FROM work_submissions ws
        JOIN agreement a ON ws.AgreementID = a.AgreementID
        JOIN freelancer f ON ws.FreelancerID = f.FreelancerID
        WHERE ws.SubmissionID = ? AND ws.ClientID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $submission_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Submission not found or you do not have permission to review it.';
    $stmt->close();
    $conn->close();
    header('Location: ongoing_projects.php');
    exit();
}

$submission = $result->fetch_assoc();
$stmt->close();

// Fetch all files for this submission
$sql = "SELECT FileID, OriginalFileName, FilePath, FileSize, FileType, UploadedAt 
        FROM submission_files 
        WHERE SubmissionID = ? 
        ORDER BY UploadedAt ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $submission_id);
$stmt->execute();
$files_result = $stmt->get_result();
$files = [];
while ($row = $files_result->fetch_assoc()) {
    $files[] = $row;
}
$stmt->close();
$conn->close();

?>

<style>
    .alert {
        padding: 15px 20px;
        margin: 20px auto;
        max-width: 1200px;
        border-radius: 8px;
        font-weight: 500;
    }
    
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .alert-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .review-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
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

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-left: 15px;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .card-header h2 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .info-grid {
        display: grid;
        gap: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-label {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        font-weight: 600;
    }

    .info-value {
        font-size: 1rem;
        color: #2c3e50;
        font-weight: 500;
    }

    .submission-content {
        background: #f8fafc;
        padding: 20px;
        border-radius: 10px;
        margin-top: 15px;
        line-height: 1.6;
        color: #555;
    }

    .files-section {
        margin-top: 25px;
    }

    .files-section h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .file-list {
        display: grid;
        gap: 12px;
    }

    .file-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        background: #f8fafc;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .file-item:hover {
        border-color: #1ab394;
        background: white;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 15px;
        flex: 1;
    }

    .file-icon {
        font-size: 2rem;
        min-width: 40px;
    }

    .file-details {
        flex: 1;
    }

    .file-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
        margin-bottom: 4px;
    }

    .file-meta {
        font-size: 0.8rem;
        color: #666;
    }

    .file-actions {
        display: flex;
        gap: 10px;
    }

    .btn-download {
        padding: 8px 16px;
        background: #1ab394;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-download:hover {
        background: #158a74;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 179, 148, 0.3);
    }

    .payment-info {
        background: linear-gradient(135deg, #1ab394 0%, #158a74 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        margin-bottom: 20px;
    }

    .payment-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 8px;
    }

    .payment-amount {
        font-size: 2rem;
        font-weight: 700;
    }

    .review-actions {
        margin-top: 30px;
    }

    .review-form {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .review-form h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 0.95rem;
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
        transition: all 0.3s ease;
    }

    .form-textarea:focus {
        outline: none;
        border-color: #1ab394;
        box-shadow: 0 0 0 3px rgba(26, 179, 148, 0.1);
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .btn {
        padding: 14px 30px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        justify-content: center;
    }

    .btn-approve {
        background: #28a745;
        color: white;
    }

    .btn-approve:hover {
        background: #218838;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .btn-reject {
        background: #dc3545;
        color: white;
    }

    .btn-reject:hover {
        background: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .btn-secondary {
        background: #f0f1f3;
        color: #333;
    }

    .btn-secondary:hover {
        background: #e0e2e8;
    }

    .review-history {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 10px;
        padding: 15px;
        margin-top: 20px;
    }

    .review-history h4 {
        font-size: 0.95rem;
        color: #856404;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .review-history p {
        color: #856404;
        margin: 0;
        line-height: 1.6;
    }

    @media (max-width: 968px) {
        .main-grid {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<div class="review-container">
    <a href="ongoing_projects.php" class="back-link">
        ← Back to Ongoing Projects
    </a>

    <div class="page-header">
        <h1>
            Review Submitted Work
            <span class="status-badge status-<?= $submission['Status'] === 'pending_review' ? 'pending' : ($submission['Status'] === 'approved' ? 'approved' : 'rejected') ?>">
                <?= ucwords(str_replace('_', ' ', $submission['Status'])) ?>
            </span>
        </h1>
    </div>

    <div class="main-grid">
        <!-- Left Column: Submission Details -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2>📄 Submission Details</h2>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Submission Title</div>
                        <div class="info-value"><?= htmlspecialchars($submission['SubmissionTitle']) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Project</div>
                        <div class="info-value"><?= htmlspecialchars($submission['ProjectTitle']) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Freelancer</div>
                        <div class="info-value"><?= htmlspecialchars($submission['FreelancerName']) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Submitted On</div>
                        <div class="info-value"><?= date('F j, Y g:i A', strtotime($submission['SubmittedAt'])) ?></div>
                    </div>
                </div>

                <div class="submission-content">
                    <strong>Freelancer's Notes:</strong><br>
                    <?= nl2br(htmlspecialchars($submission['SubmissionNotes'])) ?>
                </div>

                <?php if ($submission['ReviewNotes']): ?>
                    <div class="review-history">
                        <h4>📝 Previous Review Notes</h4>
                        <p><strong>Reviewed on:</strong> <?= date('F j, Y g:i A', strtotime($submission['ReviewedAt'])) ?></p>
                        <p><?= nl2br(htmlspecialchars($submission['ReviewNotes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="files-section">
                <div class="card">
                    <h3>📎 Submitted Files (<?= count($files) ?>)</h3>
                    
                    <div class="file-list">
                        <?php foreach ($files as $file): ?>
                            <div class="file-item">
                                <div class="file-info">
                                    <div class="file-icon">
                                        <?php
                                        $icons = [
                                            'zip' => '🗜️', 'rar' => '🗜️',
                                            'pdf' => '📄',
                                            'doc' => '📝', 'docx' => '📝',
                                            'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️',
                                            'mp4' => '🎥', 'mov' => '🎥', 'avi' => '🎥'
                                        ];
                                        echo $icons[$file['FileType']] ?? '📎';
                                        ?>
                                    </div>
                                    <div class="file-details">
                                        <div class="file-name"><?= htmlspecialchars($file['OriginalFileName']) ?></div>
                                        <div class="file-meta">
                                            <?= number_format($file['FileSize'] / 1024, 2) ?> KB • 
                                            Uploaded <?= date('M j, Y g:i A', strtotime($file['UploadedAt'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <a href="../<?= htmlspecialchars($file['FilePath']) ?>" class="btn-download" download>
                                        ⬇️ Download
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Review Actions -->
        <div>
            <div class="payment-info">
                <div class="payment-label">Project Payment</div>
                <div class="payment-amount">RM <?= number_format($submission['PaymentAmount'], 2) ?></div>
            </div>

            <?php if ($submission['Status'] === 'pending_review'): ?>
                <div class="review-form">
                    <h3>✅ Review Submission</h3>
                    
                    <form id="reviewForm" method="POST" action="review_work_process.php">
                        <input type="hidden" name="submission_id" value="<?= $submission_id ?>">
                        <input type="hidden" name="agreement_id" value="<?= $submission['AgreementID'] ?>">
                        <input type="hidden" name="freelancer_id" value="<?= $submission['FreelancerID'] ?>">
                        <input type="hidden" name="action" id="reviewAction" value="">

                        <div class="form-group">
                            <label class="form-label">Review Notes (Optional)</label>
                            <textarea name="review_notes" id="reviewNotes" class="form-textarea" 
                                      placeholder="Add comments about the submission..."></textarea>
                        </div>

                        <div class="action-buttons">
                            <button type="button" class="btn btn-approve" onclick="submitReview('approve')">
                                ✅ Accept Work
                            </button>
                            <button type="button" class="btn btn-reject" onclick="submitReview('reject')">
                                ❌ Request Revision
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="card">
                    <h3>📋 Review Status</h3>
                    <p style="color: #666; line-height: 1.6;">
                        <?php if ($submission['Status'] === 'approved'): ?>
                            This work has been approved and the payment has been processed.
                        <?php else: ?>
                            This work has been returned for revision. The freelancer will resubmit the updated work.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="card" style="margin-top: 20px;">
                <h3>ℹ️ Project Details</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Agreement ID</div>
                        <div class="info-value">#<?= $submission['AgreementID'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Freelancer Email</div>
                        <div class="info-value"><?= htmlspecialchars($submission['FreelancerEmail']) ?></div>
                    </div>
                </div>
                <div class="submission-content" style="margin-top: 15px;">
                    <strong>Original Project Description:</strong><br>
                    <?= nl2br(htmlspecialchars($submission['ProjectDetail'])) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function submitReview(action) {
    const form = document.getElementById('reviewForm');
    const actionInput = document.getElementById('reviewAction');
    const reviewNotes = document.getElementById('reviewNotes').value.trim();
    
    if (action === 'reject' && !reviewNotes) {
        alert('Please provide review notes when requesting revisions so the freelancer knows what to improve.');
        document.getElementById('reviewNotes').focus();
        return;
    }
    
    const messages = {
        'approve': 'Are you sure you want to accept this work? The payment will be released to the freelancer.',
        'reject': 'Are you sure you want to request revisions? The freelancer will be able to resubmit their work.'
    };
    
    if (confirm(messages[action])) {
        actionInput.value = action;
        form.submit();
    }
}
</script>

<?php include '../_foot.php'; ?>
