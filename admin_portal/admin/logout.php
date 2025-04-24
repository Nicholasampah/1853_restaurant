<?php
require_once __DIR__ . '/includes/auth.php';
session_start();
session_destroy();
header('Location: /1853_restaurant/admin_portal/admin/login.php');
exit();