<?php require_once("../config.php"); ?>

<!DOCTYPE html>
<html>
<head>

<title>Reports</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

.topbar{
background:#009846;
color:white;
padding:22px 30px;
font-size:24px;
}

.wrapper{
max-width:900px;
margin:40px auto;
}

.card{
background:white;
border-radius:16px;
padding:22px;
margin-bottom:18px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
cursor:pointer;
text-decoration:none;
color:#333;
}

.left{
display:flex;
align-items:center;
gap:16px;
}

.icon{
width:55px;
height:55px;
border-radius:50%;
background:#e8f5ec;
display:flex;
align-items:center;
justify-content:center;
color:#009846;
}

.title{
font-size:18px;
font-weight:600;
}
.back{
color:white;
text-decoration:none;
font-size:24px;
}

.desc{
font-size:13px;
color:#777;
}

</style>

</head>

<body>

<div class="topbar">
<a href="dashboard.php"class="back">
<span class="material-icons">arrow_back</span>
</a>
Reports
</div>

<div class="wrapper">

<a class="card" href="reports/attendance_report.php">

<div class="left">
<div class="icon"><span class="material-icons">fact_check</span></div>
<div>
<div class="title">Attendance Report</div>
<div class="desc">View student attendance details</div>
</div>
</div>

<span class="material-icons">chevron_right</span>

</a>

<a class="card" href="reports/performance_report.php">

<div class="left">
<div class="icon"><span class="material-icons">bar_chart</span></div>
<div>
<div class="title">Performance Report</div>
<div class="desc">Student academic performance</div>
</div>
</div>

<span class="material-icons">chevron_right</span>

</a>

<a class="card" href="reports/result_control.php">

<div class="left">
<div class="icon"><span class="material-icons">assignment</span></div>
<div>
<div class="title">Result Control</div>
<div class="desc">Declare results & control marksheet upload</div>
</div>
</div>

<span class="material-icons">chevron_right</span>

</a>

<a class="card" href="reports/user_approvals.php">

<div class="left">
<div class="icon"><span class="material-icons">verified_user</span></div>
<div>
<div class="title">User Approvals</div>
<div class="desc">Verify & approve registrations</div>
</div>
</div>

<span class="material-icons">chevron_right</span>

</a>

</div>

</body>
</html>