<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1853 Restaurant Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/1853_restaurant/admin_portal/admin/dashboard.php">1853 Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/1853_restaurant/admin_portal/admin/reservations.php">
                            <i class="bi bi-calendar-check"></i> Reservations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/1853_restaurant/admin_portal/admin/tables.php">
                            <i class="bi bi-grid"></i> Tables
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/1853_restaurant/admin_portal/admin/users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/1853_restaurant/admin_portal/admin/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>