<?php
session_start();

// only freelancers can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: /index.php');
    exit();
}

$_title = 'My Services';
include '../_head.php';
require_once 'config.php';

$conn = getDBConnection();

// Handle add service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] !== '' ? floatval($_POST['price']) : null;
    $delivery_time = $_POST['delivery_time'] !== '' ? intval($_POST['delivery_time']) : null; // in days

    if ($title === '' || $description === '' || $price === null || $delivery_time === null) {
        $_SESSION['error'] = 'Please fill in all fields.';
    } else {
        $freelancerID = $_SESSION['user_id'];
        $created_at = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO service (FreelancerID, Title, Description, Price, DeliveryTime, CreatedAt) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issdis", $freelancerID, $title, $description, $price, $delivery_time, $created_at);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Service added.';
                $stmt->close();
                header('Location: /page/my_services.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to add service.';
                $stmt->close();
            }
        } else {
            $_SESSION['error'] = 'Database error.';
        }
    }

    // on validation error fall through to display messages
}

// Fetch freelancer services
$freelancerID = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT ServiceID, Title, Description, Price, DeliveryTime, CreatedAt FROM service WHERE FreelancerID = ? ORDER BY CreatedAt DESC");
$stmt->bind_param("i", $freelancerID);
$stmt->execute();
$result = $stmt->get_result();
$services = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container">
    <div class="page-header">
        <h1>My Services</h1>
        <a href="/freelancer_home.php" class="btn-primary">Dashboard</a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <section class="add-service">
        <h2>Add New Service</h2>
        <form method="post" class="service-form">
            <input type="hidden" name="action" value="add">
            <label>Title
                <input type="text" name="title" required placeholder="e.g. WordPress Website Setup">
            </label>
            <label>Description
                <textarea name="description" required placeholder="Describe your service" rows="4"></textarea>
            </label>
            <div class="row">
                <label>Price (MYR)
                    <input type="number" name="price" required min="0" step="0.01" placeholder="e.g. 150.00">
                </label>
                <label>Delivery Time (days)
                    <input type="number" name="delivery_time" required min="1" step="1" placeholder="e.g. 7">
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-action">Add Service</button>
            </div>
        </form>
    </section>

    <section class="services-list">
        <h2>Your Services (<?php echo count($services); ?>)</h2>

        <?php if (empty($services)): ?>
            <div class="empty">You haven't added any services yet.</div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($services as $svc): ?>
                    <div class="service-card">
                        <div class="card-head">
                            <h3><?php echo htmlspecialchars($svc['Title']); ?></h3>
                            <div class="price">MYR <?php echo number_format($svc['Price'], 2); ?></div>
                        </div>
                        <p class="desc"><?php echo nl2br(htmlspecialchars($svc['Description'])); ?></p>
                        <div class="card-meta">
                            <span>Delivery: <?php echo intval($svc['DeliveryTime']); ?> day(s)</span>
                            <span>Added: <?php echo date('M d, Y', strtotime($svc['CreatedAt'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
$conn->close();
include '../_foot.php';
?>

<style>
/* my_services page styles - curvy, green accents */
.container { max-width: 980px; margin: 28px auto; padding: 0 16px; }
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
.page-header h1 { margin:0; font-size:1.8rem; }

.add-service, .services-list { background:#fff; padding:18px; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,0.04); margin-bottom:18px; }
.add-service h2, .services-list h2 { margin:0 0 12px 0; font-size:1.1rem; }

.service-form label { display:block; margin-bottom:10px; font-weight:600; color:#333; }
.service-form input[type="text"], .service-form input[type="number"], .service-form textarea {
    width:100%; padding:12px 14px; border-radius:12px; border:1px solid #e6e6e6; font-size:0.95rem;
}
.service-form .row { display:flex; gap:12px; }
.service-form .row label { flex:1; }

.form-actions { margin-top:12px; }
.btn-action {
    background: rgb(159, 232, 112);
    color:#333;
    padding:12px 28px;
    border-radius:20px;
    border:none;
    cursor:pointer;
    font-weight:700;
    font-size:0.95rem;
}
.btn-action:hover { background: rgb(140,210,90); }

.cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; margin-top:12px; }
.service-card { background:#fff; border-radius:16px; padding:14px; box-shadow:0 2px 10px rgba(0,0,0,0.04); display:flex; flex-direction:column; gap:10px; }
.card-head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
.card-head h3 { margin:0; font-size:1rem; }
.price { background: rgb(159,232,112); color:#333; padding:6px 10px; border-radius:12px; font-weight:700; white-space:nowrap; }
.desc { color:#555; font-size:0.95rem; margin:0; flex:1; }
.card-meta { display:flex; justify-content:space-between; color:#888; font-size:0.85rem; }

.alert { padding:12px 14px; border-radius:12px; margin-bottom:12px; }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-error { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }

.empty { padding:18px; text-align:center; color:#666; border-radius:12px; background:#fafafa; }
@media (max-width:600px){ .service-form .row { flex-direction:column; } .cards-grid{ grid-template-columns: 1fr; } }
</style>
```//