<?php
require_once "auth.php";
require_once "db.php";

$result=$conn->query("SELECT class_name,department FROM classes");
?>

<link rel="stylesheet" href="assets/style.css">

<div class="wrapper">

<div class="topbar">
<a href="dashboard.php" class="back">←</a>
Manage Classes
</div>

<?php while($row=$result->fetch_assoc()): ?>

<div class="card">
<b><?= $row['class_name'] ?></b><br>
Department: <?= $row['department'] ?>
</div>

<?php endwhile; ?>

<button class="btn" onclick="location.href='add_class.php'">
+ Add Class
</button>

</div>