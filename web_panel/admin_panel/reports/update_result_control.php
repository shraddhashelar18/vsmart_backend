<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once("../../config.php");

$data = json_decode(file_get_contents("php://input"), true);

$upload = $data['upload'] ?? 0;
$publish = $data['publish'] ?? 0;

$stmt = $conn->prepare("
UPDATE settings 
SET allow_marksheet_upload=?, final_published=?
WHERE id=1
");

$stmt->bind_param("ii", $upload, $publish);

if($stmt->execute()){
    echo json_encode(["status"=>true]);
}else{
    echo json_encode(["status"=>false]);
}