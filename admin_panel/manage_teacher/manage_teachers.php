<?php
require_once "../auth.php";
require_once "../db.php";

<<<<<<< HEAD
$result = $conn->query("
SELECT t.full_name, u.email, t.department
FROM teacher_assignments t
JOIN users u ON u.user_id = t.user_id
=======
$dept = $_GET['dept'] ?? '';

if(empty($dept)){
    header("Location: select_department.php");
    exit;
}

$stmt = $conn->prepare("
SELECT DISTINCT 
    u.user_id,
    t.full_name,
    u.email,
    t.mobile_no
FROM teacher_assignments ta
JOIN users u ON ta.user_id = u.user_id
JOIN teachers t ON ta.user_id = t.user_id
WHERE ta.department = ?
>>>>>>> b5f3620ebd6a52d6e779168b7459e9dd09ccc8ce
");
$stmt->bind_param("s",$dept);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Teachers</title>
<style>
body{margin:0;font-family:Arial;background:#f3f3f3;}
.header{
    background:#0a8f3c;
    color:white;
    padding:20px;
    border-bottom-left-radius:25px;
    border-bottom-right-radius:25px;
    font-size:20px;
}
.container{padding:20px;}
.card{
    background:white;
    padding:20px;
    border-radius:15px;
    margin-bottom:20px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}
.actions a{
    margin-right:15px;
    text-decoration:none;
}
.fab{
    position:fixed;
    bottom:30px;
    right:30px;
    width:60px;
    height:60px;
    background:#0a8f3c;
    color:white;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
    text-decoration:none;
}
</style>
</head>
<body>

<div class="header">Manage Teachers</div>

<div class="container">

<?php while($row=$result->fetch_assoc()): ?>
<div class="card">
    <h3><?= $row['full_name'] ?></h3>
    <p><?= $row['email'] ?></p>
    <p><?= $row['mobile_no'] ?></p>

    <div class="actions">
        <a href="edit_teacher.php?id=<?= $row['user_id'] ?>">✏️</a>
        <a href="delete_teacher.php?id=<?= $row['user_id'] ?>">🗑️</a>
        <a href="teacher_details.php?id=<?= $row['user_id'] ?>">➡</a>
    </div>
</div>
<?php endwhile; ?>

<a href="add_teacher.php?dept=<?= $dept ?>" class="fab">+</a>

</div>
</body>
</html>