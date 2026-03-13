<?php

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../promotion_helper.php");

$roll = $_GET['roll'];

/* ================= BASIC DETAILS ================= */

$stmt = $conn->prepare("
SELECT user_id, full_name, roll_no, enrollment_no,
       mobile_no, parent_mobile_no, class, current_semester
FROM students
WHERE roll_no = ?
");

$stmt->bind_param("s",$roll);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "Student not found";
    exit;
}

$student = $result->fetch_assoc();
$studentId = $student['user_id'];


/* ================= MARKS ================= */

$marksStmt = $conn->prepare("
SELECT subject, exam_type, obtained_marks
FROM marks
WHERE student_id = ?
");

$marksStmt->bind_param("i",$studentId);
$marksStmt->execute();
$marksResult = $marksStmt->get_result();

$ct1Marks = [];
$ct2Marks = [];
$finalResults = [];

while($row = $marksResult->fetch_assoc()){

    $marksValue = $row['obtained_marks'];

    if($marksValue === NULL){
        $marksValue = "Absent";
    }

    if($row['exam_type']=="CT1"){
        $ct1Marks[$row['subject']] = $marksValue;
    }
    elseif($row['exam_type']=="CT2"){
        $ct2Marks[$row['subject']] = $marksValue;
    }
    elseif($row['exam_type']=="FINAL"){
        $finalResults[$row['subject']] = $marksValue;
    }

}


/* ================= PROMOTION ================= */

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = $setting->fetch_assoc()['atkt_limit'];

$promotion = calculatePromotion($conn,$studentId,$atktLimit);

?>

<!DOCTYPE html>
<html>

<head>

<title>Student Profile</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="header">
Student Profile
</div>


<!-- PROFILE -->

<div class="profile-card">

<div class="profile-avatar">
👤
</div>

<h3><?php echo $student['full_name']; ?></h3>

<p>Roll : <?php echo $student['roll_no']; ?></p>

<p>Status : <b><?php echo $promotion['status']; ?></b></p>

</div>


<!-- BASIC INFO -->

<div class="info-card">

<div class="info-title">
Student Info
</div>

<div class="info-row">
<span>Enrollment</span>
<span><?php echo $student['enrollment_no']; ?></span>
</div>

<div class="info-row">
<span>Class</span>
<span><?php echo $student['class']; ?></span>
</div>

<div class="info-row">
<span>Semester</span>
<span><?php echo $student['current_semester']; ?></span>
</div>

<div class="info-row">
<span>Mobile</span>
<span><?php echo $student['mobile_no']; ?></span>
</div>

<div class="info-row">
<span>Parent Mobile</span>
<span><?php echo $student['parent_mobile_no']; ?></span>
</div>

<div class="info-row">
<span>Backlogs</span>
<span><?php echo $promotion['backlogCount']; ?></span>
</div>

</div>



<!-- CT1 MARKS -->

<div class="info-card">

<div class="info-title">
CT1 Marks
</div>

<?php foreach($ct1Marks as $subject=>$mark){ ?>

<div class="info-row">

<span><?php echo $subject; ?></span>

<span class="mark"><?php echo $mark; ?></span>

</div>

<?php } ?>

</div>



<!-- CT2 MARKS -->

<div class="info-card">

<div class="info-title">
CT2 Marks
</div>

<?php foreach($ct2Marks as $subject=>$mark){ ?>

<div class="info-row">

<span><?php echo $subject; ?></span>

<span class="mark"><?php echo $mark; ?></span>

</div>

<?php } ?>

</div>



<!-- FINAL MARKS -->

<div class="info-card">

<div class="info-title">
Final Results
</div>

<?php foreach($finalResults as $subject=>$mark){ ?>

<div class="info-row">

<span><?php echo $subject; ?></span>

<span class="mark"><?php echo $mark; ?></span>

</div>

<?php } ?>

</div>


</body>
</html>