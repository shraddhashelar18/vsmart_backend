<?php
require_once("../config.php");

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

/* ===========================
   COMMON REQUIRED VALIDATION
=========================== */

if (
    !isset($data['fullName']) ||
    !isset($data['email']) ||
    !isset($data['password']) ||
    !isset($data['selectedRole'])
) {
    echo json_encode(["status"=>false,"message"=>"Missing required fields"]);
    exit;
}

$fullName     = trim($data['fullName']);
$email        = trim($data['email']);
$password     = trim($data['password']);
$selectedRole = trim($data['selectedRole']);

/* ===========================
   FULL NAME VALIDATION
=========================== */

if ($fullName == "") {
    echo json_encode(["status"=>false,"message"=>"Full Name is required"]);
    exit;
}

if (preg_match('/[0-9]/', $fullName)) {
    echo json_encode(["status"=>false,"message"=>"Name cannot contain numbers"]);
    exit;
}

/* ===========================
   EMAIL VALIDATION
=========================== */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status"=>false,"message"=>"Invalid email format"]);
    exit;
}

/* ===========================
   PASSWORD VALIDATION
=========================== */

if (strlen($password) < 6) {
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
    echo json_encode(["status"=>false,"message"=>"Email already registered"]);
    exit;
}

/* ===========================
   INSERT INTO USERS TABLE
=========================== */

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
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
            echo json_encode(["status"=>false,"message"=>"All student fields are required"]);
            exit;
        }
    }

    $studentEnrollmentNo = trim($data['studentEnrollmentNo']);
    $rollNo              = trim($data['rollNo']);
    $studentClass        = trim($data['studentClass']);
    $studentMobile       = trim($data['studentMobile']);
    $parentMobile        = trim($data['parentMobile']);

    if (!validatePhone($studentMobile) || !validatePhone($parentMobile)) {
        echo json_encode(["status"=>false,"message"=>"Enter valid 10-digit number"]);
        exit;
    }

    /* Extract semester number from class like IF6KA */
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

    $stmt->execute();
}

/* ======================================================
                    TEACHER REGISTRATION
====================================================== */

if ($selectedRole == "teacher") {

    if (
        !isset($data['employeeId']) ||
        !isset($data['teacherMobile'])
    ) {
        echo json_encode(["status"=>false,"message"=>"All teacher fields are required"]);
        exit;
    }

    $employeeId   = trim($data['employeeId']);
    $teacherMobile = trim($data['teacherMobile']);

    if (!validatePhone($teacherMobile)) {
        echo json_encode(["status"=>false,"message"=>"Enter valid 10-digit number"]);
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

    $stmt->execute();
}

/* ======================================================
                    PARENT REGISTRATION
====================================================== */

if ($selectedRole == "parent") {

    if (
        !isset($data['enrollmentNo']) ||
        !isset($data['parentOwnMobile'])
    ) {
        echo json_encode(["status"=>false,"message"=>"All parent fields are required"]);
        exit;
    }

    $enrollmentNo   = trim($data['enrollmentNo']);
    $parentOwnMobile = trim($data['parentOwnMobile']);

    if (!validatePhone($parentOwnMobile)) {
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

    $stmt->execute();
}

/* ===========================
         SUCCESS RESPONSE
=========================== */

echo json_encode([
    "status"=>true,
    "message"=>"Registration request sent to admin"
]);