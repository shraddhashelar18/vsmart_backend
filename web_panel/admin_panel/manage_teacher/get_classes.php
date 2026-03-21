<?php
require_once("../../config.php");

$dept = $_GET['department'] ?? '';

/* GET ACTIVE SEMESTER */
$semQ = $conn->query("SELECT active_semester FROM settings LIMIT 1");
$semData = $semQ->fetch_assoc();
$activeSem = $semData['active_semester']; // EVEN / ODD

/* FETCH CLASSES */
$stmt = $conn->prepare("
SELECT class_name 
FROM classes 
WHERE department=?
");

$stmt->bind_param("s",$dept);
$stmt->execute();

$result = $stmt->get_result();

$classes = [];

while($row = $result->fetch_assoc()){

    $class = $row['class_name'];

    // extract semester number (IF6KA → 6)
    preg_match('/\d+/', $class, $matches);
    $semNumber = (int)$matches[0];

    // EVEN / ODD FILTER
    if($activeSem == "EVEN" && $semNumber % 2 == 0){
        $classes[] = $class;
    }

    if($activeSem == "ODD" && $semNumber % 2 != 0){
        $classes[] = $class;
    }
}

echo json_encode($classes);