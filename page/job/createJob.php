<?php 

$_title = 'Index';
include '../../_head.php'; 

?>

<div class="form-container">
    <form action="job_questions.php" method="post" name="create-job-form" class="create-job-form">
        <p>Job Title</p>
        <input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a job title" required>

        <p>Job Description</p>
        <input type="text" id="jobDescription" name="jobDescription" placeholder="Enter a description about the job" required>

        <p>Salary</p>
        <input type="number" id="jobSalary" name="jobSalary" placeholder="Enter salary" min="0" step="1" pattern="[0-9]+(\.[0-9]{1,2})?" required>

        <p>Looking At</p>
        <input type="text" id="professionalField" name="professionalField" placeholder="Enter professional field" required>

        <p>Posting Date</p>
        <label class="checkbox-label">
            <input type="checkbox" id="postDateNow" name="postDateOption" value="now" checked>
            <span>Post Now (Use Current Date & Time)</span>
        </label>

        <div id="postDateTimeFields" class="date-time-fields" style="display: none;">
            <p>Posting Date</p>
            <input type="date" id="postDate" name="postDate">

            <p>Posting Time</p>
            <input type="time" id="postTime" name="postTime">
        </div>

        <p>Deadline</p>
        <input type="date" id="deliveryPeriod" name="deliveryPeriod" required>

        <div class="form-buttons">
            <button type="reset" class="reset-btn">Reset</button>
            <input type="submit" value="Continue" class="submit-btn">
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const postDateNow = document.getElementById('postDateNow');
    const postDateTimeFields = document.getElementById('postDateTimeFields');
    const postDateInput = document.getElementById('postDate');
    const postTimeInput = document.getElementById('postTime');

    // Toggle visibility based on checkbox state
    function togglePostDateFields() {
        if (postDateNow.checked) {
            // Checked = Post Now
            postDateTimeFields.style.display = 'none';
            postDateInput.required = false;
            postTimeInput.required = false;
            postDateInput.value = '';
            postTimeInput.value = '';
        } else {
            // Unchecked = Manual
            postDateTimeFields.style.display = 'block';
            postDateInput.required = true;
            postTimeInput.required = true;
        }
    }

    postDateNow.addEventListener('change', togglePostDateFields);

    // Set current date and time when form is submitted with checkbox checked
    document.querySelector('.create-job-form').addEventListener('submit', function(e) {
        if (postDateNow.checked) {
            const now = new Date();
            postDateInput.value = now.toISOString().split('T')[0];
            postTimeInput.value = now.toTimeString().slice(0, 5);
        }
    });
});
</script>

<?php 

include '../../_foot.php'; 

?>