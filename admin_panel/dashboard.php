<?php
require_once "auth.php";
require_once "db.php";

$teachers=$conn->query("SELECT COUNT(*) as t FROM teachers")->fetch_assoc()['t'];
$students=$conn->query("SELECT COUNT(*) as s FROM students")->fetch_assoc()['s'];
$parents=$conn->query("SELECT COUNT(*) as p FROM parents")->fetch_assoc()['p'];
$classes=$conn->query("SELECT COUNT(*) as c FROM classes")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Arial;
background:#f4f6f9;
}

/* HEADER */

.header{
background:#009846;
color:white;
padding:40px;
border-bottom-left-radius:35px;
border-bottom-right-radius:35px;
}

.header h1{
margin:0;
font-size:32px;
}

.header p{
margin-top:10px;
opacity:0.9;
}

/* CONTAINER */

.container{
max-width:900px;
margin:auto;
padding:30px;
}

/* CARDS */

.cards{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:25px;
margin-bottom:40px;
}

.card{
background:white;
padding:35px;
border-radius:20px;
text-align:center;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

.icon{
background:#e8f5ec;
width:60px;
height:60px;
border-radius:50%;
display:flex;
align-items:center;
justify-content:center;
margin:auto;
margin-bottom:12px;
color:#009846;
font-size:30px;
}

.card h3{
margin:5px 0;
color:#666;
}

.card h2{
font-size:30px;
margin-top:10px;
}

/* QUICK ACTION */

.quick h2{
margin-bottom:20px;
}

.btn{
width:100%;
padding:18px;
margin-bottom:15px;
background:#009846;
color:white;
border:none;
border-radius:12px;
font-size:18px;
cursor:pointer;
}

.btn:hover{
background:#007a38;
}

/* BOTTOM NAV */

.bottom{
position:fixed;
bottom:0;
width:100%;
background:white;
display:flex;
justify-content:space-around;
padding:15px;
box-shadow:0 -2px 10px rgba(0,0,0,0.1);
}

.bottom a{
text-decoration:none;
color:#777;
text-align:center;
font-size:14px;
}

.bottom .material-icons{
display:block;
font-size:22px;
}

.bottom a.active{
color:#009846;
font-weight:bold;
}

</style>

</head>

<body>

<div class="header">
<h1>Admin Dashboard</h1>
<p>Welcome back, Administrator</p>
</div>

<div class="container">

<div class="cards">

<div class="card">
<div class="icon">
<span class="material-icons">school</span>
</div>
<h3>Total Teachers</h3>
<h2><?= $teachers ?></h2>
</div>

<div class="card">
<div class="icon">
<span class="material-icons">group</span>
</div>
<h3>Total Students</h3>
<h2><?= $students ?></h2>
</div>

<div class="card">
<div class="icon">
<span class="material-icons">person</span>
</div>
<h3>Total Parents</h3>
<h2><?= $parents ?></h2>
</div>

<div class="card">
<div class="icon">
<span class="material-icons">menu_book</span>
</div>
<h3>Total Classes</h3>
<h2><?= $classes ?></h2>
</div>

</div>

<div class="quick">

<h2>Quick Actions</h2>

<button class="btn" onclick="location.href='manage_teacher/select_department.php'">
Manage Teachers
</button>

<button class="btn"
onclick="location.href='manage_students/select_department.php'">
Manage Students
</button>

<button class="btn" onclick="location.href='manage_parents.php'">
Manage Parents
</button>

<button class="btn" onclick="location.href='manage_classes/select_department.php'">
Manage Classes
</button>

</div>

</div>

<div class="bottom">

<a href="dashboard.php" class="active">
<span class="material-icons">dashboard</span>
Dashboard
</a>

<a href="reports.php">
<span class="material-icons">description</span>
Reports
</a>

<a href="settings.php">
<span class="material-icons">settings</span>
Settings
</a>

</div>

</body>
</html>