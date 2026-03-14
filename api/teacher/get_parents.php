<?php
//get_parents.php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* Read JSON body */
$data = json_decode(file_get_contents("php://input"), true);

/* Accept both POST JSON and GET */
$class = $data['class'] ?? ($_GET['class'] ?? '');

if (!$class) {
    echo json_encode([
        "status" => false,
        "message" => "Class required"
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        p.user_id,
        s.full_name AS student_name,
        p.full_name AS parent_name,
        s.roll_no
    FROM students s
    JOIN parents p ON s.enrollment_no = p.enrollment_no
    WHERE s.class = ?
");

$stmt->bind_param("s", $class);
$stmt->execute();
$result = $stmt->get_result();

$parents = [];

while ($row = $result->fetch_assoc()) {
    $parents[] = $row;
}

echo json_encode([
    "status" => true,
    "parents" => $parents
]);
?>