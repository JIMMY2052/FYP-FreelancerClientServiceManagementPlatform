<?php
session_start();

// Only freelancers can apply for jobs
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: ../login.php');
    exit();
}

// Check if user is deleted
require_once '../checkUserStatus.php';

require_once '../config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse_job.php');
    exit();
}

// Get form data
$jobID = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
$freelancerID = $_SESSION['user_id'];
$coverLetter = null;
$proposedBudget = null;
$estimatedDuration = null;
$answers = isset($_POST['answers']) ? $_POST['answers'] : [];

// Debug logging
error_log('[applyJob] JobID: ' . $jobID . ', FreelancerID: ' . $freelancerID);
error_log('[applyJob] Answers: ' . print_r($answers, true));

// Validate required fields
if (!$jobID) {
    error_log('[applyJob] Error: Invalid job ID');
    $_SESSION['error'] = 'Invalid job ID.';
    header('Location: browse_job.php');
    exit();
}

// Create PDO connection
if (!function_exists('getPDOConnection')) {
    function getPDOConnection(): PDO
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            return new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection error: ' . $e->getMessage());
        }
    }
}

$pdo = getPDOConnection();

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if job exists and is available
    $sqlCheck = "SELECT JobID, Status, Budget FROM job WHERE JobID = :jobID";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([':jobID' => $jobID]);
    $job = $stmtCheck->fetch();

    if (!$job) {
        throw new Exception('Job not found.');
    }

    if ($job['Status'] !== 'available') {
        throw new Exception('This job is no longer available for applications.');
    }

    // Check freelancer wallet balance
    $jobBudget = floatval($job['Budget']);
    $sqlWallet = "SELECT Balance FROM wallet WHERE UserID = :userId";
    $stmtWallet = $pdo->prepare($sqlWallet);
    $stmtWallet->execute([':userId' => $freelancerID]);
    $wallet = $stmtWallet->fetch();

    $walletBalance = $wallet ? floatval($wallet['Balance']) : 0;

    if ($walletBalance < $jobBudget) {
        throw new Exception('Insufficient wallet balance. Your wallet balance (RM ' . number_format($walletBalance, 2) . ') must be at least RM ' . number_format($jobBudget, 2) . ' to apply for this job. This ensures security for both parties in case of contract breach. Please top up your wallet.');
    }

    // Check if freelancer has already applied to this job
    $sqlDuplicate = "SELECT ApplicationID FROM job_application 
                     WHERE JobID = :jobID AND FreelancerID = :freelancerID";
    $stmtDuplicate = $pdo->prepare($sqlDuplicate);
    $stmtDuplicate->execute([
        ':jobID' => $jobID,
        ':freelancerID' => $freelancerID
    ]);

    if ($stmtDuplicate->fetch()) {
        throw new Exception('You have already applied to this job.');
    }

    // Fetch all required questions for this job
    $sqlQuestions = "SELECT QuestionID, IsRequired FROM job_question 
                     WHERE JobID = :jobID AND IsRequired = 1";
    $stmtQuestions = $pdo->prepare($sqlQuestions);
    $stmtQuestions->execute([':jobID' => $jobID]);
    $requiredQuestions = $stmtQuestions->fetchAll();

    // Validate that all required questions are answered
    foreach ($requiredQuestions as $question) {
        $questionID = $question['QuestionID'];
        if (!isset($answers[$questionID]) || empty($answers[$questionID])) {
            throw new Exception('Please answer all required questions.');
        }
    }

    // Insert job application
    $sqlApplication = "INSERT INTO job_application 
                       (JobID, FreelancerID, CoverLetter, ProposedBudget, EstimatedDuration, Status) 
                       VALUES (:jobID, :freelancerID, :coverLetter, :proposedBudget, :estimatedDuration, 'pending')";

    $stmtApplication = $pdo->prepare($sqlApplication);
    $stmtApplication->execute([
        ':jobID' => $jobID,
        ':freelancerID' => $freelancerID,
        ':coverLetter' => $coverLetter,
        ':proposedBudget' => $proposedBudget,
        ':estimatedDuration' => $estimatedDuration
    ]);

    // Get the newly created application ID
    $applicationID = $pdo->lastInsertId();

    // Insert answers to screening questions
    if (!empty($answers)) {
        $sqlAnswer = "INSERT INTO job_application_answer 
                      (ApplicationID, QuestionID, FreelancerID, JobID, SelectedOptionID, AnswerText) 
                      VALUES (:applicationID, :questionID, :freelancerID, :jobID, :selectedOptionID, :answerText)";

        $stmtAnswer = $pdo->prepare($sqlAnswer);

        foreach ($answers as $questionID => $answer) {
            // Fetch question type to determine how to store the answer
            $sqlQuestionType = "SELECT QuestionType FROM job_question WHERE QuestionID = :questionID";
            $stmtQuestionType = $pdo->prepare($sqlQuestionType);
            $stmtQuestionType->execute([':questionID' => $questionID]);
            $questionData = $stmtQuestionType->fetch();

            if ($questionData) {
                $selectedOptionID = null;
                $answerText = null;

                if ($questionData['QuestionType'] === 'multiple_choice') {
                    // For multiple choice, store the selected option ID
                    $selectedOptionID = intval($answer);

                    // Get the option text for answer text field
                    $sqlOptionText = "SELECT OptionText FROM job_question_option WHERE OptionID = :optionID";
                    $stmtOptionText = $pdo->prepare($sqlOptionText);
                    $stmtOptionText->execute([':optionID' => $selectedOptionID]);
                    $optionData = $stmtOptionText->fetch();
                    $answerText = $optionData ? $optionData['OptionText'] : null;
                } else if ($questionData['QuestionType'] === 'yes_no') {
                    // For yes/no, store the text answer (Yes/No)
                    $answerText = $answer;
                }

                // Insert the answer
                $stmtAnswer->execute([
                    ':applicationID' => $applicationID,
                    ':questionID' => $questionID,
                    ':freelancerID' => $freelancerID,
                    ':jobID' => $jobID,
                    ':selectedOptionID' => $selectedOptionID,
                    ':answerText' => $answerText
                ]);
            }
        }
    }

    // Commit transaction
    $pdo->commit();

    error_log('[applyJob] Success: Application submitted for JobID: ' . $jobID . ', ApplicationID: ' . $applicationID);

    // Set success message
    $_SESSION['success'] = 'Your application has been submitted successfully!';

    // Redirect to job details or applications page
    header("Location: job_details.php?job_id=$jobID");
    exit();
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    error_log('[applyJob] Error: ' . $e->getMessage());
    error_log('[applyJob] Stack trace: ' . $e->getTraceAsString());

    $_SESSION['error'] = $e->getMessage();
    header("Location: answer_questions.php?job_id=$jobID");
    exit();
}
