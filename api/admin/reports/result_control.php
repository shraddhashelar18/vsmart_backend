<?php
require_once("../../config.php");
require_once("../../api_guard.php");
require_once("../../cors.php");

header("Content-Type: application/json");

/* ================= ROLE CHECK ================= */

if ($currentRole != "admin") {
    echo json_encode([
        "status" => false,
        "message" => "Access Denied"
    ]);
    exit;
}

/* ================= INPUT ================= */
$data = json_decode(file_get_contents("php://input"), true);

// 🔥 IMPORTANT FIX
if (!$data) {
    $data = $_POST; // fallback
}
/* ================= GET SETTINGS ================= */

if (isset($data['action']) && $data['action'] == "get_settings") {

    $res = $conn->query("SELECT * FROM settings LIMIT 1");
    $row = $res->fetch_assoc();

    echo json_encode([
        "status" => true,
        "data" => $row
    ]);
    exit;
}
/* ================= GET CLASSES (WITH CYCLE FILTER) ================= */

if (isset($data['action']) && $data['action'] == "classes") {

    $dept = $data['department'] ?? '';

    // 🔥 Get active cycle from settings
    $setting = $conn->query("SELECT active_cycle FROM settings LIMIT 1");
    $row = $setting->fetch_assoc();
    $cycle = $row['active_cycle'] ?? 'EVEN'; // default safety

    // 🔥 Get all classes for department
    $result = $conn->query("
        SELECT class_name 
        FROM classes 
        WHERE class_name LIKE '$dept%' 
        ORDER BY class_name
    ");

    $classes = [];

    while ($c = $result->fetch_assoc()) {

        $className = $c['class_name'];

        // 🔥 Extract semester number (IF6KA → 6)
        preg_match('/\d+/', $className, $match);
        $sem = isset($match[0]) ? (int) $match[0] : 0;

        // 🔥 Apply EVEN / ODD filter
        if (
            ($cycle == "EVEN" && $sem % 2 == 0) ||
            ($cycle == "ODD" && $sem % 2 != 0)
        ) {
            $classes[] = $className;
        }
    }

    echo json_encode([
        "status" => true,
        "cycle" => $cycle,
        "classes" => $classes
    ]);
    exit;
}
/* ================= UPDATE SETTINGS ================= */
if (isset($data['action']) && $data['action'] == "student_upload_status") {

    $class = $data['class'] ?? '';

    if (!$class) {
        echo json_encode([
            "status" => false,
            "message" => "Class missing"
        ]);
        exit;
    }

    $result = $conn->query("
        SELECT full_name, marks_uploaded
        FROM students
        WHERE class = '$class'
    ");

    if (!$result) {
        echo json_encode([
            "status" => false,
            "message" => "Query failed"
        ]);
        exit;
    }

    $students = [];

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    echo json_encode([
        "status" => true,
        "students" => $students
    ]);
    exit;
}
if (isset($data['action']) && $data['action'] == "update_settings") {

    $allow = isset($data['allow_upload']) ? (int) $data['allow_upload'] : 0;
    $publish = isset($data['publish_result']) ? (int) $data['publish_result'] : 0;

    $stmt = $conn->prepare("
        UPDATE settings
        SET allow_marksheet_upload = ?, final_published = ?
        WHERE id = 1
    ");

    $stmt->bind_param("ii", $allow, $publish);

    if ($stmt->execute()) {

        // 🔥 ONLY TRIGGER (NO LOGIC CHANGE)
        if ($publish == 1) {
            require_once("../../helpers/promotion_helper.php");
            runPromotion($conn);
        }

        echo json_encode([
            "status" => true,
            "message" => "Updated successfully"
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Update failed"
        ]);
    }

    exit;
}
echo json_encode([
    "status" => false,
    "message" => "Invalid action"
]);
exit;