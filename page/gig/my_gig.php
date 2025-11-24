<?php
session_start();

// only freelancers can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$_title = 'My Gigs';
include '../../_head.php';
require_once '../config.php';

if (!function_exists('getPDOConnection')) {
    function getPDOConnection(): PDO
    {
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

// Handle delete service (soft delete - change status to deleted)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $gigID = intval($_POST['gig_id'] ?? 0);
    $freelancerID = $_SESSION['user_id'];

    if ($gigID > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE gig SET Status = 'deleted' WHERE GigID = :gig_id AND FreelancerID = :freelancer_id");
            $stmt->execute([
                ':gig_id' => $gigID,
                ':freelancer_id' => $freelancerID
            ]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = 'Service deleted successfully.';
                header('Location: /page/gig/my_gig.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to delete gig.';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error.';
            error_log('[my_gig] Delete failed: ' . $e->getMessage());
        }
    }
}

// Fetch freelancer services (only active status)
$freelancerID = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT GigID, Title, Description, Price, DeliveryTime, RushDelivery, RushDeliveryPrice, CreatedAt FROM gig WHERE FreelancerID = :freelancer_id AND Status = 'active' ORDER BY CreatedAt DESC");
    $stmt->execute([':freelancer_id' => $freelancerID]);
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
    $_SESSION['error'] = 'Failed to load your gigs.';
    error_log('[my_gig] Fetch failed: ' . $e->getMessage());
}
?>

<div class="container">
    <div class="my-service-header">
        <h1>My Gigs</h1>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (!empty($services)): ?>
        <!-- Services List -->
        <section class="services-section">
            <div class="services-top">
                <h2>Your Gigs (<?php echo count($services); ?>)</h2>
                <a href="/page/gig/create_gig.php" class="btn-add-new">+ Add New Gig</a>
            </div>
            
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($service['Title']); ?></h3>
                            <span class="service-price">
                                MYR <?php echo number_format($service['Price'], 0); ?>
                            </span>
                        </div>
                        
                        <p class="service-description"><?php echo nl2br(htmlspecialchars(mb_strimwidth($service['Description'], 0, 200, '...'))); ?></p>
                        
                        <div class="service-meta">
                            <span class="meta-item">
                                <strong>Delivery:</strong> <?php echo intval($service['DeliveryTime']); ?> day(s)
                            </span>
                            <?php if (!empty($service['RushDelivery']) && $service['RushDelivery'] > 0): ?>
                                <span class="meta-item">
                                    <strong>Rush Delivery:</strong> <?php echo intval($service['RushDelivery']); ?> day(s) 
                                    <?php if (!empty($service['RushDeliveryPrice']) && $service['RushDeliveryPrice'] > 0): ?>
                                        <span style="color: rgb(159, 232, 112); font-weight: 700;">(+RM<?php echo number_format($service['RushDeliveryPrice'], 0); ?>)</span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <span class="meta-item">
                                <strong>Added:</strong> <?php echo date('M d, Y', strtotime($service['CreatedAt'])); ?>
                            </span>
                        </div>

                        <div class="card-actions">
                            <a href="/page/freelancer/edit_service.php?id=<?php echo $service['GigID']; ?>" class="btn-small btn-edit">Edit</a>
                            <form method="post" style="flex: 1;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="gig_id" value="<?php echo $service['GigID']; ?>">
                                <button type="submit" class="btn-small btn-delete" onclick="return confirm('Delete this service?');">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h2>No Gigs Yet</h2>
            <p>You haven't added any gigs yet. Create your first gig to start offering your skills to clients.</p>
            <a href="/page/gig/create_gig.php" class="btn-add-first">Add Your First Gig</a>
        </div>
    <?php endif; ?>
</div>

<?php
$pdo = null;
include '../../_foot.php';
?>

<style>
/* My Service Page */
.container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 16px;
}

.my-service-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.my-service-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

/* Alert Messages */
.alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-weight: 500;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
}

/* Services List */
.services-section {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.services-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.services-top h2 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.btn-add-new {
    background: rgb(159, 232, 112);
    color: #333;
    padding: 10px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-add-new:hover {
    background: rgb(140, 210, 90);
    box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

/* Service Card */
.service-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.service-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    transform: translateY(-3px);
    border-color: rgb(159, 232, 112);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}

.card-header h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    flex: 1;
}

.service-price {
    background: rgb(159, 232, 112);
    color: #333;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
}

.service-description {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0 0 12px 0;
    flex: 1;
}

.service-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px 0;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 12px;
}

.meta-item {
    color: #666;
    font-size: 0.85rem;
}

.meta-item strong {
    color: #2c3e50;
}

.card-actions {
    display: flex;
    gap: 10px;
}

.btn-small {
    flex: 1;
    padding: 10px 14px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-edit {
    background: rgb(159, 232, 112);
    color: #333;
}

.btn-edit:hover {
    background: rgb(140, 210, 90);
}

.btn-delete {
    background: white;
    color: #333;
    border: 1px solid #333;
}

.btn-delete:hover {
    background: #f5f5f5;
    border-color: #333;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 40px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 16px;
    border: 2px dashed #dee2e6;
    margin: 40px 0;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.empty-state h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 12px 0;
}

.empty-state p {
    font-size: 1rem;
    color: #666;
    margin: 0 0 24px 0;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.btn-add-first {
    background: rgb(159, 232, 112);
    color: #333;
    padding: 14px 32px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.95rem;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-add-first:hover {
    background: rgb(140, 210, 90);
    box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .my-service-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .services-top {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .services-grid {
        grid-template-columns: 1fr;
    }

    .empty-state {
        padding: 60px 30px;
    }

    .empty-icon {
        font-size: 3rem;
    }
}

@media (max-width: 480px) {
    .my-service-header h1 {
        font-size: 1.4rem;
    }

    .services-section {
        padding: 20px;
    }

    .service-card {
        padding: 16px;
    }

    .card-header h3 {
        font-size: 1rem;
    }

    .empty-state {
        padding: 50px 20px;
    }
}
</style>

<?php
// Database table structure update (if not exists - add Status column if needed)
/*
ALTER TABLE service ADD COLUMN Status VARCHAR(50) DEFAULT 'active';

Or if creating new:

CREATE TABLE service (
    ServiceID INT PRIMARY KEY AUTO_INCREMENT,
    FreelancerID INT NOT NULL,
    Title VARCHAR(200) NOT NULL,
    Description TEXT NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    DeliveryTime INT NOT NULL COMMENT 'in days',
    Status VARCHAR(50) DEFAULT 'active' COMMENT 'active, paused, deleted',
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (FreelancerID) REFERENCES user(UserID),
    INDEX(FreelancerID),
    INDEX(Status),
    INDEX(CreatedAt)
);
*/
?>