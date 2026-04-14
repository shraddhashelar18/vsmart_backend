<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../../config.php");

/* VALIDATION */
if(!isset($_POST['roll_no'])){
    die("Invalid request");
}

/* GET FORM DATA */
$name        = $_POST['name'];
$email       = $_POST['email'];
$mobile      = $_POST['mobile'];
$parent_mob  = $_POST['parent_mobile'];
$roll        = $_POST['roll_no'];
$enroll      = $_POST['enrollment_no'];
$class       = $_POST['class'];

/* =========================
   UPDATE STUDENT TABLE
========================= */
$stmt = $conn->prepare("
UPDATE students 
SET 
    full_name = ?, 
    mobile_no = ?, 
    parent_mobile_no = ?, 
    enrollment_no = ?, 
    class = ?
WHERE roll_no = ?
");

$stmt->bind_param("ssssss", $name, $mobile, $parent_mob, $enroll, $class, $roll);
$stmt->execute();

/* =========================
   GET USER ID FROM STUDENT
========================= */
$res = $conn->query("SELECT user_id FROM students WHERE roll_no = '$roll'");

if(!$res){
    die("User fetch error: " . $conn->error);
}

$row = $res->fetch_assoc();
$user_id = $row['user_id'] ?? null;

/* =========================
   UPDATE EMAIL IN USERS TABLE
========================= */
if($user_id){
    $stmt2 = $conn->prepare("
    UPDATE users 
    SET email = ?
    WHERE user_id = ?
    ");

    $stmt2->bind_param("si", $email, $user_id);
    $stmt2->execute();
}

/* =========================
   REDIRECT BACK
========================= */
header("Location: manage_students.php?class=".$class);
exit;
?>