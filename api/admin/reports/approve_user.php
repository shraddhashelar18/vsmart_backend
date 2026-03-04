<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");
header("Content-Type: application/json");

if ($currentRole != "admin") {
    echo json_encode(["status"=>false,"message"=>"Access Denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? '';

if(empty($user_id) || !is_numeric($user_id)){
    echo json_encode([
        "status"=>false,
        "message"=>"Valid user ID required"
    ]);
    exit;
}

if (!isset($data['user_id'])) {
    echo json_encode(["status"=>false,"message"=>"User ID required"]);
    exit;
}

$userId = $data['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id=? AND status='pending'");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status"=>false,"message"=>"User not found or already approved"]);
    exit;
}

$user = $result->fetch_assoc();

$update = $conn->prepare("UPDATE users SET status='approved' WHERE user_id=?");
$update->bind_param("s", $userId);
$update->execute();

echo json_encode([
    "status" => true,
    "message" => "User approved successfully"
]);
?>