<?php
require_once("config.php");

$result = $conn->query("
SELECT u.full_name, u.email, t.department
FROM teacher_assignments t
JOIN users u ON u.user_id = t.user_id
");
?>

<link rel="stylesheet" href="assets/style.css">
<?php include("sidebar.php"); ?>

<div class="main">
<h1>Manage Teachers</h1>

<table class="table">
<tr>
<th>Name</th>
<th>Email</th>
<th>Department</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['full_name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['department']; ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>
