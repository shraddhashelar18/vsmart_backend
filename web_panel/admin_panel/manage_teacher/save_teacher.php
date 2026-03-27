<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../../config.php");
session_start();

/* =========================
   GET FORM DATA
========================= */

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$department = $_POST['department'] ?? '';

if(empty($department)){
    die("Department missing");
}

$classes = $_POST['classes'] ?? [];
$subjects = $_POST['subjects'] ?? [];

/* =========================
   VALIDATION
========================= */

if(!$name || !$email || !$password){
    die("All fields required");
}

/* =========================
   CHECK EMAIL EXISTS
========================= */

$check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$check->bind_param("s",$email);
$check->execute();
$res = $check->get_result();

if($res->num_rows > 0){
    die("Email already exists");
}

/* =========================
   INSERT INTO USERS
========================= */

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
INSERT INTO users (email,password,role,status,created_at)
VALUES (?,?, 'teacher','approved',NOW())
");

$stmt->bind_param("ss",$email,$hashedPassword);
$stmt->execute();

$user_id = $conn->insert_id;

/* =========================
   GENERATE EMPLOYEE ID
========================= */

$emp_id = "vp".$user_id;

/* =========================
   INSERT INTO TEACHERS
========================= */

$stmt = $conn->prepare("
INSERT INTO teachers (employee_id,user_id,full_name)
VALUES (?,?,?)
");

$stmt->bind_param("sis",$emp_id,$user_id,$name);
$stmt->execute();

/* =========================
   INSERT CLASS-WISE SUBJECT ASSIGNMENTS
========================= */

if(isset($subjects) && is_array($subjects)){

    foreach($subjects as $class => $subjectList){

        foreach($subjectList as $sub){

            $stmt = $conn->prepare("
            INSERT INTO teacher_assignments
            (user_id, department, class, subject, status)
            VALUES (?,?,?,?, 'active')
            ");

            $stmt->bind_param("isss",$user_id,$department,$class,$sub);
            $stmt->execute();
        }
    }
}

/* =========================
   REDIRECT
========================= */

header("Location: manage_teachers.php?department=".$department."&success=1");
exit();
?>