<?php
require_once("../config.php");
require_once("../cors.php");
header("Content-Type: application/json");

/* ===========================
   REQUEST METHOD VALIDATION
=========================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid request method"
    ]);
    exit;
}

/* ===========================
   START DATABASE TRANSACTION
=========================== */

$conn->begin_transaction();

$data = json_decode(file_get_contents("php://input"), true);
if(!$data){
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid JSON data"
    ]);
    exit;
}
/* ===========================
   COMMON REQUIRED VALIDATION
=========================== */

if (
    !isset($data['fullName']) ||
    !isset($data['email']) ||
    !isset($data['password']) ||
    !isset($data['selectedRole'])
) {
    $conn->rollback();
    echo json_encode(["status"=>false,"message"=>"Missing required fields"]);
    exit;
}

$fullName     = trim($data['fullName']);
$email = strtolower(trim($data['email']));
$password     = trim($data['password']);
$selectedRole = trim($data['selectedRole']);

/* ===========================
   FULL NAME VALIDATION
=========================== */

if ($fullName == "") {
    $conn->rollback();
    echo json_encode(["status"=>false,"message"=>"Full Name is required"]);
    exit;
}

if (preg_match('/[0-9]/', $fullName)) {
    $conn->rollback();
    echo json_encode(["status"=>false,"message"=>"Name cannot contain numbers"]);
    exit;
}

/* ===========================
   EMAIL VALIDATION
=========================== */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $conn->rollback();
    echo json_encode(["status"=>false,"message"=>"Invalid email format"]);
    exit;
}

/* ===========================
   PASSWORD VALIDATION
=========================== */

if (strlen($password) < 6) {
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>"Password must be at least 6 characters"
    ]);
    exit;
}

/* ===========================
   ROLE VALIDATION
=========================== */

$allowedRoles = ["student","teacher","parent"];

if (!in_array($selectedRole, $allowedRoles)) {
    $conn->rollback();
    echo json_encode(["status"=>false,"message"=>"Invalid role selected"]);
    exit;
}

/* ===========================
   CHECK DUPLICATE EMAIL
=========================== */

$checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$checkStmt->bind_param("s",$email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $conn->rollback();
    echo json_encode(["status"=>false,"message"=>"Email already registered"]);
    exit;
}

/* ===========================
   INSERT INTO USERS TABLE
=========================== */

$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
$status = "pending";
$first_login = 0;

