<?php
require_once "../config.php";
require_once "../api_guard.php";
header("Content-Type: application/json");

$user_id = $_POST['user_id'] ?? '';

if ($user_id==='') {
    echo json_encode(["status"=>false,"message"=>"Invalid parent"]);
    exit;
}

$conn->query("DELETE FROM parents WHERE user_id=$user_id");
$conn->query("DELETE FROM users WHERE user_id=$user_id");

echo json_encode([
    "status"=>true,
    "message"=>"Parent deleted successfully"
]);