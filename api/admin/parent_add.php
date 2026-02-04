<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

// collect inputs
$full_name     = trim($_POST['full_name'] ?? '');
$email         = trim($_POST['email'] ?? '');
$mobile        = trim($_POST['mobile_no'] ?? '');
$enrollment_no = trim($_POST['enrollment_no'] ?? '');

// validation
if ($full_name === '' || $email === '' || $mobile === '') {
    echo json_encode([
        "status" => false,
        "message" => "All required fields must be filled"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email address"
    ]);
    exit;
}

// check duplicate email
$check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "status" => false,
        "message" => "Email already exists"
    ]);
    exit;
}

// START TRANSACTION
$conn->begin_transaction();

try {
    // insert into users (NO password)
    $stmt = $conn->prepare(
        "INSERT INTO users (email, password, role, status, first_login)
         VALUES (?, NULL, 'parent', 'approved', 1)"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $user_id = $stmt->insert_id;

    // insert into parents (USING full_name)
    $stmt = $conn->prepare(
        "INSERT INTO parents (user_id, full_name, mobile_no, enrollment_no)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "isss",
        $user_id,
        $full_name,
        $mobile,
        $enrollment_no
    );
    $stmt->execute();

    // commit if both succeed
    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Parent added successfully. Parent will set password on first login"
    ]);

} catch (Exception $e) {

    // rollback on error
    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => "Failed to add parent",
        "error" => $e->getMessage()
    ]);
}