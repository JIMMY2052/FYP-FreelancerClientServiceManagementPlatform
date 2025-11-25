<?php
session_start();

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: login.php');
    exit();
}

$_title = 'Review & Sign Agreement';
$freelancer_id = $_SESSION['user_id'];

require_once 'config.php';

// Get agreement ID from URL
$agreement_id = isset($_GET['agreement_id']) ? intval($_GET['agreement_id']) : 0;

if ($agreement_id === 0) {
    header('Location: agreementListing.php');
    exit();
}

// Fetch agreement details
$conn = getDBConnection();
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
            a.DeliveryTime,
            a.ClientSignaturePath,
            a.FreelancerSignaturePath,
            CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
            f.Email as FreelancerEmail,
            c.CompanyName as ClientName,
            c.Email as ClientEmail
        FROM agreement a
        JOIN freelancer f ON a.FreelancerID = f.FreelancerID
        JOIN client c ON a.ClientID = c.ClientID
        WHERE a.AgreementID = ? AND a.FreelancerID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $agreement_id, $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$agreement = $result->fetch_assoc();
$stmt->close();

if (!$agreement) {
    $conn->close();
    header('Location: agreementListing.php');
    exit();
}

// Check if agreement has expired
$now = new DateTime();
$expiration = new DateTime($agreement['ExpiredDate']);
if ($now > $expiration) {
    $agreement['Status'] = 'expired';
}

$conn->close();

// Include head
include '../_head.php';
?>

