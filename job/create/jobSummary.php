<?php 

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
    $deliveryPeriodValue = $_POST["deliveryPeriodValue"] ?? '';
?>

<div class="form-container">
    <form class="create-job-form">
        <p>Job Title</p>
        <input type="text" value="<?php echo htmlspecialchars($jobTitle); ?>" disabled>

        <p>Job Description</p>
        <input type="text" value="<?php echo htmlspecialchars($jobDescription); ?>" disabled>

        <p>Salary</p>
        <input type="text" value="<?php echo htmlspecialchars($jobSalary); ?>" disabled>

        <p>Looking At</p>
        <input type="text" value="<?php echo htmlspecialchars($professionalField); ?>" disabled>

        <p>Posting Date and Time</p>
        <div style="display: flex; gap: 1rem;">
            <select disabled style="flex: 1;">
                <option value="<?php echo htmlspecialchars($postDate); ?>" selected><?php echo htmlspecialchars($postDate); ?></option>
            </select>
            <select disabled style="flex: 1;">
                <option value="<?php echo htmlspecialchars($postTime); ?>" selected><?php echo htmlspecialchars($postTime); ?></option>
            </select>
        </div>

        <p>Delivery Period</p>
        <div style="display: flex; gap: 1rem;">
            <select disabled style="flex: 1;">
                <option value="<?php echo htmlspecialchars($deliveryPeriod); ?>" selected><?php echo htmlspecialchars($deliveryPeriod); ?></option>
            </select>
            <input type="text" value="<?php echo htmlspecialchars($deliveryPeriodValue); ?>" disabled style="flex: 1;">
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="button" onclick="history.back()" class="back-btn">Back</button>
            <button type="button" class="submit-btn">Post</button>
        </div>
    </form>
</div>


<?php 

include '../../_foot.php'; 

?>