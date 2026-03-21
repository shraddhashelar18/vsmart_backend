<?php
require_once("../config.php");
session_start();

/* LOGIN CHECK */
if(!isset($_SESSION['teacher_id'])){
    header("Location: ../auth_panel/login.php");
    exit();
}

$teacher_id   = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

/* DEPARTMENT CHECK */
if(!isset($_SESSION['department_id'])){
    header("Location: switch_department.php");
    exit();
}

$department_id = $_SESSION['department_id'];

/* FETCH CLASSES */
$classes = $conn->query("
SELECT class_name 
FROM classes 
WHERE department_id = '$department_id'
");

/* FETCH SUBJECTS */
$subjects = $conn->query("
SELECT subject_name 
FROM subjects 
WHERE department_id = '$department_id'
");

$date = date("l, F d, Y");
?>

<!DOCTYPE html>
<html>
<head>

<title>Teacher Dashboard</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Arial;
background:#f4f6f9;
}

/* HEADER */

.header{
background:#009846;
color:white;
padding:20px 40px;
border-bottom-left-radius:25px;
border-bottom-right-radius:25px;
}

.header h1{
margin:0;
font-size:24px;
}

.header p{
margin-top:5px;
opacity:0.9;
}

/* CONTAINER */

.container{
width:95%;
margin:auto;
padding:25px;
}

/* SWITCH */

.switch{
text-align:right;
color:#009846;
font-weight:bold;
cursor:pointer;
margin-bottom:15px;
}

/* SELECT BOX */

.select-box{
background:white;
padding:15px;
border-radius:10px;
margin-bottom:15px;
box-shadow:0 2px 6px rgba(0,0,0,0.08);
}

.select-box label{
display:block;
margin-bottom:6px;
color:#666;
}

.select-box select{
width:100%;
padding:10px;
border:none;
background:#f1f1f1;
border-radius:6px;
}

/* CARDS */

.card{
background:white;
padding:18px;
border-radius:12px;
display:flex;
align-items:center;
margin-bottom:15px;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
cursor:pointer;
}

.icon{
background:#e8f5ec;
width:50px;
height:50px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
margin-right:15px;
color:#009846;
font-size:22px;
}

.card h3{
margin:0;
}

.card p{
margin:2px 0 0;
color:#777;
font-size:13px;
}

/* BOTTOM NAV */

.bottom{
position:fixed;
bottom:0;
width:100%;
background:white;
display:flex;
justify-content:space-around;
padding:12px;
box-shadow:0 -2px 10px rgba(0,0,0,0.1);
}

.bottom a{
text-decoration:none;
color:#777;
text-align:center;
font-size:14px;
}

.bottom .material-icons{
display:block;
font-size:22px;
}

.bottom a.active{
color:#009846;
font-weight:bold;
}

</style>

</head>

<body>

<div class="header">
<h1>Teacher Dashboard</h1>
<p>Good Afternoon, <?= $teacher_name ?></p>
<p><?= $date ?></p>
</div>

<div class="container">

<div class="switch" onclick="location.href='switch_department.php'">
↔ Switch Department
</div>

<!-- CLASS -->
<div class="select-box">
<label>Select Class</label>
<select>
<?php while($row = $classes->fetch_assoc()){ ?>
<option><?= $row['class_name'] ?></option>
<?php } ?>
</select>
</div>

<!-- SUBJECT -->
<div class="select-box">
<label>Select Subject</label>
<select>
<?php while($row = $subjects->fetch_assoc()){ ?>
<option><?= $row['subject_name'] ?></option>
<?php } ?>
</select>
</div>

<!-- CARDS -->

<div class="card" onclick="location.href='mark_attendance.php'">
<div class="icon">
<span class="material-icons">check_circle</span>
</div>
<div>
<h3>Mark Attendance</h3>
<p>Take attendance</p>
</div>
</div>

<div class="card" onclick="location.href='enter_marks.php'">
<div class="icon">
<span class="material-icons">edit</span>
</div>
<div>
<h3>Enter Marks</h3>
<p>Add student marks</p>
</div>
</div>

<div class="card" onclick="location.href='reports.php'">
<div class="icon">
<span class="material-icons">bar_chart</span>
</div>
<div>
<h3>View Reports</h3>
<p>Performance Reports</p>
</div>
</div>

<div class="card" onclick="location.href='send_notification.php'">
<div class="icon">
<span class="material-icons">notifications</span>
</div>
<div>
<h3>Send Notifications</h3>
<p>Message class or students</p>
</div>
</div>

</div>

<div class="bottom">

<a href="teacher_dashboard.php" class="active">
<span class="material-icons">dashboard</span>
Dashboard
</a>

<a href="settings.php">
<span class="material-icons">settings</span>
Settings
</a>

</div>

</body>
</html>