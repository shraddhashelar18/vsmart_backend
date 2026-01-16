<?php
include("../db.php");
session_start();

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";

/* FETCH CLASSES (USING class_id) */
$classes = mysqli_query(
    $conn,
    "SELECT class_id, class_name FROM classes"
);

/* FETCH PARENTS (OPTIONAL) */
$parents = mysqli_query(
    $conn,
    "SELECT user_id, full_name FROM users 
     WHERE role='parent' AND status='approved'"
);

if (isset($_POST['save'])) {

    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $class_id  = $_POST['class_id'];
    $parent_id = $_POST['parent_id']; // optional

    /* ---------- VALIDATION (MATCH FLUTTER) ---------- */
    if ($name == "") {
        $error = "Name is required";
    } elseif (preg_match('/[0-9]/', $name)) {
        $error = "Name cannot contain numbers";
    } elseif ($email == "") {
        $error = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter valid email";
    } elseif ($phone == "") {
        $error = "Phone is required";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be 10 digits";
    } elseif ($class_id == "") {
        $error = "Please select a class";
    } else {

        /* DEFAULT PASSWORD */
        $password = password_hash("student@123", PASSWORD_DEFAULT);

        /* INSERT STUDENT (STATUS = PENDING FOR ADMIN APPROVAL) */
        mysqli_query(
            $conn,
            "INSERT INTO users 
            (full_name, email, phone, password, role, status, class_id)
            VALUES 
            ('$name','$email','$phone','$password','student','pending','$class_id')"
        );

        $student_id = mysqli_insert_id($conn);

        /* LINK PARENT (OPTIONAL) */
        if ($parent_id != "") {
            mysqli_query(
                $conn,
                "INSERT INTO parent_student (parent_id, student_id)
                 VALUES ($parent_id, $student_id)"
            );
        }

        header("Location: students.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; }
        .box {
            width:420px; margin:40px auto; background:#fff;
            padding:20px; border-radius:10px;
        }
        h2 { color:#009846; text-align:center; }
        input, select, button {
            width:100%; padding:10px; margin-bottom:12px;
        }
        button {
            background:#009846; color:white; border:none;
            font-size:15px; cursor:pointer;
        }
        .cancel {
            background:#ccc; padding:10px; text-align:center;
            display:block; text-decoration:none; color:black;
            border-radius:6px;
        }
        .note {
            background:#e8f0fe; padding:10px; font-size:13px;
            border-radius:6px; margin-bottom:14px;
        }
        .error { color:red; margin-bottom:10px; }
    </style>
</head>

<body>

<div class="box">
<h2>Add Student</h2>

<?php if ($error != "") { ?>
    <div class="error"><?= $error ?></div>
<?php } ?>

<form method="post">

    <input type="text" name="name" placeholder="Enter student's full name">

    <input type="email" name="email" placeholder="student@example.com">

    <input type="text" name="phone" placeholder="Phone Number">

    <!-- CLASS DROPDOWN -->
    <select name="class_id">
        <option value="">Select a class</option>
        <?php while ($c = mysqli_fetch_assoc($classes)) { ?>
            <option value="<?= $c['class_id'] ?>">
                <?= $c['class_name'] ?>
            </option>
        <?php } ?>
    </select>

    <!-- PARENT DROPDOWN (OPTIONAL) -->
    <select name="parent_id">
        <option value="">Select a parent (optional)</option>
        <?php while ($p = mysqli_fetch_assoc($parents)) { ?>
            <option value="<?= $p['id'] ?>">
                <?= $p['full_name'] ?>
            </option>
        <?php } ?>
    </select>

    <div class="note">
        💡 Student details will be added to the system and parents will be notified via email.
    </div>

    <button type="submit" name="save">Save Student</button>
</form>

<br>
<a href="students.php" class="cancel">Cancel</a>

</div>

</body>
</html>
