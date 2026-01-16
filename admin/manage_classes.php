<?php
include("../db.php");
session_start();

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

/* FETCH CLASSES */
$classes = mysqli_query(
    $conn,
    "SELECT class_id, class_name, department FROM classes"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Classes</title>
<style>
body {
    font-family: Arial;
    background: #f5f5f5;
    margin-bottom: 80px;
}
.header {
    background: #009846;
    color: white;
    padding: 20px;
}
.header small {
    color: #e0f2ea;
}
.container {
    padding: 16px;
}
.card {
    background: white;
    padding: 14px;
    border-radius: 12px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-title {
    font-weight: bold;
    font-size: 16px;
}
.card-sub {
    color: #555;
    font-size: 13px;
}
.actions a {
    margin-left: 10px;
    text-decoration: none;
    font-weight: bold;
}
.edit { color: #1e88e5; }
.delete { color: #e53935; }
.add-btn {
    position: fixed;
    bottom: 90px;
    right: 20px;
    background: #009846;
    color: white;
    padding: 14px 18px;
    border-radius: 50%;
    font-size: 22px;
    text-decoration: none;
}
</style>

<script>
function confirmDelete(className, id) {
    if (confirm("Delete class " + className + "?")) {
        window.location.href = "delete_class.php?id=" + id;
    }
}
</script>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <h2>Manage Classes</h2>
    <small>View, add and organize classes</small>
</div>

<!-- CLASS LIST -->
<div class="container">

<?php while ($row = mysqli_fetch_assoc($classes)) { ?>
    <div class="card">
        <div>
            <div class="card-title"><?= $row['class_name'] ?></div>
            <div class="card-sub">Department: <?= $row['department'] ?></div>
        </div>
        <div class="actions">
            <a class="edit" href="add_class.php?id=<?= $row['class_id'] ?>">Edit</a>
            <a class="delete" href="javascript:void(0)"
               onclick="confirmDelete('<?= $row['class_name'] ?>', <?= $row['class_id'] ?>)">
               Delete
            </a>
        </div>
    </div>
<?php } ?>

</div>

<!-- FLOATING ADD BUTTON -->
<a href="add_class.php" class="add-btn">+</a>

<?php include("bottom_nav.php"); ?>

</body>
</html>
