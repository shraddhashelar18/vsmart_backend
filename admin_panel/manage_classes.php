<?php
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
