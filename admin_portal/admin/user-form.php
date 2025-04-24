<?php
require_once __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? null;
$user = ['active' => 1];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
}
?>

<div class="container mt-4">
    <h2><?php echo $id ? 'Edit User' : 'Add New User'; ?></h2>

    <div class="card">
        <div class="card-body">
            <form action="/1853_restaurant/admin_portal/admin/actions/save-user.php" method="post" class="needs-validation" novalidate>
                <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName"
                               value="<?php echo htmlspecialchars($user['firstName'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName"
                               value="<?php echo htmlspecialchars($user['lastName'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phoneNo" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNo" name="phoneNo"
                               value="<?php echo htmlspecialchars($user['phoneNo'] ?? ''); ?>">
                    </div>
                </div>

                <?php if (!$id): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="active" name="active"
                           <?php echo ($user['active'] ?? true) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="active">Active</label>
                </div>

                <button type="submit" class="btn btn-primary">Save User</button>
                <a href="/1853_restaurant/admin_portal/admin/users.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Password confirmation check for new users
        <?php if (!$id): ?>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        
        if (password.value !== confirmPassword.value) {
            event.preventDefault();
            alert('Passwords do not match!');
            confirmPassword.setCustomValidity('Passwords do not match!');
            return false;
        } else {
            confirmPassword.setCustomValidity('');
        }
        <?php endif; ?>
        
        form.classList.add('was-validated');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>