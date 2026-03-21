<?php
require_once("../config.php");

$type = $_POST['type'] ?? '';

if($type=="semester"){

    $q = $conn->query("
    UPDATE settings 
    SET active_semester = IF(active_semester='ODD','EVEN','ODD') 
    WHERE id=1
    ");

    if(!$q){
        echo "error";
    } else {
        echo "success";
    }
}

if($type=="registration"){

    $q = $conn->query("
    UPDATE settings 
    SET registration_open = IF(registration_open=1,0,1) 
    WHERE id=1
    ");

    if(!$q){
        echo "error";
    } else {
        echo "success";
    }
}

if($type=="attendance"){

    $q = $conn->query("
    UPDATE settings 
    SET attendance_locked = IF(attendance_locked=1,0,1) 
    WHERE id=1
    ");

    if(!$q){
        echo "error";
    } else {
        echo "success";
    }
}
?>