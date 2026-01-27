<?php
// api/user_login.php

require_once "api_guard.php";   // API key validation
require_once "../db.php";       // DB connection

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// 🔐 Validate input
if ($email === '' || $password === '') {
    echo "INVALID";
    exit;
}

// 🔎 Query user
$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "INVALID";
    exit;
}

$user = mysqli_fetch_assoc($result);

// ⏳ Approval check
if ($user['status'] !== 'approved') {
    echo "PENDING";
    exit;
}

/*
RESPONSE FORMAT (PLAIN TEXT):
user_id|role|department1,department2
*/
echo $user['user_id'] . "|" . $user['role'] . "|" . ($user['departments'] ?? '');
exit;
