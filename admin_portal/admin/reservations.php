<?php
require_once __DIR__ . '/includes/header.php';

$date = $_GET['date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT r.*, 
           u.firstName, u.lastName, u.email,
           t.tableNumber
    FROM reservations_table r
    JOIN users u ON r.user_id = u.id
    JOIN dining_tables t ON r.table_id = t.id
    WHERE r.date = ?
    ORDER BY r.time ASC
");
$stmt->execute([$date]);
$reservations = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Reservations</h2>
        <div>
            <a href="/1853_restaurant/admin_portal/admin/reservation-form.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Reservation
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end">
                <div class="col-auto">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="<?php echo htmlspecialchars($date); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Customer</th>
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
                        <td><?php echo date('H:i', strtotime($reservation['time'])); ?></td>
                        <td>
                            <?php echo htmlspecialchars($reservation['firstName'] . ' ' . $reservation['lastName']); ?>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($reservation['email']); ?></small>
                        </td>
                        <td>Table <?php echo htmlspecialchars($reservation['tableNumber']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['numberOfGuests']); ?></td>
                        <td>
                            <?php
                            $statusClass = match($reservation['status']) {
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
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" 
                                           href="/1853_restaurant/admin_portal/admin/reservation-form.php?id=<?php echo $reservation['id']; ?>">
                                            Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php
                                    $statuses = ['CONFIRMED', 'SEATED', 'COMPLETED', 'NO_SHOW'];
                                    foreach ($statuses as $status):
                                        if ($status !== $reservation['status']):
                                    ?>
                                        <li>
                                            <form action="/1853_restaurant/admin_portal/admin/actions/update-reservation-status.php" method="post">
                                                <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                                <input type="hidden" name="status" value="<?php echo $status; ?>">
                                                <button type="submit" class="dropdown-item">
                                                    Mark as <?php echo $status; ?>
                                                </button>
                                            </form>
                                        </li>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="/1853_restaurant/admin_portal/admin/actions/cancel-reservation.php" method="post"
                                              onsubmit="return confirm('Are you sure you want to cancel this reservation?')">
                                            <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" class="dropdown-item text-danger">
                                                Cancel Reservation
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>