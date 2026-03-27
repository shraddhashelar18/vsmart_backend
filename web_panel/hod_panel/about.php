<?php
session_start();
require_once("../config.php");

if(!isset($_SESSION['user_id'])){
header("Location: ../login.php");
exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>About Application</title>

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;

/* CENTER FULL PAGE */
display:flex;
flex-direction:column;
align-items:center;
justify-content:center;
height:100vh;
}

/* HEADER */
.header {
background:#009846;
color:white;
padding:16px 20px;
display:flex;
align-items:center;
width:100%;
position:absolute;
top:0;
left:0;
}

.back-arrow {
font-size:24px;
margin-right:12px;
text-decoration:none;
color:white;
}

.header-title {
font-size:20px;
font-weight:500;
}

/* CARD */
.card{
background:white;
padding:50px 70px;
border-radius:18px;
box-shadow:0 6px 18px rgba(0,0,0,0.12);
text-align:center;
min-width:400px;
transition:0.3s;
}

.card:hover{
transform:translateY(-5px);
}

/* LOGO */
.logo-img{
width:140px;
margin-bottom:15px;
}

/* TEXT */
.title{
font-size:24px;
font-weight:600;
margin-bottom:5px;
}

.sub{
font-size:15px;
color:#777;
margin-bottom:12px;
}

</style>

</head>

<body>

<div class="header">

<div class="back" onclick="history.back()">←</div>

About Application

</div>

<div class="container">

<div class="card">

<img src="/vsmart/web_panel/assets/logo.png" class="logo-img">

<div class="title">VSmart</div>
<div class="sub">Smart Academic Management System</div>

<hr>

<p>Version 1.0.0</p>
<p>© 2026 All Rights Reserved</p>

</div>

</body>
</html>