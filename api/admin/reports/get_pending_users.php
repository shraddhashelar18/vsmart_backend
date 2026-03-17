<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");
header("Content-Type: application/json");
if($currentRole != "admin"){
    echo json_encode(["status"=>false,"message"=>"Access denied"]);
    exit;
}

$sql = "
SELECT 
    u.user_id,
    u.email,
    u.role,
    COALESCE(s.full_name, t.full_name) AS fullName,
    t.employee_id AS employeeId,
    COALESCE(s.mobile_no, t.mobile_no) AS mobile_no
FROM users u
LEFT JOIN students s ON u.user_id = s.user_id
LEFT JOIN teachers t ON u.user_id = t.user_id
WHERE u.status = 'pending'
";

$result = $conn->query($sql);

$users = [];

while($row = $result->fetch_assoc()){
    $users[] = $row;
}

echo json_encode([
    "status" => true,
    "users" => $users
]);
?>