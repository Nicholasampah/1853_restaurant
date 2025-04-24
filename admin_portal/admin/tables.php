<?php
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->query("SELECT * FROM dining_tables ORDER BY tableNumber");
$tables = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tables</h2>
        <div>
            <a href="/1853_restaurant/admin_portal/admin/table-form.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New Table
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Table Number</th>
                    <th>Capacity</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $table): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($table['tableNumber']); ?></td>
                        <td><?php echo htmlspecialchars($table['capacity']); ?></td>
                        <td><?php echo htmlspecialchars($table['location']); ?></td>
                        <td>
                            <span class="badge <?php echo $table['active'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $table['active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="/1853_restaurant/admin_portal/admin/table-form.php?id=<?php echo $table['id']; ?>" 
                               class="btn btn-sm btn-warning">
                                Edit
                            </a>
                            <form action="/1853_restaurant/admin_portal/admin/actions/delete-table.php" method="post" 
                                  class="d-inline" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="id" value="<?php echo $table['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>