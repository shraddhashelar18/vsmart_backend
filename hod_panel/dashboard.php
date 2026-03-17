<?php

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../promotion_helper.php");

/* SET DEPARTMENT */
$department = "IF";

/* GET ACTIVE SEMESTER (ODD / EVEN) */

$semSetting = $conn->query("SELECT active_semester FROM settings LIMIT 1");
$activeSemester = $semSetting->fetch_assoc()['active_semester'];

/* GET ATKT LIMIT */

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = (int)$setting->fetch_assoc()['atkt_limit'];

/* ================= GET STUDENTS BASED ON SEMESTER ================= */

if($activeSemester == "EVEN"){

$stmt = $conn->prepare("
SELECT user_id
FROM students
WHERE class LIKE CONCAT(?, '%')
AND CAST(SUBSTRING(class,3,1) AS UNSIGNED) IN (2,4,6)
");

}else{

$stmt = $conn->prepare("
SELECT user_id
FROM students
WHERE class LIKE CONCAT(?, '%')
AND CAST(SUBSTRING(class,3,1) AS UNSIGNED) IN (1,3,5)
");

}

$stmt->bind_param("s",$department);
$stmt->execute();
$result = $stmt->get_result();

/* ================= PROMOTION COUNT ================= */

$totalStudents = 0;
$promoted = 0;
$promotedWithBacklog = 0;
$detained = 0;

while($row = $result->fetch_assoc()){

$totalStudents++;

$promotion = calculatePromotion($conn,$row['user_id'],$atktLimit);

if($promotion['status']=="PROMOTED"){
$promoted++;
}
elseif($promotion['status']=="PROMOTED_WITH_ATKT"){
$promotedWithBacklog++;
}
elseif($promotion['status']=="DETAINED"){
$detained++;
}

}

/* ================= COUNT TEACHERS ================= */

$teacherStmt = $conn->prepare("
SELECT COUNT(DISTINCT ta.user_id) AS totalTeachers
FROM teacher_assignments ta
JOIN teachers t ON t.user_id = ta.user_id
WHERE ta.department = ?
");

$teacherStmt->bind_param("s",$department);
$teacherStmt->execute();

$teacherResult = $teacherStmt->get_result();
$totalTeachers = $teacherResult->fetch_assoc()['totalTeachers'];

?>

<!DOCTYPE html>
<html>

<head>

<title>HOD Dashboard</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="header">
☰ HOD Dashboard
</div>

<div class="dashboard-cards">

<div class="card">
<h3>Total Students</h3>
<p><?php echo $totalStudents; ?></p>
</div>

<div class="card">
<h3>Total Teachers</h3>
<p><?php echo $totalTeachers; ?></p>
</div>

<div class="card">
<h3>Promoted</h3>
<p><?php echo $promoted; ?></p>
</div>

<div class="card">
<h3>Promoted With ATKT</h3>
<p><?php echo $promotedWithBacklog; ?></p>
</div>

<div class="card">
<h3>Detained</h3>
<p><?php echo $detained; ?></p>
</div>

</div>


<div class="section-title">
Quick Actions
</div>

<div class="action-buttons">

<a class="action-btn" href="student_by_class.php">
View Students
</a>

<a class="action-btn" href="teacher.php">
View Teachers
</a>

<a class="action-btn" href="promoted_classes.php">
View Promoted List 
</a>

<a class="action-btn" href="atkt_classes.php">
View ATKT List
</a>

<a class="action-btn" href="detained_classes.php">
View Detained List
</a>

</div>
<!-- Bottom Navigation -->
<div class="bottom-nav">

<a href="dashboard.php" class="nav-item active">
<div class="icon">📊</div>
<div class="label">Dashboard</div>
</a>

<a href="settings.php" class="nav-item">
<div class="icon">⚙️</div>
<div class="label">Settings</div>
</a>

</div>

</body>
</html>