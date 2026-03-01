<?php
require_once("../../config.php");
require_once("../../api_guard.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . "/vsmart_backend/PHPMailer/PHPMailer-master/src/PHPMailer.php";
require $_SERVER['DOCUMENT_ROOT'] . "/vsmart_backend/PHPMailer/PHPMailer-master/src/SMTP.php";
require $_SERVER['DOCUMENT_ROOT'] . "/vsmart_backend/PHPMailer/PHPMailer-master/src/Exception.php";

header("Content-Type: application/json");

if ($currentRole != "admin") {
    echo json_encode(["status"=>false,"message"=>"Access Denied"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status"=>false,"message"=>"User ID required"]);
    exit;
}

$userId = $data['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id=? AND status='pending'");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status"=>false,"message"=>"User not found or already approved"]);
    exit;
}

$user = $result->fetch_assoc();

$update = $conn->prepare("UPDATE users SET status='approved' WHERE user_id=?");
$update->bind_param("s", $userId);
$update->execute();

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kadwadkarkaveri@gmail.com';
    $mail->Password = 'xkndfopkqzdaoqjn';
    $mail->addAddress($user['email']);
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('kadwadkarkaveri@gmail.com', 'VSmart Admin');
    $mail->addAddress($user['surekhashelar18672@gmail.com']);

    $mail->isHTML(true);
    $mail->Subject = "Account Approved";
    $mail->Body = "
        <h3>Your account has been approved</h3>
        <p>You can now login to the system.</p>
    ";

    $mail->send();

    echo json_encode([
        "status" => true,
        "message" => "User approved and email sent"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "Approved but email failed: " . $mail->ErrorInfo
    ]);
}