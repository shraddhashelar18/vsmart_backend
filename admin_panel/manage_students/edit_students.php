<?php
require_once("../auth.php");
require_once("../db.php");

$id=$_GET['id'];

$stmt=$conn->prepare("
SELECT s.*,u.email
FROM students s
JOIN users u ON s.user_id=u.user_id
WHERE s.user_id=?
");

$stmt->bind_param("i",$id);
$stmt->execute();

$student=$stmt->get_result()->fetch_assoc();

if(isset($_POST['update'])){

$name=$_POST['name'];
$mobile=$_POST['mobile'];
$parent_mobile=$_POST['parent_mobile'];
$roll=$_POST['roll'];

$update=$conn->prepare("
UPDATE students
SET full_name=?,mobile_no=?,parent_mobile_no=?,roll_no=?
WHERE user_id=?
");

$update->bind_param("ssssi",$name,$mobile,$parent_mobile,$roll,$id);
$update->execute();

header("Location:manage_students.php?class=".$student['class']);
exit;
}
?>

<!DOCTYPE html>
<html>

<head>

<link rel="stylesheet" href="/vsmart/admin_panel/assets/style.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>

<body>

<div class="header">
<h1>Edit Student</h1>
</div>

<div class="form-box">

<form method="POST">

<div class="input-box">
<i class="material-icons">person</i>
<input type="text" name="name" value="<?php echo $student['full_name']; ?>">
</div>

<div class="input-box readonly">
<i class="material-icons">email</i>
<input type="text" value="<?php echo $student['email']; ?>" readonly>
</div>

<div class="input-box">
<i class="material-icons">phone</i>
<input type="text" name="mobile" value="<?php echo $student['mobile_no']; ?>">
</div>

<div class="input-box">
<i class="material-icons">phone</i>
<input type="text" name="parent_mobile" value="<?php echo $student['parent_mobile_no']; ?>">
</div>

<div class="input-box">
<i class="material-icons">badge</i>
<input type="text" name="roll" value="<?php echo $student['roll_no']; ?>">
</div>

<div class="input-box readonly">
<i class="material-icons">school</i>
<input type="text" value="<?php echo $student['class']; ?>" readonly>
</div>

<button class="btn" name="update">Update Student</button>

</form>

</div>

</body>
</html>