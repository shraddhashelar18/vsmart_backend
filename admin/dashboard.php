<?php
include("../db.php");
session_start();

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$teachers = mysqli_num_rows(mysqli_query($conn,"SELECT user_id FROM users WHERE role='teacher'"));
$students = mysqli_num_rows(mysqli_query($conn,"SELECT user_id FROM users WHERE role='student'"));
$parents  = mysqli_num_rows(mysqli_query($conn,"SELECT user_id FROM users WHERE role='parent'"));
$classes  = mysqli_num_rows(mysqli_query($conn,"SELECT class_id FROM classes"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
.card { padding:20px; background:#e8f5ee; margin:10px; display:inline-block; width:200px; }
</style>
</head>
<body>

<h2>Welcome Admin</h2>

<div class="card">Teachers: <?= $teachers ?></div>
<div class="card">Students: <?= $students ?></div>
<div class="card">Parents: <?= $parents ?></div>
<div class="card">Classes: <?= $classes ?></div>

<hr>

<a href="manage_teachers.php">Manage Teachers</a> |
<a href="manage_students.php">Manage Students</a> |
<a href="manage_parents.php">Manage Parents</a> |
<a href="manage_classes.php">Manage Classes</a> |
<a href="approvals.php">Approve Users</a>|
<a href="logout.php">Logout</a>


</body>
</html>
