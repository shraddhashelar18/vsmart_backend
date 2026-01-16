<?php
include("../db.php");
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$res = mysqli_query(
    $conn,
    "SELECT user_id, email, role FROM users WHERE status='pending'"
);
?>

<h2>Pending Approvals</h2>

<?php if (mysqli_num_rows($res) == 0) echo "No pending users"; ?>

<?php while ($row = mysqli_fetch_assoc($res)) { ?>
<p>
<?= $row['email'] ?> (<?= $row['role'] ?>)
<a href="approve_user.php?user_id=<?= $row['user_id'] ?>">Approve</a>
</p>
<?php } ?>
