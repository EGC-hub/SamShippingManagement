<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

require_once '../mailerConfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form inputs
    $name = filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST["subject"]), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING);
    
    // Validation checks
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }

    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com'; // Change to your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USERNAME; // Your SMTP username
        $mail->Password = EMAIL_PASSWORD; // Your SMTP password or App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 465;

        // Email settings
        $mail->setFrom(EMAIL_USERNAME, 'Sam Ship Management'); // Your admin email and name
        $mail->isHTML(false); // Set to true if you want HTML emails

        // Send email to admin
        $mail->addAddress(EMAIL_USERNAME); // Your admin email
        $mail->Subject = "New Contact Form Submission: $subject";
        $mail->Body = "Name: $name\n" .
                      "Email: $email\n" .
                      "Subject: $subject\n" .
                      "Message:\n$message";

        $mail->send();

        // Clear recipients and send confirmation email to user
        $mail->clearAddresses();
        $mail->addAddress($email); // User's email
        $mail->Subject = "Thank You for Contacting Us";
        $mail->Body = "Dear $name,\n\n" .
                      "Thank you for reaching out to us. We have received your message:\n\n" .
                      "Subject: $subject\n" .
                      "Message: $message\n\n" .
                      "We'll get back to you soon!\n\n" .
                      "Best regards,\nYour Website Team";

        $mail->send();

        // Send success response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
        exit;

    } catch (Exception $e) {
        // Send error response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Error: {$mail->ErrorInfo}"]);
        exit;
    }
} else {
    // If not POST request
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}
?>