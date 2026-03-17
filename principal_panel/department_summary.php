<?php
session_start();
include "../config.php";
include "../promotion_helper.php";

/* ROLE */
$currentRole = $_SESSION['role'] ?? "principal";
$currentDepartment = $_SESSION['department'] ?? null;

/* GET DEPARTMENT */
if($currentRole == "hod"){
    $department = $currentDepartment;
}else{
    $department = isset($_GET['department']) ? $_GET['department'] : null;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- ICONS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{
margin:0;
font-family:'Segoe UI';
background:#f2f2f2;
}

/* HEADER */
.header{
background:#0b8f3a;
color:white;
padding:15px;
display:flex;
align-items:center;
gap:10px;
font-size:18px;
}

/* TITLE */
.title{
font-size:18px;
margin:15px;
font-weight:600;
}

/* ROW */
.row{
display:flex;
gap:12px;
padding:0 15px;
margin-bottom:12px;
}

/* CARD */
.summary-card{
background:#fff;
padding:18px;
border-radius:15px;
text-align:center;
flex:1;
box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

/* NUMBER */
.number{
font-size:22px;
font-weight:bold;
}

/* BUTTON */
.action-btn{
display:block;
background:#0b8f3a;
color:white;
margin:10px 15px;
padding:15px;
text-align:center;
border-radius:12px;
text-decoration:none;
}

/* DEPT CARD */
.card{
display:flex;
justify-content:space-between;
background:#fff;
padding:15px;
margin:10px 15px;
border-radius:12px;
text-decoration:none;
color:black;
}

/* BOTTOM NAV */
.bottom{
position:fixed;
bottom:0;
width:100%;
display:flex;
background:#fff;
border-top:1px solid #ddd;
}

.nav-item{
flex:1;
text-align:center;
padding:10px;
color:#aaa;
text-decoration:none;
}

.nav-item.active{
color:#0b8f3a;
}

.icon{
font-size:20px;
display:block;
}

</style>
</head>

<body>

<div class="header">
<i class="fa-solid fa-arrow-left" onclick="history.back()"></i>
<?php echo $department ? $department." Department" : "Dashboard"; ?>
</div>

<?php

/* SELECT DEPARTMENT */

if(!$department && $currentRole=="principal"){

echo "<div class='title'>Select Department</div>";

$q=mysqli_query($conn,"SELECT DISTINCT department FROM students");

while($r=mysqli_fetch_assoc($q)){
$d=$r['department'];

echo "<a class='card' href='?department=$d'>
<div>$d Department</div>
<i class='fa-solid fa-chevron-right'></i>
</a>";
}

}

/* SUMMARY */

elseif($department){

$setting=$conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit=(int)$setting->fetch_assoc()['atkt_limit'];

$stmt=$conn->prepare("SELECT user_id FROM students WHERE department=?");
$stmt->bind_param("s",$department);
$stmt->execute();
$res=$stmt->get_result();

$total=0; $prom=0; $atkt=0; $det=0;

while($row=$res->fetch_assoc()){

$total++;

$p=calculatePromotion($conn,$row['user_id'],$atktLimit,$department);

if($p['status']=="PROMOTED") $prom++;
elseif($p['status']=="PROMOTED_WITH_ATKT") $atkt++;
else $det++;

}

/* TEACHERS */

$t=$conn->prepare("
SELECT COUNT(DISTINCT ta.user_id) t
FROM teacher_assignments ta
WHERE ta.department=?
");
$t->bind_param("s",$department);
$t->execute();
$totalTeachers=$t->get_result()->fetch_assoc()['t'];

?>

<div class="title">Department Summary</div>

<div class="row">
<div class="summary-card"><div class="number"><?php echo $total; ?></div>Students</div>
<div class="summary-card"><div class="number"><?php echo $totalTeachers; ?></div>Teachers</div>
</div>

<div class="row">
<div class="summary-card"><div class="number"><?php echo $prom; ?></div>Promoted</div>
<div class="summary-card"><div class="number"><?php echo $atkt; ?></div>ATKT</div>
<div class="summary-card"><div class="number"><?php echo $det; ?></div>Detained</div>
</div>

<div class="title">Actions</div>

<a class="action-btn" href="student_by_class.php?department=<?php echo $department;?>">View Students</a>
<a class="action-btn" href="teacher.php?department=<?php echo $department;?>">View Teachers</a>
<a class="action-btn" href="promoted_classes.php?department=<?php echo $department;?>">View Promoted List</a>
<a class="action-btn" href="atkt_classes.php?department=<?php echo $department;?>">View ATKT List</a>
<a class="action-btn" href="detained_classes.php?department=<?php echo $department;?>">View Detained List</a>

<?php } ?>

<!-- BOTTOM NAV -->
<div class="bottom">

<a href="dashboard.php" class="nav-item active">
<i class="fa-solid fa-table-cells-large icon"></i>
Dashboard
</a>

<a href="settings.php" class="nav-item">
<i class="fa-solid fa-gear icon"></i>
Settings
</a>

</div>

</body>
</html>