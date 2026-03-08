<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

/* ================= INPUT ================= */

$data = json_decode(file_get_contents("php://input"), true);

$class = $data['class'] ?? '';
$examType = $data['exam_type'] ?? '';

if (!$class || !$examType) {
    echo json_encode([
        "status" => false,
        "message" => "Class and exam type required"
    ]);
    exit;
}

/* ================= CHECK RESULT PUBLISHED ================= */

$column = "";

if ($examType == "CT1") {
    $column = "ct1_published";
}

if ($examType == "CT2") {
    $column = "ct2_published";
}

if ($column != "") {

    $check = $conn->prepare("
    SELECT $column FROM classes WHERE class_name=?
    ");

    $check->bind_param("s", $class);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if (!$res || $res[$column] == 0) {
        echo json_encode([
            "status" => false,
            "message" => "Result not published yet"
        ]);
        exit;
    }
}

/* ================= FETCH MARKS ================= */

$stmt = $conn->prepare("
SELECT 
    s.user_id,
    s.full_name,
    m.subject,
    m.obtained_marks,
    m.total_marks,
    m.exam_type
FROM marks m
JOIN students s ON m.student_id = s.user_id
WHERE m.class=? AND m.exam_type=? AND m.status='published'
ORDER BY s.full_name
");

$stmt->bind_param("ss", $class, $examType);
$stmt->execute();
$result = $stmt->get_result();

$marks = [];

while ($row = $result->fetch_assoc()) {

    $marks[] = [
        "student_id" => $row['user_id'],
        "name" => $row['full_name'],
        "subject" => $row['subject'],
        "obtained_marks" => $row['obtained_marks'],
        "total_marks" => $row['total_marks'],
        "exam_type" => $row['exam_type']
    ];
}

/* ================= RESPONSE ================= */

echo json_encode([
    "status" => true,
    "class" => $class,
    "exam_type" => $examType,
    "marks" => $marks
]);
?>