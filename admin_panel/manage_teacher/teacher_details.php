<?php
require_once("../auth.php");
require_once("../db.php");

$id=$_GET['id'];
$department=$_GET['department'] ?? '';

$teacher=$conn->query("
SELECT full_name FROM teachers
WHERE user_id='$id'
")->fetch_assoc();

$assignments=$conn->query("
SELECT class,subject
FROM teacher_assignments
WHERE user_id='$id'
AND status='active'
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Teacher Detail</title>
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

.container{
width:95%;
margin:auto;
margin-top:30px;
}
.wrapper{
width:800px;
margin:40px auto;
}

.card{
background:white;
padding:30px;
border-radius:14px;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
margin-bottom:15px;
}

.card p{
display:flex;
align-items:center;
gap:6px;
margin:4px 0;
}

.material-icons{
color: grey;
font-size:25px;
margin-right:6px;
}

.back{
color:white;
text-decoration:none;
font-size:22px;
margin-right:10px;
}

</style>

</head>

<body>

<div class="topbar">
<A href="manage_teachers.php?department=<?= $department ?>" class="back">
<span class="material-icons">arrow_back</span>
</A>
<?= $teacher['full_name'] ?>
</div>

<div class="wrapper">

<?php while($row=$assignments->fetch_assoc()): ?>

<div class="card">

<b><?= $row['class'] ?></b>

<p>
<span
class="material-icons">book
</span>
<?=$row['subject'] ?>
</p>

</div>

<?php endwhile; ?>

</div>

</body>
</html>