<?php
<<<<<<< HEAD
require_once("config.php");

$result = $conn->query("
SELECT c.class_name, c.department_code, u.full_name as teacher
FROM classes c
LEFT JOIN users u ON u.user_id = c.teacher_user_id
");
?>

<link rel="stylesheet" href="assets/style.css">
<?php include("sidebar.php"); ?>

<div class="main">
<h1>Manage Classes</h1>

<table class="table">
<tr>
<th>Class</th>
<th>Department</th>
<th>Teacher</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['class_name']; ?></td>
<td><?php echo $row['department_code']; ?></td>
<td><?php echo $row['teacher']; ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
=======
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
>>>>>>> b5f3620ebd6a52d6e779168b7459e9dd09ccc8ce
