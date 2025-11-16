<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validate input
    if (empty($user_type) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields.';
        header('Location: login.php');
        exit();
    }
    
    // Validate user type
    if (!in_array($user_type, ['freelancer', 'client'])) {
        $_SESSION['error'] = 'Invalid user type.';
        header('Location: login.php');
        exit();
    }
    
    $conn = getDBConnection();
    
    // Determine table and ID column based on user type
    if ($user_type === 'freelancer') {
        $table = 'freelancer';
        $id_column = 'FreelancerID';
    } else {
        $table = 'client';
        $id_column = 'ClientID';
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT $id_column, Email, Password, Status FROM $table WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['Password'])) {
            // Check if user is active
            if ($user['Status'] === 'active' || $user['Status'] === null || $user['Status'] === '') {
                // Set session variables
                $_SESSION['user_id'] = $user[$id_column];
                $_SESSION['user_type'] = $user_type;
                $_SESSION['email'] = $user['Email'];
                
                // Set remember me cookie if checked
                if ($remember_me) {
                    $cookie_value = base64_encode($user[$id_column] . ':' . $user_type);
                    setcookie('worksnyc_remember', $cookie_value, time() + (86400 * 30), '/'); // 30 days
                }
                
                // Redirect back to login page
                $_SESSION['success'] = 'Login successful! Welcome back.';
                header('Location: login.php');
                exit();
            } else {
                $_SESSION['error'] = 'Your account is not active. Please contact support.';
            }
        } else {
            $_SESSION['error'] = 'Invalid email or password.';
        }
    } else {
        $_SESSION['error'] = 'Invalid email or password.';
    }
    
    $stmt->close();
    $conn->close();
    
    header('Location: login.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>

