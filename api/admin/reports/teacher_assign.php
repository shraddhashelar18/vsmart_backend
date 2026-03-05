```php
<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

$action = $_GET['action'] ?? '';

/* ---------------------------------------------------
1️⃣ GET CLASSES BY DEPARTMENT
API:
teacher_assignment_api.php?action=get_classes&department=IF
--------------------------------------------------- */

if ($action == "get_classes") {

    $department = $_GET['department'] ?? '';

    if (empty($department)) {
        echo json_encode([
            "status" => false,
            "message" => "Department required"
        ]);
        exit;
    }

    $stmt = $conn->prepare("SELECT class_name FROM classes WHERE department=?");
    $stmt->bind_param("s", $department);
    $stmt->execute();

    $result = $stmt->get_result();

    $classes = [];

    while ($row = $result->fetch_assoc()) {
        $classes[] = $row['class_name'];
    }

    echo json_encode([
        "status" => true,
        "classes" => $classes
    ]);

    exit;
}


/* ---------------------------------------------------
2️⃣ GET SUBJECTS BY CLASS
API:
teacher_assignment_api.php?action=get_subjects&class=IF6KA
--------------------------------------------------- */

if ($action == "get_subjects") {

    $class = $_GET['class'] ?? '';

    if (empty($class)) {
        echo json_encode([
            "status" => false,
            "message" => "Class required"
        ]);
        exit;
    }

    $stmt = $conn->prepare("SELECT subject_name FROM semester_subjects WHERE class=?");
    $stmt->bind_param("s", $class);
    $stmt->execute();

    $result = $stmt->get_result();

    $subjects = [];

    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row['subject_name'];
    }

    echo json_encode([
        "status" => true,
        "subjects" => $subjects
    ]);

    exit;
}


/* ---------------------------------------------------
3️⃣ GET ALLOCATED SUBJECTS (ALL DIVISIONS)
--------------------------------------------------- */

if ($action == "get_allocated") {

    $class = $_GET['class'] ?? '';

    if (empty($class)) {
        echo json_encode([
            "status" => false,
            "message" => "Class required"
        ]);
        exit;
    }

    // Example: IF6KA → IF6K
    $prefix = substr($class, 0, 4);

    $stmt = $conn->prepare("
        SELECT DISTINCT subject_name
        FROM semester_subjects
        WHERE class LIKE CONCAT(?, '%')
    ");

    $stmt->bind_param("s", $prefix);
    $stmt->execute();

    $result = $stmt->get_result();

    $subjects = [];

    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row['subject_name'];
    }

    echo json_encode([
        "status" => true,
        "allocated_subjects" => $subjects
    ]);

    exit;
}


/* ---------------------------------------------------
4️⃣ ASSIGN TEACHER + APPROVE
API:
POST teacher_assignment_api.php?action=assign_teacher
--------------------------------------------------- */

if ($action == "assign_teacher") {

    if ($currentRole != "admin") {
        echo json_encode([
            "status" => false,
            "message" => "Access denied"
        ]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'] ?? '';
    $department = $data['department'] ?? '';
    $class = $data['class'] ?? '';
    $subjects = $data['subjects'] ?? [];

    if (empty($user_id) || empty($department) || empty($class) || empty($subjects)) {

        echo json_encode([
            "status" => false,
            "message" => "All fields required"
        ]);
        exit;
    }

    /* ---------------------------------------------------
    1️⃣ INSERT INTO TEACHERS TABLE
    --------------------------------------------------- */

    $check = $conn->prepare("SELECT user_id FROM teachers WHERE user_id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows == 0) {

        $insertTeacher = $conn->prepare("
            INSERT INTO teachers (user_id, department)
            VALUES (?,?)
        ");

        $insertTeacher->bind_param("is", $user_id, $department);
        $insertTeacher->execute();
    }

    /* ---------------------------------------------------
    2️⃣ INSERT SUBJECT ASSIGNMENTS
    --------------------------------------------------- */

    foreach ($subjects as $subject) {

        $stmt = $conn->prepare("
        INSERT INTO teacher_assignments
        (user_id, department, class, subject)
        VALUES (?,?,?,?)
        ");

        $stmt->bind_param("isss", $user_id, $department, $class, $subject);
        $stmt->execute();
    }

    /* ---------------------------------------------------
    3️⃣ APPROVE USER
    --------------------------------------------------- */

    $update = $conn->prepare("UPDATE users SET status='approved' WHERE user_id=?");
    $update->bind_param("i", $user_id);
    $update->execute();

    echo json_encode([
        "status" => true,
        "message" => "Teacher assigned and approved successfully"
    ]);

    exit;
}


/* ---------------------------------------------------
INVALID ACTION
--------------------------------------------------- */

echo json_encode([
    "status" => false,
    "message" => "Invalid action"
]);
?>
```
