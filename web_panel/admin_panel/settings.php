<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../config.php");

/* FETCH SETTINGS SAFELY */
$res = $conn->query("SELECT * FROM settings WHERE id=1");

if(!$res){
    die("DB Error");
}

$data = $res->fetch_assoc();

if(!$data){
    die("Settings not found");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}


/* CONTAINER */
.container{
padding:15px;
}

/* SECTION */
.section-title{
color:#009846;
margin:20px 10px 10px;
font-weight:600;
}

/* CARD */
.card{
background:white;
border-radius:15px;
padding:15px;
margin-bottom:12px;
display:flex;
align-items:center;
justify-content:space-between;
box-shadow:0 3px 8px rgba(0,0,0,0.08);
}

/* LEFT */
.left{
display:flex;
align-items:center;
gap:12px;
}

.icon{
width:40px;
height:40px;
border-radius:50%;
background:#e6f4ec;
display:flex;
align-items:center;
justify-content:center;
color:#009846;
}

.subtitle{
font-size:13px;
color:#777;
}

/* TOGGLE */
.toggle{
width:50px;
height:25px;
border-radius:20px;
background:#ccc;
position:relative;
cursor:pointer;
}

.toggle.active{
background:#009846;
}

.toggle::after{
content:'';
width:22px;
height:22px;
background:white;
border-radius:50%;
position:absolute;
top:1.5px;
left:2px;
transition:0.3s;
}

.toggle.active::after{
left:25px;
}

/* LINKS */
.link{
background:white;
border-radius:15px;
padding:15px;
margin-bottom:10px;
display:flex;
justify-content:space-between;
cursor:pointer;
box-shadow:0 3px 8px rgba(0,0,0,0.08);
}

.logout{
color:red;
}

/* MODAL */
.modal{
display:none;
position:fixed;
top:0;left:0;
width:100%;height:100%;
background:rgba(0,0,0,0.4);
justify-content:center;
align-items:center;
}

.modal-box{
background:white;
padding:25px;
border-radius:15px;
text-align:center;
}

.btn{
padding:10px 20px;
border:none;
border-radius:8px;
margin:5px;
cursor:pointer;
}

.header {
    background: #009846;
    color: white;
    padding: 16px 18px;
    display: flex;
    align-items: center;
}

.back-arrow {
    font-size: 24px;
    margin-right: 12px;
    cursor: pointer;
    text-decoration: none;
    color: white;
}

.title {
    font-size: 20px;
    font-weight: 500;
}

.cancel{ background:#ccc; }
.confirm{ background:red; color:white; }

</style>
</head>

<body>

<div class="header">
    <a href="dashboard.php" class="material-icons back-arrow">arrow_back</a>
    <span class="title">Settings</span>
</div>

<div class="container">

<!-- Academic -->
<div class="section-title">Academic Control</div>

<div class="card">
<div class="left">
<div class="icon"><i class="fa fa-graduation-cap"></i></div>
<div>
<div class="title">Active Semester</div>
<div class="subtitle"><?=htmlspecialchars($data['active_semester'])?></div>
</div>
</div>

<div class="toggle <?=($data['active_semester']=='EVEN'?'active':'')?>"
onclick="toggleSetting('semester')"></div>
</div>

<div class="card">
<div class="left">
<div class="icon"><i class="fa fa-user-check"></i></div>
<div class="title">Registration Open</div>
</div>

<div class="toggle <?=($data['registration_open'] ? 'active':'')?>"
onclick="toggleSetting('registration')"></div>
</div>

<div class="card">
<div class="left">
<div class="icon"><i class="fa fa-lock"></i></div>
<div class="title">Lock Attendance</div>
</div>

<div class="toggle <?=($data['attendance_locked'] ? 'active':'')?>"
onclick="toggleSetting('attendance')"></div>
</div>

<!-- Account -->
<div class="section-title">Account</div>

<div class="link" onclick="location.href='change_password.php'">
<span>Change Password</span>
<i class="fa fa-chevron-right"></i>
</div>

<div class="link" onclick="location.href='about.php'">
<span>About Application</span>
<i class="fa fa-chevron-right"></i>
</div>

<div class="link logout" onclick="openLogout()">
<span>Logout</span>
<i class="fa fa-chevron-right"></i>
</div>

</div>

<!-- MODAL -->
<div class="modal" id="logoutModal">
<div class="modal-box">
<h3>Logout</h3>
<p>Are you sure you want to logout?</p>

<button class="btn cancel" onclick="closeLogout()">Cancel</button>
<button class="btn confirm" onclick="location.href='logout.php'">Logout</button>
</div>
</div>

<script>

function toggleSetting(type){

fetch("update_settings.php",{
method:"POST",
headers:{"Content-Type":"application/x-www-form-urlencoded"},
body:"type="+type
})
.then(res=>res.text())
.then(()=>location.reload())
.catch(()=>alert("Error updating setting"));

}

function openLogout(){
document.getElementById("logoutModal").style.display="flex";
}

function closeLogout(){
document.getElementById("logoutModal").style.display="none";
}

</script>

</body>
</html>