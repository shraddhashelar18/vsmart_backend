<?php
include("../db.php");
session_start();

/* 🔐 ADMIN PROTECTION (OPTIONAL BUT RECOMMENDED) */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";
$success = "";

if (isset($_POST['save'])) {

    $class_name = trim($_POST['class_name']);
    $department = $_POST['department'];
    $teacher    = $_POST['teacher'];

    /* ---------- VALIDATION ---------- */
    if ($class_name == "") {
        $error = "Class name is required";
    } elseif (preg_match('/^[0-9]+$/', $class_name)) {
        $error = "Class name cannot be only numbers";
    } elseif ($department == "") {
        $error = "Please select a department";
    } elseif ($teacher == "") {
        $error = "Please select a class teacher";
    } else {

        /* ---------- INSERT QUERY ---------- */
        $query = "
            INSERT INTO classes (class_name, department, class_teacher)
            VALUES ('$class_name', '$department', '$teacher')
        ";

        if (mysqli_query($conn, $query)) {
            header("Location: classes.php");
            exit;
        } else {
            $error = "Database error!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Class</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
        }
        .box {
            width: 420px;
            margin: 60px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            color: #009846;
        }
        label {
            font-weight: bold;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 14px;
        }
        button {
            background: #009846;
            color: #fff;
            border: none;
            font-size: 15px;
            cursor: pointer;
        }
        .cancel {
            background: #ccc;
            text-align: center;
            padding: 10px;
            display: block;
            text-decoration: none;
            color: black;
            border-radius: 5px;
        }
        .note {
            background: #e9f7ef;
            padding: 10px;
            font-size: 13px;
            border-radius: 6px;
            margin-bottom: 14px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

<div class="box">
    <h2>Add Class</h2>

    <?php if ($error != "") { ?>
        <div class="error"><?= $error ?></div>
    <?php } ?>

    <form method="post">

        <label>Class Name</label>
        <input type="text" name="class_name" placeholder="Enter class name (e.g. IF6K-A)">

        <label>Department</label>
        <select name="department">
            <option value="">Select department</option>
            <option value="IT">IT</option>
            <option value="CO">CO</option>
            <option value="EJ">EJ</option>
        </select>

        <label>Class Teacher</label>
        <select name="teacher">
            <option value="">Select class teacher</option>
            <option value="Prof Sunil Dodake">Prof Sunil Dodake</option>
            <option value="Mrs Sushma Pawar">Mrs Sushma Pawar</option>
            <option value="Mrs Gauri Bobade">Mrs Gauri Bobade</option>
        </select>

        <div class="note">
            ℹ️ Note: Students and teachers can be assigned to this class later.
        </div>

        <button type="submit" name="save">Save Class</button>
    </form>

    <br>
    <a href="classes.php" class="cancel">Cancel</a>
</div>

</body>
</html>
