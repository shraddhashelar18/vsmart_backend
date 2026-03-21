<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$class=$_GET['class'];

$stmt=$conn->prepare("
SELECT 
p.full_name parent_name,
p.mobile_no,
p.enrollment_no,
s.full_name student_name,
s.class
FROM parents p
JOIN students s ON p.enrollment_no=s.enrollment_no
WHERE s.class=?
");

$stmt->bind_param("s",$class);
$stmt->execute();
$res=$stmt->get_result();

$count=$res->num_rows;
?>

<!DOCTYPE html>
<html>
<head>

<title>Manage Parents</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

/* GLOBAL */
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Segoe UI;
}

body{
background:#f4f6f9;
}

/* TOPBAR */
.topbar{
background:#009846;
color:white;
padding:18px 30px;
font-size:20px;
display:flex;
align-items:center;
gap:10px;
}

.back{
color:white;
text-decoration:none;
font-size:22px;
}

/* LEFT ALIGNED WRAPPER (IMPORTANT) */
.wrapper{
    width:2200px;      /* SAME as students */
    margin-left:80px;  /* SAME spacing */
    margin-top:40px;
}

/* SEARCH */
.search-box{
display:flex;
align-items:center;
background:#eeeeee;
border-radius:30px;
padding:14px 18px;
margin-bottom:20px;
width:100%;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

.search-box input{
border:none;
outline:none;
background:transparent;
width:100%;
font-size:15px;
margin-left:10px;
}

/* COUNT */
.count{
font-size:14px;
color:#777;
margin-bottom:20px;
}

/* CARD */
.parent-card{
width:100%;
background:white;
border-radius:16px;
padding:20px;
margin-bottom:18px;
display:flex;
justify-content:space-between;
align-items:flex-start;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
transition:0.2s;
}

.parent-card:hover{
transform:translateY(-2px);
box-shadow:0 10px 18px rgba(0,0,0,0.12);
}

/* LEFT SECTION */
.left{
display:flex;
gap:14px;
}

/* AVATAR FIX (NO OVAL BUG) */
.avatar{
width:45px;
height:45px;
border-radius:50%;
background:#e8f5ec;
display:flex;
align-items:center;
justify-content:center;
color:#009846;
flex-shrink:0;
}

/* INFO */
.info b{
font-size:16px;
display:block;
}

.info p{
font-size:13px;
color:#555;
margin:2px 0;
}

/* LINKED BOX */
.linked{
background:#e8f5ec;
border-radius:10px;
padding:10px;
margin-top:8px;
font-size:13px;
display:flex;
justify-content:space-between;
align-items:center;
}

/* BADGE */
.badge{
background:#009846;
color:white;
padding:5px 10px;
border-radius:8px;
font-size:12px;
}

/* ACTIONS */
.actions{
display:flex;
gap:12px;
}

.actions .edit{
color:#2196f3;
}

.actions .delete{
color:red;
}

/* FAB */
.fab{
position:fixed;
bottom:30px;
right:30px;
width:60px;
height:60px;
background:#009846;
color:white;
font-size:30px;
border:none;
border-radius:15px;
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

<a href="classes.php?department=<?=substr($class,0,2)?>" class="back">
<span class="material-icons">arrow_back</span>
</a>

Manage Parents - <?=$class?>

</div>

<div class="wrapper">

<!-- SEARCH -->
<div class="search-box">
<span class="material-icons">search</span>
<input placeholder="Search by name, email, phone or ID...">
</div>

<div class="count">
<?=$count?> parents found
</div>

<?php while($row=$res->fetch_assoc()): ?>

<div class="parent-card">

<!-- LEFT -->
<div class="left">

<div class="avatar">
<span class="material-icons">person</span>
</div>

<div class="info">

<b><?=$row['parent_name']?></b>
<p><?=$row['mobile_no']?></p>

<div class="linked">

<div>
<?=$row['student_name']?><br>
<small>ID: <?=$row['enrollment_no']?></small>
</div>

<div class="badge">
<?=$row['class']?>
</div>

</div>

</div>

</div>

<!-- RIGHT ACTIONS -->
<div class="actions">

<a href="edit_parent.php?phone=<?=$row['mobile_no']?>" class="edit">
<span class="material-icons">edit</span>
</a>

<a href="delete_parent.php?phone=<?=$row['mobile_no']?>&class=<?=$class?>" class="delete">
<span class="material-icons">delete</span>
</a>

</div>

</div>

<?php endwhile; ?>

</div>

<button class="fab"
onclick="location.href='add_parent.php?class=<?=$class?>'">
+
</button>

</body>
</html>