<style>
    .header-search {
        display: none !important;
    }

    .container {
        max-width: 1000px;
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

    .agreement-section {
        background: white;
        border: 1px solid #e0e6ed;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e0e6ed;
    }

    .agreement-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .detail-group {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 12px;
        text-transform: uppercase;
        color: #7f8c8d;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .detail-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 600;
    }

    .detail-value.amount {
        color: #1ab394;
        font-size: 18px;
    }

    .detail-value.description {
        color: #555;
        font-weight: 400;
        line-height: 1.6;
    }

    .parties-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .party-card {
        background: #f5f7fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #1ab394;
    }

    .party-card.client {
        border-left-color: #3498db;
    }

    .party-title {
        font-size: 13px;
        text-transform: uppercase;
        color: #7f8c8d;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .party-name {
        font-size: 16px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 6px;
    }

    .party-email {
        font-size: 13px;
        color: #7f8c8d;
    }

    .signature-section {
        margin-top: 30px;
    }

    .canvas-container {
        border: 2px solid #e0e6ed;
        border-radius: 8px;
        margin-bottom: 15px;
        background: white;
        position: relative;
    }

    #signatureCanvas {
        display: block;
        cursor: crosshair;
        width: 100%;
        height: 200px;
        background: white;
    }

    .canvas-actions {
        display: flex;
        gap: 10px;
        margin-top: 12px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-primary {
        background: #1ab394;
        color: white;
        flex: 1;
    }

    .btn-primary:hover {
        background: #158a74;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 179, 148, 0.3);
    }

    .btn-secondary {
        background: #e9ecef;
        color: #2c3e50;
        flex: 1;
    }

    .btn-secondary:hover {
        background: #dee2e6;
    }

    .btn-small {
        padding: 8px 16px;
        font-size: 12px;
        flex: none;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    .status-expired {
        background: #f8d7da;
        color: #721c24;
    }

    .signature-info {
        background: #f5f7fa;
        padding: 12px;
        border-radius: 6px;
        font-size: 12px;
        color: #555;
        margin-bottom: 15px;
    }

    .existing-signature {
        border: 1px solid #e0e6ed;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        text-align: center;
        background: #f5f7fa;
    }

    .existing-signature img {
        max-width: 100%;
        max-height: 150px;
        margin: 10px 0;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .pdf-preview-section {
        margin-top: 30px;
    }

    .pdf-preview {
        width: 100%;
        height: 600px;
        border: 1px solid #e0e6ed;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid;
    }

    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border-left-color: #ffc107;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left-color: #28a745;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left-color: #dc3545;
    }

    .loading-spinner {
        display: none;
        text-align: center;
        padding: 20px;
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #1ab394;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 768px) {
        .agreement-details {
            grid-template-columns: 1fr;
        }

        .parties-section {
            grid-template-columns: 1fr;
        }

        .canvas-actions {
            flex-direction: column;
        }

        #signatureCanvas {
            height: 150px;
        }

        .pdf-preview {
            height: 400px;
        }
    }
</style>

<!-- Main Content -->
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">📋 Review & Sign Agreement</h1>
        <p class="page-subtitle">Review the agreement details and sign with your digital signature</p>
    </div>

    <!-- Status Alerts -->
    <?php if ($agreement['Status'] === 'expired'): ?>
        <div class="alert alert-danger">
            ⚠️ This agreement has expired and can no longer be signed. Please contact the client to request a new agreement.
        </div>
    <?php elseif ($agreement['Status'] === 'completed'): ?>
        <div class="alert alert-success">
            ✅ This agreement has been signed by both parties. The contract is now active.
        </div>
    <?php elseif ($agreement['Status'] === 'declined'): ?>
        <div class="alert alert-danger">
            ✋ This agreement has been declined. Please contact the client for more information.
        </div>
    <?php endif; ?>

    <!-- Agreement Details Section -->
    <div class="agreement-section">
        <div class="section-title">Agreement Information</div>

        <div class="status-badge status-<?= $agreement['Status'] ?>">
            <?= ucfirst(str_replace('_', ' ', $agreement['Status'])) ?>
        </div>

        <div class="agreement-details">
            <div class="detail-group">
                <span class="detail-label">Project Title</span>
                <span class="detail-value"><?= htmlspecialchars($agreement['ProjectTitle']) ?></span>
            </div>
            <div class="detail-group">
                <span class="detail-label">Payment Amount</span>
                <span class="detail-value amount">RM <?= number_format($agreement['PaymentAmount'], 2) ?></span>
            </div>
            <div class="detail-group">
                <span class="detail-label">Delivery Time</span>
                <span class="detail-value"><?= htmlspecialchars($agreement['DeliveryTime']) ?></span>
            </div>
            <div class="detail-group">
                <span class="detail-label">Created Date</span>
                <span class="detail-value"><?= date('M d, Y', strtotime($agreement['CreatedDate'])) ?></span>
            </div>
        </div>
    </div>

    <!-- Parties Section -->
    <div class="agreement-section">
        <div class="section-title">Parties Involved</div>

        <div class="parties-section">
            <div class="party-card client">
                <div class="party-title">Client</div>
                <div class="party-name"><?= htmlspecialchars($agreement['ClientName']) ?></div>
                <div class="party-email"><?= htmlspecialchars($agreement['ClientEmail']) ?></div>
            </div>
            <div class="party-card">
                <div class="party-title">Freelancer (You)</div>
                <div class="party-name"><?= htmlspecialchars($agreement['FreelancerName']) ?></div>
                <div class="party-email"><?= htmlspecialchars($agreement['FreelancerEmail']) ?></div>
            </div>
        </div>

        <!-- Signature Section -->
        <?php if ($agreement['Status'] === 'to_accept'): ?>
            <div class="agreement-section">
                <div class="section-title">Your Digital Signature</div>

                <div class="signature-info">
                    💡 Please sign using your mouse or touchscreen. Your signature will be embedded into the agreement PDF document.
                </div>

                <!-- Check if client has already signed -->
                <?php if ($agreement['ClientSignaturePath']): ?>
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin-bottom: 10px; color: #2c3e50; font-weight: 600;">Client's Signature (Already Signed)</h4>
                        <div class="existing-signature">
                            <img src="<?= htmlspecialchars($agreement['ClientSignaturePath']) ?>" alt="Client Signature">
                            <div style="font-size: 12px; color: #7f8c8d; margin-top: 10px;">
                                Signed on <?= date('M d, Y', strtotime($agreement['ClientSignedDate'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 20px;">
                    <h4 style="margin-bottom: 10px; color: #2c3e50; font-weight: 600;">Your Signature</h4>
                    <div class="canvas-container">
                        <canvas id="signatureCanvas"></canvas>
                    </div>
                    <div class="canvas-actions">
                        <button class="btn btn-secondary btn-small" onclick="clearSignature()">
                            <i class="fas fa-eraser"></i> Clear
                        </button>
                    </div>
                </div>

                <form id="signatureForm" method="POST" action="freelancer_agreement_process.php">
                    <input type="hidden" name="agreement_id" value="<?= $agreement['AgreementID'] ?>">
                    <input type="hidden" id="signatureData" name="signature_data" value="">

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary" onclick="return captureSignature()">
                            <i class="fas fa-check-circle"></i> Sign & Submit Agreement
                        </button>
                        <a href="agreementListing.php" class="btn btn-secondary">
                            <i class="fas fa-times-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Show PDF Preview for non-pending agreements -->
            <div class="agreement-section">
                <div class="section-title">Signed Agreement</div>

                <?php if ($agreement['FreelancerSignaturePath']): ?>
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin-bottom: 10px; color: #2c3e50; font-weight: 600;">Your Signature</h4>
                        <div class="existing-signature">
                            <img src="<?= htmlspecialchars($agreement['FreelancerSignaturePath']) ?>" alt="Your Signature">
                            <div style="font-size: 12px; color: #7f8c8d; margin-top: 10px;">
                                Signed on <?= date('M d, Y', strtotime($agreement['FreelancerSignedDate'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 20px;">
                    <a href="agreementListing.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Agreements
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let hasSignature = false;

        // Set canvas size
        function resizeCanvas() {
            const container = canvas.parentElement;
            canvas.width = container.offsetWidth;
            canvas.height = 200;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.lineWidth = 2;
            ctx.strokeStyle = '#2c3e50';
        }

        // Initialize canvas
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Mouse events
        canvas.addEventListener('mousedown', (e) => {
            isDrawing = true;
            hasSignature = true;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ctx.beginPath();
            ctx.moveTo(x, y);
        });

        canvas.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ctx.lineTo(x, y);
            ctx.stroke();
        });

        canvas.addEventListener('mouseup', () => {
            isDrawing = false;
        });

        // Touch events for mobile
        canvas.addEventListener('touchstart', (e) => {
            isDrawing = true;
            hasSignature = true;
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            const x = touch.clientX - rect.left;
            const y = touch.clientY - rect.top;
            ctx.beginPath();
            ctx.moveTo(x, y);
        });

        canvas.addEventListener('touchmove', (e) => {
            if (!isDrawing) return;
            e.preventDefault();
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            const x = touch.clientX - rect.left;
            const y = touch.clientY - rect.top;
            ctx.lineTo(x, y);
            ctx.stroke();
        });

        canvas.addEventListener('touchend', () => {
            isDrawing = false;
        });

        // Clear signature
        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasSignature = false;
            document.getElementById('signatureData').value = '';
        }

        // Capture signature
        function captureSignature() {
            if (!hasSignature) {
                alert('Please sign before submitting the agreement');
                return false;
            }

            // Convert canvas to image data
            const signatureImage = canvas.toDataURL('image/png');
            document.getElementById('signatureData').value = signatureImage;
            return true;
        }
    </script>

    </body>

    </html>