<?php
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit();
}

$conn = getDBConnection();
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $status = $_POST['status'] ?? 'active';

    // Validation
    if (empty($user_type) || !in_array($user_type, ['freelancer', 'client'])) {
        $errors[] = 'Please select a valid user type.';
    }
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        if ($user_type === 'freelancer') {
            // Insert freelancer
            $stmt = $conn->prepare("INSERT INTO freelancer (Name, Email, Password, Status, CreatedAt) VALUES (?, ?, ?, ?, NOW())");
            if (!$stmt) {
                $errors[] = 'Database error: ' . $conn->error;
            } else {
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $status);
                if ($stmt->execute()) {
                    $success = true;
                    $_SESSION['success'] = 'Freelancer created successfully!';
                } else {
                    if (strpos($stmt->error, 'Duplicate entry') !== false) {
                        $errors[] = 'This email is already registered.';
                    } else {
                        $errors[] = 'Error creating freelancer: ' . $stmt->error;
                    }
                }
                $stmt->close();
            }
        } else {
            // Insert client
            $stmt = $conn->prepare("INSERT INTO client (CompanyName, Email, Password, Status, CreatedAt) VALUES (?, ?, ?, ?, NOW())");
            if (!$stmt) {
                $errors[] = 'Database error: ' . $conn->error;
            } else {
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $status);
                if ($stmt->execute()) {
                    $success = true;
                    $_SESSION['success'] = 'Client created successfully!';
                } else {
                    if (strpos($stmt->error, 'Duplicate entry') !== false) {
                        $errors[] = 'This email is already registered.';
                    } else {
                        $errors[] = 'Error creating client: ' . $stmt->error;
                    }
                }
                $stmt->close();
            }
        }

        if ($success) {
            header('Location: admin_manage_users.php');
            exit();
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
    <title>Add User - WorkSnyc Admin</title>
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
                    <h1>Add New User</h1>
                    <p>Create a new freelancer or client account</p>
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

                    <form method="POST" action="add_user.php">
                        <div class="form-group">
                            <label for="user_type">User Type *</label>
                            <select id="user_type" name="user_type" required>
                                <option value="">Select a user type</option>
                                <option value="freelancer" <?php echo isset($_POST['user_type']) && $_POST['user_type'] === 'freelancer' ? 'selected' : ''; ?>>Freelancer</option>
                                <option value="client" <?php echo isset($_POST['user_type']) && $_POST['user_type'] === 'client' ? 'selected' : ''; ?>>Client</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required>
                            <small style="color: var(--text-secondary);">Minimum 6 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit">Create User</button>
                            <a href="admin_manage_users.php" class="btn-cancel">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
