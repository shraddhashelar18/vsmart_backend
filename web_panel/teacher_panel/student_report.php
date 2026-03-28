<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");
session_start();

/* LOGIN CHECK */
if(!isset($_SESSION['teacher_id'])){
    header("Location: ../auth_panel/login.php");
    exit();
}

$student_id = $_GET['user_id'] ?? '';

if(!$student_id){
    die("Student ID missing");
}

/* ===========================
   FETCH STUDENT DETAILS
=========================== */
$stmt = $conn->prepare("
SELECT full_name, roll_no, current_semester 
FROM students 
WHERE user_id=?
");
$stmt->bind_param("i",$student_id);
$stmt->execute();
$res = $stmt->get_result();
$student = $res->fetch_assoc();

$name = $student['full_name'] ?? 'N/A';
$roll = $student['roll_no'] ?? '-';
$semester = $student['current_semester'] ?? 1;

/* ===========================
   FETCH MARKS
=========================== */
$stmt = $conn->prepare("
SELECT subject, exam_type, obtained_marks, total_marks
FROM marks
WHERE student_id=? AND semester=? AND status='published'
");
$stmt->bind_param("ii",$student_id,$semester);
$stmt->execute();
$result = $stmt->get_result();

$marks = [];

while($row = $result->fetch_assoc()){

    $subject = $row['subject'];
    $exam = strtoupper(trim($row['exam_type'])); // normalize

    if(!isset($marks[$subject])){
        $marks[$subject] = [];
    }

    $marks[$subject][$exam] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Report</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body{
margin:0;
font-family:Arial;
background:#f3f3f3;
}
.back{
margin-right:10px;
cursor:pointer;
text-decoration:none; /* 🔥 remove underline */
color:white; /* keep icon white */
}
.marksheet-btn{
width:90%;
margin:20px auto;
display:block;
padding:14px;
border:none;
border-radius:10px;
font-size:16px;
font-weight:bold;
cursor:pointer;
}

.btn-active{
background:#009846;
color:white;
}

.btn-disabled{
background:#ccc;
color:#333;
cursor:not-allowed;
}
/* HEADER */
.header{
background:#009846;
color:white;
padding:18px;
display:flex;
align-items:center;
font-size:20px;
}

.back{
margin-right:10px;
cursor:pointer;
}

/* STUDENT ROW */
.student{
display:flex;
align-items:center;
padding:20px;
gap:15px;
}

.avatar{
width:60px;
height:60px;
border-radius:50%;
background:#009846;
color:white;
display:flex;
align-items:center;
justify-content:center;
font-size:24px;
}

.name{
font-size:18px;
font-weight:bold;
}

.roll{
color:#666;
}

/* RIGHT LINK */
.link{
color:#009846;
cursor:pointer;
font-weight:bold;
white-space:nowrap;
}

/* SECTION */
.section{
padding:0 20px;
font-weight:bold;
margin-top:10px;
}

/* SUBJECT */
.subject{
margin:15px 20px;
font-weight:bold;
}

/* CARD */
.card{
background:white;
margin:10px 20px;
padding:15px;
border-radius:12px;
display:flex;
justify-content:space-between;
box-shadow:0 2px 6px rgba(0,0,0,0.08);
}
</style>
</head>

<body>

<div class="header">
    <span class="material-icons back" onclick="goBack()">arrow_back</span>
    Student Report
</div>

<!-- STUDENT + RIGHT LINK -->
<div class="student">
    
    <div class="avatar"><?= strtoupper(substr($name,0,1)) ?></div>
    
    <div style="flex:1">
        <div class="name"><?= $name ?></div>
        <div class="roll">Roll No: <?= $roll ?></div>
    </div>

    <div class="link" onclick="goPrevious()">
        View Previous Semesters
    </div>

</div>

<div class="section">Exam Performance</div>

<?php if(empty($marks)){ ?>
<p style="text-align:center;color:#999;">No data available</p>
<?php } ?>

<?php foreach($marks as $subject => $exams){ ?>

<div class="subject"><?= $subject ?></div>

<?php
$order = ['CT1','CT2'];

foreach($order as $exam){

    if(isset($exams[$exam])){
        $m = $exams[$exam];
        $score = $m['obtained_marks'];
        $max = $m['total_marks'];
    } else {
        $score = "-";
        $max = "-";
    }
?>
<div class="card">
<span><?= $exam ?></span>
<span><?= $score ?> / <?= $max ?></span>
</div>
<?php
}
?>

<?php } ?>

<script>
function goPrevious(){
    window.location.href = "previous_semesters.php?user_id=<?= $student_id ?>&class=<?= $_GET['class'] ?>";
}

function goBack(){
    window.location.href = "reports.php?class=<?= $_GET['class'] ?>";
}
</script>

</body>
</html>