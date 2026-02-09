<?php
require_once "../../config.php";
require_once "../../api_guard.php";

header("Content-Type: application/json");

/* ---------------- INPUT ---------------- */
$user_id = $_POST['user_id'] ?? '';
$role    = $_POST['role'] ?? '';

if ($user_id === '' || $role === '') {
    echo json_encode([
        "status" => false,
        "message" => "user_id and role are required"
    ]);
    exit;
}

/* ---------------- CHECK USER ---------------- */
$check = $conn->prepare("
    SELECT status FROM users WHERE user_id = ?
");
$check->bind_param("i", $user_id);
$check->execute();
$user = $check->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode([
        "status" => false,
        "message" => "User not found"
    ]);
    exit;
}

if ($user['status'] !== 'pending') {
    echo json_encode([
        "status" => false,
        "message" => "User is not pending approval"
    ]);
    exit;
}

/* ---------------- TRANSACTION ---------------- */
$conn->begin_transaction();

try {

    /* ================= TEACHER ASSIGNMENT ================= */
    if ($role === 'teacher') {

        $department = trim($_POST['department_code'] ?? '');
        $class      = trim($_POST['class'] ?? '');
        $subject    = trim($_POST['subject'] ?? '');

        if ($department === '' || $class === '' || $subject === '') {
            throw new Exception("Department, class and subject are required for teacher approval");
        }

        // insert assignment using ONLY user_id
        $stmt = $conn->prepare("
            INSERT INTO teacher_assignments
            (user_id, department_code, class, subject, status)
            VALUES (?, ?, ?, ?, 'active')
        ");
        $stmt->bind_param("isss", $user_id, $department, $class, $subject);
        $stmt->execute();
    }

    /* ---------------- APPROVE USER ---------------- */
    $stmt = $conn->prepare("
        UPDATE users
        SET status = 'approved'
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => ucfirst($role) . " approved successfully"
    ]);

} catch (Exception $e) {

    $conn->rollback();

    echo json_encode([
        "status" => false,
        "message" => "Approval failed",
        "error" => $e->getMessage()
    ]);
}