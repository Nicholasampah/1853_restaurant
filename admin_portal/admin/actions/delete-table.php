<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = $_POST['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("UPDATE dining_tables SET active = 0 WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

header('Location: /admin/tables.php');