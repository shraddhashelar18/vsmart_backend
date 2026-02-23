<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$user_id   = intval($data['user_id']);
$full_name = $data['full_name'];
$mobile_no = $data['mobile_no'];
$subjects  = $data['subjects']; 
// structure: department -> class -> subjects

// 1️⃣ Update teacher basic details
$stmt = $conn->prepare("
    UPDATE teachers
    SET full_name = ?, mobile_no = ?
    WHERE user_id = ?
");

$stmt->bind_param("ssi", $full_name, $mobile_no, $user_id);
$stmt->execute();

// 2️⃣ Delete old assignments for this teacher
$conn->query("DELETE FROM teacher_assignments WHERE user_id = $user_id");

// 3️⃣ Insert new assignments
foreach ($subjects as $department => $classes) {

    foreach ($classes as $class => $subs) {

        foreach ($subs as $subject) {

            $stmt2 = $conn->prepare("
                INSERT INTO teacher_assignments
                (user_id, department_code, class, status, subject)
                VALUES (?, ?, ?, 'active', ?)
            ");

            $stmt2->bind_param("isss", $user_id, $department, $class, $subject);
            $stmt2->execute();
        }
    }
}

echo json_encode([
    "status" => true,
    "message" => "Teacher updated successfully"
]);