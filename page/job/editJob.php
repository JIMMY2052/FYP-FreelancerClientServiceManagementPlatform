<?php
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../login.php');
    exit();
}

$_title = 'Edit Job - WorkSnyc';
require_once '../config.php';

// Get job ID from URL
$jobID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clientID = $_SESSION['user_id'];

if (!$jobID) {
    $_SESSION['error'] = 'Invalid job ID.';
    header('Location: ../my_jobs.php');
    exit();
}

$conn = getDBConnection();

// Fetch job details - verify it belongs to this client
$sql = "SELECT * FROM job WHERE JobID = ? AND ClientID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $jobID, $clientID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Job not found or you do not have permission to edit it.';
    $stmt->close();
    $conn->close();
    header('Location: ../my_jobs.php');
    exit();
}

$job = $result->fetch_assoc();
$stmt->close();

// Fetch existing questions for this job
$sql = "SELECT jq.*, 
               GROUP_CONCAT(jqo.OptionID ORDER BY jqo.DisplayOrder SEPARATOR '|||') as option_ids,
               GROUP_CONCAT(jqo.OptionText ORDER BY jqo.DisplayOrder SEPARATOR '|||') as options
        FROM job_question jq
        LEFT JOIN job_question_option jqo ON jq.QuestionID = jqo.QuestionID
        WHERE jq.JobID = ?
        GROUP BY jq.QuestionID
        ORDER BY jq.QuestionID";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $jobID);
$stmt->execute();
$questions_result = $stmt->get_result();
$existing_questions = $questions_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;
    $deliveryTime = isset($_POST['deliveryTime']) ? intval($_POST['deliveryTime']) : 0;
    $deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : '';
    
    // Validation
    if (empty($title) || empty($description) || empty($budget) || empty($deadline)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } else {
        // Update job
        $sql = "UPDATE job SET Title = ?, Description = ?, Budget = ?, DeliveryTime = ?, Deadline = ? WHERE JobID = ? AND ClientID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdisii', $title, $description, $budget, $deliveryTime, $deadline, $jobID, $clientID);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Handle screening questions
            $questions = $_POST['questions'] ?? [];
            
            // Delete all existing questions and their options (CASCADE will handle options)
            $sql = "DELETE FROM job_question WHERE JobID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $jobID);
            $stmt->execute();
            $stmt->close();
            
            // Insert new questions
            if (!empty($questions)) {
                foreach ($questions as $questionData) {
                    if (!empty($questionData['text'])) {
                        $questionText = trim($questionData['text']);
                        $questionType = $questionData['type'] ?? 'multiple_choice';
                        $isRequired = isset($questionData['required']) ? 1 : 0;
                        
                        // Insert question
                        $sql = "INSERT INTO job_question (JobID, QuestionText, QuestionType, IsRequired) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('issi', $jobID, $questionText, $questionType, $isRequired);
                        $stmt->execute();
                        $questionID = $conn->insert_id;
                        $stmt->close();
                        
                        // Insert options for multiple choice questions
                        if ($questionType === 'multiple_choice' && !empty($questionData['options'])) {
                            $displayOrder = 0;
                            foreach ($questionData['options'] as $optionText) {
                                if (!empty(trim($optionText))) {
                                    $displayOrder++;
                                    $sql = "INSERT INTO job_question_option (QuestionID, OptionText, DisplayOrder) VALUES (?, ?, ?)";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param('isi', $questionID, $optionText, $displayOrder);
                                    $stmt->execute();
                                    $stmt->close();
                                }
                            }
                        }
                    }
                }
            }
            
            $_SESSION['success'] = 'Job updated successfully!';
            $conn->close();
            header('Location: client_job_details.php?id=' . $jobID);
            exit();
        } else {
            $_SESSION['error'] = 'Failed to update job. Please try again.';
        }
        $stmt->close();
    }
}

$conn->close();

