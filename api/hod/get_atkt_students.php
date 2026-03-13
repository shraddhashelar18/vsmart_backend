<?php
require_once("../config.php");
require_once("../cors.php");
require_once("../api_guard.php");
require_once("../promotion_helper.php"); 
header("Content-Type: application/json");

if ($currentRole != 'hod' && $currentRole != 'principal') {
    echo json_encode([
        "status" => false,      
        "message" => "Access Denied"  
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['class'])) {
    echo json_encode(["status"=>false,"message"=>"Class is required"]);
    exit;
}

$class = $data['class'];

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = $setting->fetch_assoc()['atkt_limit'];

$stmt = $conn->prepare("SELECT user_id, full_name FROM students WHERE class=?");
$stmt->bind_param("s",$class);
$stmt->execute();
$result = $stmt->get_result();

$students = [];

while($row = $result->fetch_assoc()){

    $promotion = calculatePromotion($conn,$row['user_id'],$atktLimit);

    if($promotion['status']=="PROMOTED_WITH_ATKT"){

        $students[] = [
            "name"=>$row['full_name'],
            "backlogCount"=>$promotion['backlogCount'],
            "promotionStatus"=>$promotion['status'],
            "ktSubjects"=>$promotion['ktSubjects']
        ];
    }
}

echo json_encode(["status"=>true,"students"=>$students]);