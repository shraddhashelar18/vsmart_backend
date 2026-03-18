<?php
require_once("../config.php");

/* CHECK REQUEST */
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    die("Invalid request");
}

/* GET DATA */
$id = $_POST['class_id'];
$class_name = $_POST['class_name'];
$department = $_POST['department'];
$teacher = $_POST['class_teacher'];
$old_department = $_POST['old_department'];

/* UPDATE QUERY */
$stmt = $conn->prepare("
UPDATE classes 
SET class_name=?, department=?, class_teacher=? 
WHERE class_id=?
");

$stmt->bind_param("sssi", $class_name, $department, $teacher, $id);
$stmt->execute();

/* REDIRECT BACK */
header("Location: manage_classes.php?department=".$department);
exit;
?>