<?php 

$_title = 'Index';
include '../../_head.php'; 

?>

<?php 
    $jobTitle = $_POST["jobTitle"];
    $jobDescription = $_POST["jobDescription"];
    $jobSalary = $_POST["jobSalary"];
    $professionalField = $_POST["professionalField"];

    echo "<p>Job Title</p><div>$jobTitle<div>"
?>

<p>Job Title</p>
<input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a job title" disabled>

<p>Job Description</p>
<input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a description about the job" disabled>

<p>Salary</p>
<input type="text" id="jobTitle" name="jobTitle" placeholder="Enter salary" disabled>

<p>Looking At</p>
<input type="text" id="jobTitle" name="jobTitle" placeholder="Enter professional field" disabled>

<p>Posting Date and Time</p>
<select name="postDate" id="postDate" disabled>
    <option value="Date">Date</option>
</select>
<select name="postTime" id="postTime" disabled>
    <option value="Time">Time</option>
</select>

<p>Delivery Period</p>
<select name="deliveryPeriod" id="deliveryPeriod" disabled>
    <option value="days">Days</option>
    <option value="weeks">weeks</option>
    <option value="months">Months</option>
</select>
<input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a delivery period" disabled>

<br>

<button onclick="history.back()">Back</button>
<a href="jobSummary">
    <button>
        Post
    </button>
</a>


<?php 

include '../../_foot.php'; 

?>