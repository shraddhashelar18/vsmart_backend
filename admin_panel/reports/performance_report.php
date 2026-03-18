<?php
require_once("../auth.php");
require_once("../db.php");

/* =========================
ACTIVE SEMESTER
========================= */

$settings=$conn->query("SELECT active_semester FROM settings WHERE id=1")->fetch_assoc();
$cycle=$settings['active_semester'];

if($cycle=="EVEN"){
$semFilter="semester IN (2,4,6)";
}else{
$semFilter="semester IN (1,3,5)";
}

/* =========================
DEPARTMENTS
========================= */

$departments=$conn->query("SELECT DISTINCT department FROM classes");

$selectedDept=$_GET['department'] ?? '';
$selectedClass=$_GET['class'] ?? '';
$selectedExam=$_GET['exam'] ?? '';

$classes=[];
$students=[];

/* =========================
CLASSES BY DEPARTMENT
========================= */

if($selectedDept){

$stmt=$conn->prepare("
SELECT class_name
FROM classes
WHERE department=? AND $semFilter
");

$stmt->bind_param("s",$selectedDept);
$stmt->execute();

$classes=$stmt->get_result();
}

/* =========================
LOAD PERFORMANCE
========================= */

if($selectedClass && $selectedExam){

$subjectClass=substr($selectedClass,0,4);

/* total subjects */

$stmt=$conn->prepare("
SELECT COUNT(*) total
FROM semester_subjects
WHERE class=?
");

$stmt->bind_param("s",$subjectClass);
$stmt->execute();

$totalSubjects=$stmt->get_result()->fetch_assoc()['total'];

/* check published */

$stmt=$conn->prepare("
SELECT COUNT(DISTINCT subject) published
FROM marks
WHERE class=? AND exam_type=? AND status='published'
");

$stmt->bind_param("ss",$selectedClass,$selectedExam);
$stmt->execute();

$published=$stmt->get_result()->fetch_assoc()['published'];

if($published==$totalSubjects){

$stmt=$conn->prepare("
SELECT 
s.full_name,
SUM(m.obtained_marks) obtained,
SUM(m.total_marks) max_marks
FROM students s
LEFT JOIN marks m
ON s.user_id=m.student_id
AND m.class=?
AND m.exam_type=?
WHERE s.class=?
GROUP BY s.user_id
");

$stmt->bind_param("sss",$selectedClass,$selectedExam,$selectedClass);
$stmt->execute();

$res=$stmt->get_result();

while($row=$res->fetch_assoc()){

$percent=0;

if($row['max_marks']>0){
$percent=round(($row['obtained']/$row['max_marks'])*100);
}

$row['percent']=$percent;

$students[]=$row;
}

}
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Performance Report</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

/* HEADER */

.topbar{
background:#009846;
color:white;
padding:18px 30px;
font-size:20px;
display:flex;
align-items:center;
gap:10px;
}

/* CONTAINER */

.container{
max-width:900px;
margin:50px auto;
padding:30px;
background:white;
border-radius:12px;
box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* FIELD */

.field{
margin-bottom:18px;
}

.field label{
font-size:14px;
color:#666;
display:block;
margin-bottom:6px;
}

/* SELECT */

.select{
width:100%;
padding:14px;
border-radius:12px;
border:none;
background:#f2f2f2;
font-size:15px;
}

/* STUDENT TITLE */

.title{
font-size:18px;
font-weight:600;
margin-top:25px;
margin-bottom:15px;
}

/* CARD */

.card{
background:white;
border-radius:12px;
padding:18px;
margin-bottom:14px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

/* STUDENT */

.student{
display:flex;
align-items:center;
gap:14px;
}

.avatar{
width:50px;
height:50px;
border-radius:50%;
background:#EAF7F1;
display:flex;
align-items:center;
justify-content:center;
color:#009846;
font-size:24px;
}

.name{
font-size:18px;
font-weight:600;
}

.sub{
font-size:14px;
color:#666;
}

.percent{
font-size:18px;
font-weight:bold;
}

.green{
color:#009846;
}

.red{
color:#e53935;
}

</style>

</head>

<body>

<div class="topbar">
<a href="../reports.php" style="color:white;text-decoration:none;">
<span class="material-icons">arrow_back</span>
</a>

Performance Report

</div>

<div class="container">

<form method="GET">

<div class="field">

<label>Department</label>

<select class="select" name="department" onchange="this.form.submit()">

<option value="">Select</option>

<?php while($d=$departments->fetch_assoc()): ?>

<option value="<?=$d['department']?>" 
<?=$selectedDept==$d['department']?'selected':''?>>

<?=$d['department']?>

</option>

<?php endwhile; ?>

</select>

</div>

<div class="field">

<label>Class</label>

<select class="select" name="class" onchange="this.form.submit()">

<option value="">Select</option>

<?php if($classes) while($c=$classes->fetch_assoc()): ?>

<option value="<?=$c['class_name']?>"
<?=$selectedClass==$c['class_name']?'selected':''?>>

<?=$c['class_name']?>

</option>

<?php endwhile; ?>

</select>

</div>

<div class="field">

<label>Exam</label>

<select class="select" name="exam" onchange="this.form.submit()">

<option value="">Select</option>

<option value="CT1" <?=$selectedExam=="CT1"?'selected':''?>>CT1</option>
<option value="CT2" <?=$selectedExam=="CT2"?'selected':''?>>CT2</option>

</select>

</div>

</form>

<div class="title">Students</div>

<?php if(empty($students)){ ?>

<p style="color:#777;">Marks will appear after all teachers publish marks</p>

<?php } else { ?>

<?php foreach($students as $s): ?>

<div class="card">

<div class="student">

<div class="avatar">
<span class="material-icons">person</span>
</div>

<div>

<div class="name"><?=$s['full_name']?></div>

<div class="sub">

<?=$s['obtained']?> / <?=$s['max_marks']?> marks

</div>

</div>

</div>

<div class="percent <?=$s['percent']>=40?'green':'red'?>">

<?=$s['percent']?>%

</div>

</div>

<?php endforeach; ?>

<?php } ?>

</div>

</body>
</html>