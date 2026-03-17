<?php
require_once("../auth.php");
require_once("../db.php");

if(!isset($_GET['id'])){
die("Teacher ID missing");
}

$user_id = intval($_GET['id']);

/* DELETE TEACHER ASSIGNMENTS */

$conn->query("
DELETE FROM teacher_assignments
WHERE user_id='$user_id'
");

/* DELETE TEACHER PROFILE */

$conn->query("
DELETE FROM teachers
WHERE user_id='$user_id'
");

/* DELETE USER ACCOUNT */

$conn->query("
DELETE FROM users
WHERE user_id='$user_id'
");

/* REDIRECT BACK */

header("Location: manage_teachers.php?deleted=1");
exit;
?>