<?php
include("../config.php");

header("Content-Type: application/json");

/* ======================
   CHECK POST PARAMETER
======================*/

if(!isset($_POST['user_id'])){
    echo json_encode([
        "status"=>false,
        "message"=>"user_id is required"
    ]);
    exit;
}

$user_id = intval($_POST['user_id']);

/* ======================
   GET STUDENT DATA
======================*/

$stmt = $conn->prepare("
    SELECT roll_no, user_id, full_name, class, mobile_no, 
           parent_mobile_no, enrollment_no, department, status
    FROM students
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo json_encode([
        "status"=>false,
        "message"=>"Student not found"
    ]);
    exit;
}

$student = $result->fetch_assoc();

/* ======================
   ATTENDANCE SECTION
======================*/

$total_days = 180;  // you can change later

$stmt2 = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM attendance 
    WHERE student_id = ? 
    AND status = 'Present'
");

$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();

$present = $row2['total'] ?? 0;
$absent = $total_days - $present;

$percentage = 0;
if($total_days > 0){
    $percentage = round(($present/$total_days)*100,2);
}

/* ======================
   FINAL RESPONSE
======================*/

echo json_encode([
    "status"=>true,
    "student"=>$student,
    "attendance"=>[
        "total_days"=>$total_days,
        "present"=>$present,
        "absent"=>$absent,
        "percentage"=>$percentage
    ]
]);
?>