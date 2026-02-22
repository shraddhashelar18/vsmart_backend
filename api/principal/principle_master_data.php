<?php
require_once "../config.php";
header("Content-Type: application/json");

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// If year is passed, return semesters only
if(isset($data['year'])){

    $year = $data['year'];
    $semesters = [];

    if($year == "FY"){
        $semesters = ["SEM1","SEM2"];
    }
    elseif($year == "SY"){
        $semesters = ["SEM3","SEM4"];
    }
    elseif($year == "TY"){
        $semesters = ["SEM5","SEM6"];
    }

    echo json_encode([
        "status" => true,
        "semesters" => $semesters
    ]);
    exit();
}


// Otherwise return departments + years
$departments = [];
$result = $conn->query("SELECT DISTINCT department_code FROM students");

while($row = $result->fetch_assoc()){
    $departments[] = $row['department_code'];
}

// Static years
$years = ["FY","SY","TY"];

echo json_encode([
    "status" => true,
    "departments" => $departments,
    "years" => $years
]);
?>