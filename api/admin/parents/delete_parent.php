<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

$phone = $data['phone'] ?? '';

if(empty($phone)){
    echo json_encode([
        "status"=>false,
        "message"=>"Phone number required"
    ]);
    exit;
}

$conn->begin_transaction();

try {

    // 1️⃣ Get user_id from parents table
    $stmt1 = $conn->prepare("SELECT user_id FROM parents WHERE mobile_no=?");
    $stmt1->bind_param("s", $phone);
    $stmt1->execute();
    $result = $stmt1->get_result();

    if ($result->num_rows == 0) {
        throw new Exception("Parent not found");
    }

    $row = $result->fetch_assoc();
    $user_id = $row['user_id'];

    // 2️⃣ Delete from parents table
    $stmt2 = $conn->prepare("DELETE FROM parents WHERE mobile_no=?");
    $stmt2->bind_param("s", $phone);
    $stmt2->execute();

    // 3️⃣ Delete from users table
    $stmt3 = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt3->bind_param("i", $user_id);
    $stmt3->execute();

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Parent deleted from both tables"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}