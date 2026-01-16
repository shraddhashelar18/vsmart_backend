<?php
include("../db.php");
session_start();

/* 🔐 ADMIN ACCESS ONLY */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";

/* FETCH CLASSES FOR DROPDOWN */
$classes = mysqli_query(
    $conn,
    "SELECT class_id, class_name FROM classes"
);

if (isset($_POST['save'])) {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $class_id = $_POST['class_id'];

    /* ---------- VALIDATION (MATCH FLUTTER) ---------- */
    if ($name == "") {
        $error = "Name is required";
    } elseif (preg_match('/[0-9]/', $name)) {
        $error = "Name cannot contain numbers";
    } elseif ($email == "") {
        $error = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter valid email";
    } elseif ($class_id == "") {
        $error = "Please select a class";
    } else {

        /* DEFAULT PASSWORD */
        $password = password_hash("teacher@123", PASSWORD_DEFAULT);

        /* INSERT TEACHER (STATUS = PENDING FOR ADMIN APPROVAL) */
        mysqli_query(
            $conn,
            "INSERT INTO users 
            (full_name, email, password, role, status, class_id)
            VALUES 
            ('$name','$email','$password','teacher','pending','$class_id')"
        );

        header("Location: teachers.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Teacher</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; }
        .box {
            width:420px; margin:40px auto; background:#fff;
            padding:20px; border-radius:10px;
        }
        h2 { color:#009846; text-align:center; }
        label { font-weight:600; }
        input, select, button {
            width:100%; padding:10px; margin-top:6px; margin-bottom:14px;
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
            background:#eaf7f1; padding:10px; font-size:13px;
            border-radius:6px; margin-bottom:14px;
        }
        .error { color:red; margin-bottom:10px; }
    </style>
</head>

<body>

<div class="box">
<h2>Add Teacher</h2>

<?php if ($error != "") { ?>
    <div class="error"><?= $error ?></div>
<?php } ?>

<form method="post">

    <label>Teacher Name</label>
    <input type="text" name="name" placeholder="Enter full name">

    <label>Email Address</label>
    <input type="email" name="email" placeholder="teacher@example.com">

    <label>Assign Class</label>
    <select name="class_id">
        <option value="">Select a class</option>
        <?php while ($c = mysqli_fetch_assoc($classes)) { ?>
            <option value="<?= $c['class_id'] ?>">
                <?= $c['class_name'] ?>
            </option>
        <?php } ?>
    </select>

    <div class="note">
        💡 Note: Teacher will receive a confirmation email with login credentials after saving.
    </div>

    <button type="submit" name="save">Save Teacher</button>
</form>

<br>
<a href="teachers.php" class="cancel">Cancel</a>

<p style="text-align:center;font-size:12px;color:gray;">
    Vsmart Academic Platform © 2024
</p>

</div>

</body>
</html>
