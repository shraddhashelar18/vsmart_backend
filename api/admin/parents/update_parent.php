<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$phone = $data['phone'] ?? '';
$oldPhone = $data['oldPhone'] ?? '';

if (empty($name) || empty($phone) || empty($oldPhone)) {
    echo json_encode([
        "status" => false,
        "message" => "All fields required"
    ]);
    exit;
}

$conn->begin_transaction();

try {

    /* =========================
       UPDATE PARENTS TABLE
    ========================= */

    $stmt = $conn->prepare("
        UPDATE parents 
        SET full_name=?, mobile_no=? 
        WHERE mobile_no=?
    ");

    $stmt->bind_param("sss", $name, $phone, $oldPhone);
    $stmt->execute();

    /* =========================
       UPDATE STUDENTS LINK
    ========================= */

    $stmt2 = $conn->prepare("
        UPDATE students 
        SET parent_mobile_no=? 
        WHERE parent_mobile_no=?
    ");

    $stmt2->bind_param("ss", $phone, $oldPhone);
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