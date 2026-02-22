<?php
require_once "../config.php";

header("Content-Type: application/json");

// Read JSON body (POST)
$data = json_decode(file_get_contents("php://input"), true);

$student_id = $data['student_id'] ?? '';

if ($student_id === '') {
    echo json_encode([
        "status" => false,
        "message" => "Student ID required"
    ]);
    exit;
}

$sql = "
    SELECT 
        subject,
        SUM(obtained_marks) AS obtained,
        SUM(total_marks) AS total
    FROM marks
    WHERE student_id = ?
    GROUP BY subject
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {

    if ($row['total'] > 0) {
        $percentage = ($row['obtained'] / $row['total']) * 100;
    } else {
        $percentage = 0;
    }

    if ($percentage >= 75) {
        $grade = "Excellent";
    } elseif ($percentage >= 60) {
        $grade = "Good";
    } elseif ($percentage >= 40) {
        $grade = "Average";
    } else {
        $grade = "Poor";
    }

    $data[] = [
        "subject"     => $row['subject'],
        "marks"       => $row['obtained'] . "/" . $row['total'],
        "percentage"  => round($percentage, 2),
        "grade"       => $grade
    ];
}

echo json_encode([
    "status" => true,
    "subjects" => $data
]);