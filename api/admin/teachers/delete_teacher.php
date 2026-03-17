<?php
//add teacher.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . "/../../cors.php");
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../api_guard.php");

header("Content-Type: application/json");
if($currentRole != "admin"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? 0;

if (!$id) {
    echo json_encode([
        "status" => false,
        "message" => "Teacher ID required"
    ]);
    exit;
}

/* delete assignments first */

$stmt = $conn->prepare("
DELETE FROM teacher_assignments
WHERE user_id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

/* delete teacher */

$stmt = $conn->prepare("
DELETE FROM teachers
WHERE user_id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

/* delete user */

$stmt = $conn->prepare("
DELETE FROM users
WHERE user_id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode([
    "status" => true,
    "message" => "Teacher deleted successfully"
]);