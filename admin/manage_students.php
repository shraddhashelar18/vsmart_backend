<?php
session_start();
include("../db.php");

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* 📊 STATS */
$totalStudents = mysqli_num_rows(
    mysqli_query($conn, "SELECT user_id FROM users WHERE role='student'")
);

/* Parent linking not implemented yet */
$withParents = 0;
$withoutParents = $totalStudents;

/* 📘 FETCH STUDENTS (NO CLASS JOIN – CORRECT) */
$students = mysqli_query(
    $conn,
    "SELECT 
        u.user_id,
        u.email,
        s.full_name,
        s.mobile_no
     FROM users u
     INNER JOIN students s ON u.user_id = s.user_id
     WHERE u.role = 'student'"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Students</title>

<style>
body { font-family: Arial; background:#f5f5f5; margin-bottom:80px; }
.header {
    background:#009846;
    color:white;
    padding:20px;
}
.header small { color:#e0f2ea; }
.container { padding:16px; }

.stats {
    display:flex;
    gap:12px;
}
.stat {
    flex:1;
    background:#009846;
    color:white;
    padding:12px;
    border-radius:12px;
}
.search input {
    width:100%;
    padding:10px;
    border-radius:12px;
    border:none;
    background:#eee;
}

.card {
    background:white;
    padding:14px;
    border-radius:12px;
    margin-bottom:12px;
}
.row {
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.actions span {
    margin-left:8px;
    font-weight:bold;
    color:#aaa;
    cursor:not-allowed;
}

.add-btn {
    position:fixed;
    bottom:90px;
    right:20px;
    background:#009846;
    color:white;
    width:56px;
    height:56px;
    border-radius:50%;
    text-align:center;
    font-size:30px;
    line-height:56px;
    text-decoration:none;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <h2>Manage Students</h2>
    <small>View and manage student information</small>
</div>

<div class="container">

<!-- STATS -->
<div class="stats">
    <div class="stat">
        <div>Total Students</div>
        <h2><?= $totalStudents ?></h2>
    </div>
    <div class="stat">
        <div>With Parents</div>
        <h2><?= $withParents ?></h2>
    </div>
    <div class="stat">
        <div>Without</div>
        <h2><?= $withoutParents ?></h2>
    </div>
</div>

<br>

<!-- SEARCH (UI ONLY) -->
<div class="search">
    <input type="text" placeholder="Search by name, email, phone or ID...">
</div>

<br>

<p><?= $totalStudents ?> students found</p>

<!-- STUDENT LIST -->
<?php while ($row = mysqli_fetch_assoc($students)) { ?>
<div class="card">
    <div class="row">
        <strong><?= htmlspecialchars($row['full_name']) ?></strong>
        <div class="actions">
            <span>Edit</span>
            <span>Delete</span>
        </div>
    </div>

    <p>
        📧 <?= htmlspecialchars($row['email']) ?><br>
        📞 <?= htmlspecialchars($row['mobile_no']) ?>
    </p>

    <small>
        Class: Not Assigned
    </small>
</div>
<?php } ?>

</div>

<!-- ADD STUDENT BUTTON -->
<a href="add_student.php" class="add-btn">+</a>

<?php include("bottom_nav.php"); ?>

</body>
</html>
