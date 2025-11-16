<?php 

$_title = 'Index';
include '../../_head.php'; 

?>

<p>Gig Title</p>
<input type="text" id="jobTitle" name="jobTitle" placeholder="Enter a gig title">

<p>Category</p>
<select name="gigCategory" id="gigCategory">
    <option value="" disabled selected hidden>Gig Category</option>
    <option value="graphic & design">Graphic & Design</option>
    <option value="digital marketing">Digital Marketing</option>
    <option value="writing & traslation">Writing & Translation</option>
    <option value="programming & Tech">Programming & Tech</option>
</select>
<select name="gigSubcategory" id="gigSubcategory">
    <option value="" disabled selected hidden>Gig Subcategory</option>
</select>

<p>Search Tags</p>
<input type="text" id="searchTags" name="searchTags" placeholder="Enter a search terms">

<br>

<a href="jobSummary.php">
    <button>
        Continue
    </button>
</a>

<?php 

include '../../_foot.php'; 

?>