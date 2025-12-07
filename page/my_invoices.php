<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

$_title = 'My Invoices';
include '../_head.php';

$conn = getDBConnection();

// Fetch invoices based on user type
if ($user_type === 'client') {
    $sql = "SELECT i.InvoiceID, i.InvoiceNumber, i.Amount, i.InvoiceDate, i.InvoiceFilePath, i.Status,
                   CONCAT(f.FirstName, ' ', f.LastName) as FreelancerName,
                   a.ProjectTitle
            FROM invoices i
            JOIN agreement a ON i.AgreementID = a.AgreementID
            JOIN freelancer f ON i.FreelancerID = f.FreelancerID
            WHERE i.ClientID = ?
            ORDER BY i.InvoiceDate DESC";
} else {
    $sql = "SELECT i.InvoiceID, i.InvoiceNumber, i.Amount, i.InvoiceDate, i.InvoiceFilePath, i.Status,
                   c.CompanyName as ClientName,
                   a.ProjectTitle
            FROM invoices i
            JOIN agreement a ON i.AgreementID = a.AgreementID
            JOIN client c ON i.ClientID = c.ClientID
            WHERE i.FreelancerID = ?
            ORDER BY i.InvoiceDate DESC";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$invoices = [];
while ($row = $result->fetch_assoc()) {
    $invoices[] = $row;
}
$stmt->close();
$conn->close();
?>

<style>
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        flex: 1;
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

    .invoices-grid {
        display: grid;
        gap: 20px;
    }

    .invoice-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 20px;
        align-items: center;
        transition: all 0.3s ease;
    }

    .invoice-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .invoice-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(140, 210, 90) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }

    .invoice-details {
        flex: 1;
    }

    .invoice-number {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .invoice-project {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 5px;
    }

    .invoice-meta {
        display: flex;
        gap: 15px;
        font-size: 0.85rem;
        color: #999;
    }

    .invoice-amount {
        text-align: right;
    }

    .amount-label {
        font-size: 0.85rem;
        color: #666;
        margin-bottom: 5px;
    }

    .amount-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #16a34a;
    }

    .invoice-actions {
        display: flex;
        gap: 10px;
    }

    .btn-download {
        padding: 10px 20px;
        background: rgb(159, 232, 112);
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-download:hover {
        background: rgb(140, 210, 90);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #d4edda;
        color: #155724;
    }

    .empty-state {
        text-align: center;
        padding: 60px 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .empty-state i {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 20px;
        display: block;
    }

    .empty-state h3 {
        font-size: 1.3rem;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #666;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .invoice-card {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .invoice-icon {
            margin: 0 auto;
        }

        .invoice-amount {
            text-align: center;
        }

        .invoice-actions {
            justify-content: center;
        }
    }
</style>

<div class="container">
    <div class="page-header">
        <h1>📄 My Invoices</h1>
        <p>View and download your e-invoices for completed projects</p>
    </div>

    <?php if (empty($invoices)): ?>
        <div class="empty-state">
            <i class="fas fa-file-invoice"></i>
            <h3>No Invoices Yet</h3>
            <p>Invoices will appear here when projects are completed and payments are processed.</p>
        </div>
    <?php else: ?>
        <div class="invoices-grid">
            <?php foreach ($invoices as $invoice): ?>
                <div class="invoice-card">
                    <div class="invoice-icon">
                        📄
                    </div>
                    <div class="invoice-details">
                        <div class="invoice-number"><?= htmlspecialchars($invoice['InvoiceNumber']) ?></div>
                        <div class="invoice-project">
                            <strong>Project:</strong> <?= htmlspecialchars($invoice['ProjectTitle']) ?>
                        </div>
                        <div class="invoice-meta">
                            <span>
                                <i class="fas fa-calendar"></i> 
                                <?= date('d M Y', strtotime($invoice['InvoiceDate'])) ?>
                            </span>
                            <span>
                                <?php if ($user_type === 'client'): ?>
                                    <i class="fas fa-user"></i> 
                                    <?= htmlspecialchars($invoice['FreelancerName']) ?>
                                <?php else: ?>
                                    <i class="fas fa-building"></i> 
                                    <?= htmlspecialchars($invoice['ClientName']) ?>
                                <?php endif; ?>
                            </span>
                            <span class="status-badge"><?= strtoupper($invoice['Status']) ?></span>
                        </div>
                    </div>
                    <div class="invoice-amount">
                        <div class="amount-label">Amount</div>
                        <div class="amount-value">RM <?= number_format($invoice['Amount'], 2) ?></div>
                    </div>
                    <div class="invoice-actions">
                        <a href="../<?= htmlspecialchars($invoice['InvoiceFilePath']) ?>" 
                           class="btn-download" 
                           download
                           target="_blank">
                            <i class="fas fa-download"></i>
                            Download
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../_foot.php'; ?>
