<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit();
}

$user_id = $_GET['id'] ?? '';
$user_type = $_GET['type'] ?? '';

if (empty($user_id) || !in_array($user_type, ['freelancer', 'client'])) {
    header('Location: admin_manage_users.php');
    exit();
}

$conn = getDBConnection();
$errors = [];
$success = false;
$user = null;

// Fetch user data
if ($user_type === 'freelancer') {
    $stmt = $conn->prepare("SELECT FreelancerID, Name, Email, Status FROM freelancer WHERE FreelancerID = ?");
} else {
    $stmt = $conn->prepare("SELECT ClientID, CompanyName as Name, Email, Status FROM client WHERE ClientID = ?");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: admin_manage_users.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (empty($errors)) {
        // Check if email is unique (excluding current user)
        $check_stmt = $conn->prepare($user_type === 'freelancer' 
            ? "SELECT COUNT(*) as count FROM freelancer WHERE Email = ? AND FreelancerID != ?" 
            : "SELECT COUNT(*) as count FROM client WHERE Email = ? AND ClientID != ?");
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_row = $check_result->fetch_assoc();
        $check_stmt->close();

        if ($check_row['count'] > 0) {
            $errors[] = 'This email is already in use.';
        }
    }

    if (empty($errors)) {
        if ($user_type === 'freelancer') {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE freelancer SET Name = ?, Email = ?, Status = ?, Password = ? WHERE FreelancerID = ?");
                $stmt->bind_param("ssssi", $name, $email, $status, $hashed_password, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE freelancer SET Name = ?, Email = ?, Status = ? WHERE FreelancerID = ?");
                $stmt->bind_param("sssi", $name, $email, $status, $user_id);
            }
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE client SET CompanyName = ?, Email = ?, Status = ?, Password = ? WHERE ClientID = ?");
                $stmt->bind_param("ssssi", $name, $email, $status, $hashed_password, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE client SET CompanyName = ?, Email = ?, Status = ? WHERE ClientID = ?");
                $stmt->bind_param("sssi", $name, $email, $status, $user_id);
            }
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = 'User updated successfully!';
            header('Location: admin_manage_users.php');
            exit();
        } else {
            $errors[] = 'Error updating user: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - WorkSnyc Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .error-list {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #991b1b;
        }

        .error-list ul {
            margin: 0;
            padding-left: 20px;
        }

        .error-list li {
            margin: 5px 0;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .form-actions button,
        .form-actions a {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .btn-submit {
            background-color: var(--primary);
            color: white;
        }

        .btn-cancel {
            background-color: var(--light-bg);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .info-box {
            background-color: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--text-secondary);
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
                <div class="dashboard-header">
                    <h1>Edit User</h1>
                    <p><?php echo ucfirst($user_type); ?> - <?php echo htmlspecialchars($user['Name']); ?></p>
                </div>

                <div class="form-container">
                    <?php if (!empty($errors)): ?>
                        <div class="error-list">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="info-box">
                        <strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?> | 
                        <strong>Type:</strong> <?php echo ucfirst($user_type); ?>
                    </div>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['Name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="active" <?php echo ($user['Status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($user['Status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="suspended" <?php echo ($user['Status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="password">New Password (leave blank to keep current)</label>
                            <input type="password" id="password" name="password">
                            <small style="color: var(--text-secondary);">Minimum 6 characters if changing</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">Update User</button>
                            <a href="admin_manage_users.php" class="btn-cancel">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
