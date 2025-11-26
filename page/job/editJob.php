<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../login.php');
    exit();
}

$_title = 'Edit Job - WorkSnyc';
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
$sql = "SELECT * FROM job WHERE JobID = ? AND ClientID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $jobID, $clientID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Job not found or you do not have permission to edit it.';
    $stmt->close();
    $conn->close();
    header('Location: ../my_jobs.php');
    exit();
}

$job = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
    $deliveryTime = isset($_POST['deliveryTime']) ? intval($_POST['deliveryTime']) : 0;
    $deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : '';
    
    // Validation
    if (empty($title) || empty($description) || empty($budget) || empty($deadline)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } else {
        // Update job
        $sql = "UPDATE job SET Title = ?, Description = ?, Budget = ?, DeliveryTime = ?, Deadline = ? WHERE JobID = ? AND ClientID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdisii', $title, $description, $budget, $deliveryTime, $deadline, $jobID, $clientID);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Job updated successfully!';
            $stmt->close();
            $conn->close();
            header('Location: client_job_details.php?id=' . $jobID);
            exit();
        } else {
            $_SESSION['error'] = 'Failed to update job. Please try again.';
        }
        $stmt->close();
    }
}

$conn->close();

require_once '../../_head.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="client_job_details.php?id=<?= $jobID ?>">← Back to Job Details</a>
    </div>

    <div class="form-container">
        <h1>Edit Job</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="edit-job-form">
            <div class="form-group">
                <label for="title">Job Title *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($job['Title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Job Description *</label>
                <textarea id="description" name="description" rows="6" required><?= htmlspecialchars($job['Description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="budget">Budget (RM) *</label>
                    <input type="number" id="budget" name="budget" value="<?= htmlspecialchars($job['Budget']) ?>" min="0" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="deliveryTime">Delivery Time (Days)</label>
                    <input type="number" id="deliveryTime" name="deliveryTime" value="<?= htmlspecialchars($job['DeliveryTime'] ?? '') ?>" min="1" step="1">
                </div>
            </div>

            <div class="form-group">
                <label for="deadline">Deadline *</label>
                <input type="date" id="deadline" name="deadline" value="<?= htmlspecialchars(date('Y-m-d', strtotime($job['Deadline']))) ?>" required>
            </div>

            <div class="form-actions">
                <a href="client_job_details.php?id=<?= $jobID ?>" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Update Job</button>
            </div>
        </form>
    </div>
</div>

<style>
.container {
    max-width: 900px;
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

.form-container {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.form-container h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 30px 0;
}

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
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

.edit-job-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-group input,
.form-group textarea {
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: rgb(159, 232, 112);
    box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    justify-content: flex-end;
}

.btn-cancel,
.btn-submit {
    padding: 14px 32px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-block;
}

.btn-cancel {
    background: #f8f9fa;
    color: #2c3e50;
    border: 2px solid #e9ecef;
}

.btn-cancel:hover {
    background: #fff;
    border-color: #ddd;
}

.btn-submit {
    background: rgb(159, 232, 112);
    color: #2c3e50;
}

.btn-submit:hover {
    background: rgb(140, 210, 90);
    box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .form-container {
        padding: 25px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-cancel,
    .btn-submit {
        width: 100%;
    }
}
</style>

<?php require_once '../../_foot.php'; ?>
