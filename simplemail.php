<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'admissions@sevenministriesint.com';
    $mail->Password = '';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('admissions@sevenministriesint.com', 'Seven Ministries School');
    $mail->addAddress('admissions@sevenministriesint.com');

    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email from PHPMailer on Hostinger';
    $mail->send();

    echo "Test email sent!";
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
?>
