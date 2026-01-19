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

$teachers = mysqli_query($conn,
    "SELECT full_name, email, phone 
     FROM users 
     WHERE role='teacher' AND department='$dept'"
);
?>

<h2><?= $dept ?> Department – Teachers</h2>

<?php while ($t = mysqli_fetch_assoc($teachers)) { ?>
<p>
    <strong><?= $t['full_name'] ?></strong><br>
    <?= $t['email'] ?><br>
    <?= $t['phone'] ?>
</p>
<hr>
<?php } ?>

<a href="dashboard.php">Back</a>
