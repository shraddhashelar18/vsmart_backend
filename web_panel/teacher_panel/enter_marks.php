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

$teacher_id = $_SESSION['teacher_id'];

/* GET DATA */
$class = $_GET['class'] ?? '';
$subject = $_GET['subject'] ?? '';
$examType = $_POST['exam_type'] ?? 'CT1';
$totalMarks = 30;

/* FETCH STUDENTS */
$students = $conn->query("
SELECT user_id, full_name, roll_no 
FROM students 
WHERE class = '$class'
");
/* FETCH EXISTING MARKS (FOR PREFILL) */
$existingMarks = [];

$examType = $_POST['exam_type'] ?? 'CT1'; // default

$marksRes = $conn->query("
SELECT student_id, obtained_marks 
FROM marks 
WHERE class = '$class'
AND subject = '$subject'
AND exam_type = '$examType'
");

while($m = $marksRes->fetch_assoc()){
    $existingMarks[$m['student_id']] = $m['obtained_marks'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Enter Marks</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
body{margin:0;font-family:Arial;background:#f3f3f3;}
.header{background:#009846;color:white;padding:18px;display:flex;align-items:center;font-size:20px;}
.back{margin-right:10px;cursor:pointer;}
.container{padding:20px;padding-bottom:120px;}
.box{background:#eee;padding:15px;border-radius:12px;margin-bottom:15px;}
.stats{display:flex;gap:10px;margin:15px 0;}
.stat{flex:1;padding:15px;border-radius:12px;text-align:center;font-weight:bold;background:white;}
.student{background:white;padding:15px;border-radius:12px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
input{width:90px;padding:8px;border:none;border-bottom:2px solid #ccc;outline:none;text-align:center;}
.btn{position:fixed;left:0;width:100%;padding:15px;border:none;font-size:16px;}
.draft{bottom:60px;background:#eee;}
.publish{bottom:0;background:#009846;color:white;}

.custom-select{
    width:100%;
    padding:12px;
    border:none;
    background:#f1f1f1;
    border-radius:10px;
    font-size:14px;
    outline:none;

    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;

    background-image:url("data:image/svg+xml;utf8,<svg fill='gray' height='20' viewBox='0 0 24 24' width='20' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
    background-repeat:no-repeat;
    background-position:right 10px center;
}
</style>
</head>

<body>

<div class="header">
<span class="material-icons back" onclick="history.back()">arrow_back</span>
Enter Marks
</div>

<div class="container">

<form method="POST">

<div class="box">
<b>Class</b><br><?= $class ?><br><br>
<b>Subject</b><br><?= $subject ?><br><br>

<div class="select-box">
<label>Exam Type</label>
<select name="exam_type" class="custom-select" onchange="this.form.submit()">
    <option value="CT1" <?= ($examType=='CT1')?'selected':'' ?>>CT-1</option>
    <option value="CT2" <?= ($examType=='CT2')?'selected':'' ?>>CT-2</option>
</select>
</div>

<div class="stats">
<div class="stat"><?= $totalMarks ?><br>Max Marks</div>
<div class="stat">
<span id="completed">0</span>/<span id="total"><?= $students->num_rows ?></span><br>Completed
</div>
<div class="stat"><span id="avg">0</span><br>Average</div>
</div>

<?php while($row = $students->fetch_assoc()){ ?>

<div class="student">
<div>
<b><?= $row['full_name'] ?></b><br>
<small>Roll No: <?= $row['roll_no'] ?></small>
</div>

<input type="number"
name="marks[<?= $row['user_id'] ?>]"
value="<?= isset($existingMarks[$row['user_id']]) ? $existingMarks[$row['user_id']] : '' ?>"
oninput="updateStats()"
max="30" min="0">

<input type="hidden" name="semester[<?= $row['user_id'] ?>]" 
value="<?= preg_replace('/[^0-9]/','',$class) ?>">

</div>

<?php } ?>

<button class="btn draft" name="save_draft">Save Draft</button>
<button class="btn publish" name="publish">Publish Marks</button>

</form>

</div>

<script>
function updateStats(){
    let inputs = document.querySelectorAll("input[type='number']");
    let completed = 0;
    let sum = 0;

    inputs.forEach(i=>{
        if(i.value !== ""){
            completed++;
            sum += parseFloat(i.value);
        }
    });

    document.getElementById("completed").innerText = completed;
    document.getElementById("avg").innerText = completed ? (sum/completed).toFixed(1) : 0;
}
</script>

</body>
</html>

<?php

/* =========================
   FORM SUBMIT (API LOGIC)
========================= */

if(isset($_POST['publish']) || isset($_POST['save_draft'])){

    $examType = $_POST['exam_type'];
    $isDraft = isset($_POST['save_draft']);

    /* SUBJECT CHECK */
    $prefix = substr($class, 0, 4);

    $subjectCheck = $conn->prepare("
    SELECT subject_name FROM semester_subjects
    WHERE class=? AND subject_name=?
    ");
    $subjectCheck->bind_param("ss", $prefix, $subject);
    $subjectCheck->execute();

    if($subjectCheck->get_result()->num_rows == 0){
        echo "<script>alert('Subject not found');</script>";
        exit;
    }

    foreach($_POST['marks'] as $student_id => $marks){

        $semester = $_POST['semester'][$student_id];

        /* NULL MARKS */
        if($marks === "" || $marks === null){
            $marks = NULL;
        } else {
            if($marks > $totalMarks){
                echo "<script>alert('Marks exceed limit');</script>";
                exit;
            }
        }

        $status = $isDraft ? "draft" : "published";

        /* CHECK EXISTING */
        $check = $conn->prepare("
        SELECT id FROM marks
        WHERE student_id=? AND subject=? AND exam_type=? AND class=?
        ");
        $check->bind_param("isss", $student_id, $subject, $examType, $class);
        $check->execute();

        if($check->get_result()->num_rows > 0){

            /* UPDATE */
            $update = $conn->prepare("
            UPDATE marks
            SET obtained_marks=?, total_marks=?, semester=?, status=?
            WHERE student_id=? AND subject=? AND exam_type=? AND class=?
            ");

            $update->bind_param(
                "iiisisss",
                $marks,
                $totalMarks,
                $semester,
                $status,
                $student_id,
                $subject,
                $examType,
                $class
            );

            $update->execute();

        } else {

            /* INSERT */
            $insert = $conn->prepare("
            INSERT INTO marks
            (student_id, teacher_user_id, class, semester, subject, exam_type, total_marks, obtained_marks, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $insert->bind_param(
                "iissssiis",
                $student_id,
                $teacher_id,
                $class,
                $semester,
                $subject,
                $examType,
                $totalMarks,
                $marks,
                $status
            );

            $insert->execute();
        }
    }

    echo "<script>alert('".($isDraft ? "Draft saved" : "Marks published")."'); window.location='teacher_dashboard.php';</script>";
}
?>