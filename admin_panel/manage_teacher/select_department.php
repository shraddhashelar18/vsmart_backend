<?php require_once("../auth.php"); ?>

<!DOCTYPE html>
<html>

<head>

<title>Select Department</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

/* BODY */

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

/* TOP BAR */

.topbar{
background:#009846;
color:white;
padding:20px 30px;
font-size:24px;
display:flex;
align-items:center;
gap:15px;
}

/* BACK BUTTON */

.back{
color:white;
text-decoration:none;
font-size:26px;
}

/* MAIN CONTAINER */

.container{
max-width:900px;
margin:auto;
padding:40px;
}

/* DEPARTMENT CARD */

.card{
background:white;
padding:28px 35px;
border-radius:18px;
margin-bottom:22px;
display:flex;
justify-content:space-between;
align-items:center;
text-decoration:none;
color:#333;
font-size:22px;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
transition:0.2s;
}

/* HOVER EFFECT */

.card:hover{
transform:translateY(-3px);
box-shadow:0 8px 20px rgba(0,0,0,0.12);
}

/* ARROW ICON */

.arrow{
color:#777;
font-size:28px;
}

</style>

</head>

<body>

<div class="topbar">

<a href="../dashboard.php" class="back">
←
</a>

Select Department

</div>

<div class="container">

<a class="card" href="manage_teachers.php?department=IF">
IF Department
<span class="material-icons arrow">chevron_right</span>
</a>

<a class="card" href="manage_teachers.php?department=CO">
CO Department
<span class="material-icons arrow">chevron_right</span>
</a>

<a class="card" href="manage_teachers.php?department=EJ">
EJ Department
<span class="material-icons arrow">chevron_right</span>
</a>

</div>

</body>
</html>