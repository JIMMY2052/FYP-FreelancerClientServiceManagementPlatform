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
$valid_statuses = ['all', 'to_accept', 'ongoing', 'completed', 'declined', 'cancelled', 'disputed', 'expired'];

if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'all';
}

// Build query based on user type
$conn = getDBConnection();

// Update expired agreements - check if to_accept agreements have passed their expiration date
$update_expired_sql = "UPDATE agreement 
                       SET Status = 'expired' 
                       WHERE Status = 'to_accept' 
                       AND ExpiredDate IS NOT NULL 
                       AND ExpiredDate < NOW()";
$conn->query($update_expired_sql);

// First, fetch ALL agreements (without status filter) to count by status
if ($user_type === 'client') {
    $count_sql = "SELECT a.Status
                FROM agreement a
                WHERE a.ClientID = ?";
} else {
    $count_sql = "SELECT a.Status
                FROM agreement a
                WHERE a.FreelancerID = ?";
}

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param('i', $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$all_agreements_for_count = [];

while ($row = $count_result->fetch_assoc()) {
    $all_agreements_for_count[] = $row;
}

$count_stmt->close();

// Now fetch filtered agreements for display
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
                a.DeliveryDate,
                a.CompleteDate,
                a.ExpiredDate,
                a.agreeementPath,
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
                a.DeliveryDate,
                a.CompleteDate,
                a.ExpiredDate,
                a.agreeementPath,
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
        case 'expired':
            return 'status-expired';
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
        case 'expired':
            return 'Expired';
        default:
            return 'Unknown';
    }
}

// Count agreements by status (from ALL agreements, not filtered)
$counts = ['all' => 0, 'to_accept' => 0, 'ongoing' => 0, 'completed' => 0, 'declined' => 0, 'cancelled' => 0, 'disputed' => 0, 'expired' => 0];
foreach ($all_agreements_for_count as $agreement) {
    $counts['all']++;
    if (isset($counts[$agreement['Status']])) {
        $counts[$agreement['Status']]++;
    }
}

