<?php
require_once("config.php");

$result = $conn->query("
SELECT u.full_name, u.email, p.mobile_no,
s.full_name as student_name, s.enrollment_no, s.class
FROM parents p
JOIN users u ON u.user_id = p.user_id
JOIN students s ON s.parent_mobile_no = p.mobile_no
");
?>

<link rel="stylesheet" href="assets/style.css">
<?php include("sidebar.php"); ?>

<div class="main">
<h1>Manage Parents</h1>

<table class="table">
<tr>
<th>Parent Name</th>
<th>Email</th>
<th>Mobile</th>
<th>Student</th>
<th>Enrollment</th>
<th>Class</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['full_name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['mobile_no']; ?></td>
<td><?php echo $row['student_name']; ?></td>
<td><?php echo $row['enrollment_no']; ?></td>
<td><?php echo $row['class']; ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

