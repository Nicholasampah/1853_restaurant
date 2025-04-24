<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if ($id && $status) {
    try {
        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        if ($status === 'CONFIRMED') {
            // Send confirmation email
            $stmt = $pdo->prepare("
                SELECT r.*, u.email, u.firstName
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $reservation = $stmt->fetch();
            
            if ($reservation) {
                require_once __DIR__ . '/../includes/email.php';
                sendReservationConfirmation($reservation);
            }
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

header('Location: /admin/reservations.php');