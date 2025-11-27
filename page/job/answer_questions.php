<?php 
session_start();

// Only freelancers can answer job questions
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header('Location: ../login.php');
    exit();
}

$_title = 'Answer Screening Questions';
include '../../_head.php';
require_once '../config.php';

// Get job ID from URL
$jobID = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

if (!$jobID) {
    header('Location: browse_job.php');
    exit();
}

if (!function_exists('getPDOConnection')) {
    function getPDOConnection(): PDO {
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

// Fetch job details
try {
    $sql = "SELECT j.*, c.CompanyName 
            FROM job j
            INNER JOIN client c ON j.ClientID = c.ClientID
            WHERE j.JobID = :jobID AND j.Status = 'available'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':jobID' => $jobID]);
    $job = $stmt->fetch();
    
    if (!$job) {
        $_SESSION['error'] = 'Job not found or no longer available.';
        header('Location: browse_job.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('[answer_questions] Job fetch failed: ' . $e->getMessage());
    die('Database error');
}

// Fetch screening questions for this job
try {
    $sql = "SELECT QuestionID, QuestionText, QuestionType, IsRequired 
            FROM job_question 
            WHERE JobID = :jobID 
            ORDER BY QuestionID ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':jobID' => $jobID]);
    $questions = $stmt->fetchAll();
    
    // Fetch options for each question
    for ($i = 0; $i < count($questions); $i++) {
        if ($questions[$i]['QuestionType'] === 'multiple_choice') {
            $sqlOpt = "SELECT OptionID, OptionText 
                       FROM job_question_option 
                       WHERE QuestionID = :questionID 
                       ORDER BY DisplayOrder ASC";
            
            $stmtOpt = $pdo->prepare($sqlOpt);
            $stmtOpt->execute([':questionID' => $questions[$i]['QuestionID']]);
            $questions[$i]['options'] = $stmtOpt->fetchAll();
        } else {
            $questions[$i]['options'] = [];
        }
    }
    
} catch (PDOException $e) {
    error_log('[answer_questions] Questions fetch failed: ' . $e->getMessage());
    $questions = [];
}

?>

