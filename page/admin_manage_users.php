<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit();
}

$conn = getDBConnection();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $user_id = $_POST['user_id'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    if (!empty($user_id) && in_array($user_type, ['freelancer', 'client'])) {
        $table = $user_type === 'freelancer' ? 'freelancer' : 'client';
        $id_column = $user_type === 'freelancer' ? 'FreelancerID' : 'ClientID';

        $stmt = $conn->prepare("DELETE FROM $table WHERE $id_column = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'User deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete user.';
        }
        $stmt->close();
    }

    header('Location: admin_manage_users.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $user_id = $_POST['user_id'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if (!empty($user_id) && in_array($user_type, ['freelancer', 'client']) && in_array($status, ['active', 'inactive', 'suspended'])) {
        $table = $user_type === 'freelancer' ? 'freelancer' : 'client';
        $id_column = $user_type === 'freelancer' ? 'FreelancerID' : 'ClientID';

        $stmt = $conn->prepare("UPDATE $table SET Status = ? WHERE $id_column = ?");
        $stmt->bind_param("si", $status, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'User status updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update user status.';
        }
        $stmt->close();
    }

    header('Location: admin_manage_users.php');
    exit();
}

// Get filter parameters
$filter_type = $_GET['type'] ?? 'all';
$search_query = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'date_desc';

// Build freelancer query
$freelancer_query = "SELECT FreelancerID, Name, Email, Status, CreatedAt, ProfilePicture FROM freelancer";
if (!empty($search_query)) {
    $freelancer_query .= " WHERE Name LIKE '%$search_query%' OR Email LIKE '%$search_query%'";
}

// Build client query
$client_query = "SELECT ClientID, CompanyName as Name, Email, Status, CreatedAt, CompanyLogo as ProfilePicture FROM client";
if (!empty($search_query)) {
    $client_query .= " WHERE CompanyName LIKE '%$search_query%' OR Email LIKE '%$search_query%'";
}

// Get data based on filter
$users = [];
if ($filter_type === 'all') {
    // Get both freelancers and clients
    $freelancer_result = $conn->query($freelancer_query);
    $client_result = $conn->query($client_query);

    if ($freelancer_result) {
        while ($row = $freelancer_result->fetch_assoc()) {
            $row['type'] = 'freelancer';
            $row['created'] = $row['CreatedAt'];
            $users[] = $row;
        }
    }

    if ($client_result) {
        while ($row = $client_result->fetch_assoc()) {
            $row['type'] = 'client';
            $row['created'] = $row['CreatedAt'];
            $users[] = $row;
        }
    }
} elseif ($filter_type === 'freelancer') {
    $result = $conn->query($freelancer_query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['type'] = 'freelancer';
            $row['created'] = $row['CreatedAt'];
            $users[] = $row;
        }
    }
} elseif ($filter_type === 'client') {
    $result = $conn->query($client_query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['type'] = 'client';
            $row['created'] = $row['CreatedAt'];
            $users[] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - WorkSnyc Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="admin-layout">
    <div class="admin-sidebar">
        <?php include '../includes/admin_sidebar.php'; ?>
    </div>

    <div class="admin-layout-wrapper">
        <?php include '../includes/admin_header.php'; ?>

        <main class="admin-main-content">
            <div class="dashboard-container">
                <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1>User Management</h1>
                        <p>Manage access and roles for platform users</p>
                    </div>
                    <a href="add_user.php" class="btn-signin" style="padding: 12px 24px; text-decoration: none; display: inline-block; border-radius: 8px; margin: 0;">+ Add User</a>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="error-message" style="background-color: #d1fae5; border-color: #6ee7b7; color: #065f46;">
                        <?php
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Filter Section -->
                <div style="background-color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center;">
                    <form method="GET" action="admin_manage_users.php" style="display: flex; gap: 15px; width: 100%; align-items: center;">
                        <div style="flex: 1;">
                            <input
                                type="text"
                                name="search"
                                placeholder="Search by name or email..."
                                class="form-control"
                                value="<?php echo htmlspecialchars($search_query); ?>"
                                style="margin: 0;">
                        </div>

                        <select name="type" class="form-control" style="flex: 0 1 150px; margin: 0;">
                            <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>All Users</option>
                            <option value="freelancer" <?php echo $filter_type === 'freelancer' ? 'selected' : ''; ?>>Freelancers</option>
                            <option value="client" <?php echo $filter_type === 'client' ? 'selected' : ''; ?>>Clients</option>
                        </select>

                        <button type="submit" class="btn-signin" style="margin: 0; padding: 12px 20px; flex: 0 0 auto;">Filter</button>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>User List (<?php echo count($users); ?> users)</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <img src="<?php echo !empty($user['ProfilePicture']) ? htmlspecialchars($user['ProfilePicture']) : '../images/default-avatar.png'; ?>" 
                                                     alt="<?php echo htmlspecialchars($user['Name'] ?? ''); ?>" 
                                                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                                <div>
                                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($user['Name'] ?? ''); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['Email'] ?? ''); ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['type'] === 'freelancer' ? 'badge-freelancer' : 'badge-client'; ?>">
                                                <?php echo ucfirst($user['type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" action="admin_manage_users.php" style="display: inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="user_id" value="<?php echo $user[$user['type'] === 'freelancer' ? 'FreelancerID' : 'ClientID']; ?>">
                                                <input type="hidden" name="user_type" value="<?php echo $user['type']; ?>">
                                                <select name="status" class="form-control" onchange="this.form.submit();" style="padding: 6px 8px; font-size: 12px; border-radius: 6px; border: 1px solid var(--border-color);">
                                                    <option value="active" <?php echo ($user['Status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo ($user['Status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    <option value="suspended" <?php echo ($user['Status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($user['created'] ?? 'now')); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_user.php?id=<?php echo $user[$user['type'] === 'freelancer' ? 'FreelancerID' : 'ClientID']; ?>&type=<?php echo $user['type']; ?>" class="btn-sm" style="background-color: #3b82f6; color: white; text-decoration: none; padding: 6px 12px; border-radius: 6px;">Edit</a>
                                                <form method="POST" action="admin_manage_users.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?php echo $user[$user['type'] === 'freelancer' ? 'FreelancerID' : 'ClientID']; ?>">
                                                    <input type="hidden" name="user_type" value="<?php echo $user['type']; ?>">
                                                    <button type="submit" class="btn-sm btn-delete">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px;">
                                        <p style="color: #9ca3af;">No users found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>