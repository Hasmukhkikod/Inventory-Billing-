<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Database;

$db = new Database('demo');
$email = 'soni25121991@gmail.com';
$existingUser = $db->query("SELECT id FROM users WHERE email = ? LIMIT 1", [$email])->fetch();
$existingOrg = $db->query("SELECT id FROM organizations WHERE email = ? LIMIT 1", [$email])->fetch();

echo "User: " . json_encode($existingUser) . "\n";
echo "Org: " . json_encode($existingOrg) . "\n";

// Test Email
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER'] ?? '';
    $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

    $mail->setFrom($_ENV['SMTP_USER'] ?? 'infogrovixo@gmail.com', 'Grovixo');
    $mail->addAddress('test@example.com', 'Test');
    $mail->isHTML(true);
    $mail->Subject = 'Test Mail';
    $mail->Body    = 'Test';
    $mail->send();
    echo "Mail Sent!\n";
} catch (\Exception $e) {
    echo "Mail Error: " . $mail->ErrorInfo . "\n";
}
