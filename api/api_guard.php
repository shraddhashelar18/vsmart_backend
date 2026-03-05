<?php

$headers = getallheaders();

/* ✅ Check Authorization header safely */

if (!isset($headers['Authorization'])) {
    echo json_encode([
        "status" => false,
        "message" => "Authorization header missing"
    ]);
    exit;
}

/* ✅ Extract token */

$token = str_replace("Bearer ", "", $headers['Authorization']);

$stmt = $conn->prepare("
    SELECT u.user_id, u.role, h.department
    FROM users u
    LEFT JOIN hods h ON u.user_id = h.hod_id
    WHERE u.auth_token = ?
");

$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Token"
    ]);
    exit;
}

$user = $result->fetch_assoc();

/* ✅ Make global user info available */

$currentUserId = $user['user_id'];
$currentRole = $user['role'];
$currentDepartment = $user['department'] ?? null;