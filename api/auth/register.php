<?php
require_once("../config.php");
require_once("../cors.php");

header("Content-Type: application/json");

/* ===========================
   REQUEST METHOD CHECK
=========================== */

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode([
        "status"=>false,
        "message"=>"Only POST method allowed"
    ]);
    exit;
}

/* ===========================
   START TRANSACTION
=========================== */

$conn->begin_transaction();

$data = json_decode(file_get_contents("php://input"),true);

if(!$data){
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid JSON"
    ]);
    exit;
}

/* ===========================
   COMMON FIELDS
=========================== */

$fullName = trim($data['fullName'] ?? "");
$email = strtolower(trim($data['email'] ?? ""));
$password = trim($data['password'] ?? "");
$role = trim($data['selectedRole'] ?? "");

if($fullName=="" || $email=="" || $password=="" || $role==""){
    echo json_encode([
        "status"=>false,
        "message"=>"Missing required fields"
    ]);
    exit;
}

/* ===========================
   NAME VALIDATION
=========================== */

if(preg_match('/[0-9]/',$fullName)){
    echo json_encode([
        "status"=>false,
        "message"=>"Name cannot contain numbers"
    ]);
    exit;
}

/* ===========================
   EMAIL VALIDATION
=========================== */

if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid email"
    ]);
    exit;
}

/* ===========================
   PASSWORD VALIDATION
=========================== */

if(strlen($password) < 6){
    echo json_encode([
        "status"=>false,
        "message"=>"Password must be minimum 6 characters"
    ]);
    exit;
}

/* ===========================
   ROLE VALIDATION
=========================== */

$allowedRoles = ["student","teacher","parent"];

if(!in_array($role,$allowedRoles)){
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid role"
    ]);
    exit;
}

/* ===========================
   DUPLICATE EMAIL CHECK
=========================== */

$checkEmail = $conn->prepare("SELECT user_id FROM users WHERE email=?");
$checkEmail->bind_param("s",$email);
$checkEmail->execute();
$checkEmail->store_result();

if($checkEmail->num_rows > 0){
    echo json_encode([
        "status"=>false,
        "message"=>"Email already registered"
    ]);
    exit;
}

/* ===========================
   CREATE USER
=========================== */

$hashedPassword = password_hash($password,PASSWORD_BCRYPT);

$first_login = 0;
$status = "pending";

$userStmt = $conn->prepare("
INSERT INTO users (email,password,first_login,role,status)
VALUES (?,?,?,?,?)
");

$userStmt->bind_param("ssiss",
$email,
$hashedPassword,
$first_login,
$role,
$status
);

if(!$userStmt->execute()){
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>"User creation failed"
    ]);
    exit;
}

$userId = $conn->insert_id;

/* ===========================
   PHONE VALIDATOR
=========================== */

function validPhone($phone){
    return preg_match('/^[0-9]{10}$/',$phone);
}

########################################################
# STUDENT REGISTRATION
########################################################

if($role == "student"){

    $enroll = trim($data['studentEnrollmentNo'] ?? "");
    $roll = trim($data['rollNo'] ?? "");
    $class = trim($data['studentClass'] ?? "");
    $mobile = trim($data['studentMobile'] ?? "");
    $parentMobile = trim($data['parentMobile'] ?? "");

    if($enroll=="" || $roll=="" || $class=="" || $mobile=="" || $parentMobile==""){
        echo json_encode([
            "status"=>false,
            "message"=>"Student fields missing"
        ]);
        exit;
    }

    if(!preg_match('/^[0-9]{11}$/',$enroll)){
        echo json_encode([
            "status"=>false,
            "message"=>"Enrollment must be 11 digits"
        ]);
        exit;
    }

    if(!validPhone($mobile) || !validPhone($parentMobile)){
        echo json_encode([
            "status"=>false,
            "message"=>"Invalid mobile number"
        ]);
        exit;
    }

    $stmt = $conn->prepare("
    INSERT INTO students
    (roll_no,user_id,full_name,class,mobile_no,parent_mobile_no,enrollment_no)
    VALUES (?,?,?,?,?,?,?)
    ");

    $stmt->bind_param("sisssss",
    $roll,
    $userId,
    $fullName,
    $class,
    $mobile,
    $parentMobile,
    $enroll
    );

    if(!$stmt->execute()){
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Student registration failed"
        ]);
        exit;
    }
}

/* ======================================================
                    TEACHER REGISTRATION
====================================================== */

if ($role == "teacher") {

    if (!isset($data['employeeId']) || !isset($data['teacherMobile'])) {
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Employee ID and Teacher Mobile are required"
        ]);
        exit;
    }

    $employeeId = trim($data['employeeId']);
    $teacherMobile = trim($data['teacherMobile']);

    /* EMPLOYEE ID VALIDATION */

    if(!preg_match('/^[A-Za-z0-9]{10}$/', $employeeId)){
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Employee ID must be exactly 10 characters"
        ]);
        exit;
    }

    /* MOBILE VALIDATION */

    if(!preg_match('/^[0-9]{10}$/',$teacherMobile)){
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Invalid mobile number"
        ]);
        exit;
    }

    /* CHECK DUPLICATE EMPLOYEE */

    $checkTeacher = $conn->prepare(
        "SELECT employee_id FROM teachers WHERE employee_id=?"
    );

    $checkTeacher->bind_param("s",$employeeId);
    $checkTeacher->execute();
    $checkTeacher->store_result();

    if($checkTeacher->num_rows > 0){
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Teacher already exists"
        ]);
        exit;
    }

    /* INSERT TEACHER */

    $stmt = $conn->prepare("
        INSERT INTO teachers
        (employee_id, user_id, full_name, mobile_no)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "siss",
        $employeeId,
        $userId,
        $fullName,
        $teacherMobile
    );

    if(!$stmt->execute()){
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Teacher registration failed",
            "error"=>$stmt->error
        ]);
        exit;
    }
}

########################################################
# PARENT REGISTRATION
########################################################

if($role == "parent"){

    $enroll = trim($data['enrollmentNo'] ?? "");
    $mobile = trim($data['parentOwnMobile'] ?? "");

    if($enroll=="" || $mobile==""){
        echo json_encode([
            "status"=>false,
            "message"=>"Parent fields missing"
        ]);
        exit;
    }

    if(!preg_match('/^[0-9]{11}$/',$enroll)){
        echo json_encode([
            "status"=>false,
            "message"=>"Enrollment must be 11 digits"
        ]);
        exit;
    }

    if(!validPhone($mobile)){
        echo json_encode([
            "status"=>false,
            "message"=>"Invalid mobile number"
        ]);
        exit;
    }

    $stmt = $conn->prepare("
    INSERT INTO parents
    (user_id,full_name,enrollment_no,mobile_no)
    VALUES (?,?,?,?)
    ");

    $stmt->bind_param("isss",
    $userId,
    $fullName,
    $enroll,
    $mobile
    );

    if(!$stmt->execute()){
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Parent registration failed"
        ]);
        exit;
    }
}

/* ===========================
   COMMIT
=========================== */

$conn->commit();

echo json_encode([
    "status"=>true,
    "message"=>"Registration request sent to admin"
]);

?>