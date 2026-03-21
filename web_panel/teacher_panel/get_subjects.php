<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");
session_start();

$teacher_id = $_SESSION['teacher_id'] ?? '';
$class = $_GET['class'] ?? '';

if(empty($class) || empty($teacher_id)){
    echo json_encode([]);
    exit;
}

/* Fetch subjects based on class + teacher */
$stmt = $conn->prepare("
SELECT DISTINCT subject 
FROM teacher_assignments 
WHERE user_id = ? AND class = ?
");

$stmt->bind_param("is", $teacher_id, $class);
$stmt->execute();

$result = $stmt->get_result();

$subjects = [];

while($row = $result->fetch_assoc()){
    $subjects[] = $row['subject'];
}

echo json_encode($subjects);