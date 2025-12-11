<?php 

$_title = 'Index';
include '../../_head.php';

?>

<div class="form-container">
    <form action="job_questions.php" method="post" name="create-job-form" class="create-job-form">
        <p>Project Title</p>
        <input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a project title" required>

        <p>Project Description</p>
        <input type="text" id="jobDescription" name="jobDescription" placeholder="Enter a description about the project" required>
        <p>Salary</p>
        <input type="number" id="jobSalary" name="jobSalary" placeholder="Enter salary" min="0" step="1" pattern="[0-9]+(\.[0-9]{1,2})?" required>

        <p>Delivery Time (Days)</p>
        <input type="number" id="deliveryTime" name="deliveryTime" placeholder="Enter delivery time in days" min="1" step="1" required>

        <p>Posting Date</p>
        <label class="checkbox-label">
            <input type="checkbox" id="postDateNow" name="postDateOption" value="now" checked>
            <span>Post Now (Use Current Date & Time)</span>
        </label>

        <div id="postDateTimeFields" class="date-time-fields" style="display: none;">
            <p>Posting Date</p>
            <input type="date" id="postDate" name="postDate">
        </div>

        <p>Expiry Date</p>
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

    // Toggle visibility based on checkbox state
    function togglePostDateFields() {
        if (postDateNow.checked) {
            // Checked = Post Now
            postDateTimeFields.style.display = 'none';
            postDateInput.required = false;
            postDateInput.value = '';
        } else {
            // Unchecked = Manual
            postDateTimeFields.style.display = 'block';
            postDateInput.required = true;
        }
    }

    postDateNow.addEventListener('change', togglePostDateFields);

    // Set current date and time when form is submitted with checkbox checked
    document.querySelector('.create-job-form').addEventListener('submit', function(e) {
        if (postDateNow.checked) {
            const now = new Date();
            postDateInput.value = now.toISOString().split('T')[0];
        }
    });

    // Make entire date/time input clickable
    const dateTimeInputs = document.querySelectorAll('input[type="date"], input[type="time"]');
    dateTimeInputs.forEach(input => {
        input.addEventListener('click', function() {
            this.showPicker();
        });
        
        // Also trigger on focus for better UX
        input.addEventListener('focus', function() {
            this.showPicker();
        });
    });

    // Set minimum date to tomorrow for postDate and deadline
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    
    postDateInput.setAttribute('min', tomorrowStr);
    const deliveryPeriodInput = document.getElementById('deliveryPeriod');
    deliveryPeriodInput.setAttribute('min', tomorrowStr);

    // Update deadline minimum date when postDate changes
    postDateInput.addEventListener('change', function() {
        if (this.value) {
            // Set deadline minimum to the selected post date
            deliveryPeriodInput.setAttribute('min', this.value);
            
            // If deadline is already set but is before the new post date, clear it
            if (deliveryPeriodInput.value && deliveryPeriodInput.value < this.value) {
                deliveryPeriodInput.value = '';
            }
        }
    });
});
</script>

<?php 

include '../../_foot.php'; 

?>