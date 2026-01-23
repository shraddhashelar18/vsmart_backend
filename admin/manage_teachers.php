<?php
session_start();
include("db.php");

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
<<<<<<< HEAD
    header("Location: login.php");
    exit;
}

/* 📘 FETCH TEACHERS (NO CLASS JOIN – CORRECT AS PER DB) */
=======
    header("Location: ../login.php");
    exit;
}

/* ✅ FETCH TEACHERS */
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2
$teachers = mysqli_query(
    $conn,
    "SELECT 
        u.user_id,
        u.email,
        t.full_name,
        t.mobile_no
     FROM users u
     INNER JOIN teachers t ON u.user_id = t.user_id
     WHERE u.role = 'teacher'"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Teachers</title>

<style>
body {
    font-family: Arial;
    background:#f5f5f5;
    margin-bottom:80px;
}
.header {
    background:#009846;
    color:white;
    padding:20px;
}
.header small { color:#e0f2ea; }
.container { padding:16px; }

.search input {
    width:100%;
    padding:10px;
    border-radius:12px;
    border:none;
    background:#fff;
}

.card {
    background:white;
    padding:12px;
    border-radius:12px;
    margin-bottom:12px;
}
.row {
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.avatar {
    width:40px;
    height:40px;
    border-radius:50%;
    background:#e0f2e9;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}
.actions span {
    margin-left:8px;
    color:#aaa;
    cursor:not-allowed;
    font-size:13px;
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
    <h2>Manage Teachers</h2>
    <small>View and manage teacher information</small>
</div>

<div class="container">

<!-- SEARCH BAR -->
<div class="search">
    <input type="text" placeholder="Search teachers...">
</div>

<br>

<!-- TEACHER LIST -->
<?php if (mysqli_num_rows($teachers) > 0): ?>
<?php while ($row = mysqli_fetch_assoc($teachers)) { ?>
<div class="card">

    <div class="row">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="avatar">👤</div>
            <div>
                <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
<<<<<<< HEAD
                <small>Teacher</small>
=======
                <small>Not Assigned</small>
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2
            </div>
        </div>
        <div class="actions">
            <span>Edit</span>
            <span>Delete</span>
        </div>
    </div>

    <br>

    <div>
        📧 <?= htmlspecialchars($row['email']) ?><br>
        📞 <?= htmlspecialchars($row['mobile_no']) ?>
    </div>

</div>
<?php } ?>
<?php else: ?>
    <p>No teachers registered yet.</p>
<?php endif; ?>

</div>

<!-- ADD TEACHER BUTTON -->
<a href="add_teacher.php" class="add-btn">+</a>

<?php include("bottom_nav.php"); ?>

</body>
</html>
