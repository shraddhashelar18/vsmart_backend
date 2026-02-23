<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'];   // required
$name    = $data['name'];
$phone   = $data['phone'];

$conn->begin_transaction();

try {

    // Update parents table
    $stmt1 = $conn->prepare("
        UPDATE parents 
        SET full_name=?, mobile_no=? 
        WHERE user_id=?
    ");
    $stmt1->bind_param("ssi", $name, $phone, $user_id);
    $stmt1->execute();

    // If you also want to update student name in users table (NOT email)
    $stmt2 = $conn->prepare("
        UPDATE users 
        SET first_login=first_login 
        WHERE user_id=?
    ");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Parent updated successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => "Update failed"
    ]);
}