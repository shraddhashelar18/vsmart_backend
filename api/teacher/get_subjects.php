<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

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