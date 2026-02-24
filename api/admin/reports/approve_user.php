<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'];

$stmt = $conn->prepare("
UPDATE users SET status='approved' WHERE user_id=?
");
$stmt->bind_param("i",$user_id);
$stmt->execute();

echo json_encode(["status"=>true]);

