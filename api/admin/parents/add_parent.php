<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$name   = $data['name'] ?? '';
$email  = $data['email'] ?? '';
$password = $data['password'] ?? '';
$phone  = $data['phone'] ?? '';
$children = $data['children'][0] ?? '';

if(!$name || !$email || !$password || !$phone || !$children){
    echo json_encode(["status"=>false,"message"=>"All fields required"]);
    exit;
}

$conn->begin_transaction();

try {

    // 1️⃣ Insert into users table
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt1 = $conn->prepare("
        INSERT INTO users (email, password, role, status, first_login)
        VALUES (?, ?, 'parent', 'approved', 1)
    ");
    $stmt1->bind_param("ss", $email, $hash);
    $stmt1->execute();

    $user_id = $stmt1->insert_id;

    // 2️⃣ Insert into parents table
    $stmt2 = $conn->prepare("
        INSERT INTO parents (user_id, full_name, mobile_no, enrollment_no)
        VALUES (?, ?, ?, ?)
    ");
    $stmt2->bind_param("isss", $user_id, $name, $phone, $children);
    $stmt2->execute();

    // 3️⃣ Update student parent_mobile_no
    $stmt3 = $conn->prepare("
        UPDATE students
        SET parent_mobile_no = ?
        WHERE enrollment_no = ?
    ");
    $stmt3->bind_param("ss", $phone, $children);
    $stmt3->execute();

    $conn->commit();

    echo json_encode([
        "status"=>true,
        "message"=>"Parent added successfully"
    ]);

} catch(Exception $e){
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>$e->getMessage()
    ]);
}