<div class="form-container">
    <div class="page-header">
        <h1>Screening Questions</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); endif; ?>
        
        <div class="job-info-banner">
            <div class="job-info-item">
                <i class="fas fa-briefcase"></i>
                <span><?= htmlspecialchars($job['Title']) ?></span>
            </div>
            <div class="job-info-item">
                <i class="fas fa-building"></i>
                <span><?= htmlspecialchars($job['CompanyName']) ?></span>
            </div>
            <div class="job-info-item">
                <i class="fas fa-dollar-sign"></i>
                <span>RM <?= number_format($job['Budget'], 2) ?></span>
            </div>
        </div>
        <?php if (!empty($questions)): ?>
        <p class="subtitle">Please answer the following questions to complete your application.</p>
        <?php else: ?>
        <p class="subtitle">No screening questions for this job. You can proceed directly to apply.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($questions)): ?>
    <form method="POST" action="applyJob.php" class="answers-form" id="answersForm">
        <input type="hidden" name="job_id" value="<?= $jobID ?>">
        
        <?php if (!empty($questions)): ?>
        <!-- Screening Questions Section -->
        <div class="screening-questions-section">
            <h3 class="section-title">
                <i class="fas fa-clipboard-question"></i>
                Screening Questions
            </h3>
        </div>
        <?php endif; ?>
        
        <div class="questions-container">
            <?php foreach ($questions as $index => $question): ?>
            <div class="question-card">
                <div class="question-header">
                    <span class="question-number">Question <?= $index + 1 ?></span>
                    <?php if ($question['IsRequired']): ?>
                    <span class="required-badge">Required</span>
                    <?php endif; ?>
                </div>
                
                <div class="question-text">
                    <?= nl2br(htmlspecialchars($question['QuestionText'])) ?>
                </div>
                
                <div class="answer-section">
                    <?php if ($question['QuestionType'] === 'yes_no'): ?>
                        <!-- Yes/No Question -->
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" 
                                       name="answers[<?= $question['QuestionID'] ?>]" 
                                       value="yes" 
                                       <?= $question['IsRequired'] ? 'required' : '' ?>>
                                <span class="radio-label">Yes</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" 
                                       name="answers[<?= $question['QuestionID'] ?>]" 
                                       value="no" 
                                       <?= $question['IsRequired'] ? 'required' : '' ?>>
                                <span class="radio-label">No</span>
                            </label>
                        </div>
                    <?php else: ?>
                        <!-- Multiple Choice Question -->
                        <div class="radio-group">
                            <?php foreach ($question['options'] as $option): ?>
                            <label class="radio-option">
                                <input type="radio" 
                                       name="answers[<?= $question['QuestionID'] ?>]" 
                                       value="<?= $option['OptionID'] ?>" 
                                       <?= $question['IsRequired'] ? 'required' : '' ?>>
                                <span class="radio-label"><?= htmlspecialchars($option['OptionText']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="form-buttons">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                Submit Application
            </button>
        </div>
    </form>
    <?php else: ?>
    <!-- No questions, show application form only -->
    <form method="POST" action="applyJob.php" class="answers-form" id="answersForm">
        <input type="hidden" name="job_id" value="<?= $jobID ?>">
        
        <div class="no-questions-message">
            <i class="fas fa-info-circle"></i>
            <p>This job has no screening questions.</p>
        </div>
        
        <div class="form-buttons">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i>
                Submit Application
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<style>
    .form-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert i {
        font-size: 1.2rem;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 15px 0;
    }

    .job-info-banner {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
        padding: 15px 20px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        margin-bottom: 15px;
    }

    .job-info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #555;
        font-size: 0.9rem;
    }

    .job-info-item i {
        color: rgb(159, 232, 112);
    }

    .subtitle {
        color: #666;
        font-size: 0.95rem;
        margin: 0;
    }

    .answers-form {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Application Details Section */
    .application-details-section,
    .screening-questions-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: rgb(159, 232, 112);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .required-star {
        color: #dc3545;
    }

    .form-textarea,
    .form-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .form-textarea:focus,
    .form-input:focus {
        outline: none;
        border-color: rgb(159, 232, 112);
        background: white;
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-hint {
        display: block;
        color: #6c757d;
        font-size: 0.85rem;
        margin-top: 6px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .questions-container {
        margin-bottom: 25px;
    }

    .question-card {
        background: #f8fafc;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
    }

    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .question-number {
        font-weight: 700;
        color: #2c3e50;
        font-size: 1rem;
    }

    .required-badge {
        background: #dc3545;
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .question-text {
        font-size: 1.05rem;
        color: #2c3e50;
        line-height: 1.6;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .answer-section {
        margin-top: 15px;
    }

    .radio-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .radio-option {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .radio-option:hover {
        border-color: rgb(159, 232, 112);
        background: #f8fef5;
    }

    .radio-option input[type="radio"] {
        width: 20px;
        height: 20px;
        margin: 0;
        cursor: pointer;
        accent-color: rgb(159, 232, 112);
    }

    .radio-option input[type="radio"]:checked + .radio-label {
        color: rgb(159, 232, 112);
        font-weight: 600;
    }

    .radio-option:has(input[type="radio"]:checked) {
        border-color: rgb(159, 232, 112);
        background: #f0fce8;
    }

    .radio-label {
        flex: 1;
        margin-left: 12px;
        color: #2c3e50;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .form-buttons {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }

    .back-btn,
    .submit-btn {
        flex: 1;
        padding: 14px 20px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .back-btn {
        background: #f8fafc;
        color: #2c3e50;
        border: 2px solid #e9ecef;
    }

    .back-btn:hover {
        background: #fff;
        border-color: #ddd;
    }

    .submit-btn {
        background: rgb(159, 232, 112);
        color: #2c3e50;
    }

    .submit-btn:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
        transform: translateY(-2px);
    }

    /* No Questions Message */
    .no-questions-message {
        background: white;
        border-radius: 16px;
        padding: 60px 30px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .no-questions-message i {
        font-size: 3rem;
        color: rgb(159, 232, 112);
        margin-bottom: 20px;
    }

    .no-questions-message p {
        font-size: 1.1rem;
        color: #666;
        margin-bottom: 25px;
    }

    .apply-direct-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 14px 30px;
        background: rgb(159, 232, 112);
        color: #2c3e50;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .apply-direct-btn:hover {
        background: rgb(140, 210, 90);
        box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .job-info-banner {
            flex-direction: column;
            gap: 10px;
        }

        .form-buttons {
            flex-direction: column;
        }

        .question-card {
            padding: 20px;
        }

        .radio-option {
            padding: 12px 15px;
        }

        .no-questions-message {
            padding: 40px 20px;
        }

        .no-questions-message i {
            font-size: 2.5rem;
        }
    }
</style>

<script>
// Form validation
document.getElementById('answersForm')?.addEventListener('submit', function(e) {
    // Validate required screening questions
    const requiredQuestions = document.querySelectorAll('.question-card:has(.required-badge)');
    let isValid = true;
    let errorMessage = '';
    
    requiredQuestions.forEach(questionCard => {
        const radios = questionCard.querySelectorAll('input[type="radio"]');
        const isAnswered = Array.from(radios).some(radio => radio.checked);
        
        if (!isAnswered) {
            isValid = false;
            errorMessage += 'Please answer all required screening questions.\\n';
            questionCard.style.borderColor = '#dc3545';
            setTimeout(() => {
                questionCard.style.borderColor = '#e9ecef';
            }, 2000);
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert(errorMessage);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

// Reset border color when radio is selected
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const questionCard = this.closest('.question-card');
        questionCard.style.borderColor = '#e9ecef';
    });
});
</script>

<?php 
include '../../_foot.php'; 
?>
