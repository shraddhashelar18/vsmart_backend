<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$name=$_POST['name'];
$email=$_POST['email'];
$password=$_POST['password'];
$phone=$_POST['phone'];
$enrollment=$_POST['enrollment'];
$class=$_POST['class'];

/* CHECK STUDENT EXISTS */

$check=$conn->query("
SELECT enrollment_no
FROM students
WHERE enrollment_no='$enrollment'
");

if($check->num_rows==0){
die("Student enrollment not found");
}

/* CHECK PARENT ALREADY EXISTS */

$checkParent=$conn->query("
SELECT mobile_no
FROM parents
WHERE mobile_no='$phone'
");

if($checkParent->num_rows>0){
die("Parent already exists");
}

/* PASSWORD HASH */

$hashed=password_hash($password,PASSWORD_DEFAULT);

/* INSERT INTO USERS */

$stmt=$conn->prepare("
INSERT INTO users (email,password,role)
VALUES (?,?, 'parent')
");

$stmt->bind_param("ss",$email,$hashed);
$stmt->execute();

$user_id=$stmt->insert_id;

/* INSERT INTO PARENTS */

$stmt=$conn->prepare("
INSERT INTO parents (enrollment_no,user_id,full_name,mobile_no)
VALUES (?,?,?,?)
");

$stmt->bind_param("siss",$enrollment,$user_id,$name,$phone);
$stmt->execute();

/* REDIRECT */

header("Location: manage_parents.php?class=".$class);
exit;
?>