// Include head (this will output the header and start of body)

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

    .status-expired {
        background: #ccc;
        color: #555;
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

    .btn-decline {
        background: #e74c3c;
        color: white;
    }

    .btn-decline:hover {
        background: #c0392b;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
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

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-dialog {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #f8d7da;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 24px;
    }

    .modal-title {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }

    .modal-body {
        margin-bottom: 25px;
        color: #7f8c8d;
        font-size: 14px;
        line-height: 1.6;
    }

    .modal-footer {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .modal-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .modal-btn-cancel {
        background: #e9ecef;
        color: #2c3e50;
    }

    .modal-btn-cancel:hover {
        background: #dee2e6;
    }

    .modal-btn-confirm {
        background: #e74c3c;
        color: white;
    }

    .modal-btn-confirm:hover {
        background: #c0392b;
    }

    .btn-dispute {
        background: #e74c3c;
        color: white;
    }

    .btn-dispute:hover {
        background: #c0392b;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
    }

    /* Dispute Modal Styles */
    .dispute-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .dispute-modal-overlay.active {
        display: flex;
    }

    .dispute-modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 500px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
        max-height: 90vh;
        overflow-y: auto;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dispute-modal-header {
        margin-bottom: 25px;
    }

    .dispute-modal-title {
        font-size: 20px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 8px;
    }

    .dispute-modal-subtitle {
        font-size: 13px;
        color: #7f8c8d;
        margin: 0;
    }

    .dispute-form-group {
        margin-bottom: 20px;
    }

    .dispute-form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .dispute-form-select,
    .dispute-form-textarea,
    .dispute-form-file {
        width: 100%;
        padding: 12px;
        border: 1.5px solid #e0e6ed;
        border-radius: 6px;
        font-size: 13px;
        font-family: inherit;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .dispute-form-select:focus,
    .dispute-form-textarea:focus,
    .dispute-form-file:focus {
        outline: none;
        border-color: #1ab394;
        box-shadow: 0 0 0 3px rgba(26, 179, 148, 0.1);
    }

    .dispute-form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .file-upload-area {
        border: 2px dashed #e0e6ed;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: #f9fafb;
        cursor: pointer;
    }

    .file-upload-area:hover {
        border-color: #1ab394;
        background: #f0f9f7;
    }

    .file-upload-area.dragover {
        border-color: #1ab394;
        background: #e8f8f4;
        transform: scale(1.02);
    }

    .file-upload-icon {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .file-upload-text {
        font-size: 13px;
        color: #7f8c8d;
        margin: 0;
    }

    .file-upload-hint {
        font-size: 12px;
        color: #95a5a6;
        margin-top: 5px;
    }

    .file-name-display {
        margin-top: 10px;
        padding: 10px;
        background: #d4edda;
        color: #155724;
        border-radius: 6px;
        font-size: 12px;
        display: none;
    }

    .dispute-modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 25px;
    }

    .dispute-modal-btn {
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .dispute-modal-btn-cancel {
        background: #e9ecef;
        color: #2c3e50;
    }

    .dispute-modal-btn-cancel:hover {
        background: #dee2e6;
    }

    .dispute-modal-btn-submit {
        background: #e74c3c;
        color: white;
    }

    .dispute-modal-btn-submit:hover {
        background: #c0392b;
        transform: translateY(-1px);
    }

    .dispute-modal-btn-submit:disabled {
        background: #bdc3c7;
        cursor: not-allowed;
        transform: none;
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

<script>
    // Decline agreement function
    function declineAgreement(agreementId) {
        showDeclineModal(agreementId);
    }

    // Show decline confirmation modal
    function showDeclineModal(agreementId) {
        const modal = document.getElementById('declineModal');
        const confirmBtn = document.getElementById('confirmDeclineBtn');

        // Store agreement ID in the button's data attribute
        confirmBtn.setAttribute('data-agreement-id', agreementId);

        modal.classList.add('active');
    }

    // Close modal
    function closeDeclineModal() {
        const modal = document.getElementById('declineModal');
        modal.classList.remove('active');
    }

    // Confirm decline action
    function confirmDecline(agreementId) {
        closeDeclineModal();

        console.log('Declining agreement ID:', agreementId);

        fetch('decline_agreement.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    agreement_id: agreementId
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    alert('Agreement declined successfully. Funds have been refunded to the client.');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to decline agreement'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while declining the agreement. Please try again.');
            });
    }

    // Close modal when clicking overlay
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('declineModal');
        const confirmBtn = document.getElementById('confirmDeclineBtn');

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeDeclineModal();
                }
            });
        }

        // Add click event listener to confirm button
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                const agreementId = this.getAttribute('data-agreement-id');
                if (agreementId) {
                    confirmDecline(parseInt(agreementId));
                }
            });
        }

        updateExpirationTimes();
        setInterval(updateExpirationTimes, 2 * 60 * 1000);
    });

    // Update expiration times every 2 minutes (120000 milliseconds)
    function updateExpirationTimes() {
        const expirationElements = document.querySelectorAll('.expiration-warning[data-expiry-date]');

        expirationElements.forEach(function(element) {
            const expiryDate = new Date(element.getAttribute('data-expiry-date'));
            const now = new Date();
            const timeDiff = expiryDate - now;

            if (timeDiff <= 0) {
                element.innerHTML = '⚠️ Agreement has expired';
                element.removeAttribute('data-expiry-date');
            } else {
                const hoursRemaining = Math.floor(timeDiff / (1000 * 60 * 60));
                const minutesRemaining = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));

                const hoursSpan = element.querySelector('.hours-remaining');
                const minutesSpan = element.querySelector('.minutes-remaining');
                if (hoursSpan) {
                    hoursSpan.textContent = hoursRemaining;
                }
                if (minutesSpan) {
                    minutesSpan.textContent = minutesRemaining;
                }
            }
        });
    }

    // ===== DISPUTE MODAL FUNCTIONS =====
    let currentDisputeAgreementId = null;

    function openDisputeModal(agreementId, projectTitle) {
        currentDisputeAgreementId = agreementId;
        document.getElementById('disputeProjectTitle').value = projectTitle;
        document.getElementById('disputeReason').value = '';
        document.getElementById('disputeDetails').value = '';
        document.getElementById('evidenceFile').value = '';
        document.getElementById('fileNameDisplay').style.display = 'none';
        document.getElementById('disputeForm').reset();
        document.getElementById('disputeModal').classList.add('active');
    }

    function closeDisputeModal() {
        document.getElementById('disputeModal').classList.remove('active');
        currentDisputeAgreementId = null;
    }

    // File upload handling
    const fileUploadArea = document.getElementById('fileUploadArea');
    const evidenceFile = document.getElementById('evidenceFile');
    const fileNameDisplay = document.getElementById('fileNameDisplay');

    if (fileUploadArea && evidenceFile) {
        fileUploadArea.addEventListener('click', function() {
            evidenceFile.click();
        });

        evidenceFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                const fileSize = (this.files[0].size / 1024).toFixed(2);
                fileNameDisplay.innerHTML = `✓ ${fileName} (${fileSize} KB)`;
                fileNameDisplay.style.display = 'block';
            }
        });

        // Drag and drop
        fileUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                evidenceFile.files = e.dataTransfer.files;
                const event = new Event('change', {
                    bubbles: true
                });
                evidenceFile.dispatchEvent(event);
            }
        });
    }

    function updateReasonText() {
        const reason = document.getElementById('disputeReason').value;
        const detailsField = document.getElementById('disputeDetails');

        if (reason === 'Other') {
            detailsField.placeholder = 'Please explain the issue in detail...';
        } else {
            detailsField.placeholder = 'Provide additional context or evidence for this issue...';
        }
    }

    function handleDisputeSubmit(event) {
        event.preventDefault();

        const reason = document.getElementById('disputeReason').value;
        const reasonText = document.getElementById('disputeDetails').value;

        if (!reason || !reasonText) {
            alert('Please fill in all required fields');
            return;
        }

        if (!currentDisputeAgreementId) {
            alert('Error: Agreement ID not found');
            return;
        }

        const submitBtn = document.getElementById('disputeSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Filing Dispute...';

        const formData = new FormData();
        formData.append('agreement_id', currentDisputeAgreementId);
        formData.append('reason', reason);
        formData.append('reason_text', reasonText);

        if (document.getElementById('evidenceFile').files.length > 0) {
            formData.append('evidence_file', document.getElementById('evidenceFile').files[0]);
        }

        fetch('file_dispute.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Error filing dispute');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Dispute filed successfully! Our admin team will review it shortly.');
                    closeDisputeModal();
                    // Reload page to show updated status
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + (data.message || 'Failed to file dispute'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'File Dispute';
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'File Dispute';
            });
    }

    // Close dispute modal when clicking outside
    document.getElementById('disputeModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDisputeModal();
        }
    });
