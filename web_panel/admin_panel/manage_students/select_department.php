<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php"); ?>

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
padding:18px 40px;
font-size:22px;
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
width:95%;
margin:auto;
margin-top:40px;
}

/* DEPARTMENT CARD */

.card{
background:white;
padding:25px 30px;
border-radius:16px;
margin-bottom:20px;
display:flex;
justify-content:space-between;
align-items:center;
text-decoration:none;
color:#333;
font-size:20px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
transition:0.2s;
}

/* HOVER EFFECT */

.card:hover{
transform:translateY(-3px);
box-shadow:0 8px 18px rgba(0,0,0,0.12);
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
<span class="material-icons">arrow_back</span></a>

Select Department

</div>

<div class="container">

<a class="card" href="select_class.php?department=IF">
IF Department
<span class="material-icons arrow">chevron_right</span>
</a>

<a class="card" href="select_class.php?department=CO">
CO Department
<span class="material-icons arrow">chevron_right</span>
</a>

<a class="card" href="select_class.php?department=EJ">
EJ Department
<span class="material-icons arrow">chevron_right</span>
</a>

</div>

</body>
</html>