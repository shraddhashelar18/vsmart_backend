<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once("../config.php");

if(!isset($_SESSION['teacher_id'])){
    header("Location: ../auth_panel/login.php");
    exit();
}

$class = $_GET['class'] ?? '';

/* FETCH STUDENTS DIRECTLY */
$stmt = $conn->prepare("
SELECT user_id, full_name, roll_no 
FROM students 
WHERE class = ?
");

$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Students</title>
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
.back{
margin-right:10px;
cursor:pointer;
text-decoration:none; /* 🔥 remove underline */
color:white; /* keep icon white */
}
.container{padding:20px;}

.card{
background:white;
padding:15px;
border-radius:12px;
margin-bottom:15px;
display:flex;
justify-content:space-between;
align-items:center;
cursor:pointer;
box-shadow:0 2px 6px rgba(0,0,0,0.08);
}

.left{display:flex;gap:12px;align-items:center;}

.circle{
width:45px;height:45px;
background:#009846;color:white;
border-radius:50%;
display:flex;align-items:center;justify-content:center;
font-weight:bold;
}
</style>
</head>

<body>

<div class="header">
<a href="teacher_dashboard.php" class="material-icons back">arrow_back</a>
Reports
</div>

<div class="container">

<?php while($row = $result->fetch_assoc()){ ?>

<div class="card" onclick="openReport(<?= $row['user_id'] ?>,'<?= $class ?>')">
<div class="left">
<div class="circle"><?= strtoupper($row['full_name'][0]) ?></div>
<div>
<b><?= $row['full_name'] ?></b><br>
<small>Roll No: <?= $row['roll_no'] ?></small>
</div>
</div>
<span class="material-icons">chevron_right</span>
</div>

<?php } ?>

</div>

<script>
function openReport(id, cls){
    window.location.href = "student_report.php?user_id=" + id + "&class=" + cls;
}
</script>

</body>
</html>