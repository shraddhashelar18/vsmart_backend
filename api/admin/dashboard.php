<?php
include("../db.php");
session_start();

// ----------------- API KEY -----------------
define('API_KEY', 'VSMART_API_2026');
$provided_key = $_GET['api_key'] ?? $_POST['api_key'] ?? '';

if ($provided_key !== API_KEY) {
    http_response_code(401); // Unauthorized
    echo "Invalid API key!";
    exit;
}

// ----------------- ADMIN SESSION CHECK -----------------
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// ----------------- FETCH COUNTS -----------------
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
body { font-family: Arial, sans-serif; background:#f5f5f5; }
.card { 
    padding:20px; 
    background:#e8f5ee; 
    margin:10px; 
    display:inline-block; 
    width:200px; 
    text-align:center; 
    border-radius:10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
a { text-decoration:none; margin-right:10px; color:#009846; }
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
<a href="approvals.php">Approve Users</a> |
<a href="logout.php">Logout</a>

</body>
</html>