$userStmt = $conn->prepare("
    INSERT INTO users (email,password,first_login,role,status)
    VALUES (?,?,?,?,?)
");

$userStmt->bind_param(
    "ssiss",
    $email,
    $hashedPassword,
    $first_login,
    $selectedRole,
    $status
);

if (!$userStmt->execute()) {
    $conn->rollback();
    echo json_encode(["status"=>false,"message"=>"User creation failed"]);
    exit;
}

$userId = $conn->insert_id;

/* ===========================
   HELPER FUNCTION
=========================== */

function validatePhone($phone){
    return preg_match('/^[0-9]{10}$/', $phone);
}

/* ======================================================
                    STUDENT REGISTRATION
====================================================== */

if ($selectedRole == "student") {

    $requiredFields = [
        "studentEnrollmentNo",
        "rollNo",
        "studentClass",
        "studentMobile",
        "parentMobile"
    ];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) == "") {
            $conn->rollback();
            echo json_encode(["status"=>false,"message"=>"All student fields are required"]);
            exit;
        }
    }

    $studentEnrollmentNo = trim($data['studentEnrollmentNo']);
    if(!preg_match('/^[0-9]{11}$/', $studentEnrollmentNo)){
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>"Enrollment number must be 11 digits"
    ]);
    exit;
}
    $rollNo = trim($data['rollNo']);
    if(!preg_match('/^[A-Za-z0-9]{10}$/', $rollNo)){
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid roll number format"
    ]);
    exit;
}
    $studentClass        = trim($data['studentClass']);
    $studentMobile       = trim($data['studentMobile']);
    $parentMobile        = trim($data['parentMobile']);

    if (!validatePhone($studentMobile) || !validatePhone($parentMobile)) {
        $conn->rollback();
        echo json_encode(["status"=>false,"message"=>"Enter valid 10-digit number"]);
        exit;
    }

    /* DUPLICATE STUDENT CHECK */

    $checkStudent = $conn->prepare("
    SELECT roll_no FROM students
    WHERE roll_no = ? OR enrollment_no = ?
    ");

    $checkStudent->bind_param("ss",$rollNo,$studentEnrollmentNo);
    $checkStudent->execute();
    $checkStudent->store_result();

    if($checkStudent->num_rows > 0){
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Student already registered"
        ]);
        exit;
    }

    $semester = (int) filter_var($studentClass, FILTER_SANITIZE_NUMBER_INT);
    $departmentCode = substr($studentClass, 0, 2);

    $stmt = $conn->prepare("
        INSERT INTO students 
        (roll_no,user_id,full_name,class,mobile_no,parent_mobile_no,enrollment_no,department_code,current_semester,status)
        VALUES (?,?,?,?,?,?,?,?,?,?)
    ");

    $studentStatus = "studying";
    $currentSemester = "SEM".$semester;

    $stmt->bind_param(
        "sissssssss",
        $rollNo,
        $userId,
        $fullName,
        $studentClass,
        $studentMobile,
        $parentMobile,
        $studentEnrollmentNo,
        $departmentCode,
        $currentSemester,
        $studentStatus
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

if ($selectedRole == "teacher") {

    if (
        !isset($data['employeeId']) ||
        !isset($data['teacherMobile'])
    ) {
        $conn->rollback();
        echo json_encode(["status"=>false,"message"=>"All teacher fields are required"]);
        exit;
    }

    $employeeId   = trim($data['employeeId']);
    /* EMPLOYEE ID VALIDATION */

if(!preg_match('/^[A-Za-z0-9]{10}$/', $employeeId)){
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>"Employee ID must be exactly 10 characters"
    ]);
    exit;
}
    $teacherMobile = trim($data['teacherMobile']);

    if (!validatePhone($teacherMobile)) {
        $conn->rollback();
        echo json_encode(["status"=>false,"message"=>"Enter valid 10-digit number"]);
        exit;
    }

    /* DUPLICATE TEACHER CHECK */

    $checkTeacher = $conn->prepare("
    SELECT employee_id FROM teachers WHERE employee_id = ?
    ");

    $checkTeacher->bind_param("s",$employeeId);
    $checkTeacher->execute();
    $checkTeacher->store_result();

    if($checkTeacher->num_rows > 0){
        $conn->rollback();
        echo json_encode([
            "status"=>false,
            "message"=>"Teacher already registered"
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO teachers
        (employee_id,user_id,full_name,mobile_no)
        VALUES (?,?,?,?)
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
            "message"=>"Teacher registration failed"
        ]);
        exit;
    }
}

/* ======================================================
                    PARENT REGISTRATION
====================================================== */

if ($selectedRole == "parent") {

    if (
        !isset($data['enrollmentNo']) ||
        !isset($data['parentOwnMobile'])
    ) {
        $conn->rollback();
        echo json_encode(["status"=>false,"message"=>"All parent fields are required"]);
        exit;
    }


    $enrollmentNo   = trim($data['enrollmentNo']);
    if(!preg_match('/^[0-9]{11}$/', $enrollmentNo)){
    $conn->rollback();
    echo json_encode([
        "status"=>false,
        "message"=>"Enrollment number must be 11 digits"
    ]);
    exit;
    }
    $parentOwnMobile = trim($data['parentOwnMobile']);

    if (!validatePhone($parentOwnMobile)) {
        $conn->rollback();
        echo json_encode(["status"=>false,"message"=>"Enter valid 10-digit number"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO parents
        (user_id,full_name,enrollment_no,mobile_no)
        VALUES (?,?,?,?)
    ");

    $stmt->bind_param(
        "isss",
        $userId,
        $fullName,
        $enrollmentNo,
        $parentOwnMobile
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
         COMMIT TRANSACTION
=========================== */

$conn->commit();

/* ===========================
         SUCCESS RESPONSE
=========================== */

echo json_encode([
    "status"=>true,
    "message"=>"Registration request sent to admin"
]);