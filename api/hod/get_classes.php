<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['department'])) {
    echo json_encode(["status"=>false,"message"=>"Department required"]);
    exit;
}

$department = $data['department'];

$setting = $conn->query("SELECT active_semester FROM settings LIMIT 1");
$active = $setting->fetch_assoc()['active_semester'];

if($active=="EVEN"){
    $stmt = $conn->prepare("
        SELECT class_name FROM classes
        WHERE department=? AND semester IN (2,4,6)
    ");
}else{
    $stmt = $conn->prepare("
        SELECT class_name FROM classes
        WHERE department=? AND semester IN (1,3,5)
    ");
}

$stmt->bind_param("s",$department);
$stmt->execute();
$result = $stmt->get_result();

$classes=[];

while($row=$result->fetch_assoc()){
    $classes[]=$row['class_name'];
}

echo json_encode(["status"=>true,"classes"=>$classes]);