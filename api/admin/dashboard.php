<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../api_guard.php");
require_once(__DIR__ . "/../cors.php");

header("Content-Type: application/json");

try {

   if (!isset($currentRole)) {
      throw new Exception("Role not set");
   }

   if ($currentRole != "admin") {
      throw new Exception("Access denied");
   }

   $total_teachers = 0;
   $total_students = 0;
   $total_parents = 0;
   $total_classes = 0;

   // TEACHERS
   $result = $conn->query("SELECT COUNT(*) AS total FROM teachers");
   if (!$result)
      throw new Exception($conn->error);
   $total_teachers = intval($result->fetch_assoc()['total']);

   // STUDENTS
   $result = $conn->query("SELECT COUNT(*) AS total FROM students");
   if (!$result)
      throw new Exception($conn->error);
   $total_students = intval($result->fetch_assoc()['total']);

   // PARENTS
   $result = $conn->query("SELECT COUNT(*) AS total FROM parents");
   if (!$result)
      throw new Exception($conn->error);
   $total_parents = intval($result->fetch_assoc()['total']);

   // CLASSES
   $result = $conn->query("SELECT COUNT(*) AS total FROM classes");
   if (!$result)
      throw new Exception($conn->error);
   $total_classes = intval($result->fetch_assoc()['total']);

   // ADMIN NAME
   $stmt = $conn->prepare("SELECT full_name FROM admins WHERE user_id = ?");
   if (!$stmt)
      throw new Exception($conn->error);

   $stmt->bind_param("i", $currentUserId);
   $stmt->execute();
   $stmt->bind_result($admin_name);
   $stmt->fetch();
   $stmt->close();

   echo json_encode([
      "status" => true,
      "admin_name" => $admin_name,
      "total_teachers" => $total_teachers,
      "total_students" => $total_students,
      "total_parents" => $total_parents,
      "total_classes" => $total_classes
   ]);

} catch (Throwable $e) {
   echo json_encode([
      "status" => false,
      "error" => $e->getMessage()
   ]);
}