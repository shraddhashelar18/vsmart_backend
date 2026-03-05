<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

if($currentRole!="admin"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}

$data=json_decode(file_get_contents("php://input"),true);

$user_id=$data['user_id'] ?? '';

if(empty($user_id)){
    echo json_encode([
        "status"=>false,
        "message"=>"User id required"
    ]);
    exit;
}

$stmt=$conn->prepare("UPDATE users SET status='approved' WHERE user_id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();

echo json_encode([
    "status"=>true,
    "message"=>"User approved successfully"
]);