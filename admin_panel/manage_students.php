<?php
require_once "includes/auth.php";
require_once "includes/db.php";

$result=$conn->query("SELECT user_id,full_name,roll_no,class FROM students");
?>

<link rel="stylesheet" href="assets/style.css">

<div class="wrapper">

<div class="topbar">
<a href="dashboard.php" class="back">←</a>
Manage Students
</div>

<?php while($row=$result->fetch_assoc()): ?>

<div class="card">
<b><?= $row['full_name'] ?></b><br>
<?= $row['class'] ?><br>
Roll: <?= $row['roll_no'] ?><br>
<a href="delete_student.php?id=<?= $row['user_id'] ?>">Delete</a>
</div>

<?php endwhile; ?>

<button class="btn" onclick="location.href='add_student.php'">
+ Add Student
</button>

</div>
