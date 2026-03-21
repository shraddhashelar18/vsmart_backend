<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$id = $_POST['id'] ?? 0;

if(!$id){
    die("Invalid ID");
}

/* UPDATE STATUS */
$stmt = $conn->prepare("
UPDATE users 
SET status='rejected' 
WHERE user_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();

/* NOTIFICATION */
$message = "Your registration has been rejected. Please contact admin.";

$notif = $conn->prepare("
INSERT INTO notifications (teacher_user_id, class, subject, message, created_at)
VALUES (NULL, NULL, NULL, ?, NOW())
");

$notif->bind_param("s", $message);
$notif->execute();

$notification_id = $conn->insert_id;

$receiver = $conn->prepare("
INSERT INTO notification_receivers (notification_id, receiver_user_id)
VALUES (?, ?)
");

$receiver->bind_param("ii", $notification_id, $id);
$receiver->execute();

/* REDIRECT */
header("Location: user_approvals.php?success=rejected");
exit;
?>