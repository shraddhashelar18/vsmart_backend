<?php
require_once("../auth.php");
require_once("../db.php");

$department=$_GET['department'] ?? '';

$result=$conn->query("
SELECT t.*,u.email
FROM teachers t
JOIN users u ON u.user_id=t.user_id
");
?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Teachers</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f5f7f9;
}

.fab{
position:fixed;
bottom:30px;
right:30px;
width:70px;
height:70px;
background:#009846;
color:white;
font-size:36px;
border:none;
border-radius:18px;
cursor:pointer;
display:flex;
align-items:center;
justify-content:center;
box-shadow:0 6px 15px rgba(0,0,0,0.25);
}

.fab:hover{
background:#007a38;
}

/* TOPBAR */

.topbar{
background:#009846;
color:white;
padding:18px 30px;
font-size:20px;
}

/* WRAPPER */

.wrapper{
width:700px;
margin-left:50px;
margin-top:40px;
}

/* TEACHER CARD */

.teacher-card{
background:white;
border-radius:16px;
padding:30px;
margin-bottom:20px;
display:flex;
align-items:center;
justify-content:space-between;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
width:100%;
}

/* LEFT SECTION */

.left{
display:flex;
align-items:center;
gap:15px;
cursor:pointer;
}

.back{
color:white;
text-decoration:none;
font-size:24px;
margin-right:10px;
}

/* AVATAR */

.avatar{
width:45px;
height:45px;
border-radius:50%;
background:#e8f5ec;
display:flex;
align-items:center;
justify-content:center;
color:#009846;
}

/* INFO */

.info b{
font-size:16px;
}

.info p{
margin:2px 0;
font-size:13px;
color:#666;
}

/* ACTION ICONS */

.actions{
display:flex;
gap:12px;
font-size:20px;
}

.edit{
color:#2196f3;
text-decoration:none;
}

.delete{
color:red;
text-decoration:none;
}

</style>

</head>

<body>

<div class="topbar">
<a href="select_department.php"
class="back">
←
</a>
Manage Teachers
</div>

<div class="wrapper">

<?php while($row=$result->fetch_assoc()): ?>

<div class="teacher-card">

<div class="left"
onclick="location.href='teacher_details.php?id=<?=$row['user_id'] ?>department=<?= $department ?>'">

<div class="avatar">
<span class="material-icons">person</span>
</div>

<div class="info">
<b><?= $row['full_name'] ?></b>
<p><?= $row['email'] ?></p>
<p><?= $row['mobile_no'] ?></p>
</div>

</div>

<div class="actions">

<a class="edit"
href="edit_teacher.php?id=<?=$row['user_id']?>">

<span class="material-icons">edit</span>

</a>

<a class="delete"
href="delete_teacher.php?id=<?=$row['user_id']?>"
onclick="return confirm('Delete teacher?')">

<span class="material-icons">delete</span>

</a>

</div>

</div>

<?php endwhile; ?>

</div>
<button class="fab"
onclick="location.href='add_teacher.php?department=<?= $department ?>'">
+
</button>
</body>
</html>