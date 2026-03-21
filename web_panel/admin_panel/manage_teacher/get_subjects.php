<?php
require_once("../../config.php");

$class = $_GET['class'] ?? '';

// try both formats
$baseClass = substr($class, 0, -1);

$s = $conn->prepare("
SELECT subject_name 
FROM semester_subjects 
WHERE class=? OR class=?
");

$s->bind_param("ss", $class, $baseClass);
$s->execute();

$res = $s->get_result();

$subjects = [];

while($row = $res->fetch_assoc()){
    $subjects[] = $row['subject_name'];
}

// 2️⃣ GET ALL ASSIGNED SUBJECTS (ANY TEACHER)
$a = $conn->prepare("
SELECT subject 
FROM teacher_assignments 
WHERE class=? AND status='active'
");

$a->bind_param("s", $class);
$a->execute();

$res2 = $a->get_result();

$assigned = [];

while($row = $res2->fetch_assoc()){
    $assigned[] = $row['subject'];
}

// 3️⃣ RETURN BOTH
echo json_encode([
    "subjects" => $subjects,
    "assigned" => $assigned
]);