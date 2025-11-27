<?php 
session_start();

// Check if user is logged in as client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../login.php');
    exit();
}

$_title = 'Add Screening Questions';
include '../../_head.php';

// Get job data from POST
$jobTitle = $_POST["jobTitle"] ?? '';
$jobDescription = $_POST["jobDescription"] ?? '';
$jobSalary = $_POST["jobSalary"] ?? '';
$deliveryTime = $_POST["deliveryTime"] ?? '';
$professionalField = $_POST["professionalField"] ?? '';
$postDate = $_POST["postDate"] ?? '';
$postTime = $_POST["postTime"] ?? '';
$deliveryPeriod = $_POST["deliveryPeriod"] ?? '';

// Validate required fields
if (empty($jobTitle) || empty($jobDescription) || empty($jobSalary) || empty($deliveryPeriod)) {
    $_SESSION['error'] = 'Missing required job information.';
    header('Location: createJob.php');
    exit();
}
?>

<div class="form-container">
    <div class="page-header">
        <h1>Add Screening Questions</h1>
        <p class="subtitle">Add questions to help screen applicants for this job. You can add multiple choice questions or yes/no questions.</p>
    </div>

    <form method="POST" action="jobSummary.php" class="questions-form" id="questionsForm">
        <!-- Hidden fields to preserve job data -->
        <input type="hidden" name="jobTitle" value="<?= htmlspecialchars($jobTitle) ?>">
        <input type="hidden" name="jobDescription" value="<?= htmlspecialchars($jobDescription) ?>">
        <input type="hidden" name="jobSalary" value="<?= htmlspecialchars($jobSalary) ?>">
        <input type="hidden" name="deliveryTime" value="<?= htmlspecialchars($deliveryTime) ?>">
        <input type="hidden" name="professionalField" value="<?= htmlspecialchars($professionalField) ?>">
        <input type="hidden" name="postDate" value="<?= htmlspecialchars($postDate) ?>">
        <input type="hidden" name="postTime" value="<?= htmlspecialchars($postTime) ?>">
        <input type="hidden" name="deliveryPeriod" value="<?= htmlspecialchars($deliveryPeriod) ?>">

        <div id="questionsContainer">
            <!-- Questions will be added here dynamically -->
        </div>

        <button type="button" class="add-question-btn" onclick="addQuestion()">
            <i class="fas fa-plus-circle"></i> Add Question
        </button>

        <div class="form-buttons">
            <button type="button" onclick="history.back()" class="back-btn">Back</button>
            <button type="button" onclick="skipQuestions()" class="skip-btn">Skip Questions</button>
            <input type="submit" value="Continue to Review" class="submit-btn">
        </div>
    </form>
</div>

<style>
    .form-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 10px 0;
    }

    .subtitle {
        color: #666;
        font-size: 0.95rem;
        margin: 0;
    }

    .questions-form {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    #questionsContainer {
        margin-bottom: 25px;
    }

    .question-card {
        background: #f8fafc;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        position: relative;
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
    }

    .remove-question-btn:hover {
        background: #c82333;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-group input[type="text"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-group textarea {
        min-height: 80px;
        resize: vertical;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: rgb(159, 232, 112);
        box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.1);
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
    }

    .options-container {
        margin-top: 15px;
        padding: 15px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .options-container h4 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 12px 0;
    }

    .option-item {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
    }

    .option-item input[type="text"] {
        flex: 1;
    }

    .remove-option-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .remove-option-btn:hover {
        background: #5a6268;
    }

    .add-option-btn {
        background: rgb(159, 232, 112);
        color: #2c3e50;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 10px;
        transition: all 0.3s ease;
    }

    .add-option-btn:hover {
        background: rgb(140, 210, 90);
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

    .form-buttons {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }

    .back-btn,
    .skip-btn,
    .submit-btn {
        flex: 1;
        padding: 14px 20px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
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

    .skip-btn {
        background: #6c757d;
        color: white;
    }

    .skip-btn:hover {
        background: #5a6268;
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

    @media (max-width: 768px) {
        .form-buttons {
            flex-direction: column;
        }

        .option-item {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<script>
let questionCount = 0;

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
            <label for="questionText_${questionCount}">Question Text *</label>
            <textarea name="questions[${questionCount}][text]" id="questionText_${questionCount}" 
                      placeholder="Enter your screening question" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="questionType_${questionCount}">Question Type *</label>
            <select name="questions[${questionCount}][type]" id="questionType_${questionCount}" 
                    onchange="toggleOptions(${questionCount})" required>
                <option value="multiple_choice">Multiple Choice</option>
                <option value="yes_no">Yes/No</option>
            </select>
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" name="questions[${questionCount}][required]" 
                   id="questionRequired_${questionCount}" value="1" checked>
            <label for="questionRequired_${questionCount}">This question is required</label>
        </div>
        
        <div class="options-container" id="optionsContainer_${questionCount}">
            <h4>Answer Options</h4>
            <div id="optionsList_${questionCount}">
                <div class="option-item">
                    <input type="text" name="questions[${questionCount}][options][]" 
                           placeholder="Option 1" required>
                    <button type="button" class="remove-option-btn" 
                            onclick="removeOption(this)">Remove</button>
                </div>
                <div class="option-item">
                    <input type="text" name="questions[${questionCount}][options][]" 
                           placeholder="Option 2" required>
                    <button type="button" class="remove-option-btn" 
                            onclick="removeOption(this)">Remove</button>
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
    const typeSelect = document.getElementById(`questionType_${questionId}`);
    const optionsContainer = document.getElementById(`optionsContainer_${questionId}`);
    
    if (typeSelect.value === 'yes_no') {
        optionsContainer.style.display = 'none';
        // Remove required attribute from options when yes/no
        const optionInputs = optionsContainer.querySelectorAll('input[type="text"]');
        optionInputs.forEach(input => input.required = false);
    } else {
        optionsContainer.style.display = 'block';
        const optionInputs = optionsContainer.querySelectorAll('input[type="text"]');
        optionInputs.forEach(input => input.required = true);
    }
}

function addOption(questionId) {
    const optionsList = document.getElementById(`optionsList_${questionId}`);
    const optionCount = optionsList.children.length + 1;
    
    const optionItem = document.createElement('div');
    optionItem.className = 'option-item';
    optionItem.innerHTML = `
        <input type="text" name="questions[${questionId}][options][]" 
               placeholder="Option ${optionCount}" required>
        <button type="button" class="remove-option-btn" onclick="removeOption(this)">Remove</button>
    `;
    
    optionsList.appendChild(optionItem);
}

function removeOption(button) {
    const optionItem = button.parentElement;
    const optionsList = optionItem.parentElement;
    
    // Only allow removal if there are more than 2 options
    if (optionsList.children.length > 2) {
        optionItem.remove();
    } else {
        alert('You must have at least 2 options for a multiple choice question.');
    }
}

function skipQuestions() {
    // Submit form without questions
    const form = document.getElementById('questionsForm');
    
    // Remove all questions before submitting
    const questionsContainer = document.getElementById('questionsContainer');
    questionsContainer.innerHTML = '';
    
    form.submit();
}

// Add validation before form submission
document.getElementById('questionsForm').addEventListener('submit', function(e) {
    const questions = document.querySelectorAll('.question-card');
    
    if (questions.length === 0) {
        const confirmSkip = confirm('You have not added any screening questions. Continue without questions?');
        if (!confirmSkip) {
            e.preventDefault();
        }
    }
});
</script>

<?php 
include '../../_foot.php'; 
?>
