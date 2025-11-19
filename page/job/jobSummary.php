<?php 

session_start();

$_title = 'Index';
include '../../_head.php'; 

?>

<?php 
    $jobTitle = $_POST["jobTitle"] ?? '';
    $jobDescription = $_POST["jobDescription"] ?? '';
    $jobSalary = $_POST["jobSalary"] ?? '';
    $professionalField = $_POST["professionalField"] ?? '';
    $postDate = $_POST["postDate"] ?? '';
    $postTime = $_POST["postTime"] ?? '';
    $deliveryPeriod = $_POST["deliveryPeriod"] ?? '';
    $postDateOption = $_POST["postDateOption"] ?? 'now';
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

        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="button" onclick="history.back()" class="back-btn">Back</button>
            <input type="submit" value="Post" class="submit-btn">
        </div>
    </form>
</div>

<?php 

include '../../_foot.php'; 

?>