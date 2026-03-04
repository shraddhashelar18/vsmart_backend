<?php
require_once "auth.php";
require_once "db.php";

$user_id = $_GET['id'] ?? '';

if(empty($user_id)){
    die("Invalid Teacher ID");
}

/* ===============================
   FETCH TEACHER INFO
=================================*/
$stmt = $conn->prepare("
    SELECT u.email, t.full_name
    FROM users u
    JOIN teachers t ON u.user_id = t.user_id
    WHERE u.user_id=?
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

$name = $teacher['full_name'];
$email = $teacher['email'];


/* ===============================
   GET ACTIVE SEMESTER
=================================*/
$set = $conn->query("SELECT active_semester FROM settings LIMIT 1")->fetch_assoc();
$activeSemester = $set['active_semester']; // 1=even, 0=odd


/* ===============================
   FETCH CLASSES (ACTIVE SEM)
=================================*/
if($activeSemester == 1){
    $classQuery = $conn->prepare("
        SELECT class_name, department 
        FROM classes 
        WHERE semester % 2 = 0
        ORDER BY semester ASC
    ");
}else{
    $classQuery = $conn->prepare("
        SELECT class_name, department 
        FROM classes 
        WHERE semester % 2 != 0
        ORDER BY semester ASC
    ");
}
$classQuery->execute();
$classResult = $classQuery->get_result();

$allClasses = [];
while($row = $classResult->fetch_assoc()){
    $allClasses[] = $row;
}


/* ===============================
   FETCH CURRENT ASSIGNMENTS
=================================*/
$assignQuery = $conn->prepare("
    SELECT department, class, subject 
    FROM teacher_assignments 
    WHERE user_id=? AND status='active'
");
$assignQuery->bind_param("i",$user_id);
$assignQuery->execute();
$assignRes = $assignQuery->get_result();

$assigned = [];
while($row = $assignRes->fetch_assoc()){
    $assigned[$row['class']][] = $row['subject'];
}


/* ===============================
   UPDATE LOGIC
=================================*/
if($_SERVER['REQUEST_METHOD']=='POST'){

    $newName = $_POST['name'];
    $subjects = $_POST['subjects'] ?? [];

    // Update name
    $up = $conn->prepare("UPDATE teachers SET full_name=? WHERE user_id=?");
    $up->bind_param("si",$newName,$user_id);
    $up->execute();

    // Remove old assignments
    $del = $conn->prepare("DELETE FROM teacher_assignments WHERE user_id=?");
    $del->bind_param("i",$user_id);
    $del->execute();

    // Insert new assignments
    foreach($subjects as $class => $subList){

        $dept = $_POST['class_department'][$class];

        foreach($subList as $subject){

            $insert = $conn->prepare("
                INSERT INTO teacher_assignments 
                (user_id,department,class,subject,status)
                VALUES (?,?,?,?, 'active')
            ");
            $insert->bind_param("isss",$user_id,$dept,$class,$subject);
            $insert->execute();
        }
    }

    header("Location: manage_teachers.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Teacher</title>
<style>
body{background:#f5f5f5;font-family:sans-serif;margin:0;}
.header{
    background:#138808;
    color:white;
    padding:20px;
    font-size:20px;
}
.container{
    padding:20px;
}
input[type=text], input[type=email]{
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:10px;
    border:1px solid #ddd;
}
.class-box{
    margin:15px 0;
    padding:15px;
    background:white;
    border-radius:12px;
    box-shadow:0 2px 6px rgba(0,0,0,0.05);
}
.subject-box{
    margin-left:20px;
    margin-top:10px;
}
.save-btn{
    background:#138808;
    color:white;
    border:none;
    padding:15px;
    width:100%;
    border-radius:12px;
    margin-top:25px;
    font-size:16px;
    cursor:pointer;
}
.disabled{
    color:#aaa;
}
</style>
</head>
<body>

<div class="header">Edit Teacher</div>

<div class="container">
<form method="POST">

<!-- NAME -->
<input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

<!-- EMAIL (ONLY DISABLED FIELD) -->
<input type="email" value="<?= htmlspecialchars($email) ?>" disabled>

<h3>Assign Classes & Subjects</h3>

<?php foreach($allClasses as $classRow): 

    $class = $classRow['class_name'];
    $dept  = $classRow['department'];

    // Preselect class if any subject assigned
    $isClassSelected = !empty($assigned[$class]);
?>

<div class="class-box">

<label>
<input type="checkbox"
       name="class_select[]"
       value="<?= $class ?>"
       <?= $isClassSelected ? 'checked' : '' ?>>
<?= $class ?> (<?= $dept ?>)
</label>

<input type="hidden" name="class_department[<?= $class ?>]" value="<?= $dept ?>">

<div class="subject-box">

<?php
$subQuery = $conn->prepare("SELECT subject_name FROM semester_subjects WHERE class=?");
$subQuery->bind_param("s",$class);
$subQuery->execute();
$subRes = $subQuery->get_result();

while($sub = $subRes->fetch_assoc()){

    $subject = $sub['subject_name'];

    // Check if subject assigned to another teacher
    $check = $conn->prepare("
        SELECT user_id FROM teacher_assignments 
        WHERE class=? AND subject=? AND status='active'
    ");
    $check->bind_param("ss",$class,$subject);
    $check->execute();
    $checkRes = $check->get_result();

    $assignedToOther = false;

    if($checkRes->num_rows>0){
        $row = $checkRes->fetch_assoc();
        if($row['user_id'] != $user_id){
            $assignedToOther = true;
        }
    }

    $isSubSelected = isset($assigned[$class]) && in_array($subject,$assigned[$class]);
?>

<label class="<?= $assignedToOther ? 'disabled' : '' ?>">
<input type="checkbox"
       name="subjects[<?= $class ?>][]"
       value="<?= $subject ?>"
       <?= $isSubSelected ? 'checked' : '' ?>
       <?= $assignedToOther ? 'disabled' : '' ?>>
<?= $subject ?>
</label>

<?php } ?>

</div>
</div>

<?php endforeach; ?>

<button class="save-btn">Update Teacher</button>

</form>
</div>

</body>
</html>