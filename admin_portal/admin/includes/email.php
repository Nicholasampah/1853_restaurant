<?php
function sendReservationConfirmation($reservation) {
    $to = $reservation['email'];
    $subject = "Reservation Confirmation - 1853 Restaurant";
    
    $message = "Dear {$reservation['firstName']},\n\n";
    $message .= "Your reservation has been confirmed:\n\n";
    $message .= "Date: " . date('F j, Y', strtotime($reservation['date'])) . "\n";
    $message .= "Time: " . date('g:i A', strtotime($reservation['time'])) . "\n";
    $message .= "Number of Guests: {$reservation['numberOfGuests']}\n";
    $message .= "Confirmation Code: {$reservation['confirmationCode']}\n\n";
    
    if ($reservation['specialRequests']) {
        $message .= "Special Requests: {$reservation['specialRequests']}\n\n";
    }
    
    $message .= "If you need to modify or cancel your reservation, please contact us at:\n";
    $message .= "Phone: (+44) 123-4567-890\n";
    $message .= "Email: reservations@1853restaurant.com\n\n";
    $message .= "We look forward to serving you!\n\n";
    $message .= "Best regards,\n";
    $message .= "1853 Restaurant Team";
    
    $headers = "From: 1853 Restaurant <ampahnicholas61@gmail.com>\r\n";
    $headers .= "Reply-To: ampahnicholas61@gmail.com\r\n";
    
    mail($to, $subject, $message, $headers);
}