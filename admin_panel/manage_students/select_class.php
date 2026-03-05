<?php
require_once("../auth.php");
require_once("../db.php");

$dept = $_GET['dept'];

$set = $conn->query("SELECT active_semester FROM settings LIMIT 1")->fetch_assoc();

if($set['active_semester']=="even"){
$condition="semester % 2 = 1";
}else{
$condition="semester % 2 = 0";
}

$classes=$conn->query("
SELECT class_name
FROM classes
WHERE department='$dept'
AND $condition
ORDER BY semester
");
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/style.css">
<title>Select Class</title>
</head>

<body>

<div class="header">
<h1>Select Class</h1>
</div>

<div class="container">

<?php while($c=$classes->fetch_assoc()){ ?>

<a href="manage_students.php?class=<?=$c['class_name']?>">

<button class="action-btn">
<?=$c['class_name']?> →
</button>

</a>

<?php } ?>

</div>

</body>
</html>