<?php 

session_start();

$_title = 'Index';

?>

<?php 
    $jobTitle = $_POST["jobTitle"] ?? '';
    $jobDescription = $_POST["jobDescription"] ?? '';
    $jobSalary = $_POST["jobSalary"] ?? '';
    $deliveryTime = $_POST["deliveryTime"] ?? '';
    $professionalField = $_POST["professionalField"] ?? '';
    $postDate = $_POST["postDate"] ?? '';
    $postTime = $_POST["postTime"] ?? '';
    $deliveryPeriod = $_POST["deliveryPeriod"] ?? '';
    $postDateOption = $_POST["postDateOption"] ?? 'now';
    $questions = $_POST["questions"] ?? [];
?>

<div class="form-container">
    <form method="POST" action="saveJob.php" class="create-job-form">
        <p>Job Title</p>
        <input type="text" value="<?php echo htmlspecialchars($jobTitle); ?>" disabled>
        <input type="hidden" name="title" value="<?php echo htmlspecialchars($jobTitle); ?>">

        <p>Job Description</p>
        <input type="text" value="<?php echo htmlspecialchars($jobDescription); ?>" disabled>
        <input type="hidden" name="description" value="<?php echo htmlspecialchars($jobDescription); ?>">

        <p>Salary</p>
        <input type="text" value="<?php echo htmlspecialchars($jobSalary); ?>" disabled>
        <input type="hidden" name="budget" value="<?php echo htmlspecialchars($jobSalary); ?>">

        <p>Delivery Time (Days)</p>
        <input type="text" value="<?php echo htmlspecialchars($deliveryTime); ?>" disabled>
        <input type="hidden" name="deliveryTime" value="<?php echo htmlspecialchars($deliveryTime); ?>">

        <p>Looking At</p>
        <input type="text" value="<?php echo htmlspecialchars($professionalField); ?>" disabled>

        <p>Posting Date and Time</p>
        <div style="display: flex; gap: 1rem;">
            <input type="text" value="<?php echo htmlspecialchars($postDate); ?>" disabled style="flex: 1;">
            <input type="text" value="<?php echo htmlspecialchars($postTime); ?>" disabled style="flex: 1;">
        </div>
        <input type="hidden" name="postDate" value="<?php echo htmlspecialchars($postDate); ?>">
        <input type="hidden" name="postTime" value="<?php echo htmlspecialchars($postTime); ?>">

        <p>Deadline</p>
        <div style="display: flex; gap: 1rem;">
            <input type="text" value="<?php echo htmlspecialchars($deliveryPeriod); ?>" disabled style="flex: 1;">
        </div>
        <input type="hidden" name="deadline" value="<?php echo htmlspecialchars($deliveryPeriod); ?>">

        <?php if (!empty($questions)): ?>
        <p>Screening Questions</p>
        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e9ecef;">
            <?php foreach ($questions as $index => $question): ?>
                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e9ecef;">
                    <p style="font-weight: 600; color: #2c3e50; margin-bottom: 8px;">
                        Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['text']); ?>
                    </p>
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 8px;">
                        Type: <?php echo $question['type'] === 'yes_no' ? 'Yes/No' : 'Multiple Choice'; ?>
                        <?php if (isset($question['required'])): ?>
                        <span style="color: rgb(159, 232, 112); font-weight: 600;">• Required</span>
                        <?php endif; ?>
                    </p>
                    <?php if ($question['type'] === 'multiple_choice' && !empty($question['options'])): ?>
                    <ul style="margin: 8px 0 0 20px; color: #555;">
                        <?php foreach ($question['options'] as $option): ?>
                            <?php if (!empty($option)): ?>
                            <li><?php echo htmlspecialchars($option); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <!-- Hidden inputs to preserve question data -->
                <input type="hidden" name="questions[<?php echo $index; ?>][text]" value="<?php echo htmlspecialchars($question['text']); ?>">
                <input type="hidden" name="questions[<?php echo $index; ?>][type]" value="<?php echo htmlspecialchars($question['type']); ?>">
                <?php if (isset($question['required'])): ?>
                <input type="hidden" name="questions[<?php echo $index; ?>][required]" value="1">
                <?php endif; ?>
                <?php if (!empty($question['options'])): ?>
                    <?php foreach ($question['options'] as $optIndex => $option): ?>
                        <?php if (!empty($option)): ?>
                        <input type="hidden" name="questions[<?php echo $index; ?>][options][]" value="<?php echo htmlspecialchars($option); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color: #999; font-style: italic;">No screening questions added</p>
        <?php endif; ?>

        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="button" onclick="history.back()" class="back-btn">Back</button>
            <input type="submit" value="Post" class="submit-btn">
        </div>
    </form>
</div>

<?php 

include '../../_foot.php'; 

?>