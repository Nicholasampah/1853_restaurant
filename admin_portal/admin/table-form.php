<?php
require_once __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? null;
$table = ['active' => true];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM dining_tables WHERE id = ?");
    $stmt->execute([$id]);
    $table = $stmt->fetch();
}

$locations = ['MAIN', 'OUTDOOR', 'PRIVATE', 'BAR'];
?>

<div class="container mt-4">
    <h2><?php echo $id ? 'Edit Table' : 'Add New Table'; ?></h2>

    <div class="card">
        <div class="card-body">
            <form action="/1853_restaurant/admin_portal/admin/actions/save-table.php" method="post" class="needs-validation" novalidate>
                <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="tableNumber" class="form-label">Table Number</label>
                    <input type="text" class="form-control" id="tableNumber" name="tableNumber"
                           value="<?php echo htmlspecialchars($table['tableNumber'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity"
                           value="<?php echo htmlspecialchars($table['capacity'] ?? ''); ?>" 
                           min="1" required>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Location</label>
                    <select class="form-select" id="location" name="location">
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo $location; ?>"
                                    <?php echo ($table['location'] ?? '') === $location ? 'selected' : ''; ?>>
                                <?php echo $location; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="active" name="active"
                           <?php echo ($table['active'] ?? true) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="active">Active</label>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="/1853_restaurant/admin_portal/admin/tables.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>