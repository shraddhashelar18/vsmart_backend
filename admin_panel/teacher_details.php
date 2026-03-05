<?php
require_once "auth.php";
require_once "db.php";

$id = intval($_GET['id'] ?? 0);

if($id <= 0){
    header("Location: select_department.php");
    exit;
}

/* Get teacher name */
$nameQuery = $conn->prepare("
SELECT full_name FROM teachers WHERE user_id=?
");
$nameQuery->bind_param("i",$id);
$nameQuery->execute();
$nameResult = $nameQuery->get_result();
$teacher = $nameResult->fetch_assoc();
$teacherName = $teacher['full_name'] ?? 'Teacher';

/* Get assignments */
$stmt = $conn->prepare("
SELECT class, subject
FROM teacher_assignments
WHERE user_id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($teacherName) ?></title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f3f3f3;
}

/* HEADER */
.header{
    background:#0a8f3c;
    color:white;
    padding:20px;
    border-bottom-left-radius:25px;
    border-bottom-right-radius:25px;
    display:flex;
    align-items:center;
    font-size:20px;
    font-weight:bold;
}

.back{
    margin-right:15px;
    color:white;
    text-decoration:none;
    font-size:22px;
}

/* CONTAINER */
.container{
    padding:20px;
}

/* CARD */
.card{
    background:white;
    padding:20px;
    border-radius:15px;
    margin-bottom:20px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

.class-title{
    font-size:18px;
    font-weight:bold;
    margin-bottom:8px;
}

.subject-text{
    color:#555;
    font-size:16px;
}
</style>

</head>
<body>

<div class="header">
    <a href="javascript:history.back()" class="back">←</a>
    <?= htmlspecialchars($teacherName) ?>
</div>

<div class="container">

<?php if($result->num_rows == 0): ?>
    <div class="card">
        No classes assigned.
    </div>
<?php endif; ?>

<?php while($row=$result->fetch_assoc()): ?>
    <div class="card">
        <div class="class-title"><?= htmlspecialchars($row['class']) ?></div>
        <div class="subject-text"><?= htmlspecialchars($row['subject']) ?></div>
    </div>
<?php endwhile; ?>

</div>

</body>
</html>