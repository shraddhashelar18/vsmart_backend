<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

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
LOAD PERFORMANCE (API MATCHED)
========================= */

if($selectedClass && $selectedExam){

$subjectClass = substr($selectedClass, 0, 4);

/* TOTAL SUBJECTS */

$stmt = $conn->prepare("
SELECT COUNT(*) as total_subjects
FROM semester_subjects
WHERE class = ?
");

$stmt->bind_param("s", $subjectClass);
$stmt->execute();

$totalSubjects = $stmt->get_result()->fetch_assoc()['total_subjects'] ?? 0;


/* PUBLISHED SUBJECTS */

$stmt = $conn->prepare("
SELECT COUNT(DISTINCT m.subject) as published_count
FROM marks m
INNER JOIN semester_subjects ss 
ON TRIM(LOWER(m.subject)) = TRIM(LOWER(ss.subject_name))
WHERE m.class=? 
AND m.exam_type=? 
AND m.status='published'
AND ss.class=?
");

$stmt->bind_param("sss", $selectedClass, $selectedExam, $subjectClass);
$stmt->execute();

$publishedSubjects = $stmt->get_result()->fetch_assoc()['published_count'] ?? 0;


/* CHECK ALL PUBLISHED */

if($publishedSubjects == $totalSubjects){

$stmt = $conn->prepare("
SELECT 
s.user_id,
s.full_name,
SUM(m.obtained_marks) AS obtained
FROM students s

LEFT JOIN marks m 
ON s.user_id = m.student_id
AND m.class = ?
AND m.exam_type = ?
AND m.status='published'

INNER JOIN semester_subjects ss
ON TRIM(LOWER(ss.subject_name)) = TRIM(LOWER(m.subject))
AND ss.class = ?

WHERE s.class = ?

GROUP BY s.user_id
");

$stmt->bind_param("ssss", $selectedClass, $selectedExam, $subjectClass, $selectedClass);
$stmt->execute();

$res = $stmt->get_result();

while($row = $res->fetch_assoc()){

/* =========================
CORRECT LOGIC (MATCH API)
========================= */

// CT = 30, FINAL = 100
$maxPerSubject = ($selectedExam == "FINAL") ? 100 : 30;

// TOTAL MAX MARKS
$maxMarks = $totalSubjects * $maxPerSubject;

// OBTAINED
$obtained = $row['obtained'] ?? null;

// HANDLE ABSENT
if($obtained === null){
    $obtainedValue = "ABSENT";
    $percent = 0;
}else{
    $obtainedValue = (int)$obtained;

    $percent = ($maxMarks > 0)
        ? round(($obtainedValue / $maxMarks) * 100)
        : 0;
}

// ASSIGN
$row['max_marks'] = $maxMarks;
$row['obtained'] = $obtainedValue;
$row['percent'] = $percent;

$students[] = $row;
}

}
}
/* =========================
CHECK EXAM ENABLE STATUS
========================= */

$isCT1Enabled = false;
$isCT2Enabled = false;

/* CHECK CT1 */
$stmt = $conn->prepare("
SELECT COUNT(DISTINCT m.subject) as published
FROM marks m
INNER JOIN semester_subjects ss 
ON TRIM(LOWER(m.subject)) = TRIM(LOWER(ss.subject_name))
WHERE m.class = ?
AND m.exam_type = 'CT1'
AND m.status = 'published'
AND ss.class = ?
");

$stmt->bind_param("ss", $selectedClass, $subjectClass);
$stmt->execute();

$ct1Published = $stmt->get_result()->fetch_assoc()['published'] ?? 0;

if($ct1Published == $totalSubjects){
    $isCT1Enabled = true;
}


/* CHECK CT2 */
$stmt = $conn->prepare("
SELECT COUNT(DISTINCT m.subject) as published
FROM marks m
INNER JOIN semester_subjects ss 
ON TRIM(LOWER(m.subject)) = TRIM(LOWER(ss.subject_name))
WHERE m.class = ?
AND m.exam_type = 'CT2'
AND m.status = 'published'
AND ss.class = ?
");

$stmt->bind_param("ss", $selectedClass, $subjectClass);
$stmt->execute();

$ct2Published = $stmt->get_result()->fetch_assoc()['published'] ?? 0;

if($ct2Published == $totalSubjects){
    $isCT2Enabled = true;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Performance Report</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body{margin:0;font-family:Segoe UI;background:#eef2f5;}
.topbar{background:#009846;color:white;padding:18px 40px;font-size:22px;display:flex;align-items:center;gap:10px;}
.container{max-width:700px;margin:40px auto;padding:25px;}
.box{background:white;border-radius:16px;padding:18px;margin-bottom:15px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.field label{font-size:14px;color:#666;margin-bottom:6px;display:block;}
.select{width:100%;padding:14px;border-radius:12px;border:none;background:#f1f1f1;font-size:15px;}
.card{background:#f9f9f9;border-radius:16px;padding:16px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.08);}
.student{display:flex;align-items:center;gap:12px;}
.avatar{width:45px;height:45px;border-radius:50%;background:#e6f5ee;display:flex;align-items:center;justify-content:center;color:#009846;}
.name{font-size:17px;font-weight:600;}
.sub{font-size:14px;color:#777;}
.percent{font-size:16px;font-weight:bold;}
.green{ color:#009846; }
.red{ color:#e53935; }
.empty{color:#777;text-align:center;margin-top:20px;}
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

<div class="box field">
<label>Department</label>
<select class="select" name="department" onchange="this.form.submit()">
<option value="">Select</option>
<?php while($d=$departments->fetch_assoc()): ?>
<option value="<?=$d['department']?>" <?=$selectedDept==$d['department']?'selected':''?>>
<?=$d['department']?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="box field">
<label>Class</label>
<select class="select" name="class" onchange="this.form.submit()">
<option value="">Select</option>
<?php if($classes) while($c=$classes->fetch_assoc()): ?>
<option value="<?=$c['class_name']?>" <?=$selectedClass==$c['class_name']?'selected':''?>>
<?=$c['class_name']?>
</option>
<?php endwhile; ?>
</select>
</div>

<div class="box field">
<label>Exam</label>
<select class="select" name="exam" onchange="this.form.submit()">

<option value="">Select</option>

<option value="CT1"
<?=$selectedExam=="CT1"?'selected':''?>
<?=$isCT1Enabled ? '' : 'disabled style="color:gray;"'?>>
CT1
</option>

<option value="CT2"
<?=$selectedExam=="CT2"?'selected':''?>
<?=$isCT2Enabled ? '' : 'disabled style="color:gray;"'?>>
CT2
</option>

</select>
</div>

</form>

<?php if(empty($students)){ ?>

<p class="empty">Marks will appear after all teachers publish marks</p>

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