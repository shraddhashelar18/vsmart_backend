<?php
require_once("config.php");

$students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$teachers = $conn->query("SELECT COUNT(*) as total FROM teacher_assignments")->fetch_assoc()['total'];
$parents  = $conn->query("SELECT COUNT(*) as total FROM parents")->fetch_assoc()['total'];
$classes  = $conn->query("SELECT COUNT(*) as total FROM classes")->fetch_assoc()['total'];
?>

<link rel="stylesheet" href="assets/style.css">
<?php include("sidebar.php"); ?>

<div class="main">
    <h1>Dashboard</h1>

    <div class="card">Total Students: <?php echo $students; ?></div>
    <div class="card">Total Teachers: <?php echo $teachers; ?></div>
    <div class="card">Total Parents: <?php echo $parents; ?></div>
    <div class="card">Total Classes: <?php echo $classes; ?></div>
</div>