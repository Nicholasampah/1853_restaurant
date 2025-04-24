<?php
require_once __DIR__ . '/../includes/header.php';

requireLogin();

$id = $_POST['id'] ?? null;
$active = $_POST['active'] ?? null;

if ($id && $active !== null) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET active = ? WHERE id = ?");
        $stmt->execute([$active, $id]);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

header('Location: /1853_restaurant/admin_portal/admin/users.php');