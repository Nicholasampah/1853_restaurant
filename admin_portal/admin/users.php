<?php
require_once __DIR__ . '/includes/header.php';

// Get all users
$stmt = $pdo->query("
    SELECT id, CONCAT(firstName, ' ', lastName) AS fullName, email, phoneNo, 
           active, created_at 
    FROM users 
    ORDER BY firstName, lastName
");
$users = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Users</h2>
        <div>
            <a href="/1853_restaurant/admin_portal//admin/user-form.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New User
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phoneNo'] ?? '-'); ?></td>
                        <td>
                            <span class="badge <?php echo $user['active'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $user['active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="/admin/user-form.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-warning">
                                Edit
                            </a>
                            <form action="/1853_restaurant/admin_portal//admin/actions/toggle-user-status.php" method="post" 
                                  class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="active" value="<?php echo $user['active'] ? '0' : '1'; ?>">
                                <button type="submit" class="btn btn-sm <?php echo $user['active'] ? 'btn-secondary' : 'btn-success'; ?>">
                                    <?php echo $user['active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                            <a href="/1853_restaurant/admin_portal//admin/user-reservations.php?user_id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-info">
                                View Reservations
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>