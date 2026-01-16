<?php
include("../db.php");
session_start();

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

/* STATS */
$totalParents = mysqli_num_rows(
    mysqli_query($conn, "SELECT user_id FROM users WHERE role='parent'")
);

$activeStudents = mysqli_num_rows(
    mysqli_query($conn, "SELECT user_id FROM users WHERE role='student' AND status='approved'")
);

/* FETCH PARENTS (JOIN users + parents) */
$parents = mysqli_query(
    $conn,
    "SELECT 
        u.user_id,
        u.email,
        p.full_name,
        p.mobile_no
     FROM users u
     JOIN parents p ON u.user_id = p.user_id
     WHERE u.role = 'parent'"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Parents</title>
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
    padding:14px;
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
    <h2>Manage Parents</h2>
    <small>View and manage parent information</small>
</div>

<div class="container">

<!-- STATS -->
<div class="stats">
    <div class="stat">
        <div>Total Parents</div>
        <h2><?= $totalParents ?></h2>
    </div>
    <div class="stat">
        <div>Active Students</div>
        <h2><?= $activeStudents ?></h2>
    </div>
</div>

<br>

<!-- SEARCH (UI ONLY) -->
<div class="search">
    <input type="text" placeholder="Search by name, email or phone...">
</div>

<br>

<p><?= $totalParents ?> parents found</p>

<!-- PARENT LIST -->
<?php while ($row = mysqli_fetch_assoc($parents)) { ?>
<div class="card">

    <div class="row">
        <strong><?= $row['full_name'] ?></strong>
        <div class="actions">
            <span>Edit</span>
            <span>Delete</span>
        </div>
    </div>

    <small>Parent ID: P<?= $row['user_id'] ?></small>

    <p>
        📧 <?= $row['email'] ?><br>
        📞 <?= $row['mobile_no'] ?>
    </p>

    <div style="color:gray;font-size:13px;">
        No student linked
    </div>

</div>
<?php } ?>

</div>

<!-- FLOATING ADD BUTTON -->
<a href="add_parent.php" class="add-btn">+</a>

<?php include("bottom_nav.php"); ?>

</body>
</html>
