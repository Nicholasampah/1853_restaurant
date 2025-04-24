<?php
require_once __DIR__ . '/includes/header.php';

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    header('Location: /1853_restaurant/admin_portal/admin/users.php');
    exit;
}

// Get user details
$stmt = $pdo->prepare("SELECT id, CONCAT(firstName, ' ', lastName) AS fullName, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /1853_restaurant/admin_portal/admin/users.php');
    exit;
}

// Get user's reservations
$stmt = $pdo->prepare("
    SELECT r.*, t.tableNumber
    FROM reservations_table r
    JOIN dining_tables t ON r.table_id = t.id
    WHERE r.user_id = ?
    ORDER BY r.date DESC, r.time DESC
");
$stmt->execute([$userId]);
$reservations = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reservations for <?php echo htmlspecialchars($user['fullName']); ?></h2>
        <div>
            <a href="/1853_restaurant/admin_portal/admin/users.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
            <a href="/1853_restaurant/admin_portal/admin/reservation-form.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Create Reservation
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total Reservations:</strong> <?php echo count($reservations); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">No reservations found for this user.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Table</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Special Requests</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($reservation['date'])); ?></td>
                            <td><?php echo date('H:i', strtotime($reservation['time'])); ?></td>
                            <td>Table <?php echo htmlspecialchars($reservation['tableNumber']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['numberOfGuests']); ?></td>
                            <td>
                                <?php
                                $statusClass = match ($reservation['status']) {
                                    'CONFIRMED' => 'bg-success',
                                    'PENDING' => 'bg-warning',
                                    'SEATED' => 'bg-info',
                                    'COMPLETED' => 'bg-primary',
                                    'CANCELLED' => 'bg-danger',
                                    'NO_SHOW' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($reservation['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($reservation['occasion']): ?>
                                    <small class="d-block text-primary">
                                        Occasion: <?php echo htmlspecialchars($reservation['occasion']); ?>
                                    </small>
                                <?php endif; ?>
                                <?php if ($reservation['dietaryRequirements']): ?>
                                    <small class="d-block text-danger">
                                        Dietary: <?php echo htmlspecialchars($reservation['dietaryRequirements']); ?>
                                    </small>
                                <?php endif; ?>
                                <?php if ($reservation['specialRequests']): ?>
                                    <small class="d-block">
                                        <?php echo htmlspecialchars($reservation['specialRequests']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/1853_restaurant/admin_portal/admin/reservation-form.php?id=<?php echo $reservation['id']; ?>"
                                    class="btn btn-sm btn-warning">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>