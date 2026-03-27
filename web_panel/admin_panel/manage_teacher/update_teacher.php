<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("../../config.php");

$user_id = $_POST['user_id'] ?? '';
$department = $_POST['department'] ?? '';

if(!$user_id){
    die("User ID missing");
}

/* DELETE OLD */
$conn->query("
DELETE FROM teacher_assignments
WHERE user_id='$user_id'
");

/* INSERT NEW */
if(isset($_POST['subjects'])){

    foreach($_POST['subjects'] as $class => $subs){

        // extract department from class (IF, CO, EJ)
        $dept = substr($class, 0, 2);

        foreach($subs as $sub){

            $conn->query("
            INSERT INTO teacher_assignments
            (user_id, department, class, subject, status)
            VALUES
            ('$user_id', '$dept', '$class', '$sub', 'active')
            ");
        }
    }
}

/* REDIRECT */
header("Location: manage_teachers.php?department=".$department."&updated=1");
exit;
?>