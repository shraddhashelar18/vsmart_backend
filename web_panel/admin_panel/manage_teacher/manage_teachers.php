<?php
require_once("../config.php");

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

/* TOPBAR */

.topbar{
background:#009846;
color:white;
padding:18px 40px;
font-size:20px;
display:flex;
align-items:center;
gap:10px;
}

.back{
color:white;
text-decoration:none;
font-size:24px;
}

/* MAIN CONTAINER */

.container{
width:95%;
margin:auto;
margin-top:30px;
}

/* SEARCH BAR */

.search-box{
display:flex;
align-items:center;
background:#eceff1;
border-radius:10px;
margin-bottom:25px;
padding:0 12px;
}

.search-icon{
color:#777;
margin-right:8px;
}

.search{
width:100%;
padding:14px;
border:none;
background:transparent;
font-size:14px;
outline:none;
}


/* TEACHER CARD */

.teacher-card{
background:white;
border-radius:14px;
padding:20px;
margin-bottom:18px;
display:flex;
align-items:center;
justify-content:space-between;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

/* LEFT SIDE */

.left{
display:flex;
align-items:center;
gap:15px;
cursor:pointer;
}

/* AVATAR */

.avatar{
width:50px;
height:50px;
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
gap:15px;
font-size:22px;
}

.edit{
color:#2196f3;
text-decoration:none;
}

.delete{
color:red;
text-decoration:none;
}

/* FLOAT ADD BUTTON */

.fab{
position:fixed;
bottom:35px;
right:35px;
width:65px;
height:65px;
background:#009846;
color:white;
font-size:34px;
border:none;
border-radius:16px;
cursor:pointer;
display:flex;
align-items:center;
justify-content:center;
box-shadow:0 6px 15px rgba(0,0,0,0.25);
}

.fab:hover{
background:#007a38;
}

</style>

</head>

<body>

<div class="topbar">

<a href="select_department.php" class="back">
<span class="material-icons">arrow_back</span>
</a>

Manage Teachers

</div>

<div class="container">

<div class="search-box">

<span class="material-icons search-icon">search</span>

<input 
type="text" 
class="search"
placeholder="Search teacher...">

</div>

<?php while($row=$result->fetch_assoc()): ?>

<div class="teacher-card">

<div class="left"
onclick="location.href='teacher_details.php?id=<?=$row['user_id']?>&department=<?=$department?>'">

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
onclick="location.href='add_teacher.php?department=<?=$department?>'">
+
</button>

</body>
</html>