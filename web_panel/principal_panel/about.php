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
font-family:Arial;
background:#f2f2f2;
}

/* HEADER */

.header{
background:#0a8f3c;
color:white;
padding:15px;
font-size:20px;
display:flex;
align-items:center;
}

.back{
margin-right:10px;
cursor:pointer;
font-size:20px;
}

/* CENTER */

.container{
display:flex;
justify-content:center;
align-items:center;
height:80vh;
}

/* CARD */

.card{
background:white;
width:360px;
padding:35px;
border-radius:12px;
text-align:center;
box-shadow:0 3px 12px rgba(0,0,0,0.15);
}

/* LOGO */

.logo img{
width:90px;
margin-bottom:15px;
}

/* TEXT */

.title{
font-size:24px;
font-weight:bold;
margin-bottom:5px;
}

.subtitle{
color:#777;
margin-bottom:20px;
font-size:14px;
}

hr{
border:none;
border-top:1px solid #ddd;
margin:20px 0;
}

.version{
font-size:15px;
}

.copy{
color:#777;
font-size:14px;
margin-top:5px;
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

<div class="logo">
<img src="../assets/logo.png">
</div>

<div class="title">VSmart</div>

<div class="subtitle">
Smart Academic Management System
</div>

<hr>

<div class="version">
Version 1.0.0
</div>

<div class="copy">
© 2026 All Rights Reserved
</div>

</div>

</div>

</body>
</html>