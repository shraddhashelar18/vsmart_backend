<?php
include("../db.php");
session_start();

/* 🔐 HOD ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'hod') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* GET HOD DEPARTMENT */
$res = mysqli_query($conn,
    "SELECT department FROM users WHERE user_id=$user_id"
);
$hod = mysqli_fetch_assoc($res);
$dept = $hod['department'];

/* STATS */
$totalStudents = mysqli_num_rows(
    mysqli_query($conn,
        "SELECT user_id FROM users WHERE role='student' AND department='$dept'")
);

$totalTeachers = mysqli_num_rows(
    mysqli_query($conn,
        "SELECT user_id FROM users WHERE role='teacher' AND department='$dept'")
);

/* Dummy academic stats (future scope) */
$promoted = 312;
$detained = 38;
?>

<!DOCTYPE html>
<html>
<head>
<title>HOD Dashboard</title>
</head>
<body>

<h2>HOD Dashboard (<?= $dept ?>)</h2>

<ul>
    <li>Total Students: <?= $totalStudents ?></li>
    <li>Total Teachers: <?= $totalTeachers ?></li>
    <li>Promoted: <?= $promoted ?></li>
    <li>Detained: <?= $detained ?></li>
</ul>

<hr>

<a href="students.php">View Students</a><br>
<a href="teachers.php">View Teachers</a><br>
<a href="promoted.php">View Promoted List</a><br>
<a href="detained.php">View Detained List</a><br>
<a href="settings.php">Settings</a><br>
<a href="logout.php">Logout</a>

</body>
</html>
