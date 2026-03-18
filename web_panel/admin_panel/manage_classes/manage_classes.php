<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

/* CHECK DEPARTMENT */

if(!isset($_GET['department'])){
die("Department missing");
}

$department=$_GET['department'];

/* GET CLASSES */

$result=$conn->query("
SELECT c.*, t.full_name
FROM classes c
LEFT JOIN teachers t ON t.user_id=c.class_teacher
WHERE c.department='$department'
");
?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Classes</title>

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
padding:18px 30px;
font-size:22px;
display:flex;
align-items:center;
gap:10px;
}

.back{
color:white;
text-decoration:none;
font-size:24px;
}

/* CENTER WRAPPER */

.wrapper{
width:95%;        /* same desktop width */
margin-left:80px;   /* left spacing */
margin-top:40px;
}

/* CLASS CARD */

.card{
background:white;
border-radius:16px;
padding:25px;
margin-bottom:20px;
display:flex;
align-items:center;
justify-content:space-between;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

/* LEFT SIDE */

.left{
display:flex;
align-items:center;
gap:15px;
}

/* ICON */

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
font-size:17px;
}

.info p{
margin:2px 0;
font-size:14px;
color:#666;
}

/* ACTIONS */

.actions{
display:flex;
gap:12px;
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

/* FLOAT BUTTON */

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

</style>

</head>

<body>

<div class="topbar">

<a href="select_department.php" class="back">
<span class="material-icons">arrow_back</span>
</a>

Manage Classes - <?= $department ?>

</div>

<div class="wrapper">

<?php while($row=$result->fetch_assoc()): ?>

<div class="card">

<div class="left">

<div class="avatar">
<span class="material-icons">school</span>
</div>

<div class="info">

<b><?= $row['class_name'] ?></b>

<p>Department: <?= $row['department'] ?></p>

<p>
Class Teacher:
<?= $row['full_name'] ? $row['full_name'] : "Not Assigned" ?>
</p>

</div>

</div>

<div class="actions">

<a class="edit"
href="edit_class.php?id=<?= $row['class_id'] ?>&department=<?= $department ?>">
<span class="material-icons">edit</span>
</a>

<a class="delete"
href="delete_class.php?id=<?=$row['class_id']?>&department=<?=$department?>"
onclick="return confirm('Are you sure you want to delete this class?')">

<span class="material-icons">delete</span>

</a>

</div>

</div>

<?php endwhile; ?>

</div>

<button class="fab"
onclick="location.href='add_class.php?department=<?= $department ?>'">
+
</button>

</body>
</html>