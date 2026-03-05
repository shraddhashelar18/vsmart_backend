
<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php");
require_once("../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['department'])) {
    echo json_encode([
        "status" => false,
        "message" => "Department required"
    ]);
    exit;
}

$department = $data['department'];

/* GET ATKT LIMIT */
$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = (int)$setting->fetch_assoc()['atkt_limit'];

/* COUNT STUDENTS */
$stmt = $conn->prepare("SELECT user_id FROM students WHERE class LIKE CONCAT(?, '%')");
$stmt->bind_param("s",$department);
$stmt->execute();
$result = $stmt->get_result();

$totalStudents = 0;
$promoted = 0;
$promotedWithBacklog = 0;
$detained = 0;

while($row = $result->fetch_assoc()){

    $totalStudents++;

    $promotion = calculatePromotion($conn,$row['user_id'],$atktLimit);

    if($promotion['status'] == "PROMOTED"){
        $promoted++;
    }
    elseif($promotion['status'] == "PROMOTED_WITH_ATKT"){
        $promotedWithBacklog++;
    }
    elseif($promotion['status'] == "DETAINED"){
        $detained++;
    }
}

/* COUNT TEACHERS */
$teacherStmt = $conn->prepare("
    SELECT COUNT(DISTINCT user_id) AS totalTeachers
    FROM teachers
    WHERE department = ?
");

$teacherStmt->bind_param("s",$department);
$teacherStmt->execute();
$teacherResult = $teacherStmt->get_result();
$totalTeachers = $teacherResult->fetch_assoc()['totalTeachers'];

/* RESPONSE */

echo json_encode([
    "totalStudents" => (int)$totalStudents,
    "totalTeachers" => (int)$totalTeachers,
    "promoted" => (int)$promoted,
    "detained" => (int)$detained,
    "promotedWithBacklog" => (int)$promotedWithBacklog
]);

$stmt->close();
$teacherStmt->close();
$conn->close();

