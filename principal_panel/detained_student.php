<?php
require_once("../config.php");
require_once("../promotion_helper.php");

/* ================= GET CLASS ================= */

if(!isset($_GET['class'])){
    echo "Class not selected";
    exit;
}

$class = $_GET['class'];

/* Extract semester from class (IF6KA → 6) */

$currentSemester = (int)preg_replace('/[^0-9]/','',$class);

/* ================= ATKT LIMIT ================= */

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = $setting->fetch_assoc()['atkt_limit'];

/* ================= GET STUDENTS ================= */

$stmt = $conn->prepare("
SELECT user_id, full_name
FROM students
WHERE class = ?
");

$stmt->bind_param("s",$class);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

<title>Detained Students</title>

<style>

body{
margin:0;
font-family:Arial;
background:#e9e4ea;
}

.header{
background:#0a8f3c;
color:white;
padding:18px;
font-size:22px;
}

.container{
padding:15px;
}

.student-card{
background:white;
padding:18px;
margin-bottom:15px;
border-radius:12px;
box-shadow:0 2px 6px rgba(0,0,0,0.2);
}

.name{
font-size:18px;
font-weight:bold;
margin-bottom:6px;
}

.info{
font-size:14px;
margin-top:4px;
}

.kt{
color:red;
font-weight:bold;
}

</style>

</head>

<body>

<div class="header">
Detained Students - <?php echo $class; ?>
</div>

<div class="container">

<?php

while($row = $result->fetch_assoc()){

$promotion = calculatePromotion($conn,$row['user_id'],$atktLimit);

/* ===== DETAINED LOGIC ===== */

if($currentSemester == 6){

    if(
        $promotion['status']=="DETAINED" ||
        $promotion['status']=="PROMOTED_WITH_ATKT"
    ){

?>

<div class="student-card">

<div class="name">
<?php echo $row['full_name']; ?>
</div>

<div class="info">
Backlogs : <span class="kt">
<?php echo $promotion['backlogCount']; ?>
</span>
</div>

<div class="info">
Status : <?php echo $promotion['status']; ?>
</div>

<div class="info">
KT Subjects : <?php echo implode(", ",$promotion['ktSubjects']); ?>
</div>

</div>

<?php
    }

}
else{

    if($promotion['status']=="DETAINED"){
?>

<div class="student-card">

<div class="name">
<?php echo $row['full_name']; ?>
</div>

<div class="info">
Backlogs : <span class="kt">
<?php echo $promotion['backlogCount']; ?>
</span>
</div>

<div class="info">
Status : <?php echo $promotion['status']; ?>
</div>

<div class="info">
KT Subjects : <?php echo implode(", ",$promotion['ktSubjects']); ?>
</div>

</div>

<?php
    }

}

}

?>

</div>

</body>
</html>