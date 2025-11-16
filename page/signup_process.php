<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($user_type) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        header('Location: signup.php');
        exit();
    }
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: signup.php');
        exit();
    }
    
    // Validate password length
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long.';
        header('Location: signup.php');
        exit();
    }
    
    // Validate user type
    if (!in_array($user_type, ['freelancer', 'client'])) {
        $_SESSION['error'] = 'Invalid user type.';
        header('Location: signup.php');
        exit();
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
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        
        if (empty($first_name) || empty($last_name)) {
            $_SESSION['error'] = 'First name and last name are required for freelancers.';
            $conn->close();
            header('Location: signup.php');
            exit();
        }
        
        $stmt = $conn->prepare("INSERT INTO freelancer (FirstName, LastName, Email, Password, Status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);
    } else {
        $company_name = $_POST['company_name'] ?? '';
        
        if (empty($company_name)) {
            $_SESSION['error'] = 'Company name is required for clients.';
            $conn->close();
            header('Location: signup.php');
            exit();
        }
        
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
        $stmt->close();
    }
    
    $conn->close();
    header('Location: signup.php');
    exit();
} else {
    header('Location: signup.php');
    exit();
}
?>

