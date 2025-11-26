<?php
session_start();

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: ../index.php');
    exit();
}

$_title = 'Submit Work';
$freelancer_id = $_SESSION['user_id'];
$agreement_id = isset($_GET['agreement_id']) ? intval($_GET['agreement_id']) : null;

require_once 'config.php';

if (!$agreement_id) {
    $_SESSION['error'] = 'Invalid agreement ID.';
    header('Location: ongoing_projects.php');
    exit();
}

$conn = getDBConnection();

// Fetch agreement details
$sql = "SELECT 
            a.AgreementID,
            a.ProjectTitle,
            a.ProjectDetail,
            a.PaymentAmount,
            a.Status,
            a.ClientID,
            c.CompanyName as ClientName,
            c.Email as ClientEmail
        FROM agreement a
        JOIN client c ON a.ClientID = c.ClientID
        WHERE a.AgreementID = ? AND a.FreelancerID = ? AND a.Status = 'ongoing'";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $agreement_id, $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Agreement not found or you do not have permission to submit work for this project.';
    $stmt->close();
    $conn->close();
    header('Location: ongoing_projects.php');
    exit();
}

$agreement = $result->fetch_assoc();
$stmt->close();
$conn->close();

include '../_head.php';
?>

<style>
    .submit-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
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
        font-size: 1rem;
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

    .project-info-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .info-header h2 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .project-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #28a745;
    }

    .info-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .detail-label {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        font-weight: 600;
    }

    .detail-value {
        font-size: 1rem;
        color: #2c3e50;
        font-weight: 500;
    }

    .project-description {
        background: #f8fafc;
        padding: 15px;
        border-radius: 10px;
        color: #555;
        line-height: 1.6;
    }

    .submit-form-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .form-section {
        margin-bottom: 25px;
    }

    .form-section h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
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

    .form-label.required::after {
        content: '*';
        color: #dc3545;
        margin-left: 4px;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #1ab394;
        box-shadow: 0 0 0 3px rgba(26, 179, 148, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .file-upload-area {
        border: 2px dashed #1ab394;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .file-upload-area:hover {
        background: #e8f5f1;
        border-color: #158a74;
    }

    .file-upload-area.dragover {
        background: #d4edda;
        border-color: #28a745;
    }

    .upload-icon {
        font-size: 3rem;
        color: #1ab394;
        margin-bottom: 15px;
    }

    .upload-text {
        font-size: 1rem;
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .upload-hint {
        font-size: 0.85rem;
        color: #666;
    }

    .file-input {
        display: none;
    }

    .selected-files {
        margin-top: 20px;
    }

    .file-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        background: #f8fafc;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .file-icon {
        font-size: 1.5rem;
    }

    .file-details {
        flex: 1;
    }

    .file-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .file-size {
        font-size: 0.8rem;
        color: #666;
    }

    .file-remove {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .file-remove:hover {
        background: #c82333;
    }

    .form-note {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-left: 4px solid #ffc107;
        padding: 12px 15px;
        border-radius: 6px;
        color: #856404;
        font-size: 0.9rem;
        margin-top: 15px;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e9ecef;
    }

    .btn {
        padding: 14px 30px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }

    .btn-primary {
        background: #1ab394;
        color: white;
        flex: 1;
    }

    .btn-primary:hover:not(:disabled) {
        background: #158a74;
        box-shadow: 0 4px 12px rgba(26, 179, 148, 0.3);
        transform: translateY(-2px);
    }

    .btn-primary:disabled {
        background: #d6d8db;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-secondary {
        background: #f0f1f3;
        color: #333;
    }

    .btn-secondary:hover {
        background: #e0e2e8;
    }

    @media (max-width: 768px) {
        .submit-container {
            padding: 20px;
        }

        .info-details {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="submit-container">
    <a href="ongoing_projects.php" class="back-link">
        ← Back to Ongoing Projects
    </a>

    <div class="page-header">
        <h1>Submit Completed Work</h1>
        <p>Upload your deliverables for client review</p>
    </div>

    <div class="project-info-card">
        <div class="info-header">
            <h2><?= htmlspecialchars($agreement['ProjectTitle']) ?></h2>
            <div class="project-value">RM <?= number_format($agreement['PaymentAmount'], 2) ?></div>
        </div>

        <div class="info-details">
            <div class="detail-item">
                <div class="detail-label">Client</div>
                <div class="detail-value"><?= htmlspecialchars($agreement['ClientName']) ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Agreement ID</div>
                <div class="detail-value">#<?= $agreement['AgreementID'] ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value" style="color: #1ab394; font-weight: 700;">
                    <?= ucfirst($agreement['Status']) ?>
                </div>
            </div>
        </div>

        <div class="project-description">
            <strong>Project Description:</strong><br>
            <?= nl2br(htmlspecialchars($agreement['ProjectDetail'])) ?>
        </div>
    </div>

    <div class="submit-form-card">
        <form id="submitWorkForm" method="POST" action="submit_work_process.php" enctype="multipart/form-data">
            <input type="hidden" name="agreement_id" value="<?= $agreement_id ?>">

            <div class="form-section">
                <h3>📝 Submission Details</h3>
                
                <div class="form-group">
                    <label class="form-label required">Submission Title</label>
                    <input type="text" name="submission_title" class="form-input" 
                           placeholder="e.g., Final Project Deliverables" required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Description / Notes</label>
                    <textarea name="submission_notes" class="form-textarea" 
                              placeholder="Describe what you're submitting, any special instructions, or notes for the client..." required></textarea>
                </div>
            </div>

            <div class="form-section">
                <h3>📎 Upload Files</h3>
                
                <div class="file-upload-area" id="uploadArea">
                    <div class="upload-icon">📁</div>
                    <div class="upload-text">Click to browse or drag & drop files here</div>
                    <div class="upload-hint">
                        Support: ZIP, RAR, PDF, DOC, DOCX, Images, Videos (Max 50MB per file)
                    </div>
                    <input type="file" name="submission_files[]" id="fileInput" class="file-input" multiple 
                           accept=".zip,.rar,.pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.mp4,.mov,.avi">
                </div>

                <div class="selected-files" id="selectedFiles"></div>

                <div class="form-note">
                    <strong>Note:</strong> You can upload multiple files. For large projects, consider zipping files together. All files will be sent to the client for review.
                </div>
            </div>

            <div class="form-actions">
                <a href="ongoing_projects.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span>✅ Submit Work for Review</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const selectedFilesContainer = document.getElementById('selectedFiles');
    const submitBtn = document.getElementById('submitBtn');
    let selectedFiles = [];

    // Click to upload
    uploadArea.addEventListener('click', () => fileInput.click());

    // File input change
    fileInput.addEventListener('change', handleFiles);

    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        addFiles(files);
    });

    function handleFiles(e) {
        const files = Array.from(e.target.files);
        addFiles(files);
    }

    function addFiles(files) {
        files.forEach(file => {
            // Check file size (50MB limit)
            if (file.size > 50 * 1024 * 1024) {
                alert(`File "${file.name}" is too large. Maximum size is 50MB.`);
                return;
            }

            // Check if file already added
            if (selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                alert(`File "${file.name}" is already added.`);
                return;
            }

            selectedFiles.push(file);
        });

        updateFileList();
        updateSubmitButton();
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFileList();
        updateSubmitButton();
    }

    function updateFileList() {
        if (selectedFiles.length === 0) {
            selectedFilesContainer.innerHTML = '';
            return;
        }

        selectedFilesContainer.innerHTML = '<h4 style="margin-bottom: 15px; font-size: 0.95rem; color: #2c3e50;">Selected Files (' + selectedFiles.length + ')</h4>';
        
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            const fileIcon = getFileIcon(file.name);
            const fileSize = formatFileSize(file.size);
            
            fileItem.innerHTML = `
                <div class="file-info">
                    <div class="file-icon">${fileIcon}</div>
                    <div class="file-details">
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${fileSize}</div>
                    </div>
                </div>
                <button type="button" class="file-remove" onclick="removeFile(${index})">Remove</button>
            `;
            
            selectedFilesContainer.appendChild(fileItem);
        });

        // Update file input with selected files
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    function updateSubmitButton() {
        if (selectedFiles.length === 0) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>⚠️ Please upload at least one file</span>';
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>✅ Submit Work for Review</span>';
        }
    }

    function getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            'zip': '🗜️', 'rar': '🗜️',
            'pdf': '📄',
            'doc': '📝', 'docx': '📝',
            'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️',
            'mp4': '🎥', 'mov': '🎥', 'avi': '🎥'
        };
        return icons[ext] || '📎';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Form validation
    document.getElementById('submitWorkForm').addEventListener('submit', function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            alert('Please upload at least one file before submitting.');
            return false;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>⏳ Uploading files...</span>';
    });

    // Initialize
    updateSubmitButton();
</script>

<?php include '../_foot.php'; ?>