</script>

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

<!-- Main Content -->
<div class="main-content">
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
        <button class="filter-tab <?= $status_filter === 'disputed' ? 'active' : '' ?>" onclick="window.location.href='?status=disputed'">
            <i class="fas fa-exclamation-circle"></i> Disputed
            <span class="tab-count"><?= $counts['disputed'] ?></span>
        </button>
        <button class="filter-tab <?= $status_filter === 'expired' ? 'active' : '' ?>" onclick="window.location.href='?status=expired'">
            <i class="fas fa-clock"></i> Expired
            <span class="tab-count"><?= $counts['expired'] ?></span>
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
                                <div class="detail-value date"><?= date('M d, Y h:i A', strtotime($agreement['CreatedDate'])) ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Client Signed</div>
                                <div class="detail-value date"><?= $agreement['ClientSignedDate'] ? date('M d, Y h:i A', strtotime($agreement['ClientSignedDate'])) : '-' ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Freelancer Signed</div>
                                <div class="detail-value date"><?= $agreement['FreelancerSignedDate'] ? date('M d, Y h:i A', strtotime($agreement['FreelancerSignedDate'])) : '-' ?></div>
                            </div>
                            <?php if ($agreement['Status'] === 'ongoing'): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Delivery Date</div>
                                    <div class="detail-value date"><?= $agreement['DeliveryDate'] ? date('M d, Y', strtotime($agreement['DeliveryDate'])) : '-' ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($agreement['Status'] === 'completed'): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Completed Date</div>
                                    <div class="detail-value date"><?= $agreement['CompleteDate'] ? date('M d, Y', strtotime($agreement['CompleteDate'])) : '-' ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Delivery Deadline Warning (for ongoing agreements) -->
                        <?php
                        if ($agreement['Status'] === 'ongoing' && $agreement['DeliveryDate']) {
                            $now = new DateTime();
                            $delivery = new DateTime($agreement['DeliveryDate']);
                            $interval = $now->diff($delivery);
                            $hoursUntilDelivery = ($interval->days * 24) + $interval->h;
                            $minutesUntilDelivery = $interval->i;

                            if ($now > $delivery) {
                                echo '<div class="expiration-warning">⚠️ Delivery deadline has passed</div>';
                            } elseif ($hoursUntilDelivery <= 48) {
                                echo '<div class="expiration-warning" data-expiry-date="' . $agreement['DeliveryDate'] . '">⏰ Delivery due in <span class="hours-remaining">' . $hoursUntilDelivery . '</span>h <span class="minutes-remaining">' . $minutesUntilDelivery . '</span>m</div>';
                            }
                        }
                        ?>

                        <!-- Expiration Warning (for to_accept agreements) -->
                        <?php
                        if ($agreement['Status'] === 'to_accept' && $agreement['ExpiredDate']) {
                            $now = new DateTime();
                            $expiration = new DateTime($agreement['ExpiredDate']);
                            $interval = $now->diff($expiration);
                            $hoursUntilExpiry = ($interval->days * 24) + $interval->h;
                            $minutesUntilExpiry = $interval->i;

                            if ($now > $expiration) {
                                echo '<div class="expiration-warning">⚠️ Agreement signature deadline has expired</div>';
                            } elseif ($hoursUntilExpiry <= 24) {
                                echo '<div class="expiration-warning" data-expiry-date="' . $agreement['ExpiredDate'] . '">⏰ This agreement will expires in <span class="hours-remaining">' . $hoursUntilExpiry . '</span>h <span class="minutes-remaining">' . $minutesUntilExpiry . '</span>m</div>';
                            }
                        }
                        ?>
                    </div>

                    <!-- Actions -->
                    <div class="card-actions">
                        <a href="<?= htmlspecialchars($agreement['agreeementPath']) ?>" target="_blank" class="btn btn-view">
                            <i class="fas fa-eye"></i> View PDF
                        </a>
                        <?php
                        // Show action buttons based on status and user type
                        if ($agreement['Status'] === 'to_accept' && $user_type === 'freelancer') {
                            echo '<a href="freelancer_agreement_approval.php?agreement_id=' . $agreement['AgreementID'] . '" class="btn btn-sign">';
                            echo '<i class="fas fa-pen"></i> Review & Sign';
                            echo '</a>';
                            echo '<button type="button" class="btn btn-decline" onclick="declineAgreement(' . $agreement['AgreementID'] . ')">';
                            echo '<i class="fas fa-times"></i> Decline';
                            echo '</button>';
                        }

                        // Show dispute button for ongoing agreements
                        if ($agreement['Status'] === 'ongoing') {
                            echo '<button type="button" class="btn btn-dispute" onclick="openDisputeModal(' . $agreement['AgreementID'] . ', \'' . htmlspecialchars(str_replace("'", "\\'", $agreement['ProjectTitle']), ENT_QUOTES) . '\')">';
                            echo '<i class="fas fa-exclamation-triangle"></i> File Dispute';
                            echo '</button>';
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
                } elseif ($status_filter === 'expired') {
                    echo 'No expired agreements';
                } else {
                    echo 'Start creating agreements to manage your projects';
                }
                ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Decline Confirmation Modal -->
