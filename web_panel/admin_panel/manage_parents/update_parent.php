<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

if(!isset($_POST['old_phone'])){
die("Invalid request");
}

$name=$_POST['name'];
$phone=$_POST['phone'];
$old_phone=$_POST['old_phone'];

/* UPDATE PARENT */

$stmt=$conn->prepare("
UPDATE parents
SET full_name=?, mobile_no=?
WHERE mobile_no=?
");

$stmt->bind_param("sss",$name,$phone,$old_phone);
$stmt->execute();

/* GET CLASS FOR REDIRECT */

$res=$conn->query("
SELECT s.class
FROM students s
JOIN parents p ON s.enrollment_no=p.enrollment_no
WHERE p.mobile_no='$phone'
LIMIT 1
");

$row=$res->fetch_assoc();
$class=$row['class'];

/* REDIRECT BACK */

header("Location: manage_parents.php?class=".$class);
exit;
?>