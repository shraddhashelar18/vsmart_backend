<?php
header("Content-Type: application/json");
require_once "../config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => false, "message" => "user_id required"]);
    exit;
}

$user_id = $data['user_id'];

# Step 1: Get parent mobile
$parentQuery = $conn->prepare("SELECT mobile_no, full_name FROM parents WHERE user_id = ?");
$parentQuery->bind_param("i", $user_id);
$parentQuery->execute();
$parentResult = $parentQuery->get_result();

if ($parentResult->num_rows == 0) {
    echo json_encode(["status" => false, "message" => "Parent not found"]);
    exit;
}

$parent = $parentResult->fetch_assoc();
$parent_mobile = $parent['mobile_no'];

# Step 2: Get children
$studentsQuery = $conn->prepare("SELECT * FROM students WHERE parent_mobile_no = ?");
$studentsQuery->bind_param("s", $parent_mobile);
$studentsQuery->execute();
$studentsResult = $studentsQuery->get_result();

$children = [];

while ($student = $studentsResult->fetch_assoc()) {

    $student_id = $student['user_id'];

    # Calculate attendance
    $attendanceQuery = $conn->prepare("SELECT COUNT(*) as total,
        SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present_count
        FROM attendance WHERE student_id=?");
    $attendanceQuery->bind_param("i", $student_id);
    $attendanceQuery->execute();
    $attendanceData = $attendanceQuery->get_result()->fetch_assoc();

    $attendance = 0;
    if ($attendanceData['total'] > 0) {
        $attendance = $attendanceData['present_count'] / $attendanceData['total'];
    }

    $children[] = [
        "enrollment" => $student['enrollment_no'],
        "name" => $student['full_name'],
        "class" => $student['class'],
        "roll" => $student['roll_no'],
        "attendance" => $attendance
    ];
}

echo json_encode([
    "status" => true,
    "parent_name" => $parent['full_name'],
    "children" => $children
]);
?>

