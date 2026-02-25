<?php

$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    echo json_encode(["status" => false, "message" => "Unauthorized"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);

$stmt = $conn->prepare("
    SELECT u.user_id, u.role, h.department
    FROM users u
<<<<<<< HEAD
    LEFT JOIN hods h ON u.user_id = h.user_id
=======
    LEFT JOIN hod h ON u.user_id = h.user_id
>>>>>>> 442021c76d048be8a1c011e8e7f420eff8a9b9b5
    WHERE u.auth_token = ?
");

$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => false, "message" => "Invalid Token"]);
    exit;
}

$user = $result->fetch_assoc();

$currentUserId = $user['user_id'];
$currentRole = $user['role'];           // 🔥 VERY IMPORTANT
$currentDepartment = $user['department'];