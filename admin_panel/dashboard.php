<?php
require_once "auth.php";
require_once "db.php";

$teachers=$conn->query("SELECT COUNT(*) as t FROM teachers")->fetch_assoc()['t'];
$students=$conn->query("SELECT COUNT(*) as s FROM students")->fetch_assoc()['s'];
$parents=$conn->query("SELECT COUNT(*) as p FROM parents")->fetch_assoc()['p'];
$classes=$conn->query("SELECT COUNT(*) as c FROM classes")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="header">
    <h1>Admin Dashboard</h1>
    <p>Welcome back, Administrator</p>
</div>

<div class="container">

    <div class="stats">
        <div class="stat-card">
            <h3>Total Teachers</h3>
            <h2><?= $teachers ?></h2>
        </div>

        <div class="stat-card">
            <h3>Total Students</h3>
            <h2><?= $students ?></h2>
        </div>

        <div class="stat-card">
            <h3>Total Parents</h3>
            <h2><?= $parents ?></h2>
        </div>

        <div class="stat-card">
            <h3>Total Classes</h3>
            <h2><?= $classes ?></h2>
        </div>
    </div>

    <div class="quick">
        <h2>Quick Actions</h2>

        <button class="action-btn" onclick="location.href='manage_teachers.php'">Manage Teachers</button>
        <button class="action-btn" onclick="location.href='manage_students.php'">Manage Students</button>
        <button class="action-btn" onclick="location.href='manage_parents.php'">Manage Parents</button>
        <button class="action-btn" onclick="location.href='manage_classes.php'">Manage Classes</button>
    </div>

</div>

<div class="bottom-nav">
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="reports.php">Reports</a>
    <a href="settings.php">Settings</a>
</div>

</body>
</html>
