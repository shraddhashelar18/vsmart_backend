<?php
//get_subjects.php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");
header("Content-Type: application/json");
if($currentRole != "teacher"){
    echo json_encode([
        "status" => false,
        "message" => "Access denied"
    ]);
    exit;
}
$class = $_GET['class'] ?? '';

$stmt = $conn->prepare("
SELECT subject
FROM teacher_assignments
WHERE user_id=? AND class=?
");

$stmt->bind_param("is", $currentUserId, $class);
$stmt->execute();

$result = $stmt->get_result();

$subjects = [];

while($row = $result->fetch_assoc()){
    $subjects[] = $row['subject'];
}

echo json_encode([
"status"=>true,
"subjects"=>$subjects
]);