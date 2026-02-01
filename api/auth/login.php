<?php
require_once __DIR__ . "/../db.php";

/* API KEY */
$headers = getallheaders();
$apiKey = $headers['x-api-key'] ?? '';

if ($apiKey !== 'VSMART_API_2026') {
    http_response_code(401);
    echo "INVALID_API_KEY";
    exit;
}

/* POST only */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "METHOD_NOT_ALLOWED";
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo "INVALID";
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT user_id, password, role, status FROM users WHERE email = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if (!$user || !password_verify($password, $user['password'])) {
    echo "INVALID";
    exit;
}

if ($user['status'] !== 'approved') {
    echo "PENDING";
    exit;
}

echo "SUCCESS|{$user['user_id']}|{$user['role']}";
