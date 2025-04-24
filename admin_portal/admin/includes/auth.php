<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /1853_restaurant/admin_portal/admin/login.php');
        exit();
    }
}

function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'];
        return true;
    }
    
    return false;
}

function logout() {
    session_destroy();
    header('Location: /1853_restaurant/admin_portal/admin/login.php');
    exit();
}