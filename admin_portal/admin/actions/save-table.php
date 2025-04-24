<?php
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$id = $_POST['id'] ?? null;
$tableNumber = $_POST['tableNumber'] ?? '';
$capacity = $_POST['capacity'] ?? 0;
$location = $_POST['location'] ?? 'MAIN';
$active = isset($_POST['active']) ? 1 : 0;

try {
    if ($id) {
        $stmt = $pdo->prepare("
            UPDATE dining_tables 
            SET tableNumber = ?, capacity = ?, location = ?, active = ?
            WHERE id = ?
        ");
        $stmt->execute([$tableNumber, $capacity, $location, $active, $id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO dining_tables (tableNumber, capacity, location, active)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$tableNumber, $capacity, $location, $active]);
    }
    
    header('Location: /1853_restaurant/admin_portal/admin/tables.php');
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}