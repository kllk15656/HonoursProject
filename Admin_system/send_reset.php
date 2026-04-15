<?php
session_start();
require_once "./db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

if (isset($_POST['email'])) {

    $email = $_POST['email'];

    // 1. Look up admin by email
    $stmt = $pdo->prepare("SELECT admin_id, email FROM admins WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    // Always show same message for security
    $genericMessage = "<script>
        alert('If that email exists, a reset link has been sent.');
        window.location.href = 'Login.php';
    </script>";

    if (!$admin) {
        echo $genericMessage;
        exit;
    }

    $adminId = $admin['admin_id'];

    // 2. Generate secure token + expiry (1 hour)
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 3600);

    // 3. Store token in database
    $stmt = $pdo->prepare("
        INSERT INTO reset_tokens (admin_id, token, expires_at)
        VALUES (:admin_id, :token, :expires_at)
    ");
    $stmt->execute([
        ':admin_id' => $adminId,
        ':token' => $token,
        ':expires_at' => $expiresAt
    ]);

    // 4. Build reset link
    $resetLink = "http://localhost/HonoursProject/Admin_system/reset_password.php?token=" . urlencode($token);

    // 5. Send email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'honoursproject15786522@gmail.com';
        $mail->Password = 'bvge ydit zzkj nvhk'; // Your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('honoursproject15786522@gmail.com', 'Cozy Bear Booking System');
        $mail->addAddress($email);

        $mail->Subject = "Admin Password Reset";
        $mail->Body = "
Hi,

A password reset was requested for your admin account.

Click the link below to reset your password (valid for 1 hour):

$resetLink

If you did not request this, you can ignore this email.
";

        $mail->send();

        echo $genericMessage;
        exit;

    } catch (Exception $e) {
        echo "<script>
            alert('Error sending reset email.');
            window.location.href = 'Login.php';
        </script>";
        exit;
    }
}
?>
