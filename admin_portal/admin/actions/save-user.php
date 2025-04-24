<?php
// require_once __DIR__ . '/../../../config/database.php';
// require_once __DIR__ . '/../../../includes/auth.php'; 
// require_once __DIR__ . '/../includes/auth.php';

// For debugging path issues
echo "Current directory: " . __DIR__ . "<br>";
echo "Looking for config directory...<br>";

// Start with the current directory
$basePath = __DIR__;

// Try various parent directories until we find the config folder
$levelsUp = 0;
$maxLevels = 5; // Safety to prevent infinite loop
$configFound = false;

while ($levelsUp < $maxLevels && !$configFound) {
    $configPath = $basePath . str_repeat('/../', $levelsUp) . 'config/database.php';
    $includePath = $basePath . str_repeat('/../', $levelsUp) . 'includes/auth.php';
    
    echo "Trying path: " . $configPath . "<br>";
    
    if (file_exists($configPath)) {
        echo "Config file found at: " . $configPath . "<br>";
        require_once $configPath;
        require_once $includePath;
        $configFound = true;
        break;
    }
    
    $levelsUp++;
}

if (!$configFound) {
    die("Could not find config directory. Please check your directory structure.");
}



// Rest of your code...
requireLogin();

$id = $_POST['id'] ?? null;
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$phoneNo = $_POST['phoneNo'] ?? '';
$address = $_POST['address'] ?? '';
$active = isset($_POST['active']) ? 1 : 0;
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validate required fields
if (!$firstName || !$lastName || !$email) {
    die("Error: Missing required fields");
}

// For new users, validate password
if (!$id && (!$password || $password !== $confirmPassword)) {
    die("Error: Invalid password or passwords do not match");
}

try {
    // Check if email already exists (for new users or when changing email)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id ?: 0]);
    if ($stmt->rowCount() > 0) {
        die("Error: Email already in use");
    }
    
    if ($id) {
        // Update existing user
        $stmt = $pdo->prepare("
            UPDATE users 
            SET firstName = ?, lastName = ?, email = ?, phoneNo = ?, 
                address = ?, active = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $firstName, $lastName, $email, $phoneNo,
            $address, $active, $id
        ]);
    } else {
        // Create new user with hashed password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (firstName, lastName, email, phoneNo, address, password, active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $firstName, $lastName, $email, $phoneNo,
            $address, $hashedPassword, $active
        ]);
    }
    
    header("Location: /1853_restaurant/admin_portal/admin/users.php");
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}