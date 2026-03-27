<?php

session_start();
require_once("../config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: localhost/vsmart_backend/api/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

/* =========================
   GET PRINCIPAL PROFILE
========================= */

$query = $conn->query("
SELECT p.full_name, u.email
FROM principal p
JOIN users u ON p.user_id = u.user_id
WHERE p.user_id='$userId'
");

$user = $query->fetch_assoc();

/* =========================
   GET SETTINGS
========================= */

$set = $conn->query("SELECT * FROM settings WHERE id=1");
$s = $set->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>

<title>Settings</title>

<style>

body{
margin:0;
font-family:Arial;
background:#f2f2f2;
}

.header{
background:#0a8f3c;
color:white;
padding:18px;
font-size:22px;
}

.section{
margin:15px;
}

.card{
background:white;
border-radius:10px;
box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

.item{
padding:15px;
border-bottom:1px solid #eee;
}

.item:last-child{
border:none;
}

.title{
color:#0a8f3c;
font-weight:bold;
margin-bottom:10px;
}

.bottom{
position:fixed;
bottom:0;
width:100%;
background:white;
display:flex;
justify-content:space-around;
padding:10px;
border-top:1px solid #ccc;
}

.active{
color:#0a8f3c;
font-weight:bold;
}

</style>

</head>

<body>

<div class="header">
Settings
</div>

<div class="section">

<div class="title">Profile</div>

<div class="card">

<div class="item">
Name<br>
<small><?php echo $user['full_name']; ?></small>
</div>

<div class="item">
Email<br>
<small><?php echo $user['email']; ?></small>
</div>

<div class="item">
Department<br>
<small>IF, CO, EJ</small>
</div>

</div>

</div>


<div class="section">

<div class="title">Academic Information</div>

<div class="card">

<div class="item">
Active Semester<br>
<small><?php echo $s['active_semester']; ?></small>
</div>

<div class="item">
ATKT Limit<br>
<small><?php echo $s['atkt_limit']; ?></small>
</div>

<div class="item">
Registration Status<br>

<small>
<?php
if($s['registration_open']==1){
    echo "Open";
}else{
    echo "Closed";
}
?>
</small>

</div>

</div>

</div>

</div>

</div>

<div class="section">

<div class="title">Account</div>

<div class="card">

<div class="item" onclick="location.href='change_password.php'" style="cursor:pointer;">
Change Password
</div>

<div class="item" onclick="location.href='about.php'" style="cursor:pointer;">
About Application
</div>

<div class="item" onclick="location.href='logout.php'" style="color:red;cursor:pointer;">
Logout
</div>

</div>

</div>

</body>
</html>