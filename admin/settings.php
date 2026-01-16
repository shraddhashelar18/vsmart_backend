<?php
include("../db.php");
session_start();

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

/* LOGOUT */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

$activePage = "settings";
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>
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

/* SECTION TITLE */
.section-title {
    color:#009846;
    font-weight:bold;
    font-size:16px;
    margin:20px 0 10px;
}

/* CARD */
.card {
    background:white;
    border-radius:10px;
    margin-bottom:10px;
}
.card a {
    text-decoration:none;
    color:black;
}
.tile {
    display:flex;
    align-items:center;
    padding:14px;
    justify-content:space-between;
}
.left {
    display:flex;
    align-items:center;
    gap:12px;
}
.icon {
    width:36px;
    height:36px;
    border-radius:50%;
    background:rgba(0,152,70,0.1);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#009846;
    font-size:18px;
}
.logout {
    color:red;
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
<div class="header">Settings</div>

<div class="container">

<!-- ACCOUNT -->
<div class="section-title">Account</div>

<div class="card">
    <div class="tile">
        <div class="left">
            <div class="icon">🔒</div>
            <div>Change Password</div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<div class="card">
    <div class="tile">
        <div class="left">
            <div class="icon">🔔</div>
            <div>Notification Settings</div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<!-- ACADEMIC -->
<div class="section-title">Academic</div>

<div class="card">
    <div class="tile">
        <div class="left">
            <div class="icon">🏫</div>
            <div>Manage Classes</div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<div class="card">
    <div class="tile">
        <div class="left">
            <div class="icon">📘</div>
            <div>Manage Subjects</div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<div class="card">
    <div class="tile">
        <div class="left">
            <div class="icon">📅</div>
            <div>Academic Year</div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<!-- SYSTEM -->
<div class="section-title">System</div>

<div class="card">
    <div class="tile">
        <div class="left">
            <div class="icon">🛡</div>
            <div>Security Settings</div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<div class="card">
    <div class="tile">
        <div class="left">
            <div class="icon">💾</div>
            <div>Backup & Restore</div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<!-- APP -->
<div class="section-title">App</div>

<div class="card">
    <div class="tile">
        <div class="left">
            <div class="icon">ℹ</div>
            <div>About Application</div>
        </div>
        <div class="arrow">›</div>
    </div>
</div>

<div class="card">
    <a href="settings.php?logout=true">
        <div class="tile">
            <div class="left">
                <div class="icon logout">⎋</div>
                <div class="logout">Logout</div>
            </div>
        </div>
    </a>
</div>

</div>

<?php include("bottom_nav.php"); ?>

</body>
</html>
