<?php 

$_title = 'Index';
include '../../_head.php'; 

?>

<div class="form-container">
    <form action="jobSummary.php" method="post" name="create-job-form" class="create-job-form">
        <p>Job Title</p>
        <input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a job title" required>

        <p>Job Description</p>
        <input type="text" id="jobDescription" name="jobDescription" placeholder="Enter a description about the job" required>

        <p>Salary</p>
        <input type="number" id="jobSalary" name="jobSalary" placeholder="Enter salary" min="0" step="1" pattern="[0-9]+(\.[0-9]{1,2})?" required>

        <p>Looking At</p>
        <input type="text" id="professionalField" name="professionalField" placeholder="Enter professional field" required>

        <p>Posting Date and Time</p>
        <div class="form-row">
            <select name="postDate" id="postDate" required>
                <option value="Date">Date</option>
            </select>
            <select name="postTime" id="postTime" required>
                <option value="Time">Time</option>
            </select>
        </div>

        <p>Delivery Period</p>
        <div class="form-row">
            <select name="deliveryPeriod" id="deliveryPeriod" required>
                <option value="days">Days</option>
                <option value="weeks">weeks</option>
                <option value="months">Months</option>
            </select>
            <input type="text" id="deliveryPeriodValue" name="deliveryPeriodValue" placeholder="Enter a delivery period" required>
        </div>

        <div class="form-buttons">
            <button type="reset" class="reset-btn">Reset</button>
            <input type="submit" value="Submit" class="submit-btn">
        </div>
    </form>
</div>

<?php 

include '../../_foot.php'; 

?>