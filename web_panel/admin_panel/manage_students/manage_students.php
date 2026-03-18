<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$class = $_GET['class'];

$result = $conn->query("
SELECT s.*, u.email
FROM students s
JOIN users u ON u.user_id=s.user_id
WHERE s.class='$class'
");
?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Students</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

/* GLOBAL */
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial, sans-serif;
}

body{
background:#f5f7f9;
}

/* TOPBAR */
.topbar{
background:#009846;
color:white;
padding:18px 25px;
font-size:20px;
display:flex;
align-items:center;
gap:12px;
}

.back{
color:white;
text-decoration:none;
font-size:24px;
}

/* WRAPPER → SAME AS TEACHERS PAGE */
.students-wrapper{
margin:30px 40px;
}

/* SEARCH */
.search-box{
display:flex;
align-items:center;
gap:10px;
width:100%;
border-radius:30px;
padding:16px 20px;
background:#eeeeee;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
margin-bottom:20px;
}

.search-box input{
border:none;
outline:none;
background:transparent;
width:100%;
font-size:15px;
}

/* COUNT */
.student-count{
font-size:13px;
color:#777;
margin-bottom:15px;
}

/* CARD → FULL WIDTH */
.student-card{
background:white;
border-radius:16px;
padding:20px;
margin-bottom:18px;
display:flex;
align-items:center;
justify-content:space-between;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
transition:0.2s;
}

.student-card:hover{
transform:translateY(-2px);
box-shadow:0 8px 18px rgba(0,0,0,0.12);
}

/* LEFT */
.student-left{
display:flex;
align-items:center;
gap:14px;
}

/* AVATAR */
.student-avatar{
background:#e8f5ec;
padding:12px;
border-radius:50%;
color:#009846;
}

/* INFO */
.student-info b{
font-size:15px;
display:block;
margin-bottom:3px;
}

.student-info p{
font-size:13px;
color:#555;
margin:2px 0;
}

/* ACTIONS */
.student-actions{
display:flex;
gap:14px;
font-size:18px;
}

.student-actions .edit{
color:#2196f3;
}

.student-actions .delete{
color:red;
}

/* FAB */
.fab{
position:fixed;
bottom:30px;
right:30px;
width:65px;
height:65px;
background:#009846;
color:white;
font-size:32px;
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

<a href="select_class.php?department=<?= $_GET['department'] ?>" class="back">
<span class="material-icons">arrow_back</span>
</a>

Manage Students - <?= $class ?>

</div>

<div class="students-wrapper">

<!-- SEARCH -->
<div class="search-box">
<span class="material-icons">search</span>
<input type="text" placeholder="Search by name, email, phone or ID...">
</div>

<!-- COUNT -->
<p class="student-count">
<?= $result->num_rows ?> students found
</p>

<!-- LIST -->
<?php while($row=$result->fetch_assoc()): ?>

<div class="student-card">

<div class="student-left">

<div class="student-avatar">
<span class="material-icons">person</span>
</div>

<div class="student-info">
<b><?= $row['full_name'] ?></b>
<p><?= $row['email'] ?></p>
<p><?= $row['mobile_no'] ?></p>
<p><?= $row['class'] ?></p>
</div>

</div>

<div class="student-actions">

<a href="edit_student.php?id=<?= $row['user_id'] ?>" class="edit">
<span class="material-icons">edit</span>
</a>

<a href="delete_student.php?id=<?= $row['user_id'] ?>" 
class="delete"
onclick="return confirm('Delete this student?')">
<span class="material-icons">delete</span>
</a>

</div>

</div>

<?php endwhile; ?>

<!-- FLOAT BUTTON -->
<button class="fab"
onclick="location.href='add_student.php?class=<?= $class ?>'">
+
</button>

</div>

</body>

</html>