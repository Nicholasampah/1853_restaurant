<?php
require_once __DIR__ . '/includes/header.php';

$id = $_GET['id'] ?? null;
$reservation = [
    'date' => date('Y-m-d'),
    'time' => date('H:i', strtotime('+1 hour')),
    'numberOfGuests' => 2,
    'status' => 'PENDING'
];

// Get all users for the dropdown
$stmt = $pdo->query("SELECT id, CONCAT(firstName, ' ', lastName) AS fullName, email FROM users ORDER BY firstName, lastName");
$users = $stmt->fetchAll();

// Get all tables for the dropdown
$stmt = $pdo->query("SELECT id, tableNumber, capacity, location FROM dining_tables WHERE active = 1 ORDER BY tableNumber");
$tables = $stmt->fetchAll();

// Fetch existing reservation if editing
if ($id) {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               u.id AS user_id, CONCAT(u.firstName, ' ', u.lastName) AS userName, u.email AS userEmail
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$id]);
    $foundReservation = $stmt->fetch();
    
    if ($foundReservation) {
        $reservation = $foundReservation;
    }
}

$statusOptions = ['PENDING', 'CONFIRMED', 'SEATED', 'COMPLETED', 'CANCELLED', 'NO_SHOW'];
?>

<div class="container mt-4">
    <h2><?php echo $id ? 'Edit Reservation' : 'Create New Reservation'; ?></h2>

    <div class="card">
        <div class="card-body">
            <form action="/1853_restaurant/admin_portal/admin/actions/save-reservation.php" method="post" class="needs-validation" novalidate>
                <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Customer</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>"
                                        <?php echo (isset($reservation['user_id']) && $reservation['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['fullName']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="table_id" class="form-label">Table</label>
                        <select class="form-select" id="table_id" name="table_id" required>
                            <option value="">Select Table</option>
                            <?php foreach ($tables as $table): ?>
                                <option value="<?php echo $table['id']; ?>"
                                        <?php echo (isset($reservation['table_id']) && $reservation['table_id'] == $table['id']) ? 'selected' : ''; ?>>
                                    Table <?php echo htmlspecialchars($table['tableNumber']); ?> 
                                    (<?php echo htmlspecialchars($table['capacity']); ?> seats, <?php echo htmlspecialchars($table['location']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date"
                               value="<?php echo htmlspecialchars($reservation['date'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="time" class="form-label">Time</label>
                        <input type="time" class="form-control" id="time" name="time"
                               value="<?php echo htmlspecialchars($reservation['time'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="numberOfGuests" class="form-label">Number of Guests</label>
                        <input type="number" class="form-control" id="numberOfGuests" name="numberOfGuests"
                               value="<?php echo htmlspecialchars($reservation['numberOfGuests'] ?? 2); ?>" 
                               min="1" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <?php foreach ($statusOptions as $status): ?>
                                <option value="<?php echo $status; ?>"
                                        <?php echo (isset($reservation['status']) && $reservation['status'] === $status) ? 'selected' : ''; ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="occasion" class="form-label">Occasion (Optional)</label>
                        <input type="text" class="form-control" id="occasion" name="occasion"
                               value="<?php echo htmlspecialchars($reservation['occasion'] ?? ''); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="dietaryRequirements" class="form-label">Dietary Requirements (Optional)</label>
                    <textarea class="form-control" id="dietaryRequirements" name="dietaryRequirements" rows="2"><?php echo htmlspecialchars($reservation['dietaryRequirements'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="specialRequests" class="form-label">Special Requests (Optional)</label>
                    <textarea class="form-control" id="specialRequests" name="specialRequests" rows="3"><?php echo htmlspecialchars($reservation['specialRequests'] ?? ''); ?></textarea>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="sendConfirmation" name="sendConfirmation" 
                           <?php echo (!$id || (isset($reservation['status']) && $reservation['status'] === 'PENDING')) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="sendConfirmation">
                        Send confirmation email to customer
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Save Reservation</button>
                <a href="/1853_restaurant/admin_portal/admin/reservations.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const tableSelect = document.getElementById('table_id');
    const guestsInput = document.getElementById('numberOfGuests');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Check if selected table can accommodate the party size
        const selectedOption = tableSelect.options[tableSelect.selectedIndex];
        if (selectedOption) {
            const tableCapacity = parseInt(selectedOption.textContent.match(/\((\d+) seats/)[1]);
            const guestCount = parseInt(guestsInput.value);
            
            if (guestCount > tableCapacity) {
                event.preventDefault();
                alert(`Warning: The selected table can only accommodate ${tableCapacity} guests, but you've requested ${guestCount}.`);
            }
        }
        
        form.classList.add('was-validated');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>