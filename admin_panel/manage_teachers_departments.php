<?php require_once "auth.php"; ?>
<!DOCTYPE html>
<html>
<head>
<title>Select Department</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="header">
    <h1>Select Department</h1>
</div>

<div class="container">

<?php
$departments = ["IF","CO","EJ"];
foreach($departments as $d){
?>
<a href="manage_teachers.php?dept=<?= $d ?>" style="text-decoration:none;">
    <div class="stat-card" style="margin-bottom:20px;">
        <?= $d ?> Department →
    </div>
</a>
<?php } ?>

</div>

</body>
</html>