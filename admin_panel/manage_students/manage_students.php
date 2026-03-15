<?php
require_once("../auth.php");
require_once("../db.php");

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

/* PAGE WRAPPER */

.students-wrapper{
width:1000px;
margin-left:8px;
margin-top:40px;
}

/* SEARCH BOX */

.search-box{
display:flex;
align-items:center;
background:#eeeeee;
border-radius:30px;
padding:18px 20px;
margin-bottom:20px;
width:900px;         /* larger search bar */
box-shadow:0 4px 10px rgba(0,0,0,0.08);
}


.search-box input{
border:none;
outline:none;
background:transparent;
width:100%;
font-size:18px;      /* larger text */
margin-left:10px;
}


/* STUDENT COUNT */

.student-count{
font-size:14px;
color:#777;
margin-bottom:20px;
}

/* STUDENT CARD */

.student-card{
background:white;
border-radius:16px;
padding:22px;
margin-bottom:20px;
display:flex;
align-items:center;
justify-content:space-between;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
width:80%;
}
/* AVATAR */

.student-avatar{
background:#e8f5ec;
padding:12px;
border-radius:50%;
color:#009846;
margin-right:14px;
}

/* INFO */

.student-info{
flex:1;
}

.student-info b{
font-size:17px;
display:block;
margin-bottom:4px;
}

.student-info p{
font-size:14px;
color:#555;
margin:2px 0;
}

/* ACTION ICONS */

.student-actions{
display:flex;
gap:12px;
}

.student-actions .edit{
color:#2196f3;
}

.student-actions .delete{
color:red;
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

.fab:hover{
background:#007a38;
}

</style>

</head>

<body>

<div class="topbar">

<a href="select_class.php" class="back">←</a>

Manage Students - <?= $class ?>

</div>


<div class="students-wrapper">


<div class="search-box">

<span class="material-icons">search</span>

<input type="text" placeholder="Search by name, email, phone or ID...">

</div>


<p class="student-count">

<?= $result->num_rows ?> students found

</p>


<?php while($row=$result->fetch_assoc()): ?>

<div class="student-card">


<div class="student-avatar">

<span class="material-icons">person</span>

</div>


<div class="student-info">

<b><?= $row['full_name'] ?></b>

<p><?= $row['email'] ?></p>

<p><?= $row['mobile_no'] ?></p>

<p><?= $row['class'] ?></p>

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


<button class="fab"

onclick="location.href='add_student.php?class=<?= $class ?>'">

+

</button>


</div>

</body>

</html>