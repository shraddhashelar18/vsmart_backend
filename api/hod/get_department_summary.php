<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php");
require_once("../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['department'])){
    echo json_encode(["status"=>false,"message"=>"Department required"]);
    exit;
}

$department = $data['department'];

/* ============================= */
/* TOTAL STUDENTS */
/* ============================= */
$stmt1 = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM students 
    WHERE department=?
");
$stmt1->bind_param("s",$department);
$stmt1->execute();
$totalStudents = $stmt1->get_result()->fetch_assoc()['total'];


/* ============================= */
/* TOTAL TEACHERS */
/* ============================= */
$stmt2 = $conn->prepare("
    SELECT COUNT(DISTINCT user_id) as total 
    FROM teacher_assignments 
    WHERE department=?
");
$stmt2->bind_param("s",$department);
$stmt2->execute();
$totalTeachers = $stmt2->get_result()->fetch_assoc()['total'];


/* ============================= */
/* PROMOTION LOGIC */
/* ============================= */

$promoted = 0;
$atkt = 0;
$detained = 0;

$stmt3 = $conn->prepare("
    SELECT user_id 
    FROM students 
    WHERE department=?
");
$stmt3->bind_param("s",$department);
$stmt3->execute();
$res = $stmt3->get_result();

while($row = $res->fetch_assoc()){

    $student_id = $row['user_id'];

    $stmtMarks = $conn->prepare("
        SELECT total_marks, obtained_marks 
        FROM marks
        WHERE student_id=?
    ");
    $stmtMarks->bind_param("i",$student_id);
    $stmtMarks->execute();
    $marks = $stmtMarks->get_result();

    $failCount = 0;

    while($m = $marks->fetch_assoc()){

        $total = $m['total_marks'];
        $obtained = $m['obtained_marks'];

        if($total > 0){
            $percentage = ($obtained / $total) * 100;

            if($percentage < 40){
                $failCount++;
            }
        }
    }

    if($failCount == 0){
        $promoted++;
    }
    else if($failCount <= 2){
        $atkt++;
    }
    else{
        $detained++;
    }
}


/* ============================= */
/* FINAL RESPONSE */
/* ============================= */

echo json_encode([
    "status" => true,
    "totalStudents" => $totalStudents,
    "totalTeachers" => $totalTeachers,
    "promoted" => $promoted,
    "promotedWithBacklog" => $atkt,
    "detained" => $detained
]);