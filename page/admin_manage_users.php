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

        $stmt = $conn->prepare("UPDATE $table SET isDelete = 1 WHERE $id_column = ?");
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

    if (!empty($user_id) && in_array($user_type, ['freelancer', 'client']) && in_array($status, ['active', 'inactive'])) {
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
$filter_status = $_GET['status'] ?? 'active';
$search_query = $_GET['search'] ?? '';
$sort_by = $_GET['sort'] ?? 'date_desc';

// Count users by type and status for tabs
$count_sql = "SELECT 'freelancer' as type, COUNT(*) as count FROM freelancer WHERE isDelete = 0 AND Status = 'active' UNION ALL SELECT 'client' as type, COUNT(*) as count FROM client WHERE isDelete = 0 AND Status = 'active'";
$count_result = $conn->query($count_sql);
$user_counts = ['all' => 0, 'freelancer' => 0, 'client' => 0];

while ($row = $count_result->fetch_assoc()) {
    if ($row['type'] === 'freelancer') {
        $user_counts['freelancer'] += $row['count'];
    } elseif ($row['type'] === 'client') {
        $user_counts['client'] += $row['count'];
    }
    $user_counts['all'] += $row['count'];
}

// Count inactive users
$inactive_count_sql = "SELECT 'freelancer' as type, COUNT(*) as count FROM freelancer WHERE isDelete = 0 AND Status = 'inactive' UNION ALL SELECT 'client' as type, COUNT(*) as count FROM client WHERE isDelete = 0 AND Status = 'inactive'";
$inactive_count_result = $conn->query($inactive_count_sql);
$inactive_user_counts = ['all' => 0, 'freelancer' => 0, 'client' => 0];

while ($row = $inactive_count_result->fetch_assoc()) {
    if ($row['type'] === 'freelancer') {
        $inactive_user_counts['freelancer'] += $row['count'];
    } elseif ($row['type'] === 'client') {
        $inactive_user_counts['client'] += $row['count'];
    }
    $inactive_user_counts['all'] += $row['count'];
}

// Build freelancer query
$freelancer_query = "SELECT FreelancerID, FirstName, LastName, Email, Status, JoinedDate FROM freelancer WHERE isDelete = 0 AND Status = '$filter_status'";
if (!empty($search_query)) {
    $freelancer_query .= " AND (FirstName LIKE '%$search_query%' OR LastName LIKE '%$search_query%' OR Email LIKE '%$search_query%')";
}

// Build client query
$client_query = "SELECT ClientID, CompanyName as Name, Email, Status, JoinedDate FROM client WHERE isDelete = 0 AND Status = '$filter_status'";
if (!empty($search_query)) {
    $client_query .= " AND (CompanyName LIKE '%$search_query%' OR Email LIKE '%$search_query%')";
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
            $row['created'] = date('Y-m-d', strtotime($row['JoinedDate']));
            $row['Name'] = $row['FirstName'] . ' ' . $row['LastName'];
            $users[] = $row;
        }
    }

    if ($client_result) {
        while ($row = $client_result->fetch_assoc()) {
            $row['type'] = 'client';
            $row['created'] = date('Y-m-d', strtotime($row['JoinedDate']));
            $users[] = $row;
        }
    }
} elseif ($filter_type === 'freelancer') {
    $result = $conn->query($freelancer_query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['type'] = 'freelancer';
            $row['created'] = date('Y-m-d', strtotime($row['JoinedDate']));
            $row['Name'] = $row['FirstName'] . ' ' . $row['LastName'];
            $users[] = $row;
        }
    }
} elseif ($filter_type === 'client') {
    $result = $conn->query($client_query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['type'] = 'client';
            $row['created'] = date('Y-m-d', strtotime($row['JoinedDate']));
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Momo+Trust+Display&display=swap">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            font-family: 'Momo Trust Display', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Momo Trust Display', sans-serif;
            font-weight: 500;
        }

        p,
        .error-message,
        .form-control,
        select,
        input[type="text"],
        button,
        a {
            font-family: 'Inter', sans-serif;
        }

        table {
            font-family: 'Inter', sans-serif;
        }

        .btn-signin {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
        }

        .table-header h2 {
            font-family: 'Momo Trust Display', sans-serif;
            font-weight: 500;
        }

        /* Filter Section Styling */
        .filter-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafb 100%);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .filter-form {
            display: flex;
            gap: 16px;
            width: 100%;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-input-group {
            flex: 1;
            min-width: 250px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-input-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-select {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.9375rem;
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            transition: all 0.3s ease;
            color: #374151;
            cursor: pointer;
            min-width: 160px;
        }

        .filter-select:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
        }

        .filter-select:hover {
            border-color: rgb(159, 232, 112);
        }

        .filter-input {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.9375rem;
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            transition: all 0.3s ease;
            color: #374151;
            width: 100%;
        }

        .filter-input::placeholder {
            color: #9ca3af;
        }

        .filter-input:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
        }

        .filter-button {
            padding: 10px 24px;
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(139, 212, 92) 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
            white-space: nowrap;
        }

        .filter-button:hover {
            background: linear-gradient(135deg, rgb(139, 212, 92) 0%, rgb(119, 192, 72) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(159, 232, 112, 0.4);
        }

        .filter-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(159, 232, 112, 0.3);
        }

        /* Table Styling */
        .table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1.5px solid #f3f4f6;
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        }

        .table-header h2 {
            margin: 0;
            font-size: 18px;
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead tr {
            background-color: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        table thead th {
            padding: 14px 20px;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.3s ease;
        }

        table tbody tr:hover {
            background-color: #f9fafb;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        }

        table tbody tr:last-child {
            border-bottom: none;
        }

        table td {
            padding: 16px 20px;
            color: #1f2937;
            font-size: 14px;
        }

        /* User Avatar */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(139, 212, 92) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(159, 232, 112, 0.2);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-name {
            font-weight: 600;
            color: #1f2937;
            display: block;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-freelancer {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-client {
            background-color: #fce7f3;
            color: #9f1239;
        }

        /* Status Form */
        .form-control {
            padding: 8px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            background-color: white;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23374151' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 32px;
        }

        .form-control option {
            padding: 8px;
            background-color: white;
            color: #374151;
        }

        .form-control:hover {
            border-color: rgb(159, 232, 112);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239f6d1c' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        }

        .form-control:focus {
            outline: none;
            border-color: rgb(159, 232, 112);
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239fe870' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-sm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-edit {
            background-color: white;
            color: #1f2937;
            border: 1.5px solid #1f2937;
        }

        .btn-edit:hover {
            background-color: #1f2937;
            color: white;
            border-color: #1f2937;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(31, 41, 55, 0.3);
        }

        .btn-edit:active {
            transform: translateY(0);
        }

        .btn-delete {
            background-color: white;
            color: #1f2937;
            border: 1.5px solid #1f2937;
        }

        .btn-delete:hover {
            background-color: #1f2937;
            color: white;
            border-color: #1f2937;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(31, 41, 55, 0.3);
        }

        .btn-delete:active {
            transform: translateY(0);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-state p {
            margin: 0;
            font-size: 15px;
        }

        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }

            table th,
            table td {
                padding: 12px 10px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 6px;
            }

            .btn-sm {
                width: 100%;
                justify-content: center;
            }

            .user-avatar {
                width: 36px;
                height: 36px;
                font-size: 12px;
            }
        }

        /* Delete Confirmation Modal */
        .delete-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .delete-modal.active {
            display: flex;
        }

        .delete-modal-content {
            background: white;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 420px;
            width: 90%;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .delete-modal-icon {
            width: 60px;
            height: 60px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
            color: #dc2626;
        }

        .delete-modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 12px 0;
            text-align: center;
            font-family: 'Momo Trust Display', sans-serif;
        }

        .delete-modal-message {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 24px 0;
            text-align: center;
            line-height: 1.6;
            font-family: 'Inter', sans-serif;
        }

        .delete-modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .delete-modal-btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .delete-modal-btn-cancel {
            background: #f3f4f6;
            color: #374151;
            border: 1.5px solid #e5e7eb;
        }

        .delete-modal-btn-cancel:hover {
            background: #e5e7eb;
            border-color: #d1d5db;
        }

        .delete-modal-btn-confirm {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .delete-modal-btn-confirm:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4);
        }

        .delete-modal-btn-confirm:active {
            transform: translateY(0);
        }
    </style>
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

                <!-- Search Bar -->
                <div style="margin-bottom: 24px;">
                    <form method="GET" action="admin_manage_users.php" class="filter-form">
                        <div class="filter-input-group" style="flex: 1;">
                            <input type="hidden" name="type" value="<?php echo htmlspecialchars($filter_type); ?>">
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                            <input
                                type="text"
                                name="search"
                                placeholder="Search by name or email..."
                                class="filter-input"
                                value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        <button type="submit" class="filter-button" style="margin-top: 0;"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>

                <!-- Filter Status Tabs -->
                <div style="display: flex; gap: 15px; margin: 30px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 0;">
                    <a href="?type=all&status=active<?php echo !empty($search_query) ? '&search=' . htmlspecialchars($search_query) : ''; ?>" style="padding: 12px 20px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?php echo $filter_status === 'active' ? '#22c55e' : '#7f8c8d'; ?>; border-bottom: 3px solid <?php echo $filter_status === 'active' ? '#22c55e' : 'transparent'; ?>; transition: all 0.3s ease;">
                        Active <span style="background: #e9ecef; color: #2c3e50; border-radius: 12px; padding: 2px 8px; margin-left: 6px; font-size: 12px; font-weight: 700;"><?php echo $user_counts['all']; ?></span>
                    </a>
                    <a href="?type=all&status=inactive<?php echo !empty($search_query) ? '&search=' . htmlspecialchars($search_query) : ''; ?>" style="padding: 12px 20px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?php echo $filter_status === 'inactive' ? '#ef4444' : '#7f8c8d'; ?>; border-bottom: 3px solid <?php echo $filter_status === 'inactive' ? '#ef4444' : 'transparent'; ?>; transition: all 0.3s ease;">
                        Inactive <span style="background: #e9ecef; color: #2c3e50; border-radius: 12px; padding: 2px 8px; margin-left: 6px; font-size: 12px; font-weight: 700;"><?php echo $inactive_user_counts['all']; ?></span>
                    </a>
                </div>

                <!-- Filter Type Tabs -->
                <div style="display: flex; gap: 15px; margin: 30px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 0;">
                    <a href="?type=all&status=<?php echo htmlspecialchars($filter_status); ?><?php echo !empty($search_query) ? '&search=' . htmlspecialchars($search_query) : ''; ?>" style="padding: 12px 20px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?php echo $filter_type === 'all' ? '#22c55e' : '#7f8c8d'; ?>; border-bottom: 3px solid <?php echo $filter_type === 'all' ? '#22c55e' : 'transparent'; ?>; transition: all 0.3s ease;">
                        All <span style="background: #e9ecef; color: #2c3e50; border-radius: 12px; padding: 2px 8px; margin-left: 6px; font-size: 12px; font-weight: 700;"><?php echo $filter_type === 'all' ? ($filter_status === 'active' ? $user_counts['all'] : $inactive_user_counts['all']) : 0; ?></span>
                    </a>
                    <a href="?type=freelancer&status=<?php echo htmlspecialchars($filter_status); ?><?php echo !empty($search_query) ? '&search=' . htmlspecialchars($search_query) : ''; ?>" style="padding: 12px 20px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?php echo $filter_type === 'freelancer' ? '#22c55e' : '#7f8c8d'; ?>; border-bottom: 3px solid <?php echo $filter_type === 'freelancer' ? '#22c55e' : 'transparent'; ?>; transition: all 0.3s ease;">
                        Freelancer <span style="background: #e9ecef; color: #2c3e50; border-radius: 12px; padding: 2px 8px; margin-left: 6px; font-size: 12px; font-weight: 700;"><?php echo $filter_status === 'active' ? $user_counts['freelancer'] : $inactive_user_counts['freelancer']; ?></span>
                    </a>
                    <a href="?type=client&status=<?php echo htmlspecialchars($filter_status); ?><?php echo !empty($search_query) ? '&search=' . htmlspecialchars($search_query) : ''; ?>" style="padding: 12px 20px; text-decoration: none; font-size: 14px; font-weight: 600; color: <?php echo $filter_type === 'client' ? '#22c55e' : '#7f8c8d'; ?>; border-bottom: 3px solid <?php echo $filter_type === 'client' ? '#22c55e' : 'transparent'; ?>; transition: all 0.3s ease;">
                        Client <span style="background: #e9ecef; color: #2c3e50; border-radius: 12px; padding: 2px 8px; margin-left: 6px; font-size: 12px; font-weight: 700;"><?php echo $filter_status === 'active' ? $user_counts['client'] : $inactive_user_counts['client']; ?></span>
                    </a>
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
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($user['Name'] ?? '', 0, 1)); ?>
                                                </div>
                                                <span class="user-name"><?php echo htmlspecialchars($user['Name'] ?? ''); ?></span>
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
                                                <select name="status" class="form-control" onchange="this.form.submit();">
                                                    <option value="active" <?php echo ($user['Status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo ($user['Status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($user['created'] ?? 'now')); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn-sm btn-delete delete-btn"
                                                    data-user-id="<?php echo $user[$user['type'] === 'freelancer' ? 'FreelancerID' : 'ClientID']; ?>"
                                                    data-user-type="<?php echo $user['type']; ?>"
                                                    data-user-name="<?php echo htmlspecialchars($user['Name'] ?? ''); ?>">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <p>No users found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="delete-modal">
        <div class="delete-modal-content">
            <div class="delete-modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="delete-modal-title">Delete User?</h2>
            <p class="delete-modal-message">
                Are you sure you want to delete <strong id="modalUserName"></strong>? This action cannot be undone.
            </p>
            <div class="delete-modal-buttons">
                <button type="button" class="delete-modal-btn delete-modal-btn-cancel" onclick="closeDeleteModal()">
                    Cancel
                </button>
                <button type="button" class="delete-modal-btn delete-modal-btn-confirm" onclick="confirmDelete()">
                    Delete User
                </button>
            </div>
        </div>
    </div>

    <script>
        let deleteFormData = null;

        // Open delete modal
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const userType = this.getAttribute('data-user-type');
                const userName = this.getAttribute('data-user-name');

                deleteFormData = {
                    userId: userId,
                    userType: userType
                };

                document.getElementById('modalUserName').textContent = userName;
                document.getElementById('deleteModal').classList.add('active');
            });
        });

        // Close modal
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            deleteFormData = null;
        }

        // Confirm delete
        function confirmDelete() {
            if (deleteFormData) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_manage_users.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                const userIdInput = document.createElement('input');
                userIdInput.type = 'hidden';
                userIdInput.name = 'user_id';
                userIdInput.value = deleteFormData.userId;

                const userTypeInput = document.createElement('input');
                userTypeInput.type = 'hidden';
                userTypeInput.name = 'user_type';
                userTypeInput.value = deleteFormData.userType;

                form.appendChild(actionInput);
                form.appendChild(userIdInput);
                form.appendChild(userTypeInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside of it
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>

</html>