<?php require_once("../config.php"); ?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="header">
    <h1>Settings</h1>
</div>

<div class="container">

    <button class="action-btn">Active Semester</button>
    <button class="action-btn">Registration Open</button>
    <button class="action-btn">Lock Attendance</button>
    <button class="action-btn" onclick="location.href='logout.php'">Logout</button>

</div>

<div class="bottom-nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="reports.php">Reports</a>
    <a href="settings.php" class="active">Settings</a>
</div>

</body>
</html>

