<?php
/**
 * DIAGNOSTIC ONLY - visit this file directly in your browser:
 * https://sevenministriesint.com/test-mail.php
 *
 * It attempts to send a real test email using the same PHPMailer +
 * mail-config.php setup as send-mail.php, but prints the exact
 * connection/auth error to the page instead of hanging silently.
 *
 * DELETE THIS FILE once the forms are confirmed working -
 * it should not stay live on the site long-term.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "Starting mail test...\n\n";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

if (!file_exists(__DIR__ . '/mail-config.php')) {
    die("ERROR: mail-config.php not found in this folder. It must sit next to test-mail.php.\n");
}

$config = require __DIR__ . '/mail-config.php';

echo "Config loaded:\n";
echo "  Host: " . $config['smtp_host'] . "\n";
echo "  Port: " . $config['smtp_port'] . "\n";
echo "  Secure: " . $config['smtp_secure'] . "\n";
echo "  Username: " . $config['smtp_username'] . "\n";
echo "  Password set: " . ($config['smtp_password'] === 'REPLACE_WITH_MAILBOX_PASSWORD' ? "NO - still placeholder!" : "yes (hidden)") . "\n\n";

if ($config['smtp_password'] === 'REPLACE_WITH_MAILBOX_PASSWORD') {
    die("STOP: mail-config.php still has the placeholder password. Edit it on the server and put the real mailbox password in, then reload this page.\n");
}

$mail = new PHPMailer(true);

// Capture SMTP debug output instead of hanging silently
$debugOutput = [];
$mail->SMTPDebug = 2;
$mail->Debugoutput = function ($str, $level) use (&$debugOutput) {
    $debugOutput[] = $str;
};

// Don't let this hang forever - cap the connection attempt
$mail->Timeout = 15;
$mail->SMTPKeepAlive = false;

try {
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_username'];
    $mail->Password   = $config['smtp_password'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port       = $config['smtp_port'];

    $mail->setFrom($config['smtp_username'], 'Seven Ministries Test');
    $mail->addAddress($config['smtp_username']);

    $mail->Subject = 'Test email from test-mail.php';
    $mail->Body    = 'If you are reading this, SMTP sending works.';

    $mail->send();

    echo "SUCCESS: Test email sent! Check the admissions@sevenministriesint.com inbox.\n\n";
} catch (Exception $e) {
    echo "FAILED: " . $mail->ErrorInfo . "\n\n";
} finally {
    echo "--- SMTP debug log ---\n";
    echo implode("\n", $debugOutput);
}

echo "</pre>";
