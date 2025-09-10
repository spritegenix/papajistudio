<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Require Composer's autoload file
require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Enable error logging for debugging
error_log("POST data received: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get JSON input data (since your React form sends JSON)
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // If JSON decode fails, try $_POST (fallback for form-data)
    if (!$data) {
        $data = $_POST;
    }

    // Sanitize and validate input for wedding photography form
    $name = strip_tags(trim($data["name"]));
    $name = str_replace(array("\r", "\n"), array(" ", " "), $name);
    $email = filter_var(trim($data["email"]), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($data["phone"]));
    $venue = strip_tags(trim($data["venue"]));
    $date = strip_tags(trim($data["date"]));
    $eventType = strip_tags(trim($data["eventType"]));
    $budget = strip_tags(trim($data["budget"]));
    $message = trim($data["message"]);

    // Validate required fields
    if (empty($name) || empty($phone) || empty($venue) || empty($date) || empty($eventType) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Please complete all required fields and provide a valid email address."]);
        exit;
    }

    // Send email via PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($_ENV['SMTP_FROM_EMAIL'], 'Wedding Photography Team');
        $mail->addReplyTo($email, $name);
        $mail->isHTML(true);
        $mail->Subject = "New Wedding Photography Inquiry - " . $eventType . " on " . $date;
        $mail->Body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333333;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            color: #2c5aa0;
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 10px;
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 600;
        }
        .field-row {
            margin: 15px 0;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .field-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #555555;
            display: inline-block;
            width: 150px;
            vertical-align: top;
        }
        .value {
            color: #333333;
            display: inline-block;
            max-width: 400px;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            color: #666666;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h3 class='header'>New Wedding Photography Inquiry</h3>
        
        <div class='field-row'>
            <span class='label'>Name:</span>
            <span class='value'>$name</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Email:</span>
            <span class='value'>$email</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Phone:</span>
            <span class='value'>$phone</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Venue:</span>
            <span class='value'>$venue</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Event Date:</span>
            <span class='value'>$date</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Event Type:</span>
            <span class='value'>$eventType</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Budget:</span>
            <span class='value'>" . (!empty($budget) ? $budget : 'Not specified') . "</span>
        </div>
        
        <div class='field-row'>
            <span class='label'>Message:</span>
            <span class='value'>" . (!empty($message) ? nl2br(htmlspecialchars($message)) : 'No additional message') . "</span>
        </div>
        
        <div class='footer'>
            <p>This inquiry was submitted through your wedding photography contact form.</p>
        </div>
    </div>
</body>
</html>
";

        $mail->send();

        error_log("Wedding photography inquiry email sent successfully");
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Thank you! We will contact you soon to discuss your wedding photography needs."]);
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Message could not be sent. Please try again later."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed. Please use POST."]);
}
