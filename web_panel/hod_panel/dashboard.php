<?php
session_start();
require_once("../config.php");
require_once("../promotion_helper.php");

/* 🔒 CHECK LOGIN */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "hod"){
    header("Location: https://mycollege.vpt.edu.in/vsmart/web_panel/auth_panel/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

/* 🔥 GET HOD DEPARTMENT */
$deptQuery = $conn->prepare("
SELECT department 
FROM hods 
WHERE user_id = ?
");

$deptQuery->bind_param("i",$userId);
$deptQuery->execute();
$deptResult = $deptQuery->get_result()->fetch_assoc();

if(!$deptResult){
    die("Department not found");
}

$department = $deptResult['department']; // IF / CO / EJ

/* ATKT LIMIT */
$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = (int)$setting->fetch_assoc()['atkt_limit'];

/* STUDENTS */
$stmt = $conn->prepare("
SELECT user_id 
FROM students 
WHERE department=?
");

$stmt->bind_param("s",$department);
$stmt->execute();
$result = $stmt->get_result();

$totalStudents = 0;
$promoted = 0;
$atkt = 0;
$detained = 0;

while($row = $result->fetch_assoc()){

    $totalStudents++;

    $promo = calculatePromotion($conn,$row['user_id'],$atktLimit);

    if($promo['status']=="PROMOTED"){
        $promoted++;
    }
    elseif($promo['status']=="PROMOTED_WITH_ATKT"){
        $atkt++;
    }
    else{
        $detained++;
    }
}

/* TEACHERS */
$t = $conn->prepare("
SELECT COUNT(DISTINCT ta.user_id) AS totalTeachers
FROM teacher_assignments ta
JOIN teachers t ON t.user_id = ta.user_id
WHERE ta.department = ?
");

$t->bind_param("s",$department);
$t->execute();
$totalTeachers = $t->get_result()->fetch_assoc()['totalTeachers'];

?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HOD Dashboard</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

/* BODY */
body{
margin:0;
font-family:'Segoe UI',sans-serif;
background:#f2f2f2;
display:flex;
justify-content:center;
}

/* MAIN PANEL WIDTH (IMPORTANT 🔥) */
.wrapper{
width:100%;
max-width:380px;
background:#f2f2f2;
min-height:100vh;
}

/* HEADER */
.header{
background:#0a8f3c;
color:white;
padding:18px;
font-size:20px;
font-weight:600;
}

/* CONTENT */
.container{
padding:15px;
}

/* ROW */
.row{
display:flex;
gap:12px;
margin-bottom:15px;
}

/* CARD */
.card{
background:white;
border-radius:16px;
padding:18px;
flex:1;
text-align:center;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

.card i{
font-size:28px;
margin-bottom:5px;
}

/* SMALL CARD */
.small{
padding:14px;
}

/* COLORS */
.green{color:#0a8f3c;}
.orange{color:#ff9800;}
.red{color:#f44336;}

/* TITLE */
.section-title{
font-size:18px;
font-weight:600;
margin:15px 0 10px;
}

/* BUTTON */
.btn{
display:block;
width:100%;
background:#0a8f3c;
color:white;
padding:14px;
border-radius:12px;
text-align:center;
text-decoration:none;
margin-bottom:12px;
font-size:15px;
}

/* BOTTOM NAV */
.bottom-nav{
position:fixed;
bottom:0;
width:100%;
max-width:380px;
background:white;
display:flex;
justify-content:space-around;
padding:10px 0;
border-top:1px solid #ddd;
}

.bottom-nav div{
text-align:center;
font-size:12px;
color:#777;
}

.bottom-nav .active{
color:#0a8f3c;
}

</style>
</head>

<body>

<div class="wrapper">

<div class="header">
HOD Dashboard (<?php echo $department; ?>)
</div>

<div class="container">

<!-- TOP -->
<div class="row">
<div class="card">
<i class="material-icons green">school</i>
<div>Total Students</div>
<h2><?php echo $totalStudents; ?></h2>
</div>

<div class="card">
<i class="material-icons green">person</i>
<div>Total Teachers</div>
<h2><?php echo $totalTeachers; ?></h2>
</div>
</div>

<!-- STATUS -->
<div class="row">
<div class="card small">
<i class="material-icons green">arrow_upward</i>
<div>Promoted</div>
<h3><?php echo $promoted; ?></h3>
</div>

<div class="card small">
<i class="material-icons orange">trending_up</i>
<div>With ATKT</div>
<h3><?php echo $atkt; ?></h3>
</div>

<div class="card small">
<i class="material-icons red">warning</i>
<div>Detained</div>
<h3><?php echo $detained; ?></h3>
</div>
</div>

<!-- ACTIONS -->
<div class="section-title">Academic Actions</div>

<a href="#" class="btn">View Students</a>
<a href="#" class="btn">View Teachers</a>
<a href="#" class="btn">View Promoted List</a>
<a href="#" class="btn">View ATKT List</a>
<a href="#" class="btn">View Detained List</a>

</div>

<!-- BOTTOM NAV -->
<div class="bottom-nav">
<div class="active">
<i class="material-icons">dashboard</i><br>Dashboard
</div>
<div>
<i class="material-icons">settings</i><br>Settings
</div>
</div>

</div>

</body>
</html>