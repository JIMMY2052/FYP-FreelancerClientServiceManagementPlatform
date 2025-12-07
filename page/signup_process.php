<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $company_name = $_POST['company_name'] ?? '';

    // Store form data for re-display on error
    $form_data = [
        'user_type' => $user_type,
        'email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'company_name' => $company_name
    ];
    // Field-specific validation errors (lengths match DB schema)
    $errors = [];

    // user_type (must be freelancer or client)
    if (empty($user_type)) {
        $errors['user_type'] = 'Please select a user type.';
    } elseif (!in_array($user_type, ['freelancer', 'client'])) {
        $errors['user_type'] = 'Invalid user type.';
    }

    // email (VARCHAR(255))
    if (empty($email)) {
        $errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 255) {
        $errors['email'] = 'Email must not exceed 255 characters.';
    }

    // password (stored as hash in VARCHAR(255), so only minimum & complexity constraints)
    if (empty($password)) {
        $errors['password'] = 'Please create a password.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[!@#$%^&*()_+\-=[\]{};:\'"<>,.?\/()|`~]/', $password)) {
        $errors['password'] = 'Password must contain at least one special character (!@#$%^&* etc.).';
    }

    // confirm_password
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // freelancer-specific: FirstName, LastName (VARCHAR(100))
    if ($user_type === 'freelancer') {
        if (empty($first_name)) {
            $errors['first_name'] = 'First name is required for freelancers.';
        } elseif (strlen($first_name) > 100) {
            $errors['first_name'] = 'First name must not exceed 100 characters.';
        }

        if (empty($last_name)) {
            $errors['last_name'] = 'Last name is required for freelancers.';
        } elseif (strlen($last_name) > 100) {
            $errors['last_name'] = 'Last name must not exceed 100 characters.';
        }
    }

    // client-specific: CompanyName (VARCHAR(255))
    if ($user_type === 'client') {
        if (empty($company_name)) {
            $errors['company_name'] = 'Company name is required for clients.';
        } elseif (strlen($company_name) > 255) {
            $errors['company_name'] = 'Company name must not exceed 255 characters.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $form_data;

        // Separate banner message per role for clarity
        if ($user_type === 'freelancer') {
            $_SESSION['error'] = 'Please correct the highlighted freelancer fields.';
        } elseif ($user_type === 'client') {
            $_SESSION['error'] = 'Please correct the highlighted client fields.';
        } else {
            $_SESSION['error'] = 'Please correct the highlighted fields.';
        }

        header('Location: signup.php');
        exit();
    }

    $conn = getDBConnection();

    // Check if email already exists in either freelancer or client table
    $stmt = $conn->prepare("SELECT 'freelancer' AS user_type FROM freelancer WHERE Email = ?
                            UNION ALL
                            SELECT 'client' AS user_type FROM client WHERE Email = ?");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['errors'] = ['email' => 'Email already exists. Please use a different email or sign in.'];
        $_SESSION['form_data'] = $form_data;
        $stmt->close();
        $conn->close();
        header('Location: signup.php');
        exit();
    }
    $stmt->close();

    // For client signups, also ensure CompanyName is unique
    if ($user_type === 'client' && $company_name !== '') {
        $stmt = $conn->prepare("SELECT ClientID FROM client WHERE CompanyName = ?");
        $stmt->bind_param("s", $company_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['errors'] = ['company_name' => 'Company name already exists. Please use a different name.'];
            $_SESSION['form_data'] = $form_data;
            $_SESSION['error'] = 'Please correct the highlighted client fields.';
            $stmt->close();
            $conn->close();
            header('Location: signup.php');
            exit();
        }

        $stmt->close();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    if ($user_type === 'freelancer') {
        $stmt = $conn->prepare("INSERT INTO freelancer (FirstName, LastName, Email, Password, Status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);
    } else {
        $stmt = $conn->prepare("INSERT INTO client (CompanyName, Email, Password, Status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("sss", $company_name, $email, $hashed_password);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Account created successfully! You can now sign in.';
        $stmt->close();
        $conn->close();
        header('Location: signup.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error creating account. Please try again.';
        $_SESSION['form_data'] = $form_data;
        $stmt->close();
        $conn->close();
        header('Location: signup.php');
        exit();
    }
} else {
    header('Location: signup.php');
    exit();
}
