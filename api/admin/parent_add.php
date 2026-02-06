<?php
require_once "../config.php";
require_once "../api_guard.php";

header("Content-Type: application/json");

/* ---------- INPUTS ---------- */
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$mobile    = trim($_POST['mobile_no'] ?? '');

/* ---------- VALIDATION ---------- */
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

/* ---------- CHECK EMAIL DUPLICATE ---------- */
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

/* ---------- TRANSACTION START ---------- */
$conn->begin_transaction();

try {

    /* ---------- INSERT INTO USERS ---------- */
    $stmt = $conn->prepare(
        "INSERT INTO users (email, password, role, status, first_login)
         VALUES (?, NULL, 'parent', 'approved', 1)"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $user_id = $stmt->insert_id;

    /* ---------- INSERT INTO PARENTS ---------- */
    $stmt = $conn->prepare(
        "INSERT INTO parents (user_id, full_name, mobile_no)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param(
        "iss",
        $user_id,
        $full_name,
        $mobile
    );
    $stmt->execute();

    /* ---------- COMMIT ---------- */
    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Parent added successfully. Parent will set password on first login"
    ]);

} catch (Exception $e) {

    /* ---------- ROLLBACK ---------- */
    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => "Failed to add parent",
        "error" => $e->getMessage()
    ]);
}