<?php
require_once("config.php");

$result = $conn->query("
SELECT s.*, s.full_name, u.email
FROM students s
JOIN users u ON u.user_id = s.user_id
");
?>

<link rel="stylesheet" href="assets/style.css">
<?php include("sidebar.php"); ?>

<div class="main">
<h1>Manage Students</h1>

<table class="table">
<tr>
<th>Name</th>
<th>Email</th>
<th>Enrollment</th>
<th>Class</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['full_name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['enrollment_no']; ?></td>
<td><?php echo $row['class']; ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

