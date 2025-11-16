<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

$user_type = $_POST['user_type'] ?? '';
$user_id = $_SESSION['user_id'];

// Validate user type matches session
if ($user_type !== $_SESSION['user_type']) {
    $_SESSION['error'] = 'Invalid user type.';
    if ($_SESSION['user_type'] === 'freelancer') {
        header('Location: edit_freelancer_profile.php');
    } else {
        header('Location: edit_client_profile.php');
    }
    exit();
}

$conn = getDBConnection();

if ($user_type === 'client') {
    // Update client profile
    $company_name = $_POST['company_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validate required fields
    if (empty($company_name) || empty($email)) {
        $_SESSION['error'] = 'Company name and email are required.';
        $conn->close();
        header('Location: edit_client_profile.php');
        exit();
    }
    
    // Check if email is already taken by another client
    $stmt = $conn->prepare("SELECT ClientID FROM client WHERE Email = ? AND ClientID != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email is already taken by another account.';
        $stmt->close();
        $conn->close();
        header('Location: edit_client_profile.php');
        exit();
    }
    $stmt->close();
    
    // Update client
    $stmt = $conn->prepare("UPDATE client SET CompanyName = ?, Description = ?, Email = ?, PhoneNo = ?, Address = ? WHERE ClientID = ?");
    $stmt->bind_param("sssssi", $company_name, $description, $email, $phone, $address, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Profile updated successfully!';
        $stmt->close();
        $conn->close();
        header('Location: client_profile.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error updating profile. Please try again.';
        $stmt->close();
    }
    
} else if ($user_type === 'freelancer') {
    // Update freelancer profile
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $education = $_POST['education'] ?? '';
    $social_media = $_POST['social_media'] ?? '';
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $_SESSION['error'] = 'First name, last name, and email are required.';
        $conn->close();
        header('Location: edit_freelancer_profile.php');
        exit();
    }
    
    // Check if email is already taken by another freelancer
    $stmt = $conn->prepare("SELECT FreelancerID FROM freelancer WHERE Email = ? AND FreelancerID != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Email is already taken by another account.';
        $stmt->close();
        $conn->close();
        header('Location: edit_freelancer_profile.php');
        exit();
    }
    $stmt->close();
    
    // Update freelancer
    $stmt = $conn->prepare("UPDATE freelancer SET FirstName = ?, LastName = ?, Email = ?, PhoneNo = ?, Address = ?, Bio = ?, Experience = ?, Education = ?, SocialMediaURL = ? WHERE FreelancerID = ?");
    $stmt->bind_param("sssssssssi", $first_name, $last_name, $email, $phone, $address, $bio, $experience, $education, $social_media, $user_id);
    
    if ($stmt->execute()) {
        // Handle skills update
        $selected_skills = $_POST['skills'] ?? [];
        $proficiencies = $_POST['proficiency'] ?? [];
        
        // Delete existing skills
        $stmt2 = $conn->prepare("DELETE FROM freelancerskill WHERE FreelancerID = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $stmt2->close();
        
        // Insert new skills
        if (!empty($selected_skills)) {
            $stmt3 = $conn->prepare("INSERT INTO freelancerskill (FreelancerID, SkillID, ProficiencyLevel) VALUES (?, ?, ?)");
            foreach ($selected_skills as $skill_value) {
                // Check if it's a numeric ID (existing skill) or a string (new skill)
                if (is_numeric($skill_value)) {
                    $skill_id = (int)$skill_value;
                    $proficiency = $proficiencies[$skill_id] ?? 'Intermediate';
                    $stmt3->bind_param("iis", $user_id, $skill_id, $proficiency);
                    $stmt3->execute();
                } else {
                    // New skill - create it first
                    $skill_name = $skill_value;
                    $stmt4 = $conn->prepare("INSERT INTO skill (SkillName) VALUES (?)");
                    $stmt4->bind_param("s", $skill_name);
                    if ($stmt4->execute()) {
                        $new_skill_id = $conn->insert_id;
                        $proficiency = $proficiencies[$skill_name] ?? 'Intermediate';
                        $stmt3->bind_param("iis", $user_id, $new_skill_id, $proficiency);
                        $stmt3->execute();
                    }
                    $stmt4->close();
                }
            }
            $stmt3->close();
        }
        
        $_SESSION['success'] = 'Profile updated successfully!';
        $stmt->close();
        $conn->close();
        header('Location: freelancer_profile.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error updating profile. Please try again.';
        $stmt->close();
    }
}

$conn->close();

// Redirect back to edit page on error
if ($user_type === 'freelancer') {
    header('Location: edit_freelancer_profile.php');
} else {
    header('Location: edit_client_profile.php');
}
exit();
?>

