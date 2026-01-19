<?php
include("../db.php");
session_start();

if ($_SESSION['role'] !== 'hod') {
    header("Location: ../login.php");
    exit;
}

$dept = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT department FROM users WHERE user_id=".$_SESSION['user_id'])
)['department'];

$students = mysqli_query($conn,
    "SELECT full_name, email, phone 
     FROM users 
     WHERE role='student' AND department='$dept'"
);
?>

<h2><?= $dept ?> Department – Students</h2>

<?php while ($s = mysqli_fetch_assoc($students)) { ?>
<p>
    <strong><?= $s['full_name'] ?></strong><br>
    <?= $s['email'] ?><br>
    <?= $s['phone'] ?>
</p>
<hr>
<?php } ?>

<a href="dashboard.php">Back</a>
