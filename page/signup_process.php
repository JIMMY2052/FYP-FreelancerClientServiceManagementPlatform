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

    // Validate input
    if (empty($user_type) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        $_SESSION['form_data'] = $form_data;
        header('Location: signup.php');
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        $_SESSION['form_data'] = $form_data;
        header('Location: signup.php');
        exit();
    }

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        $_SESSION['form_data'] = $form_data;
        header('Location: signup.php');
        exit();
    }

    // Validate password length (minimum 8 characters)
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        $_SESSION['form_data'] = $form_data;
        header('Location: signup.php');
        exit();
    }

    // Validate password contains at least one special character
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'\"<>,.?\/()|`~]/', $password)) {
        $_SESSION['error'] = 'Password must contain at least one special character (!@#$%^&* etc.)';
        $_SESSION['form_data'] = $form_data;
        header('Location: signup.php');
        exit();
    }

    // Validate user type
    if (!in_array($user_type, ['freelancer', 'client'])) {
        $_SESSION['error'] = 'Invalid user type.';
        $_SESSION['form_data'] = $form_data;
        header('Location: signup.php');
        exit();
    }

    // Validate freelancer-specific fields
    if ($user_type === 'freelancer') {
        if (empty($first_name) || empty($last_name)) {
            $_SESSION['error'] = 'First name and last name are required for freelancers.';
            $_SESSION['form_data'] = $form_data;
            header('Location: signup.php');
            exit();
        }
    }

    // Validate client-specific fields
    if ($user_type === 'client') {
        if (empty($company_name)) {
            $_SESSION['error'] = 'Company name is required for clients.';
            $_SESSION['form_data'] = $form_data;
            header('Location: signup.php');
            exit();
        }
    }

    $conn = getDBConnection();

    // Check if email already exists
    if ($user_type === 'freelancer') {
        $table = 'freelancer';
        $stmt = $conn->prepare("SELECT FreelancerID FROM freelancer WHERE Email = ?");
    } else {
        $table = 'client';
        $stmt = $conn->prepare("SELECT ClientID FROM client WHERE Email = ?");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email already exists. Please use a different email or sign in.';
        $_SESSION['form_data'] = $form_data;
        $stmt->close();
        $conn->close();
        header('Location: signup.php');
        exit();
    }
    $stmt->close();

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
        header('Location: login.php');
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
