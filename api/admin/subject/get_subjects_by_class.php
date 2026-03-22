<?php
//add teacher.php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");
$rawData = file_get_contents("php://input");

if (!$rawData || empty($rawData)) {
    $data = $_POST;   // fallback
} else {
    $data = json_decode($rawData, true);
}

if (!$data) {
    echo json_encode([
        "status" => false,
        "message" => "No data received"
    ]);
    exit;
}
$className = $data['class_name'] ?? null;

if(!$className){
    echo json_encode([
        "status"=>false,
        "message"=>"class_name required"
    ]);
    exit;
}

/* =========================
   DERIVE CLASS GROUP
========================= */

$department = substr($className,0,2);
$semester = substr($className,2,1);

$classGroup = $department.$semester."K";

/* =========================
   FETCH SUBJECTS
========================= */

$stmt = $conn->prepare("
SELECT 
    s.subject_name,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM teacher_assignments ta 
            WHERE LOWER(TRIM(ta.subject)) = LOWER(TRIM(s.subject_name))
            AND ta.class = ?
            AND ta.status = 'active'
        ) THEN 1
        ELSE 0
    END AS assigned
FROM semester_subjects s
WHERE s.class = ? AND s.semester = ?
ORDER BY s.subject_name
");
$stmt->bind_param("ssi", $className, $classGroup, $semester);
$stmt->execute();
$res = $stmt->get_result();

$subjects = [];

while ($row = $res->fetch_assoc()) {
    $subjects[] = [
        "name" => $row['subject_name'],
        "assigned" => intval($row['assigned'])
    ];
}

echo json_encode([
    "status" => true,
    "subjects" => $subjects
]);