require_once '../../_head.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="javascript:history.back()">← Back</a>
    </div>

    <h1 class="page-title">Edit Job</h1>

    <div class="form-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="edit-job-form">
            <div class="form-group">
                <label for="title">Job Title *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($job['Title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Job Description *</label>
                <textarea id="description" name="description" rows="6" required><?= htmlspecialchars($job['Description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="budget">Budget (RM) *</label>
                    <input type="number" id="budget" name="budget" value="<?= htmlspecialchars($job['Budget']) ?>" min="0" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="deliveryTime">Delivery Time (Days)</label>
                    <input type="number" id="deliveryTime" name="deliveryTime" value="<?= htmlspecialchars($job['DeliveryTime'] ?? '') ?>" min="1" step="1">
                </div>
            </div>

            <div class="form-group">
                <label for="deadline">Deadline *</label>
                <input type="date" id="deadline" name="deadline" value="<?= htmlspecialchars(date('Y-m-d', strtotime($job['Deadline']))) ?>" required>
            </div>

            <div class="questions-section">
                <h2>Screening Questions</h2>
                <p class="section-subtitle">Add questions to help screen applicants. You can add multiple choice questions or yes/no questions.</p>
                
                <div id="questionsContainer">
                    <?php if (!empty($existing_questions)): ?>
                        <?php foreach ($existing_questions as $index => $question): ?>
                        <div class="question-card" id="question-<?= $index + 1 ?>">
                            <div class="question-header">
                                <span class="question-number">Question <?= $index + 1 ?></span>
                                <button type="button" class="remove-question-btn" onclick="removeQuestion(<?= $index + 1 ?>)">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                            
                            <div class="form-group">
                                <label>Question Text *</label>
                                <textarea name="questions[<?= $index + 1 ?>][text]" placeholder="Enter your screening question" required><?= htmlspecialchars($question['QuestionText']) ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Question Type *</label>
                                <select name="questions[<?= $index + 1 ?>][type]" onchange="toggleOptions(<?= $index + 1 ?>)" required>
                                    <option value="multiple_choice" <?= $question['QuestionType'] === 'multiple_choice' ? 'selected' : '' ?>>Multiple Choice</option>
                                    <option value="yes_no" <?= $question['QuestionType'] === 'yes_no' ? 'selected' : '' ?>>Yes/No</option>
                                </select>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="questions[<?= $index + 1 ?>][required]" id="questionRequired_<?= $index + 1 ?>" value="1" <?= $question['IsRequired'] ? 'checked' : '' ?>>
                                <label for="questionRequired_<?= $index + 1 ?>">This question is required</label>
                            </div>
                            
                            <div class="options-container" id="optionsContainer_<?= $index + 1 ?>" style="display: <?= $question['QuestionType'] === 'yes_no' ? 'none' : 'block' ?>">
                                <h4>Answer Options</h4>
                                <div id="optionsList_<?= $index + 1 ?>">
                                    <?php if (!empty($question['options'])): ?>
                                        <?php 
                                        $options = explode('|||', $question['options']);
                                        foreach ($options as $option): 
                                        ?>
                                        <div class="option-item">
                                            <input type="text" name="questions[<?= $index + 1 ?>][options][]" value="<?= htmlspecialchars($option) ?>" placeholder="Option" required>
                                            <button type="button" class="remove-option-btn" onclick="removeOption(this)">Remove</button>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="add-option-btn" onclick="addOption(<?= $index + 1 ?>)">
                                    <i class="fas fa-plus"></i> Add Option
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button type="button" class="add-question-btn" onclick="addQuestion()">
                    <i class="fas fa-plus-circle"></i> Add Question
                </button>
            </div>

            <div class="form-actions">
                <a href="javascript:history.back()" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Update Job</button>
            </div>
        </form>
    </div>
</div>

<style>
.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.breadcrumb {
    margin-bottom: 20px;
}

.breadcrumb a {
    color: #666;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: color 0.3s;
}

.breadcrumb a:hover {
    color: rgb(159, 232, 112);
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 25px 0;
}

.form-container {
    background: white;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-success::before {
    content: "✓";
    font-weight: 700;
    font-size: 1.2rem;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
}

.alert-error::before {
    content: "✕";
    font-weight: 700;
    font-size: 1.2rem;
}

.edit-job-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: rgb(159, 232, 112);
    box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
}

.form-group select {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 40px 12px 16px;
    cursor: pointer;
    font-weight: 600;
    color: #2c3e50;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%232c3e50' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 12px;
}

.form-group select:hover {
    border-color: rgb(159, 232, 112);
    background: white;
    box-shadow: 0 2px 8px rgba(159, 232, 112, 0.2);
}

.form-group select:focus {
    border-color: rgb(159, 232, 112);
    box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.15);
    background: white;
}

.form-group select option {
    padding: 12px;
    background: white;
    color: #2c3e50;
    font-weight: 500;
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    justify-content: flex-end;
}

.btn-cancel,
.btn-submit {
    padding: 14px 32px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-block;
}

.btn-cancel {
    background: #f8f9fa;
    color: #2c3e50;
    border: 2px solid #e9ecef;
}

.btn-cancel:hover {
    background: #fff;
    border-color: #ddd;
}

.btn-submit {
    background: rgb(159, 232, 112);
    color: #2c3e50;
}

.btn-submit:hover {
    background: rgb(140, 210, 90);
    box-shadow: 0 4px 12px rgba(159, 232, 112, 0.3);
    transform: translateY(-2px);
}

/* Screening Questions Section */
.questions-section {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #e9ecef;
}

.questions-section h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.section-subtitle {
    color: #666;
    font-size: 0.9rem;
    margin: 0 0 25px 0;
}

#questionsContainer {
    margin-bottom: 20px;
}

.question-card {
    background: #f8fafc;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
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

.remove-question-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.remove-question-btn:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: rgb(159, 232, 112);
}

.checkbox-group label {
    margin: 0;
    cursor: pointer;
    font-weight: 500;
    color: #2c3e50;
}

.options-container {
    margin-top: 15px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    border: 2px solid #e9ecef;
}

.options-container h4 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.options-container h4::before {
    content: "📝";
    font-size: 1.1rem;
}

.option-item {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
    align-items: center;
    position: relative;
}

.option-item::before {
    content: "•";
    color: rgb(159, 232, 112);
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.option-item input[type="text"] {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.option-item input[type="text"]:hover {
    border-color: rgb(159, 232, 112);
    background: white;
}

.option-item input[type="text"]:focus {
    outline: none;
    border-color: rgb(159, 232, 112);
    box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.15);
    background: white;
}

.remove-option-btn {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 10px 16px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.3s ease;
}

.remove-option-btn:hover {
    background: #f1aeb5;
    border-color: #ea868f;
    transform: scale(1.05);
}

.add-option-btn {
    background: linear-gradient(135deg, rgb(159, 232, 112) 0%, rgb(140, 210, 90) 100%);
    color: #2c3e50;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 700;
    margin-top: 12px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 6px rgba(159, 232, 112, 0.3);
}

.add-option-btn:hover {
    background: linear-gradient(135deg, rgb(140, 210, 90) 0%, rgb(120, 190, 70) 100%);
    box-shadow: 0 4px 12px rgba(159, 232, 112, 0.4);
    transform: translateY(-2px);
}

.add-question-btn {
    width: 100%;
    padding: 14px 20px;
    background: white;
    color: #2c3e50;
    border: 2px dashed rgb(159, 232, 112);
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 25px;
}

.add-question-btn:hover {
    background: #f8fef5;
    border-style: solid;
}

@media (max-width: 768px) {
    .form-container {
        padding: 25px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-cancel,
    .btn-submit {
        width: 100%;
    }

    .option-item {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script>
let questionCount = <?= count($existing_questions) ?>;

function addQuestion() {
    questionCount++;
    const container = document.getElementById('questionsContainer');
    
    const questionCard = document.createElement('div');
    questionCard.className = 'question-card';
    questionCard.id = `question-${questionCount}`;
    
    questionCard.innerHTML = `
        <div class="question-header">
            <span class="question-number">Question ${questionCount}</span>
            <button type="button" class="remove-question-btn" onclick="removeQuestion(${questionCount})">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
        
        <div class="form-group">
            <label>Question Text *</label>
            <textarea name="questions[${questionCount}][text]" placeholder="Enter your screening question" required></textarea>
        </div>
        
        <div class="form-group">
            <label>Question Type *</label>
            <select name="questions[${questionCount}][type]" onchange="toggleOptions(${questionCount})" required>
                <option value="multiple_choice">Multiple Choice</option>
                <option value="yes_no">Yes/No</option>
            </select>
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" name="questions[${questionCount}][required]" id="questionRequired_${questionCount}" value="1" checked>
            <label for="questionRequired_${questionCount}">This question is required</label>
        </div>
        
        <div class="options-container" id="optionsContainer_${questionCount}">
            <h4>Answer Options</h4>
            <div id="optionsList_${questionCount}">
                <div class="option-item">
                    <input type="text" name="questions[${questionCount}][options][]" placeholder="Option 1" required>
                    <button type="button" class="remove-option-btn" onclick="removeOption(this)">Remove</button>
                </div>
                <div class="option-item">
                    <input type="text" name="questions[${questionCount}][options][]" placeholder="Option 2" required>
                    <button type="button" class="remove-option-btn" onclick="removeOption(this)">Remove</button>
                </div>
            </div>
            <button type="button" class="add-option-btn" onclick="addOption(${questionCount})">
                <i class="fas fa-plus"></i> Add Option
            </button>
        </div>
    `;
    
    container.appendChild(questionCard);
}

function removeQuestion(id) {
    const question = document.getElementById(`question-${id}`);
    if (question) {
        question.remove();
        updateQuestionNumbers();
    }
}

function updateQuestionNumbers() {
    const questions = document.querySelectorAll('.question-card');
    questions.forEach((question, index) => {
        const numberSpan = question.querySelector('.question-number');
        if (numberSpan) {
            numberSpan.textContent = `Question ${index + 1}`;
        }
    });
}

function toggleOptions(questionId) {
    const select = document.querySelector(`#question-${questionId} select[name*="[type]"]`);
    const optionsContainer = document.getElementById(`optionsContainer_${questionId}`);
    
    if (select && optionsContainer) {
        if (select.value === 'yes_no') {
            optionsContainer.style.display = 'none';
            const optionInputs = optionsContainer.querySelectorAll('input[type="text"]');
            optionInputs.forEach(input => input.required = false);
        } else {
            optionsContainer.style.display = 'block';
            const optionInputs = optionsContainer.querySelectorAll('input[type="text"]');
            optionInputs.forEach(input => input.required = true);
        }
    }
}

function addOption(questionId) {
    const optionsList = document.getElementById(`optionsList_${questionId}`);
    const optionCount = optionsList.children.length + 1;
    
    const optionItem = document.createElement('div');
    optionItem.className = 'option-item';
    optionItem.innerHTML = `
        <input type="text" name="questions[${questionId}][options][]" placeholder="Option ${optionCount}" required>
        <button type="button" class="remove-option-btn" onclick="removeOption(this)">Remove</button>
    `;
    
    optionsList.appendChild(optionItem);
}

function removeOption(button) {
    const optionItem = button.parentElement;
    const optionsList = optionItem.parentElement;
    
    if (optionsList.children.length > 2) {
        optionItem.remove();
    } else {
        alert('You must have at least 2 options for a multiple choice question.');
    }
}
</script>

<?php require_once '../../_foot.php'; ?>
