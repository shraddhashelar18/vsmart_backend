<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$id = $_POST['id'] ?? 0;

if(!$id){
    die("Invalid ID");
}

/* GET ROLE */
$roleQuery = $conn->prepare("SELECT role FROM users WHERE user_id=?");
$roleQuery->bind_param("i",$id);
$roleQuery->execute();
$roleResult = $roleQuery->get_result()->fetch_assoc();

if(!$roleResult){
    die("User not found");
}

$role = $roleResult['role'];

/* UPDATE STATUS */
$stmt = $conn->prepare("
UPDATE users 
SET status='approved' 
WHERE user_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

/* ================= NOTIFICATION ================= */

/* 1. INSERT INTO notifications */
$message = "Your account has been approved. You can now login.";

$notif = $conn->prepare("
INSERT INTO notifications (teacher_user_id, class, subject, message, created_at)
VALUES (NULL, NULL, NULL, ?, NOW())
");

$notif->bind_param("s", $message);
$notif->execute();

/* 2. GET notification_id */
$notification_id = $conn->insert_id;

/* 3. INSERT INTO notification_receivers */
$receiver = $conn->prepare("
INSERT INTO notification_receivers (notification_id, receiver_user_id)
VALUES (?, ?)
");

$receiver->bind_param("ii", $notification_id, $id);
$receiver->execute();

/* ================= REDIRECT ================= */
if($role == "teacher"){
    header("Location: ../manage_teacher/assign_teacher.php?user_id=".$id);
    exit;
}else{
    header("Location: user_approvals.php?success=approved");
    exit;
}
?>