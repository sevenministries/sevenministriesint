<?php
/**
 * Shared form-submission handler for the Seven Ministries website.
 * Handles: contact form, general enrolment, after-school enrolment,
 * post-secondary enrolment.
 *
 * Expects a POST request (JSON or regular form-encoded) with a
 * `form_type` field of: contact | general | after-school | post-secondary
 */

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$config = require __DIR__ . '/mail-config.php';

// Accept both JSON body and normal form-encoded POST
$raw = file_get_contents('php://input');
$json = json_decode($raw, true);
$input = is_array($json) ? $json : $_POST;

function field($input, $key, $default = 'N/A') {
    $val = isset($input[$key]) ? trim((string) $input[$key]) : '';
    return $val === '' ? $default : htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

$formType = isset($input['form_type']) ? trim($input['form_type']) : '';
$validTypes = ['contact', 'general', 'after-school', 'post-secondary'];

if (!in_array($formType, $validTypes, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unknown form_type']);
    exit;
}

$toAddress = $config['deliver_to'][$formType] ?? $config['smtp_username'];

// Build subject + body per form type
switch ($formType) {
    case 'contact':
        $name    = field($input, 'name');
        $email   = field($input, 'email');
        $phone   = field($input, 'phone');
        $subject = field($input, 'subject', 'General Question');
        $message = field($input, 'message');

        if ($email === 'N/A' || !filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'A valid email is required']);
            exit;
        }

        $mailSubject = "Website Contact Form: {$subject}";
        $replyTo     = $email;
        $body = "New message from the Contact Us form on sevenministriesint.com<br><br>"
              . "<strong>Name:</strong> {$name}<br>"
              . "<strong>Email:</strong> {$email}<br>"
              . "<strong>Phone:</strong> {$phone}<br>"
              . "<strong>Subject:</strong> {$subject}<br><br>"
              . "<strong>Message:</strong><br>" . nl2br($message);
        break;

    case 'general':
        $name     = field($input, 'childName');
        $dob      = field($input, 'dob');
        $age      = field($input, 'childAge');
        $edu      = field($input, 'education');
        $parent   = field($input, 'parentName');
        $email    = field($input, 'email');
        $phone    = field($input, 'phone');
        $country  = field($input, 'country');
        $program  = field($input, 'program');

        $mailSubject = "New General Enrolment Application: {$name}";
        $replyTo     = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL) ? $input['email'] : $config['smtp_username'];
        $body = "New General Enrolment application<br><br>"
              . "<strong>Child's Name:</strong> {$name}<br>"
              . "<strong>Date of Birth:</strong> {$dob}<br>"
              . "<strong>Age:</strong> {$age}<br>"
              . "<strong>Prior Education:</strong> {$edu}<br>"
              . "<strong>Parent/Guardian:</strong> {$parent}<br>"
              . "<strong>Email:</strong> {$email}<br>"
              . "<strong>Phone:</strong> {$phone}<br>"
              . "<strong>Country:</strong> {$country}<br>"
              . "<strong>Program:</strong> {$program}<br>";
        break;

    case 'after-school':
        $name     = field($input, 'childFirstName') . ' ' . field($input, 'childSurname', '');
        $dob      = field($input, 'asDob');
        $school   = field($input, 'currentSchool');
        $parent   = field($input, 'asParentName');
        $phone    = field($input, 'asPhone');
        $address  = field($input, 'asAddress');
        $program  = field($input, 'asProgram');

        $mailSubject = "New After-School Club Enrolment: {$name}";
        $replyTo     = $config['smtp_username'];
        $body = "New After-School Club enrolment<br><br>"
              . "<strong>Child's Name:</strong> {$name}<br>"
              . "<strong>Date of Birth:</strong> {$dob}<br>"
              . "<strong>Current School:</strong> {$school}<br>"
              . "<strong>Parent/Guardian:</strong> {$parent}<br>"
              . "<strong>Phone:</strong> {$phone}<br>"
              . "<strong>Address:</strong> {$address}<br>"
              . "<strong>Program:</strong> {$program}<br>";
        break;

    case 'post-secondary':
        $name     = field($input, 'psFirstName') . ' ' . field($input, 'psLastName', '');
        $phone    = field($input, 'psPhone');
        $bg       = field($input, 'psBackground');
        $program  = field($input, 'psProgram');

        $mailSubject = "New Post-Secondary Enrolment: {$name}";
        $replyTo     = $config['smtp_username'];
        $body = "New Post-Secondary enrolment<br><br>"
              . "<strong>Name:</strong> {$name}<br>"
              . "<strong>Phone:</strong> {$phone}<br>"
              . "<strong>Background:</strong> {$bg}<br>"
              . "<strong>Program:</strong> {$program}<br>";
        break;
}

$mail = new PHPMailer(true);
$mail->Timeout = 15;         // don't hang forever if SMTP is unreachable
$mail->SMTPKeepAlive = false;

try {
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_username'];
    $mail->Password   = $config['smtp_password'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port       = $config['smtp_port'];

    $mail->setFrom($config['smtp_username'], 'Seven Ministries International School Website');
    $mail->addAddress($toAddress);
    if (!empty($replyTo)) {
        $mail->addReplyTo($replyTo);
    }

    $mail->isHTML(true);
    $mail->Subject = $mailSubject;
    $mail->Body    = $body;

    $mail->send();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    error_log('Mail send failed: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'error' => 'Could not send message. Please try again later.']);
}
