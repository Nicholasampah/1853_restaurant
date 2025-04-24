<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
$success = '';

// Registration handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $secretCode = $_POST['secret_code'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif ($secretCode !== '1853Secret') {  // Simple security measure
        $error = 'Invalid secret code';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = 'Email already in use';
            } else {
                // Create new admin user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO admin_users (name, email, password, role) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$name, $email, $hashedPassword, $role]);
                $success = 'Admin user created successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Error creating user: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - 1853 Restaurant Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">1853 Restaurant Admin Registration</h3>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <!-- Registration Form -->
                        <form method="post">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="staff">Staff</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="secret_code" class="form-label">Secret Code</label>
                                <input type="password" class="form-control" id="secret_code" name="secret_code" required>
                                <div class="form-text">Enter the secret code to register a new admin user.</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary w-100">Back to home</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>