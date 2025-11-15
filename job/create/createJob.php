<?php 

$_title = 'Index';
include '../../_head.php'; 

?>

<form action="jobSummary.php" method="post">
    <p>Job Title</p>
    <input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a job title" required>

    <p>Job Description</p>
    <input type="text" id="jobDescription" name="jobDescription" placeholder="Enter a description about the job" required>

    <p>Salary</p>
    <input type="text" id="jobSalary" name="jobSalary" placeholder="Enter salary" required>

    <p>Looking At</p>
    <input type="text" id="professionalField" name="professionalField" placeholder="Enter professional field" required>

    <p>Posting Date and Time</p>
    <select name="postDate" id="postDate" required>
        <option value="Date">Date</option>
    </select>
    <select name="postTime" id="postTime" required>
        <option value="Time">Time</option>
    </select>

    <p>Delivery Period</p>
    <select name="deliveryPeriod" id="deliveryPeriod" required>
        <option value="days">Days</option>
        <option value="weeks">weeks</option>
        <option value="months">Months</option>
    </select>
    <input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a delivery period" required>

    <br>

    <input type="submit" placeholder="Submit">
</form>


<?php 

include '../../_foot.php'; 

?>