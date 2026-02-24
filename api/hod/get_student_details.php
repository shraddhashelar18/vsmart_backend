<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$enrollmentNo = $data['enrollmentNo'];

/* ===============================
   1️⃣ GET STUDENT BASIC INFO
================================ */

$stmt = $conn->prepare("
SELECT full_name, roll_no, enrollment_no, mobile_no, parent_mobile_no, email
FROM students
WHERE enrollment_no = ?
");

$stmt->bind_param("s", $enrollmentNo);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo json_encode([
        "status" => false,
        "message" => "Student not found"
    ]);
    exit;
}

$student = $result->fetch_assoc();

/* ===============================
   2️⃣ GET MARKS
================================ */

$marksQuery = $conn->prepare("
SELECT subject, ct1, ct2, final_status
FROM student_marks
WHERE enrollment_no = ?
");

$marksQuery->bind_param("s", $enrollmentNo);
$marksQuery->execute();
$marksResult = $marksQuery->get_result();

$ct1Marks = [];
$ct2Marks = [];
$finalResults = [];

while($row = $marksResult->fetch_assoc()){

    $subject = $row['subject'];

    /* CT1 */
    if(is_null($row['ct1'])){
        $ct1Marks[$subject] = "Absent";
    } else {
        $ct1Marks[$subject] = (string)$row['ct1'];
    }

    /* CT2 */
    if(is_null($row['ct2'])){
        $ct2Marks[$subject] = "Absent";
    } else {
        $ct2Marks[$subject] = (string)$row['ct2'];
    }

    /* FINAL RESULT */
    $finalResults[$subject] = $row['final_status'];
}

/* ===============================
   3️⃣ RETURN RESPONSE
================================ */

echo json_encode([
    "status" => true,
    "student" => [
        "name" => $student['full_name'],
        "rollNo" => $student['roll_no'],
        "enrollmentNo" => $student['enrollment_no'],
        "phone" => $student['mobile_no'],
        "parentMobile" => $student['parent_mobile_no'],
        "email" => $student['email'],
        "ct1Marks" => $ct1Marks,
        "ct2Marks" => $ct2Marks,
        "finalResults" => $finalResults
    ]
]);