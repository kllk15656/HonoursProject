<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Load database connection
require 'db.php';

if (isset($_POST['sendReminder'])) {

    $appointmentId = $_POST['appointment_id'];

    // Correct SQL based on your real tables
    $sql = "SELECT 
                a.appointment_id,
                a.date AS appointment_date,
                a.start_time AS time,
                c.first_name,
                c.last_name,
                c.email AS client_email,
                aps.service_name,
                aps.price AS deposit_price
            FROM appointments a
            JOIN clients c ON a.client_id = c.client_id
            JOIN appointment_services aps ON a.appointment_id = aps.appointment_id
            WHERE a.appointment_id = :appointment_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['appointment_id' => $appointmentId]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        echo "<script>
                alert('Error: Appointment not found.');
                window.location.href = 'Dashboard.php';
              </script>";
        exit;
    }

    // Extract values
    $clientEmail = $appointment['client_email'];
    $clientName = $appointment['first_name'] . ' ' . $appointment['last_name'];
    $serviceName = $appointment['service_name'];
    $bookingDate = $appointment['appointment_date'] . ' at ' . $appointment['time'];
    $depositAmount = '£' . number_format($appointment['deposit_price'], 2);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'honoursproject15786522@gmail.com';
        $mail->Password = 'bvge ydit zzkj nvhk';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('honoursproject15786522@gmail.com', 'Booking System');
        $mail->addAddress($clientEmail);

        $mail->Subject = "Appointment Reminder";

        $mail->Body = "
Hi $clientName,

This is a reminder for your upcoming appointment:

Service: $serviceName
Date & Time: $bookingDate
Deposit Paid: $depositAmount

If you need to reschedule or have any questions, please contact us.

Thank you.
";

        $mail->send();

        echo "<script>
                alert('Reminder email sent successfully.');
                window.location.href = 'Dashboard.php';
              </script>";

    } catch (Exception $e) {
        echo "<script>
                alert('Error sending email: " . addslashes($mail->ErrorInfo) . "');
                window.location.href = 'Dashboard.php';
              </script>";
    }
}
?>
