<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../helpers/promotion_helper.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$className = $data['className'];

$setting = $conn->query("SELECT atkt_limit FROM settings LIMIT 1");
$atktLimit = $setting->fetch_assoc()['atkt_limit'];

$stmt = $conn->prepare("
SELECT enrollment_no, full_name
FROM students
WHERE class_name = ?
");

$stmt->bind_param("s",$className);
$stmt->execute();
$res = $stmt->get_result();

$students = [];

while($s = $res->fetch_assoc()){

    $result = calculatePromotion($conn, $s['enrollment_no'], $atktLimit);

    if($result['status'] == "PROMOTED_WITH_ATKT"){

        $students[] = [
            "name"=>$s['full_name'],
            "backlogCount"=>$result['backlogCount'],
            "promotionStatus"=>$result['status'],
            "ktSubjects"=>$result['ktSubjects']
        ];
    }
}

echo json_encode(["status"=>true,"students"=>$students]);