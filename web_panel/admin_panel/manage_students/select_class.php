<?php
require_once("../config.php");

if(!isset($_GET['department']) || $_GET['department']==""){
    header("Location: select_department.php");
    exit;
}

$department = $_GET['department'] ?? '';

/* get active semester from settings */
$set = $conn->query("SELECT active_semester FROM settings LIMIT 1")->fetch_assoc();

/* EVEN / ODD logic */
if($set['active_semester'] == "EVEN"){
    // show semesters 2,4,6
    $condition = "semester % 2 = 0";
}else{
    // show semesters 1,3,5
    $condition = "semester % 2 = 1";
}

/* fetch classes */
$classes = $conn->query("
SELECT class_name
FROM classes
WHERE department='$department'
AND $condition
ORDER BY semester
");
?>

<!DOCTYPE html>
<html>

<head>

<link rel="stylesheet" href="../assets/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
display:block;
background:#f4f6f9;
margin:0;
font-family:Segoe UI;
}

.topbar{
background:#009846;
color:white;
padding:22px;
font-size:24px;
display:flex;
align-items:center;
gap:10px;
}

.container{
max-width:700px;
margin:auto;
padding:40px 20px;
}

.card{
background:white;
padding:25px 30px;
border-radius:18px;
margin-bottom:20px;
display:flex;
justify-content:space-between;
align-items:center;
text-decoration:none;
color:black;
font-size:20px;
box-shadow:0 6px 15px rgba(0,0,0,0.1);
}

</style>

</head>

<body>

<div class="topbar">

<a href="select_department.php" style="color:white;text-decoration:none;font-size:22px;">
<span class="material-icons">arrow_back</span>
</a>

Select Class

</div>

<div class="container">

<?php while($row=$classes->fetch_assoc()): ?>

<a class="card"
href="manage_students.php?class=<?= $row['class_name'] ?>&department=<?= $department ?>">

<?= $row['class_name'] ?>

<span class="material-icons">chevron_right</span>

</a>

<?php endwhile; ?>

</div>

</body>
</html>