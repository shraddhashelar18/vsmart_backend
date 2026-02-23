<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$semester = $data['semester'] ?? '';

if($class == '' || $semester == ''){
    echo json_encode([
        "status"=>false,
        "message"=>"Class and Semester required"
    ]);
    exit;
}

/* ========================= */
/* GET STUDENTS OF CLASS */
/* ========================= */

$stmt = $conn->prepare("
    SELECT user_id, full_name 
    FROM students 
    WHERE class=?
");
$stmt->bind_param("s",$class);
$stmt->execute();
$result = $stmt->get_result();

$final_data = [];

while($student = $result->fetch_assoc()){

    $student_id = $student['user_id'];

    /* ========================= */
    /* COUNT FAILED FINAL SUBJECTS */
    /* ========================= */

    $fail_stmt = $conn->prepare("
        SELECT COUNT(*) as fail_count
        FROM marks
        WHERE student_id = ?
        AND semester = ?
        AND exam_type = 'FINAL'
        AND obtained_marks < 40
    ");

    $fail_stmt->bind_param("is",$student_id,$semester);
    $fail_stmt->execute();
    $fail_result = $fail_stmt->get_result()->fetch_assoc();

    $fail_count = $fail_result['fail_count'] ?? 0;

    /* ========================= */
    /* DECIDE STATUS */
    /* ========================= */

    if($fail_count == 0){
        $remark = "Promoted";
    }
    elseif($fail_count == 1){
        $remark = "KT Student";
    }
    else{
        $remark = "Detained";
    }

    $final_data[] = [
        "name"   => $student['full_name'],
        "remark" => $remark
    ];
}

/* ========================= */
/* RESPONSE */
/* ========================= */

echo json_encode([
    "status"=>true,
    "students"=>$final_data
]);