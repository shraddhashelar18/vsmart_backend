<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");
require_once("../promotion_helper.php");

/* SET DEPARTMENT */
$department = "IF";

/* ================= GET SETTINGS ================= */

$settingsQuery = $conn->query("SELECT active_semester, atkt_limit FROM settings LIMIT 1");

if (!$settingsQuery) {
    die("Settings Query Error: " . $conn->error);
}

$settings = $settingsQuery->fetch_assoc();

if (!$settings) {
    die("No settings found");
}

$activeSemester = $settings['active_semester'];
$atktLimit = (int)$settings['atkt_limit'];

/* ================= GET STUDENTS ================= */

if ($activeSemester == "EVEN") {

    $stmt = $conn->prepare("
        SELECT user_id
        FROM students
        WHERE class COLLATE utf8mb4_general_ci LIKE CONCAT(?, '%')
        AND CAST(SUBSTRING(class,3,1) AS UNSIGNED) IN (2,4,6)
    ");

} else {

    $stmt = $conn->prepare("
        SELECT user_id
        FROM students
        WHERE class COLLATE utf8mb4_general_ci LIKE CONCAT(?, '%')
        AND CAST(SUBSTRING(class,3,1) AS UNSIGNED) IN (1,3,5)
    );
}

if (!$stmt) {
    die("Student Query Prepare Error: " . $conn->error);
}

$stmt->bind_param("s", $department);

if (!$stmt->execute()) {
    die("Student Query Execute Error: " . $stmt->error);
}

/* ✅ SERVER SAFE FETCH */
$stmt->bind_result($user_id);

/* ================= PROMOTION COUNT ================= */

$totalStudents = 0;
$promoted = 0;
$promotedWithBacklog = 0;
$detained = 0;

while ($stmt->fetch()) {

    $promotion = calculatePromotion($conn, $user_id, $atktLimit);

    if (!$promotion || !isset($promotion['status'])) {
        continue;
    }

    $totalStudents++;

    if ($promotion['status'] == "PROMOTED") {
        $promoted++;
    } elseif ($promotion['status'] == "PROMOTED_WITH_ATKT") {
        $promotedWithBacklog++;
    } elseif ($promotion['status'] == "DETAINED") {
        $detained++;
    }
}

$stmt->close();

/* ================= COUNT TEACHERS ================= */

$teacherStmt = $conn->prepare("
    SELECT COUNT(DISTINCT ta.user_id)
    FROM teacher_assignments ta
    JOIN teachers t ON t.user_id = ta.user_id
    WHERE ta.department = ?
");

if (!$teacherStmt) {
    die("Teacher Query Prepare Error: " . $conn->error);
}

$teacherStmt->bind_param("s", $department);

if (!$teacherStmt->execute()) {
    die("Teacher Query Execute Error: " . $teacherStmt->error);
}

$teacherStmt->bind_result($totalTeachers);
$teacherStmt->fetch();
$teacherStmt->close();

$totalTeachers = $totalTeachers ?? 0;

?>

<!DOCTYPE html>
<html>
<head>
<title>HOD Dashboard</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="header">
☰ HOD Dashboard
</div>

<div class="dashboard-cards">

<div class="card">
<h3>Total Students</h3>
<p><?php echo $totalStudents; ?></p>
</div>

<div class="card">
<h3>Total Teachers</h3>
<p><?php echo $totalTeachers; ?></p>
</div>

<div class="card">
<h3>Promoted</h3>
<p><?php echo $promoted; ?></p>
</div>

<div class="card">
<h3>Promoted With ATKT</h3>
<p><?php echo $promotedWithBacklog; ?></p>
</div>

<div class="card">
<h3>Detained</h3>
<p><?php echo $detained; ?></p>
</div>

</div>

<div class="section-title">
Quick Actions
</div>

<div class="action-buttons">

<a class="action-btn" href="student_by_class.php">View Students</a>
<a class="action-btn" href="teacher.php">View Teachers</a>
<a class="action-btn" href="promoted_classes.php">View Promoted List</a>
<a class="action-btn" href="atkt_classes.php">View ATKT List</a>
<a class="action-btn" href="detained_classes.php">View Detained List</a>

</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">

<a href="dashboard.php" class="nav-item active">
<div class="icon">📊</div>
<div class="label">Dashboard</div>
</a>

<a href="settings.php" class="nav-item">
<div class="icon">⚙️</div>
<div class="label">Settings</div>
</a>

</div>

</body>
</html>