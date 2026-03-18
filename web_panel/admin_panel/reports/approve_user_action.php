<?php

require_once("../../config.php");

$user_id=$_GET['user_id'];

$conn->query("
UPDATE users
SET status='approved'
WHERE user_id='$user_id'
");

header("Location:user_approvals.php");