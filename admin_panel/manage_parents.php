<?php
require_once "auth.php";
require_once "db.php";

$result=$conn->query("
SELECT p.user_id,p.full_name,p.mobile_no,u.email
FROM parents p
JOIN users u ON p.user_id=u.user_id
");
?>

<link rel="stylesheet" href="assets/style.css">

<div class="wrapper">

<div class="topbar">
<a href="dashboard.php" class="back">←</a>
Manage Parents
</div>

<?php while($row=$result->fetch_assoc()): ?>

<div class="card">
<b><?= $row['full_name'] ?></b><br>
<?= $row['email'] ?><br>
<?= $row['mobile_no'] ?><br>
<a href="delete_parent.php?id=<?= $row['user_id'] ?>">Delete</a>
</div>

<?php endwhile; ?>

<button class="btn" onclick="location.href='add_parent.php'">
+ Add Parent
</button>

</div>
