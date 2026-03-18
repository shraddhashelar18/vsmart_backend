<?php

require_once("../auth.php");
require_once("../db.php");

$user_id=$_GET['user_id'];

$conn->query("
UPDATE users
SET status='rejected'
WHERE user_id='$user_id'
");

header("Location:user_approvals.php");