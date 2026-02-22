<?php
require_once("../config.php");
require_once("../api_guard.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

/* Validate Input */
if(empty($data['semester'])){
    echo json_encode([
        "status"=>false,
        "message"=>"Semester value required"
    ]);
    exit;
}

$semester = strtoupper($data['semester']);

/* Allow only ODD or EVEN */
if($semester !== "ODD" && $semester !== "EVEN"){
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid semester value"
    ]);
    exit;
}

/* Check if settings row exists */
$check = $conn->query("SELECT id FROM settings LIMIT 1");

if($check->num_rows == 0){

    /* Insert if not exists */
    $stmt = $conn->prepare("
        INSERT INTO settings (active_semester)
        VALUES (?)
    ");

    $stmt->bind_param("s",$semester);
    $stmt->execute();

}else{

    /* Update existing */
    $stmt = $conn->prepare("
        UPDATE settings
        SET active_semester=?
        LIMIT 1
    ");

    $stmt->bind_param("s",$semester);
    $stmt->execute();
}

echo json_encode([
    "status"=>true,
    "activeSemester"=>$semester,
    "message"=>"Semester updated successfully"
]);