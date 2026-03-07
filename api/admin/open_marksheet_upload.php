<?php
require_once("../config.php");
require_once("../api_guard.php");
require_once("../cors.php");

header("Content-Type: application/json");

if($currentRole!="admin"){
    echo json_encode(["status"=>false,"message"=>"Access Denied"]);
    exit;
}

$conn->query("UPDATE settings SET allow_marksheet_upload=1");

echo json_encode([
    "status"=>true,
    "message"=>"Marksheet upload enabled"
]);
