<?php
require_once "../config.php";

header("Content-Type: application/json");

// 🔐 API KEY CHECK
require_once "../api_guard.php";

// POST ONLY
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

// INPUT
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode([
        "status" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

// FETCH USER
$stmt = $conn->prepare(
    "SELECT user_id, email, password, role, status FROM users WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => false,
        "message" => "User not found"
    ]);
    exit;
}

$user = $result->fetch_assoc();

// PASSWORD VERIFY
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid password"
    ]);
    exit;
}

// STATUS CHECK
if ($user['status'] !== 'approved') {
    echo json_encode([
        "status" => false,
        "message" => "Waiting for admin approval"
    ]);
    exit;
}

// SUCCESS
echo json_encode([
    "status" => true,
    "message" => "Login successful",
    "data" => [
        "user_id" => (int)$user['user_id'],
        "email" => $user['email'],
        "role" => $user['role'],
        "status" => $user['status']
    ]
]);
