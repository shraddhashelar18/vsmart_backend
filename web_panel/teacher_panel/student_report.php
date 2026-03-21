<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");
session_start();

if(!isset($_SESSION['teacher_id'])){
    header("Location: ../auth_panel/login.php");
    exit();
}

$student_id = $_GET['user_id'] ?? '';

if(!$student_id){
    die("Student ID missing");
}

/* ✅ FETCH STUDENT DETAILS */
$stmt = $conn->prepare("
SELECT full_name, roll_no, current_semester
FROM students
WHERE user_id=?
");
$stmt->bind_param("i",$student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if(!$student){
    die("Student not found");
}

/* ✅ FETCH MARKS */
$stmt = $conn->prepare("
SELECT subject, exam_type, obtained_marks, total_marks
FROM marks
WHERE student_id=? 
AND semester=? 
AND status!='draft'
");
$stmt->bind_param("is",$student_id,$student['current_semester']);
$stmt->execute();
$result = $stmt->get_result();

/* GROUP SUBJECTS */
$marks = [];

while($row = $result->fetch_assoc()){
    $marks[$row['subject']][$row['exam_type']] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Report</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body{margin:0;font-family:Arial;background:#f3f3f3;}

.header{
background:#009846;
color:white;
padding:18px;
display:flex;
align-items:center;
}

.container{padding:20px;}

.profile{
display:flex;
align-items:center;
gap:15px;
margin-bottom:20px;
}

.circle{
width:60px;height:60px;
background:#009846;
color:white;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
font-size:22px;
}

.card{
background:white;
padding:15px;
border-radius:12px;
margin-bottom:10px;
display:flex;
justify-content:space-between;
}

.subject{
margin-top:20px;
font-weight:bold;
}

.empty{
text-align:center;
color:#999;
margin-top:50px;
}
</style>
</head>

<body>

<div class="header">
<span class="material-icons" onclick="history.back()">arrow_back</span>
&nbsp; Student Report
</div>

<div class="container">

<!-- PROFILE -->
<div class="profile">
<div class="circle"><?= strtoupper($student['full_name'][0]) ?></div>
<div>
<b><?= $student['full_name'] ?></b><br>
<small>Roll No: <?= $student['roll_no'] ?></small>
</div>
</div>

<a href="previous_semesters.php?user_id=<?= $student_id ?>" 
style="color:#009846;font-weight:bold;text-decoration:none;">
View Previous Semesters
</a>

<h3>Exam Performance</h3>

<?php if(empty($marks)){ ?>
<div class="empty">Current semester data not entered yet</div>
<?php } ?>

<?php foreach($marks as $subject => $exams){ ?>

<div class="subject"><?= $subject ?></div>

<?php foreach($exams as $exam => $m){ ?>

<div class="card">
<div><?= $exam ?></div>
<div><?= $m['obtained_marks'] ?? 0 ?> / <?= $m['total_marks'] ?></div>
</div>

<?php } ?>

<?php } ?>

</div>

</body>
</html>