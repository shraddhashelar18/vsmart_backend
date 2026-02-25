<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php");
if ($currentRole != 'hod' && $currentRole != 'principal') {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['class'])) {
    echo json_encode(["status"=>false,"message"=>"class required"]);
    exit;
}

$class = $data['class'];

/* ================= GET ATKT LIMIT ================= */

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = $setting->fetch_assoc()['atkt_limit'];

/* ================= FETCH STUDENTS ================= */

$stmt = $conn->prepare("
    SELECT s.user_id, s.full_name, s.roll_no, s.enrollment_no,
           s.mobile_no, s.parent_mobile_no, u.email
    FROM students s
    LEFT JOIN users u ON s.user_id = u.user_id
    WHERE s.class = ?
");
$stmt->bind_param("s",$class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while($student = $result->fetch_assoc()){

    $studentId = $student['user_id'];

    /* ===== Fetch Marks ===== */

    $marksStmt = $conn->prepare("
        SELECT subject, exam_type, obtained_marks
        FROM marks
        WHERE student_id = ?
    ");
    $marksStmt->bind_param("i",$studentId);
    $marksStmt->execute();
    $marksResult = $marksStmt->get_result();

    $ct1Marks = [];
    $ct2Marks = [];
    $finalResults = [];

    while($row = $marksResult->fetch_assoc()){

        $marksValue = $row['obtained_marks'];

        if ($marksValue === NULL) {
            $marksValue = "Absent";
        }

        if($row['exam_type'] == "CT-1"){
            $ct1Marks[$row['subject']] = (string)$marksValue;
        }
        elseif($row['exam_type'] == "CT-2"){
            $ct2Marks[$row['subject']] = (string)$marksValue;
        }
        elseif($row['exam_type'] == "FINAL"){
            $finalResults[$row['subject']] = (string)$marksValue;
        }
    }

    /* ===== Promotion Calculation ===== */

    $promotion = calculatePromotion($conn,$studentId,$atktLimit);

    $students[] = [
        "id" => (string)$student['user_id'],
        "name" => $student['full_name'],
        "rollNo" => $student['roll_no'],
        "enrollmentNo" => $student['enrollment_no'],
        "email" => $student['email'],
        "phone" => $student['mobile_no'],
        "parentMobile" => $student['parent_mobile_no'],
        "backlogCount" => $promotion['backlogCount'],
        "promotionStatus" => $promotion['status'],
        "ct1Marks" => $ct1Marks,
        "ct2Marks" => $ct2Marks,
        "finalResults" => $finalResults
    ];
}

echo json_encode([
    "status"=>true,
    "students"=>$students
]);