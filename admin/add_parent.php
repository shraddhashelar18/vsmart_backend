<?php
include("../db.php");
session_start();

/* 🔐 ADMIN PROTECTION */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";

/* FETCH STUDENTS FOR DROPDOWN */
$students = mysqli_query(
    $conn,
    "SELECT id, full_name FROM users WHERE role='student' AND status='approved'"
);

if (isset($_POST['save'])) {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $student_id = $_POST['student_id']; // optional

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
    } else {

        /* DEFAULT PASSWORD */
        $password = password_hash("parent@123", PASSWORD_DEFAULT);

        /* INSERT PARENT (STATUS = APPROVED or PENDING as you want) */
        mysqli_query(
            $conn,
            "INSERT INTO users (full_name,email,phone,password,role,status)
             VALUES ('$name','$email','$phone','$password','parent','approved')"
        );

        $parent_id = mysqli_insert_id($conn);

        /* LINK STUDENT (OPTIONAL) */
        if ($student_id != "") {
            mysqli_query(
                $conn,
                "INSERT INTO parent_student (parent_id, student_id)
                 VALUES ($parent_id, $student_id)"
            );
        }

        header("Location: parents.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Parent</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; }
        .box {
            width:420px; margin:40px auto; background:#fff;
            padding:20px; border-radius:10px;
        }
        h2 { color:#009846; text-align:center; }
        input, select, button {
            width:100%; padding:10px; margin-bottom:14px;
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
            background:#e9f7ef; padding:10px; font-size:13px;
            border-radius:6px; margin-bottom:14px;
        }
        .error { color:red; margin-bottom:10px; }
    </style>
</head>

<body>

<div class="box">
<h2>Add Parent</h2>

<?php if ($error != "") { ?>
    <div class="error"><?= $error ?></div>
<?php } ?>

<form method="post">

    <input type="text" name="name" placeholder="Parent Name">

    <input type="email" name="email" placeholder="Email Address">

    <input type="text" name="phone" placeholder="Phone Number">

    <select name="student_id">
        <option value="">Link Student (optional)</option>
        <?php while ($s = mysqli_fetch_assoc($students)) { ?>
            <option value="<?= $s['id'] ?>">
                <?= $s['full_name'] ?>
            </option>
        <?php } ?>
    </select>

    <div class="note">
        💡 Parent will receive login credentials via email after saving.
    </div>

    <button type="submit" name="save">Save Parent</button>
</form>

<br>
<a href="parents.php" class="cancel">Cancel</a>

<p style="text-align:center;font-size:12px;color:gray;">
    Vsmart Academic Platform © 2024
</p>

</div>

</body>
</html>
