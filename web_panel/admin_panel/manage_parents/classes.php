<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

/* CHECK DEPARTMENT */
if(!isset($_GET['department']) || $_GET['department']==""){
    header("Location: select_department.php");
    exit;
}

$department = $_GET['department'];

/* GET ACTIVE SEMESTER */
$set = $conn->query("SELECT active_semester FROM settings LIMIT 1")->fetch_assoc();

/* EVEN / ODD LOGIC */
if($set['active_semester'] == "EVEN"){
    $condition = "semester % 2 = 0";   // 2,4,6
}else{
    $condition = "semester % 2 = 1";   // 1,3,5
}

/* FETCH CLASSES */
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

<title><?=$department?> Classes</title>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f4f6f9;
}

.topbar{
background:#009846;
color:white;
padding:20px 30px;
font-size:24px;
display:flex;
align-items:center;
gap:10px;
}

.container{
max-width:900px;
margin:auto;
padding:40px;
}

.card{
background:white;
padding:26px 30px;
border-radius:18px;
margin-bottom:18px;
display:flex;
justify-content:space-between;
align-items:center;
text-decoration:none;
color:#333;
font-size:22px;
box-shadow:0 6px 15px rgba(0,0,0,0.08);
}

</style>
</head>

<body>

<div class="topbar">

<a href="select_department.php" style="color:white;text-decoration:none;">
<span class="material-icons">arrow_back</span>
</a>

<?=$department?> Classes

</div>

<div class="container">

<?php while($c=$classes->fetch_assoc()): ?>

<a class="card"
href="manage_parents.php?class=<?=$c['class_name']?>&department=<?=$department?>">

<?=$c['class_name']?>

<span class="material-icons">chevron_right</span>

</a>

<?php endwhile; ?>

</div>

</body>
</html>