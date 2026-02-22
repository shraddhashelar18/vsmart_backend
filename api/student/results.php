<?php
require_once "../config.php";
require_once "../api_guard.php";
header("Content-Type: application/json");

if(!isset($_POST['student_id'])){
    echo json_encode([
        "status"=>false,
        "message"=>"student_id is required"
    ]);
    exit;
}

$student_id = intval($_POST['student_id']);

$stmt = $conn->prepare("
    SELECT exam_type, subject, obtained_marks, total_marks
    FROM marks
    WHERE student_id = ?
    ORDER BY exam_type
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$data = [];
$res = $stmt->get_result();

while ($r = $res->fetch_assoc()) {

    $data[$r['exam_type']][] = [
        "subject"   => $r['subject'],
        "obtained_marks" => $r['obtained_marks'],
        "total_marks"    => $r['total_marks']
    ];
}

echo json_encode([
    "status"=>true,
    "results"=>$data
]);
?>