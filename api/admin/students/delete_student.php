<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "User ID required"
    ]);
    exit;
}

$conn->begin_transaction();

try {

    /* 1️⃣ Get enrollment for parent unlink */
    $stmt1 = $conn->prepare("
        SELECT enrollment_no 
        FROM students 
        WHERE user_id = ?
    ");
    $stmt1->bind_param("i", $data['user_id']);
    $stmt1->execute();
    $result = $stmt1->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        throw new Exception("Student not found");
    }

    $enrollment = $student['enrollment_no'];

    /* 2️⃣ Delete student */
    $stmt2 = $conn->prepare("
        DELETE FROM students 
        WHERE user_id = ?
    ");
    $stmt2->bind_param("i", $data['user_id']);
    $stmt2->execute();

    /* 3️⃣ Delete login */
    $stmt3 = $conn->prepare("
        DELETE FROM users 
        WHERE user_id = ?
    ");
    $stmt3->bind_param("i", $data['user_id']);
    $stmt3->execute();

    /* 4️⃣ Remove parent link */
    $stmt4 = $conn->prepare("
        UPDATE parents 
        SET enrollment_no = NULL 
        WHERE enrollment_no = ?
    ");
    $stmt4->bind_param("s", $enrollment);
    $stmt4->execute();

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Student deleted successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}