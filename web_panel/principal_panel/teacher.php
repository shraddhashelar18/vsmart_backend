<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../config.php");

/* ================= GET DEPARTMENT ================= */
$department = $_GET['department'] ?? 'IF';

/* ================= FETCH TEACHERS ================= */
$stmt = $conn->prepare("
SELECT DISTINCT
    t.user_id,
    t.full_name,
    u.email
FROM teachers t
JOIN users u ON t.user_id = u.user_id
LEFT JOIN teacher_assignments ta ON t.user_id = ta.user_id
WHERE ta.department = ?
AND ta.status = 'active'
");

$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teachers</title>

    <!-- ✅ CSS PATH (correct for your structure) -->
    <link rel="stylesheet" href="css/style.css?v=2">
</head>

<body>

<!-- ✅ HEADER -->
<div class="header">
    <span onclick="history.back()" class="back">←</span>
    <?php echo htmlspecialchars($department); ?> - Teachers
</div>

<!-- ✅ TEACHER LIST -->
<div class="teacher-list">

<?php if($result->num_rows > 0){ ?>

    <?php while($row = $result->fetch_assoc()) { ?>

    <a href="teacher_details.php?id=<?php echo $row['user_id']; ?>" class="link">

        <div class="teacher-card">

            <div class="teacher-info">

                <div class="teacher-avatar">👤</div>

                <div>
                    <div class="teacher-name">
                        <?php echo htmlspecialchars($row['full_name']); ?>
                    </div>

                    <div class="teacher-id">
                        <?php echo htmlspecialchars($row['email']); ?>
                    </div>
                </div>

            </div>

        </div>

    </a>

    <?php } ?>

<?php } else { ?>

    <div class="empty">No teachers found</div>

<?php } ?>

</div>

</body>
</html>