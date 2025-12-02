<?php
session_start();

// only clients can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: /index.php');
    exit();
}

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

// Handle delete job (soft delete - change status to deleted)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $jobID = intval($_POST['job_id'] ?? 0);
    $clientID = $_SESSION['user_id'];

    if ($jobID > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE job SET Status = 'deleted' WHERE JobID = :job_id AND ClientID = :client_id");
            $stmt->execute([
                ':job_id' => $jobID,
                ':client_id' => $clientID
            ]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = 'Project deleted successfully.';
                header('Location: /page/job/my_jobs.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to delete project.';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error.';
            error_log('[my_jobs] Delete failed: ' . $e->getMessage());
        }
    }
}

// Fetch client jobs (available status)
$clientID = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT JobID, Title, Description, Budget, Deadline, Status, PostDate FROM job WHERE ClientID = :client_id AND Status = 'available' ORDER BY PostDate DESC");
    $stmt->execute([':client_id' => $clientID]);
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    $jobs = [];
    $_SESSION['error'] = 'Failed to load your projects.';
    error_log('[my_jobs] Fetch failed: ' . $e->getMessage());
}

$_title = 'My Projects';
include '../../_head.php';
?>

<div class="container">
    <div class="my-job-header">
        <h1>My Projects</h1>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (!empty($jobs)): ?>
        <!-- Jobs List -->
        <section class="jobs-section">
            <div class="jobs-top">
                <h2>Your Projects (<?php echo count($jobs); ?>)</h2>
                <a href="/page/job/createJob.php" class="btn-add-new">+ Post New Project</a>
            </div>
            
            <div class="jobs-grid">
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card" onclick="window.location.href='/page/job/client_job_details.php?id=<?php echo $job['JobID']; ?>';" style="cursor: pointer;">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($job['Title']); ?></h3>
                            <span class="job-budget">
                                MYR <?php echo number_format($job['Budget'], 0); ?>
                            </span>
                        </div>
                        
                        <p class="job-description"><?php echo nl2br(htmlspecialchars(mb_strimwidth($job['Description'], 0, 200, '...'))); ?></p>
                        
                        <div class="job-meta">
                            <span class="meta-item">
                                <strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($job['Status'])); ?>
                            </span>
                            <span class="meta-item">
                                <strong>Deadline:</strong> <?php echo date('M d, Y', strtotime($job['Deadline'])); ?>
                            </span>
                            <span class="meta-item">
                                <strong>Posted:</strong> <?php echo date('M d, Y', strtotime($job['PostDate'])); ?>
                            </span>
                        </div>

                        <div class="card-actions">
                            <a href="/page/job/editJob.php?id=<?php echo $job['JobID']; ?>" class="btn-small btn-edit" onclick="event.stopPropagation();">Edit</a>
                            <form method="post" style="flex: 1;" onclick="event.stopPropagation();">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="job_id" value="<?php echo $job['JobID']; ?>">
                                <button type="submit" class="btn-small btn-delete" onclick="return confirm('Delete this project?');">Delete</button>
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
            <h2>No Projects Yet</h2>
            <p>You haven't posted any projects yet. Create your first project to start finding talented freelancers.</p>
            <a href="/page/job/createJob.php" class="btn-add-first">Post Your First Project</a>
        </div>
    <?php endif; ?>
</div>

<?php
$pdo = null;
include '../../_foot.php';
?>

<style>
/* My Jobs Page */
.container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 16px;
}

.my-job-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.my-job-header h1 {
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

/* Jobs List */
.jobs-section {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.jobs-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.jobs-top h2 {
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

.jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

/* Job Card */
.job-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.job-card:hover {
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
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.status-badge {
    font-size: 0.7rem;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.job-budget {
    background: rgb(159, 232, 112);
    color: #333;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.85rem;
    white-space: nowrap;
}

.job-description {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
    margin: 0 0 12px 0;
    flex: 1;
}

.job-meta {
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
    .my-job-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .jobs-top {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .jobs-grid {
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
    .my-job-header h1 {
        font-size: 1.4rem;
    }

    .jobs-section {
        padding: 20px;
    }

    .job-card {
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
