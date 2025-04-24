<?php
require_once __DIR__ . '/includes/header.php';

// Get today's reservations
$stmt = $pdo->prepare("
    SELECT COUNT(*) as count, status 
    FROM reservations_table
    WHERE date = CURDATE() 
    GROUP BY status
");
$stmt->execute();
$reservationStats = $stmt->fetchAll();

// Get total tables
$stmt = $pdo->query("SELECT COUNT(*) as count FROM dining_tables WHERE active = 1");
$totalTables = $stmt->fetch()['count'];

// Get total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE active = 1");
$totalUsers = $stmt->fetch()['count'];
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard</h2>
        <div>
            <a href="/1853_restaurant/admin_portal/admin/reservations.php?date=<?php echo date('Y-m-d'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Reservation
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Today's Reservations</h5>
                    <div class="row g-4">
                        <?php foreach ($reservationStats as $stat): ?>
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h3 class="mb-2"><?php echo $stat['count']; ?></h3>
                                    <div class="text-muted"><?php echo $stat['status']; ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="list-group">
                        <a href="/1853_restaurant/admin_portal/admin/tables.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-grid me-2"></i> Manage Tables
                        </a>
                        <a href="/1853_restaurant/admin_portal/admin/users.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-people me-2"></i> Manage Users
                        </a>
                        <a href="/1853_restaurant/admin_portal/admin/reservations.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-check me-2"></i> View All Reservations
                        </a>
                        <a href="/1853_restaurant/admin_portal/admin/register.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-person me-2"></i> Add a new admin
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">System Overview</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Active Tables
                            <span class="badge bg-primary rounded-pill"><?php echo $totalTables; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Registered Users
                            <span class="badge bg-primary rounded-pill"><?php echo $totalUsers; ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Server Info</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>PHP Version:</strong>
                            <?php echo PHP_VERSION; ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Server Time:</strong>
                            <?php echo date('Y-m-d H:i:s'); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>