<?php
include("db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

<<<<<<< HEAD
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $email     = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? '';

    if ($full_name=="" || $email=="" || $password=="" || $role=="") {
        $message = "All fields are required";
    } else {

        $valid_roles = ['student','teacher','parent'];
        if (!in_array($role, $valid_roles)) {
            $message = "Invalid role selected";
        } else {

            $check = mysqli_query($conn,
                "SELECT user_id FROM users WHERE email='$email'"
            );
=======
    // ---------- COMMON ----------
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? '';

    // ---------- BASIC VALIDATION ----------
    if ($full_name === "" || $email === "" || $password === "" || $role === "") {
        $message = "All fields are required";
    } else {

        // ---------- ALLOWED ROLES ----------
        $allowed_roles = ['student','teacher','parent'];
        if (!in_array($role, $allowed_roles)) {
            $message = "Unauthorized role";
        } else {

            // ---------- CHECK EMAIL ----------
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2

            if ($stmt->num_rows > 0) {
                $message = "Email already registered";
            } else {

<<<<<<< HEAD
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                mysqli_query($conn,
                    "INSERT INTO users (email,password,role,status)
                     VALUES ('$email','$hashed','$role','pending')"
                );
=======
                // ---------- INSERT INTO USERS ----------
                $hashed = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare(
                    "INSERT INTO users (email, password, role, status)
                     VALUES (?, ?, ?, 'pending')"
                );
                $stmt->bind_param("sss", $email, $hashed, $role);
                $stmt->execute();
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2

                $user_id = $stmt->insert_id;

                // ---------- STUDENT ----------
                if ($role === "student") {

                    $roll_no = $_POST['roll_no'] ?? '';
                    $class   = $_POST['class'] ?? '';
                    $mobile  = $_POST['mobile_no'] ?? '';
                    $pmobile = $_POST['parent_mobile_no'] ?? '';

                    if ($roll_no=="" || $class=="" || $mobile=="" || $pmobile=="") {
                        $message = "All student fields are required";
                    } else {
<<<<<<< HEAD
                        mysqli_query($conn,
                            "INSERT INTO students
                             (roll_no,user_id,full_name,class,mobile_no,parent_mobile_no)
                             VALUES
                             ('$roll_no',$user_id,'$full_name','$class','$mobile','$pmobile')"
                        );
                        $message = "Student registered successfully. Waiting for approval";
=======

                        $stmt = $conn->prepare(
                            "INSERT INTO students
                            (roll_no, user_id, full_name, class, mobile_no, parent_mobile_no)
                            VALUES (?, ?, ?, ?, ?, ?)"
                        );
                        $stmt->bind_param(
                            "sissss",
                            $roll_no, $user_id, $full_name, $class, $mobile, $pmobile
                        );
                        $stmt->execute();

                        $message = "Student registered successfully. Waiting for admin approval";
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2
                    }
                }

                // ---------- TEACHER ----------
<<<<<<< HEAD
                elseif ($role == "teacher") {
=======
                elseif ($role === "teacher") {
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2

                    $emp_id = $_POST['employee_id'] ?? '';
                    $mobile = $_POST['mobile_no'] ?? '';

                    if ($emp_id=="" || $mobile=="") {
                        $message = "All teacher fields are required";
                    } else {
<<<<<<< HEAD
                        mysqli_query($conn,
                            "INSERT INTO teachers
                             (employee_id,user_id,full_name,mobile_no)
                             VALUES
                             ('$emp_id',$user_id,'$full_name','$mobile')"
                        );
                        $message = "Teacher registered successfully. Waiting for approval";
=======

                        $stmt = $conn->prepare(
                            "INSERT INTO teachers
                            (employee_id, user_id, full_name, mobile_no)
                            VALUES (?, ?, ?, ?)"
                        );
                        $stmt->bind_param(
                            "siss",
                            $emp_id, $user_id, $full_name, $mobile
                        );
                        $stmt->execute();

                        $message = "Teacher registered successfully. Waiting for admin approval";
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2
                    }
                }

                // ---------- PARENT ----------
                elseif ($role === "parent") {

                    $enroll = $_POST['enrollment_no'] ?? '';
                    $mobile = $_POST['mobile_no'] ?? '';

                    if ($enroll=="" || $mobile=="") {
                        $message = "All parent fields are required";
                    } else {
<<<<<<< HEAD
                        mysqli_query($conn,
                            "INSERT INTO parents
                             (enrollment_no,user_id,full_name,mobile_no)
                             VALUES
                             ('$enroll',$user_id,'$full_name','$mobile')"
                        );
                        $message = "Parent registered successfully. Waiting for approval";
=======

                        $stmt = $conn->prepare(
                            "INSERT INTO parents
                            (enrollment_no, user_id, full_name, mobile_no)
                            VALUES (?, ?, ?, ?)"
                        );
                        $stmt->bind_param(
                            "siss",
                            $enroll, $user_id, $full_name, $mobile
                        );
                        $stmt->execute();

                        $message = "Parent registered successfully. Waiting for admin approval";
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<style>
body{font-family:Arial;background:#f5f5f5;}
.box{width:420px;margin:40px auto;padding:25px;background:#fff;border-radius:8px;}
input,select{width:100%;padding:10px;margin-top:10px;}
button{margin-top:15px;width:100%;padding:10px;background:#009846;color:white;border:none;border-radius:5px;}
.msg{margin-top:15px;color:green;text-align:center;}
</style>
</head>

<body>
<div class="box">
<h2>Register</h2>

<form method="POST">

<input name="full_name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<select name="role" required onchange="this.form.submit()">
    <option value="">Select Role</option>
<<<<<<< HEAD
    <option value="student" <?= (($_POST['role'] ?? '')=="student")?'selected':'' ?>>Student</option>
    <option value="teacher" <?= (($_POST['role'] ?? '')=="teacher")?'selected':'' ?>>Teacher</option>
    <option value="parent"  <?= (($_POST['role'] ?? '')=="parent")?'selected':'' ?>>Parent</option>
=======
    <option value="student">Student</option>
    <option value="teacher">Teacher</option>
    <option value="parent">Parent</option>
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2
</select>

<?php if (($_POST['role'] ?? '') === "student"): ?>
    <input name="roll_no" placeholder="Roll No" required>
    <input name="class" placeholder="Class" required>
    <input name="mobile_no" placeholder="Mobile No" required>
    <input name="parent_mobile_no" placeholder="Parent Mobile No" required>
<?php endif; ?>

<<<<<<< HEAD
<?php if (($_POST['role'] ?? '') == "teacher"): ?>
=======
<?php if (($_POST['role'] ?? '') === "teacher"): ?>
>>>>>>> a575b8dc2e3b479f9a5b967f91449caf94bda7e2
    <input name="employee_id" placeholder="Employee ID" required>
    <input name="mobile_no" placeholder="Mobile No" required>
<?php endif; ?>

<?php if (($_POST['role'] ?? '') === "parent"): ?>
    <input name="enrollment_no" placeholder="Enrollment No" required>
    <input name="mobile_no" placeholder="Mobile No" required>
<?php endif; ?>

<button type="submit">Register</button>
</form>

<?php if ($message!=""): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

</div>
</body>
</html>
