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

/* GET SELECTED CLASS & SUBJECT */
$class = $_GET['class'] ?? '';
$subject = $_GET['subject'] ?? '';
$date = date("Y-m-d");

/* FETCH STUDENTS */
$students = $conn->query("
SELECT user_id, full_name, roll_no 
FROM students 
WHERE class = '$class'
");

?>

<!DOCTYPE html>
<html>
<head>
<title>Take Attendance</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Arial;
background:#f3f3f3;
}

/* HEADER */
.header{
background:#009846;
color:white;
padding:20px;
display:flex;
align-items:center;
font-size:20px;
}

.back{
margin-right:10px;
cursor:pointer;
}

/* CONTAINER */
.container{
padding:20px;
padding-bottom:100px;
}

/* BOX */
.box{
background:#eee;
padding:15px;
border-radius:12px;
margin-bottom:15px;
}

/* STATS */
.stats{
display:flex;
gap:10px;
margin:15px 0;
}

.stat{
flex:1;
padding:15px;
border-radius:12px;
text-align:center;
font-weight:bold;
}

.present{background:#d4edda;color:green;}
.late{background:#fff3cd;color:#d39e00;}
.absent{background:#f8d7da;color:red;}

/* STUDENT CARD */
.student{
background:white;
padding:15px;
border-radius:12px;
margin-bottom:12px;
box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

.actions button{
padding:8px 12px;
margin-right:5px;
border:none;
border-radius:20px;
cursor:pointer;
}

.active{
background:#009846;
color:white;
}

/* SUBMIT */
.submit{
    position:fixed;
    bottom:10px;
    left:50%;
    transform:translateX(-50%);
    width:90%;
    background:#009846;
    color:white;
    padding:15px;
    border:none;
    font-size:16px;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
}

</style>

</head>

<body>

<div class="header">
<span class="material-icons back" onclick="location.href='teacher_dashboard.php'">arrow_back</span>
Take Attendance
</div>

<div class="container">

<div class="box">Class: <?= $class ?></div>
<div class="box">Subject: <?= $subject ?></div>
<div class="box">Date: <?= date("d-m-Y") ?></div>

<div class="stats">
<div class="stat present" id="presentCount">0<br>Present</div>
<div class="stat late" id="lateCount">0<br>Late</div>
<div class="stat absent" id="absentCount">0<br>Absent</div>
</div>

<form method="POST">

<?php while($row = $students->fetch_assoc()){ ?>

<div class="student">
<b><?= $row['full_name'] ?></b><br>
<small>Roll No: <?= $row['roll_no'] ?></small>

<div class="actions">
<button type="button" onclick="setStatus(this,'P',<?= $row['user_id'] ?>)">Present</button>
<button type="button" onclick="setStatus(this,'L',<?= $row['user_id'] ?>)">Late</button>
<button type="button" onclick="setStatus(this,'A',<?= $row['user_id'] ?>)">Absent</button>
</div>

<input type="hidden" name="status[<?= $row['user_id'] ?>]" id="s<?= $row['user_id'] ?>">

</div>

<?php } ?>

<button class="submit" name="submit">Submit Attendance</button>

</form>

</div>

<script>

let present = 0;
let late = 0;
let absent = 0;

function setStatus(btn, status, id){

    let parent = btn.parentElement;
    let buttons = parent.querySelectorAll("button");

    // remove active
    buttons.forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    let hidden = document.getElementById("s"+id);
    let old = hidden.value;

    // decrease old
    if(old === 'P') present--;
    if(old === 'L') late--;
    if(old === 'A') absent--;

    // set new
    hidden.value = status;

    if(status === 'P') present++;
    if(status === 'L') late++;
    if(status === 'A') absent++;

    // update UI
    document.getElementById("presentCount").innerHTML = present + "<br>Present";
    document.getElementById("lateCount").innerHTML = late + "<br>Late";
    document.getElementById("absentCount").innerHTML = absent + "<br>Absent";
}

</script>

</body>
</html>

<?php

/* SAVE ATTENDANCE */
if(isset($_POST['submit'])){

    if(empty($_POST['status'])){
        echo "<script>alert('Please mark attendance');</script>";
        exit;
    }

    $semester = preg_replace('/[^0-9]/','',$class);

    /* DUPLICATE CHECK */
    $check = $conn->prepare("
    SELECT id FROM attendance
    WHERE class=? AND subject_name=? AND date=?
    ");
    $check->bind_param("sss", $class, $subject, $date);
    $check->execute();

    if($check->get_result()->num_rows > 0){
        echo "<script>alert('Attendance already marked');</script>";
        exit;
    }

    foreach($_POST['status'] as $student_id => $status){

        if(empty($status)) continue;

        $stmt = $conn->prepare("
        INSERT INTO attendance
        (student_id, teacher_id, class, semester, subject_name, date, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iisssss",
            $student_id,
            $teacher_id,
            $class,
            $semester,
            $subject,
            $date,
            $status
        );

        $stmt->execute();
    }

    echo "<script>alert('Attendance submitted successfully'); window.location='teacher_dashboard.php';</script>";
}
?>