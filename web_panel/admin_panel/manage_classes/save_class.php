<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

/* CHECK REQUEST */
if($_SERVER['REQUEST_METHOD'] != "POST"){
    die("Invalid request");
}

/* GET DATA */
$class_name = $_POST['class_name'];
$department = $_POST['department'];
$teacher = $_POST['class_teacher'] ?? NULL;

/* VALIDATION */
if(empty($class_name) || empty($department)){
    die("All fields required");
}

/* CHECK DUPLICATE */
$check = $conn->prepare("SELECT class_id FROM classes WHERE class_name=?");
$check->bind_param("s", $class_name);
$check->execute();
$res = $check->get_result();

if($res->num_rows > 0){
    die("Class already exists");
}

/* INSERT CLASS */
$stmt = $conn->prepare("
INSERT INTO classes (class_name, department, class_teacher)
VALUES (?, ?, ?)
");

$stmt->bind_param("ssi", $class_name, $department, $teacher);
$stmt->execute();

/* REDIRECT */
header("Location: manage_classes.php?department=".$department);
exit;
?>