<?php
$page_title = 'Kelola Pesanan';
include('../../includes/admin-header.php');
require_once('../../config/db.php');

// Update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    
    header('Location: orders.php?success=Status berhasil diupdate');
    exit();
}

// Ambil data pesanan
$db = new Database();
$conn = $db->getConnection();

$filter_status = $_GET['filter_status'] ?? '';
$search = $_GET['search'] ?? '';

// Check which pickup columns exist in the orders table to avoid SQL errors on older schemas
$hasPickupCols = [];
try {
    $colStmt = $conn->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'orders' AND COLUMN_NAME IN ('pickup_name','pickup_phone','pickup_address','pickup_datetime')");
    $colStmt->execute([':db' => 'laundry_db']);
    $availableCols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
    $hasPickupCols = array_flip($availableCols ?: []);
} catch (Exception $e) {
    // ignore — we'll fall back to defaults
    $hasPickupCols = [];
}

$customer_expr = isset($hasPickupCols['pickup_name']) ? "COALESCE(u.name, o.pickup_name) as customer_name" : "COALESCE(u.name, '-') as customer_name";
$phone_expr = isset($hasPickupCols['pickup_phone']) ? "COALESCE(u.phone, o.pickup_phone) as phone" : "COALESCE(u.phone, '-') as phone";
$address_expr = isset($hasPickupCols['pickup_address']) ? "COALESCE(u.address, o.pickup_address) as address" : "COALESCE(u.address, '-') as address";
$pickup_dt_expr = isset($hasPickupCols['pickup_datetime']) ? "o.pickup_datetime" : "NULL as pickup_datetime";

// Also select raw pickup fields (or NULL aliases) so code can reference $order['pickup_name'] safely
$pickup_name_select = isset($hasPickupCols['pickup_name']) ? "o.pickup_name as pickup_name" : "NULL as pickup_name";
$pickup_phone_select = isset($hasPickupCols['pickup_phone']) ? "o.pickup_phone as pickup_phone" : "NULL as pickup_phone";
$pickup_address_select = isset($hasPickupCols['pickup_address']) ? "o.pickup_address as pickup_address" : "NULL as pickup_address";

$query = "SELECT o.*, {$customer_expr}, {$phone_expr}, {$address_expr}, {$pickup_dt_expr}, {$pickup_name_select}, {$pickup_phone_select}, {$pickup_address_select} 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE 1=1";

if ($filter_status) {
    $query .= " AND o.status = :status";
}

if ($search) {
    $query .= " AND (u.name LIKE :search OR o.id LIKE :search)";
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);

if ($filter_status) {
    $stmt->bindValue(':status', $filter_status);
}

if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-container">
    <h1 class="page-title">Kelola Pesanan</h1>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✅ <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
    <?php endif; ?>

    <div class="content-card">
        <div class="card-header">
            <h2>📦 Semua Pesanan</h2>
        </div>

        <!-- Filter Section -->
        <form method="GET" class="filter-section">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Cari nama pelanggan atau ID pesanan..."
                value="<?php echo htmlspecialchars($search); ?>"
            >
            
            <select name="filter_status" class="form-select">
                <option value="">Semua Status</option>
                <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                <option value="process" <?php echo $filter_status == 'process' ? 'selected' : ''; ?>>Diproses</option>
                <option value="washing" <?php echo $filter_status == 'washing' ? 'selected' : ''; ?>>Dicuci</option>
                <option value="drying" <?php echo $filter_status == 'drying' ? 'selected' : ''; ?>>Dikeringkan</option>
                <option value="ironing" <?php echo $filter_status == 'ironing' ? 'selected' : ''; ?>>Disetrika</option>
                <option value="ready" <?php echo $filter_status == 'ready' ? 'selected' : ''; ?>>Siap Diambil</option>
                <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
            </select>

            <button type="submit" class="btn-primary">🔍 Filter</button>
            <a href="orders.php" class="btn-warning">Reset</a>
            <a href="manual-order.php" class="btn btn-animate-admin" style="margin-left: 8px;">➕ Tambah Pesanan Manual</a>
        </form>

        <?php if (count($orders) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Telepon</th>
                        <th>Layanan</th>
                        <th>Berat</th>
                        <th>Total</th>
                        <th>Penjemputan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                        <td>
                            <?php 
                            $services = [
                                'cuci_setrika' => 'Cuci Setrika',
                                'cuci_kering' => 'Cuci Kering',
                                'setrika' => 'Setrika'
                            ];
                            echo $services[$order['service_type']] ?? $order['service_type']; 
                            ?>
                        </td>
                        <td><?php echo $order['weight']; ?> kg</td>
                        <td><strong>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></strong></td>
                        <td>
                            <?php
                            // Penjemputan (tampilkan ringkasan)
                            $pdt = $order['pickup_datetime'] ?? '';
                            $pname = $order['pickup_name'] ?? ($order['customer_name'] ?? '-');
                            $pphone = $order['pickup_phone'] ?? ($order['phone'] ?? '-');

                            if ($pdt) {
                                echo '<div style="font-weight:600;">' . date('d/m/Y H:i', strtotime($pdt)) . '</div>';
                            }
                            echo '<div style="font-size:0.9rem; color:#444;">' . htmlspecialchars($pname) . ' • ' . htmlspecialchars($pphone) . '</div>';
                            ?>
                        </td>

                        <td>
                            <?php
                            $status_class = 'status-' . $order['status'];
                            $status_text = [
                                'pending' => 'Menunggu',
                                'process' => 'Diproses',
                                'washing' => 'Dicuci',
                                'drying' => 'Dikeringkan',
                                'ironing' => 'Disetrika',
                                'ready' => 'Siap Diambil',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status_text[$order['status']] ?? $order['status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>">
                                    <button class="btn-primary btn-sm">Detail</button>
                                </a>
                                
                                <?php if ($order['status'] != 'completed' && $order['status'] != 'cancelled'): ?>
                                <button class="btn-success btn-sm" onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                    Update Status
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>Tidak ada pesanan</h3>
            <p>Belum ada pesanan yang sesuai dengan filter</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Update Status -->
<div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 400px; width: 90%;">
        <h3 style="margin-bottom: 1rem;">Update Status Pesanan</h3>
        <form method="POST">
            <input type="hidden" name="order_id" id="modal_order_id">
            <div class="form-group">
                <label>Status Baru:</label>
                <select name="status" id="modal_status" class="form-select" required>
                    <option value="pending">Menunggu</option>
                    <option value="process">Diproses</option>
                    <option value="washing">Dicuci</option>
                    <option value="drying">Dikeringkan</option>
                    <option value="ironing">Disetrika</option>
                    <option value="ready">Siap Diambil</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" name="update_status" class="btn-success" style="flex: 1;">Update</button>
                <button type="button" onclick="closeModal()" class="btn-danger" style="flex: 1;">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateStatus(orderId, currentStatus) {
    document.getElementById('modal_order_id').value = orderId;
    document.getElementById('modal_status').value = currentStatus;
    document.getElementById('statusModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('statusModal').style.display = 'none';
}
</script>

</body>
</html>