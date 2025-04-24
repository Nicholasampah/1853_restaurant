<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $message, $from = 'reservations@1853restaurant.com', $fromName = '1853 Restaurant')
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAIL_USER') ?: 'ampahnicholas61@gmail.com';
        $mail->Password = getenv('MAIL_PASSWORD') ?: 'kbccaartqmonyqsu';
        $mail->SMTPSecure = getenv('MAIL_SECURE') ? 'tls' : '';
        $mail->Port = getenv('MAIL_PORT') ?: 587;

        // Recipients
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($from, $fromName);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
}

function sendReservationConfirmation($reservation)
{
    $to = $reservation['email'];
    $subject = "Reservation Confirmation - 1853 Restaurant";

    $message = "Dear {$reservation['firstName']},\n\n";
    $message .= "Your reservation has been confirmed:\n\n";
    $message .= "Date: " . date('F j, Y', strtotime($reservation['date'])) . "\n";
    $message .= "Time: " . date('g:i A', strtotime($reservation['time'])) . "\n";
    $message .= "Number of Guests: {$reservation['numberOfGuests']}\n";
    $message .= "Confirmation Code: {$reservation['confirmationCode']}\n\n";

    if (!empty($reservation['specialRequests'])) {
        $message .= "Special Requests: {$reservation['specialRequests']}\n\n";
    }

    $message .= "If you need to modify or cancel your reservation, please contact us at:\n";
    $message .= "Phone: (+44) 123-4567-890\n";
    $message .= "Email: reservations@1853restaurant.com\n\n";
    $message .= "We look forward to serving you!\n\n";
    $message .= "Best regards,\n";
    $message .= "1853 Restaurant Team";

    return sendEmail($to, $subject, $message);
}
