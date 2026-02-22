<?php
require_once("../../config.php");
require_once("../../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if(empty($data['className'])){
    echo json_encode(["status"=>false,"message"=>"Class required"]);
    exit;
}

$className = $data['className'];

$stmt = $conn->prepare("
SELECT 
    p.full_name AS name,
    p.mobile_no AS phone,
    p.enrollment_no,
    s.full_name AS studentName,
    s.class AS studentClass
FROM parents p
JOIN students s ON p.enrollment_no = s.enrollment_no
WHERE s.class = ?
");

$stmt->bind_param("s",$className);
$stmt->execute();
$res = $stmt->get_result();

$parents = [];

while($row = $res->fetch_assoc()){
    $parents[] = [
        "name"=>$row['name'],
        "parentId"=>$row['phone'],
        "email"=>"", 
        "phone"=>$row['phone'],
        "children"=>[$row['enrollment_no']],
        "studentName"=>$row['studentName'],
        "studentId"=>$row['enrollment_no'],
        "studentClass"=>$row['studentClass']
    ];
}

echo json_encode([
    "status"=>true,
    "parents"=>$parents
]);