<div class="modal-overlay" id="declineModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-icon">⚠️</div>
            <h2 class="modal-title">Decline Agreement</h2>
        </div>
        <div class="modal-body">
            Are you sure you want to decline this agreement? This action cannot be undone and the client will be notified.
        </div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn-cancel" onclick="closeDeclineModal()">
                Cancel
            </button>
            <button class="modal-btn modal-btn-confirm" id="confirmDeclineBtn">
                Yes, Decline
            </button>
        </div>
    </div>
</div>

<!-- File Dispute Modal -->
<div class="dispute-modal-overlay" id="disputeModal">
    <div class="dispute-modal-content">
        <div class="dispute-modal-header">
            <h2 class="dispute-modal-title">⚠️ File a Dispute</h2>
            <p class="dispute-modal-subtitle">Report an issue with this agreement for admin review</p>
        </div>

        <form id="disputeForm" onsubmit="handleDisputeSubmit(event)">
            <div class="dispute-form-group">
                <label class="dispute-form-label">Project</label>
                <input type="text" id="disputeProjectTitle" readonly style="background: #f5f7fa; cursor: not-allowed;">
            </div>

            <div class="dispute-form-group">
                <label class="dispute-form-label">Dispute Reason *</label>
                <select name="reason" id="disputeReason" class="dispute-form-select" required onchange="updateReasonText()">
                    <option value="">-- Select a reason --</option>
                    <option value="Non-delivery of work">Non-delivery of work</option>
                    <option value="Poor quality work">Poor quality work</option>
                    <option value="Incomplete deliverables">Incomplete deliverables</option>
                    <option value="Missed deadline">Missed deadline</option>
                    <option value="Non-payment">Non-payment</option>
                    <option value="Unprofessional conduct">Unprofessional conduct</option>
                    <option value="Breach of agreement terms">Breach of agreement terms</option>
                    <option value="Other">Other (specify below)</option>
                </select>
            </div>

            <div class="dispute-form-group">
                <label class="dispute-form-label">Additional Details *</label>
                <textarea name="reason_text" id="disputeDetails" class="dispute-form-textarea" placeholder="Provide detailed explanation of the issue..." required></textarea>
                <small style="color: #7f8c8d; display: block; margin-top: 5px;">Please provide as much detail as possible to help our team resolve this issue</small>
            </div>

            <div class="dispute-form-group">
                <label class="dispute-form-label">Upload Evidence (Optional)</label>
                <div class="file-upload-area" id="fileUploadArea">
                    <div class="file-upload-icon">📎</div>
                    <p class="file-upload-text">Click or drag files here to upload</p>
                    <p class="file-upload-hint">Accepted: Images (JPG, PNG, GIF) and PDF | Max: 5MB</p>
                    <input type="file" id="evidenceFile" name="evidence_file" accept="image/jpeg,image/png,image/gif,application/pdf" style="display: none;">
                </div>
                <div class="file-name-display" id="fileNameDisplay"></div>
            </div>

            <div class="dispute-modal-buttons">
                <button type="button" class="dispute-modal-btn dispute-modal-btn-cancel" onclick="closeDisputeModal()">
                    Cancel
                </button>
                <button type="submit" class="dispute-modal-btn dispute-modal-btn-submit" id="disputeSubmitBtn">
                    File Dispute
                </button>
            </div>
        </form>
    </div>
</div>
</div>

</body>

</html>