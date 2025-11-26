<?php

session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: /index.php');
    exit();
}

// Include database config
require_once __DIR__ . '/../../page/config.php';

// Get form data
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
$deliveryTime = isset($_POST['deliveryTime']) ? intval($_POST['deliveryTime']) : 0;
$postDate = isset($_POST['postDate']) ? trim($_POST['postDate']) : '';
$postTime = isset($_POST['postTime']) ? trim($_POST['postTime']) : '';
$deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : '';
$questions = isset($_POST['questions']) ? $_POST['questions'] : [];

// Combine postDate and postTime into a single datetime
$postDateTime = $postDate . ' ' . $postTime;

// Validate input
if (empty($title) || empty($description) || empty($budget) || empty($deadline) || empty($postDate) || empty($postTime)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    header('Location: /job/create/createJob.php?error=missing_fields');
    exit();
}

try {
    $conn = getDBConnection();
    
    // Insert job into database
    $clientID = $_SESSION['user_id'];
    $status = 'available'; // Default status when job is created
    
    $stmt = $conn->prepare("INSERT INTO job (ClientID, Title, Description, Budget, DeliveryTime, Deadline, Status, PostDate) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdisss", $clientID, $title, $description, $budget, $deliveryTime, $deadline, $status, $postDateTime);
    
    if ($stmt->execute()) {
        $jobID = $stmt->insert_id;
        $stmt->close();
        
        // Save screening questions if any
        if (!empty($questions)) {
            foreach ($questions as $question) {
                if (empty($question['text'])) continue;
                
                $questionText = trim($question['text']);
                $questionType = $question['type'] ?? 'multiple_choice';
                $isRequired = isset($question['required']) ? 1 : 0;
                
                // Insert question
                $stmtQ = $conn->prepare("INSERT INTO job_question (JobID, QuestionText, QuestionType, IsRequired) 
                                         VALUES (?, ?, ?, ?)");
                $stmtQ->bind_param("issi", $jobID, $questionText, $questionType, $isRequired);
                
                if ($stmtQ->execute()) {
                    $questionID = $stmtQ->insert_id;
                    $stmtQ->close();
                    
                    // Insert options for multiple choice questions
                    if ($questionType === 'multiple_choice' && !empty($question['options'])) {
                        $displayOrder = 0;
                        foreach ($question['options'] as $optionText) {
                            if (empty($optionText)) continue;
                            
                            $optionText = trim($optionText);
                            $stmtO = $conn->prepare("INSERT INTO job_question_option (QuestionID, OptionText, DisplayOrder) 
                                                     VALUES (?, ?, ?)");
                            $stmtO->bind_param("isi", $questionID, $optionText, $displayOrder);
                            $stmtO->execute();
                            $stmtO->close();
                            $displayOrder++;
                        }
                    }
                }
            }
        }
        
        $_SESSION['success'] = 'Job posted successfully!';
        $_SESSION['new_job_id'] = $jobID;
        $conn->close();
        
        // Redirect to my jobs page
        header('Location: /page/my_jobs.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error creating job. Please try again.';
        $stmt->close();
        $conn->close();
        header('Location: /job/create/createJob.php?error=insert_failed');
        exit();
    }
    
} catch (Exception $e) {
    // Log error (optional)
    error_log('Database error: ' . $e->getMessage());
    
    $_SESSION['error'] = 'Database error. Please try again.';
    header('Location: /job/create/createJob.php?error=db_error');
    exit();
}

?>