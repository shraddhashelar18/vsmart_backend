<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$department = $data['department'];

/* Total Students */
$stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE department=?");
$stmt1->bind_param("s",$department);
$stmt1->execute();
$totalStudents = $stmt1->get_result()->fetch_assoc()['total'];

/* Total Teachers */
$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM teachers WHERE department=?");
$stmt2->bind_param("s",$department);
$stmt2->execute();
$totalTeachers = $stmt2->get_result()->fetch_assoc()['total'];

/* Promotion Logic */
$promoted=0;
$atkt=0;
$detained=0;

$stmt3 = $conn->prepare("
SELECT enrollment_no FROM students WHERE department=?
");
$stmt3->bind_param("s",$department);
$stmt3->execute();
$res = $stmt3->get_result();

while($row=$res->fetch_assoc()){

    $enr = $row['enrollment_no'];

    $marks = $conn->query("
    SELECT final_status FROM student_marks
    WHERE enrollment_no='$enr'
    ");

    $failCount=0;

    while($m=$marks->fetch_assoc()){
        if($m['final_status']=="FAIL") $failCount++;
    }

    if($failCount==0) $promoted++;
    else if($failCount<=2) $atkt++;
    else $detained++;
}

echo json_encode([
    "status"=>true,
    "totalStudents"=>$totalStudents,
    "totalTeachers"=>$totalTeachers,
    "promoted"=>$promoted,
    "promotedWithBacklog"=>$atkt,
    "detained"=>$detained
]);

