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

/* FETCH TEACHER + DEPARTMENT */

$res = $conn->query("
SELECT 
    t.full_name, 
    u.email,
    GROUP_CONCAT(DISTINCT ta.department) as department
FROM teachers t
LEFT JOIN users u 
    ON t.user_id = u.user_id
LEFT JOIN teacher_assignments ta 
    ON t.user_id = ta.user_id
WHERE t.user_id = '$teacher_id'
");
if(!$res){
    die("Query Error: " . $conn->error);
}


$data = $res->fetch_assoc();

/* SAFE VALUES */
$name = $data['full_name'] ?? 'N/A';
$email = $data['email'] ?? 'N/A';
$department = $data['department'] ?? 'N/A';

/* FETCH SETTINGS */
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();

/* FORMAT VALUES */
$semester = $settings['active_semester'] ?? '-';
$atkt = $settings['atkt_limit'] ?? '-';
$registration = ($settings['registration_open'] ?? 0) ? "Open" : "Closed";
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>
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
padding:18px;
display:flex;
align-items:center;
font-size:20px;
}

.back{
margin-right:10px;
cursor:pointer;
text-decoration:none; /* 🔥 remove underline */
color:white; /* keep icon white */
}

/* TITLE */
.title{
color:#009846;
font-weight:bold;
margin:15px 20px 5px;
}

/* CARD */
.card{
background:white;
margin:10px 20px;
border-radius:12px;
overflow:hidden;
box-shadow:0 2px 6px rgba(0,0,0,0.08);
}

/* ITEM */
.item{
display:flex;
align-items:center;
justify-content:space-between;
padding:15px;
border-bottom:1px solid #eee;
}

.item:last-child{
border-bottom:none;
}

.left{
display:flex;
align-items:center;
gap:12px;
}

.icon{
color:#009846;
}

.label{
font-size:13px;
color:#777;
}

.value{
font-size:15px;
font-weight:500;
}

.click{
cursor:pointer;
}

.logout{
color:red;
}

</style>
</head>

<body>

<div class="header">
<a href="teacher_dashboard.php" class="material-icons back">arrow_back</a>
Settings
</div>

<!-- PROFILE -->
<div class="title">Profile</div>

<div class="card">

<div class="item">
<div class="left">
<span class="material-icons icon">person</span>
<div>
<div class="label">Name</div>
<div class="value"><?= $name ?></div>
</div>
</div>
</div>

<div class="item">
<div class="left">
<span class="material-icons icon">email</span>
<div>
<div class="label">Email</div>
<div class="value"><?= $email ?></div>
</div>
</div>
</div>

<div class="item">
<div class="left">
<span class="material-icons icon">school</span>
<div>
<div class="label">Department</div>
<div class="value"><?= $department ?></div>
</div>
</div>
</div>

</div>

<!-- ACADEMIC -->
<div class="title">Academic Information</div>

<div class="card">

<div class="item">
<div class="left">
<span class="material-icons icon">school</span>
<div>
<div class="label">Active Semester</div>
<div class="value"><?= $semester ?></div>
</div>
</div>
</div>

<div class="item">
<div class="left">
<span class="material-icons icon">rule</span>
<div>
<div class="label">ATKT Limit</div>
<div class="value"><?= $atkt ?></div>
</div>
</div>
</div>

<div class="item">
<div class="left">
<span class="material-icons icon">verified_user</span>
<div>
<div class="label">Registration Status</div>
<div class="value"><?= $registration ?></div>
</div>
</div>
</div>

</div>

<!-- ACCOUNT -->
<div class="title">Account</div>

<div class="card">

<div class="item click" onclick="location.href='change_password.php'">
<div class="left">
<span class="material-icons icon">lock</span>
<div class="value">Change Password</div>
</div>
<span class="material-icons">chevron_right</span>
</div>

<div class="item click" onclick="location.href='about.php'">
<div class="left">
<span class="material-icons icon">info</span>
<div class="value">About Application</div>
</div>
<span class="material-icons">chevron_right</span>
</div>

<div class="item click logout" onclick="logout()">
<div class="left">
<span class="material-icons icon logout">logout</span>
<div class="value logout">Logout</div>
</div>
<span class="material-icons logout">chevron_right</span>
</div>

</div>

<script>
function logout(){
    if(confirm("Are you sure to logout?")){
        window.location.href="logout.php";
    }
}
</script>

</body>
</html>