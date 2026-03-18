<?php
require_once("../config.php");

$id = $_GET['id'];

/* =====================
TEACHER BASIC DETAILS
===================== */

$stmt = $conn->prepare("
SELECT 
t.user_id,
t.full_name,
t.mobile_no,
u.email
FROM teachers t
JOIN users u ON t.user_id = u.user_id
WHERE t.user_id = ?
");

$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();


/* =====================
ASSIGNMENTS
===================== */

$assignStmt = $conn->prepare("
SELECT class, subject, department
FROM teacher_assignments
WHERE user_id = ?
AND status='active'
");

$assignStmt->bind_param("i",$id);
$assignStmt->execute();
$assignResult = $assignStmt->get_result();

$assignments = [];

while($row = $assignResult->fetch_assoc()){
    $assignments[] = $row;
}


/* =====================
CLASS TEACHER
===================== */

$classStmt = $conn->prepare("
SELECT class_name
FROM classes
WHERE class_teacher = ?
");

$classStmt->bind_param("i",$id);
$classStmt->execute();
$classResult = $classStmt->get_result();

$isClassTeacher = false;
$classTeacherOf = "";

if($classResult->num_rows > 0){
    $isClassTeacher = true;
    $row = $classResult->fetch_assoc();
    $classTeacherOf = $row['class_name'];
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Teacher Profile</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="profile-card">

<div class="profile-avatar">
👨‍🏫
</div>

<h2><?php echo $teacher['full_name']; ?></h2>

</div>


<!-- CONTACT INFORMATION -->
<div class="info-card">

<div class="info-title">Contact Information</div>

<div class="info-row">
<span>Mobile</span>
<span><?php echo $teacher['mobile_no']; ?></span>
</div>

<div class="info-row">
<span>Email</span>
<span><?php echo $teacher['email']; ?></span>
</div>
</div>

<!-- SUBJECTS -->
<div class="info-card">

<div class="info-title">Teaching Assignments</div>

<?php foreach($assignments as $a){ ?>

<div class="info-row">
<span><?php echo $a['class']; ?></span>
<span><?php echo $a['subject']; ?></span>
</div>

<?php } ?>

</div>
<!-- CLASS TEACHER BOX -->
<?php if($isClassTeacher){ ?>

<div class="info-card">

<div class="info-title">Class Teacher</div>

<div class="info-row">
<span>Assigned Class </span>
<span><?php echo $classTeacherOf; ?></span>
</div>

</div>

<?php } ?>


</body>
</html>