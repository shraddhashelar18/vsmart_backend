<!DOCTYPE html>
<html>
<head>
<title>About</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

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
<a href="settings.php" class="material-icons back-arrow">arrow_back</a>
<span class="header-title">About Application</span>
</div>

<div class="card">

<img src="assets/logo.png" class="logo-img">

<div class="title">VSmart</div>
<div class="sub">Smart Academic Management System</div>

<hr>

<p>Version 1.0.0</p>
<p>© 2026 All Rights Reserved</p>

</div>

</body>
</html>