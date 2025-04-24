<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';

requireLogin();

$id = $_POST['id'] ?? null;
$user_id = $_POST['user_id'] ?? null;
$table_id = $_POST['table_id'] ?? null;
$date = $_POST['date'] ?? null;
$time = $_POST['time'] ?? null;
$numberOfGuests = $_POST['numberOfGuests'] ?? null;
$status = $_POST['status'] ?? 'PENDING';
$occasion = $_POST['occasion'] ?? '';
$dietaryRequirements = $_POST['dietaryRequirements'] ?? '';
$specialRequests = $_POST['specialRequests'] ?? '';
$sendConfirmation = isset($_POST['sendConfirmation']);

// Validate required fields
if (!$user_id || !$table_id || !$date || !$time || !$numberOfGuests) {
    die("Error: Missing required fields");
}

try {
    if ($id) {
        // Update existing reservation
        $stmt = $pdo->prepare("
            UPDATE reservations_table 
            SET user_id = ?, table_id = ?, date = ?, time = ?, numberOfGuests = ?, 
                status = ?, occasion = ?, dietaryRequirements = ?, specialRequests = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $user_id, $table_id, $date, $time, $numberOfGuests,
            $status, $occasion, $dietaryRequirements, $specialRequests, $id
        ]);
        
        // If status changed to CONFIRMED and notification requested, send email
        if ($sendConfirmation && $status === 'CONFIRMED') {
            $stmt = $pdo->prepare("
                SELECT r.*, u.firstName, u.email
                FROM reservations_table r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $reservation = $stmt->fetch();
            
            if ($reservation) {
                sendReservationConfirmation($reservation);
            }
        }
    } else {
        // Generate a confirmation code for new reservations
        $confirmationCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        
        // Create new reservation
        $stmt = $pdo->prepare("
            INSERT INTO reservations_table 
            (user_id, table_id, date, time, numberOfGuests, status, occasion, 
             dietaryRequirements, specialRequests, confirmationCode)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id, $table_id, $date, $time, $numberOfGuests,
            $status, $occasion, $dietaryRequirements, $specialRequests, $confirmationCode
        ]);
        
        $id = $pdo->lastInsertId();
        
        // Send confirmation email for new reservations if requested
        if ($sendConfirmation) {
            $stmt = $pdo->prepare("
                SELECT r.*, u.firstName, u.email
                FROM reservations_table r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $reservation = $stmt->fetch();
            
            if ($reservation) {
                sendReservationConfirmation($reservation);
            }
        }
    }
    
    // Redirect to reservations list with the date filter set to the reservation date
    header("Location: /1853_restaurant/admin_portal/admin/reservations.php?date=" . urlencode($date));
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
