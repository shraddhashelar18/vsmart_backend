<?php
require_once("../config.php");

$message = "";

/* ===========================
   FORM SUBMIT
=========================== */

if($_SERVER['REQUEST_METHOD']=="POST"){

$fullName = trim($_POST['fullName'] ?? "");
$email = strtolower(trim($_POST['email'] ?? ""));
$password = trim($_POST['password'] ?? "");
$role = trim($_POST['role'] ?? "");

/* VALIDATION */

if($fullName=="" || $email=="" || $password=="" || $role==""){
$message = "All fields required";
}

elseif(preg_match('/[0-9]/',$fullName)){
$message = "Name cannot contain numbers";
}

elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
$message = "Invalid email";
}

elseif(strlen($password) < 6){
$message = "Password must be at least 6 characters";
}

else{

/* CHECK REGISTRATION OPEN */

$row = $conn->query("SELECT registration_open FROM settings WHERE id=1")->fetch_assoc();

if($row['registration_open']==0){
$message = "Registration closed by admin";
}else{

/* CHECK EMAIL */

$check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$check->bind_param("s",$email);
$check->execute();
$check->store_result();

if($check->num_rows > 0){
$message = "Email already exists";
}else{

/* CREATE USER */

$hashedPassword = password_hash($password,PASSWORD_BCRYPT);
$status = "pending";

$stmt = $conn->prepare("
INSERT INTO users (email,password,role,status)
VALUES (?,?,?,?)
");

$stmt->bind_param("ssss",$email,$hashedPassword,$role,$status);

if($stmt->execute()){

$userId = $conn->insert_id;

/* STUDENT */

if($role=="student"){

$stmt = $conn->prepare("
INSERT INTO students
(user_id,full_name,roll_no,class,mobile_no,parent_mobile_no,enrollment_no)
VALUES (?,?,?,?,?,?,?)
");

$stmt->bind_param("issssss",
$userId,
$fullName,
$_POST['rollNo'],
$_POST['studentClass'],
$_POST['studentMobile'],
$_POST['parentMobile'],
$_POST['studentEnrollmentNo']
);

$stmt->execute();
}

/* TEACHER */

if($role=="teacher"){

$stmt = $conn->prepare("
INSERT INTO teachers
(user_id,full_name,employee_id,mobile_no)
VALUES (?,?,?,?)
");

$stmt->bind_param("isss",
$userId,
$fullName,
$_POST['employeeId'],
$_POST['teacherMobile']
);

$stmt->execute();
}

/* PARENT */

if($role=="parent"){

$stmt = $conn->prepare("
INSERT INTO parents
(user_id,full_name,enrollment_no,mobile_no)
VALUES (?,?,?,?)
");

$stmt->bind_param("isss",
$userId,
$fullName,
$_POST['enrollmentNo'],
$_POST['parentOwnMobile']
);

$stmt->execute();
}

$message = "✅ Registration Successful!";
}else{
$message = "❌ User creation failed";
}

}
}
}
}
?>
