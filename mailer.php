<?php
// Turn off ALL output buffering and clean any existing buffers
while (ob_get_level()) ob_end_clean();

// Set headers FIRST - before any output
header('Content-Type: application/json');

// Disable error display (enable for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 1 for debugging

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once '../mailerConfig.php';

// Create response array
$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

try {
    // Validate request method
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method");
    }

    // Validate required fields
    $required = ['name', 'email', 'subject', 'message'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Please fill all required fields");
        }
    }

    // Sanitize inputs
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST["subject"]), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = EMAIL_USERNAME;
    $mail->Password = EMAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Email settings
    $mail->setFrom(EMAIL_USERNAME, 'Sam Ship Management');
    $mail->isHTML(false);

    // Send to admin
    $mail->addAddress(EMAIL_USERNAME);
    $mail->Subject = "New Contact Form Submission: $subject";
    $mail->Body = "Name: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message";
    $mail->send();

    // Send confirmation to user
    $mail->clearAddresses();
    $mail->addAddress($email);
    $mail->Subject = "Thank You for Contacting Us";
    $mail->Body = "Dear $name,\n\nThank you for your message...";
    $mail->send();

    $response = ['status' => 'success', 'message' => 'Message sent successfully'];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Ensure only JSON is output
die(json_encode($response));
?>