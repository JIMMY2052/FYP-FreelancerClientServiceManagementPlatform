<?php
session_start();

// only freelancers can edit gigs
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$_title = 'Edit Gig';
require_once '../config.php';

// Get gig ID from URL
$gigID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$freelancerID = $_SESSION['user_id'];

if (!$gigID) {
    $_SESSION['error'] = 'Invalid gig ID.';
    header('Location: my_gig.php');
    exit();
}

if (!function_exists('getPDOConnection')) {
    function getPDOConnection(): PDO {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            return new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection error: ' . $e->getMessage());
        }
    }
}

$pdo = getPDOConnection();

// Fetch gig details - verify it belongs to this freelancer
try {
    $stmt = $pdo->prepare("SELECT * FROM gig WHERE GigID = :gig_id AND FreelancerID = :freelancer_id AND Status = 'active'");
    $stmt->execute([
        ':gig_id' => $gigID,
        ':freelancer_id' => $freelancerID
    ]);
    $gig = $stmt->fetch();
    
    if (!$gig) {
        $_SESSION['error'] = 'Gig not found or you do not have permission to edit it.';
        header('Location: my_gig.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error.';
    error_log('[edit_gig] Fetch failed: ' . $e->getMessage());
    header('Location: my_gig.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $deliveryTime = intval($_POST['delivery_time'] ?? 0);
    $rushDelivery = intval($_POST['rush_delivery'] ?? 0);
    $rushDeliveryPrice = floatval($_POST['rush_delivery_price'] ?? 0);
    $revisionCount = intval($_POST['revision_count'] ?? 0);
    
    // Validation
    if (empty($title) || empty($description) || $price <= 0 || $deliveryTime <= 0) {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE gig SET 
                Title = :title,
                Description = :description,
                Price = :price,
                DeliveryTime = :delivery_time,
                RushDelivery = :rush_delivery,
                RushDeliveryPrice = :rush_delivery_price,
                RevisionCount = :revision_count,
                UpdatedAt = NOW()
                WHERE GigID = :gig_id AND FreelancerID = :freelancer_id");
            
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':price' => $price,
                ':delivery_time' => $deliveryTime,
                ':rush_delivery' => $rushDelivery,
                ':rush_delivery_price' => $rushDeliveryPrice,
                ':revision_count' => $revisionCount,
                ':gig_id' => $gigID,
                ':freelancer_id' => $freelancerID
            ]);
            
            $_SESSION['success'] = 'Gig updated successfully!';
            header('Location: my_gig.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Failed to update gig. Please try again.';
            error_log('[edit_gig] Update failed: ' . $e->getMessage());
        }
    }
}

?>

<div class="container">
    <div class="breadcrumb">
        <a href="my_gig.php">← Back to My Gigs</a>
    </div>

    <div class="form-container">
        <h1>Edit Gig</h1>
        
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

        <form method="POST" class="edit-gig-form">
            <div class="form-group">
                <label for="title">Gig Title *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($gig['Title']) ?>" required maxlength="200">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="8" required><?= htmlspecialchars($gig['Description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (RM) *</label>
                    <input type="number" id="price" name="price" value="<?= htmlspecialchars($gig['Price']) ?>" min="1" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="delivery_time">Delivery Time (Days) *</label>
                    <input type="number" id="delivery_time" name="delivery_time" value="<?= htmlspecialchars($gig['DeliveryTime']) ?>" min="1" step="1" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="rush_delivery">Rush Delivery (Days)</label>
                    <input type="number" id="rush_delivery" name="rush_delivery" value="<?= htmlspecialchars($gig['RushDelivery'] ?? '') ?>" min="1" step="1">
                    <small class="form-hint">Optional: Faster delivery option</small>
                </div>

                <div class="form-group">
                    <label for="rush_delivery_price">Rush Delivery Price (RM)</label>
                    <input type="number" id="rush_delivery_price" name="rush_delivery_price" value="<?= htmlspecialchars($gig['RushDeliveryPrice'] ?? '') ?>" min="0" step="0.01">
                    <small class="form-hint">Additional charge for rush delivery</small>
                </div>
            </div>

            <div class="form-group">
                <label for="revision_count">Number of Revisions</label>
                <input type="number" id="revision_count" name="revision_count" value="<?= htmlspecialchars($gig['RevisionCount'] ?? 0) ?>" min="0" step="1">
                <small class="form-hint">How many revisions are included</small>
            </div>

            <div class="form-actions">
                <a href="my_gig.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Update Gig</button>
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

.edit-gig-form {
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

.form-hint {
    font-size: 0.85rem;
    color: #666;
    margin-top: 5px;
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

<?php include '../../_foot.php'; ?>
