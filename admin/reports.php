<?php
include("../db.php");
session_start();

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$activePage = "reports";
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports</title>
<style>
body {
    font-family: Arial;
    background:#f5f5f5;
    margin-bottom:80px;
}

/* HEADER */
.header {
    background:#009846;
    color:white;
    padding:16px;
    font-size:20px;
    font-weight:bold;
}

/* CARD */
.card {
    background:white;
    border-radius:14px;
    padding:14px;
    margin-bottom:14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.card-left {
    display:flex;
    align-items:center;
    gap:14px;
}
.icon {
    width:52px;
    height:52px;
    border-radius:50%;
    background:rgba(0,152,70,0.15);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    color:#009846;
}
.title {
    font-weight:600;
    font-size:16px;
}
.subtitle {
    font-size:13px;
    color:#555;
}
.arrow {
    color:#999;
}
.container {
    padding:16px;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">Reports</div>

<div class="container">

<!-- ATTENDANCE REPORT -->
<div class="card">
    <div class="card-left">
        <div class="icon">✔</div>
        <div>
            <div class="title">Attendance Report</div>
            <div class="subtitle">View student attendance details</div>
        </div>
    </div>
    <div class="arrow">›</div>
</div>

<!-- PERFORMANCE REPORT -->
<div class="card">
    <div class="card-left">
        <div class="icon">📊</div>
        <div>
            <div class="title">Performance Report</div>
            <div class="subtitle">Student academic performance</div>
        </div>
    </div>
    <div class="arrow">›</div>
</div>

<!-- TEACHER REPORT -->
<div class="card">
    <div class="card-left">
        <div class="icon">🏫</div>
        <div>
            <div class="title">Teacher Report</div>
            <div class="subtitle">Teacher activity & allocation</div>
        </div>
    </div>
    <div class="arrow">›</div>
</div>

<!-- PARENT ENGAGEMENT -->
<div class="card">
    <div class="card-left">
        <div class="icon">👥</div>
        <div>
            <div class="title">Parent Engagement</div>
            <div class="subtitle">Parent interactions & reports</div>
        </div>
    </div>
    <div class="arrow">›</div>
</div>

<!-- DOWNLOAD REPORT -->
<div class="card">
    <div class="card-left">
        <div class="icon">⬇</div>
        <div>
            <div class="title">Download Reports</div>
            <div class="subtitle">Export reports as PDF</div>
        </div>
    </div>
    <div class="arrow">›</div>
</div>

</div>

<?php include("bottom_nav.php"); ?>

</body>
</html>
