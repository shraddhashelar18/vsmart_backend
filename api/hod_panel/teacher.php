<?php
require_once("../config.php");

/* =========================
GET DEPARTMENT
========================= */

$department = "IF";   // change department if needed


/* =========================
GET TEACHERS FROM DATABASE
========================= */

$stmt = $conn->prepare("
SELECT DISTINCT
    t.user_id,
    t.full_name,
    t.mobile_no,
    u.email
FROM teachers t
JOIN users u ON t.user_id = u.user_id
JOIN teacher_assignments ta ON t.user_id = ta.user_id
WHERE ta.department = ?
AND ta.status = 'active'
");

$stmt->bind_param("s",$department);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>

<title>Teachers</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="teacher-list">

<?php while($row = $result->fetch_assoc()) { ?>

<a href="teacher_details.php?id=<?php echo $row['user_id']; ?>" style="text-decoration:none;color:black;">

<div class="student-card">

<div class="student-info">

<div class="avatar">
👨‍🏫
</div>

<div>
<div class="student-name">
<?php echo $row['full_name']; ?>
</div>

<div class="roll">
<?php echo $row['email']; ?>
</div>

</div>

</div>

</div>

</a>

<?php } ?>

</div>


</body>
</html>