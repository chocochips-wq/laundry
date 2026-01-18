<?php
$page_title = 'Detail Pesanan';
include('../../includes/admin-header.php');
require_once('../../config/db.php');

$order_id = $_GET['id'] ?? 0;

// Ambil detail pesanan
$db = new Database();
$conn = $db->getConnection();

// Check pickup columns
$hasPickupCols = [];
try {
    $colStmt = $conn->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'orders' AND COLUMN_NAME IN ('pickup_name','pickup_phone','pickup_address','pickup_datetime')");
    $colStmt->execute([':db' => 'laundry_db']);
    $availableCols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
    $hasPickupCols = array_flip($availableCols ?: []);
} catch (Exception $e) {
    $hasPickupCols = [];
}

$customer_expr = isset($hasPickupCols['pickup_name']) ? "COALESCE(u.name, o.pickup_name) as customer_name" : "COALESCE(u.name, '-') as customer_name";
$phone_expr = isset($hasPickupCols['pickup_phone']) ? "COALESCE(u.phone, o.pickup_phone) as phone" : "COALESCE(u.phone, '-') as phone";
$address_expr = isset($hasPickupCols['pickup_address']) ? "COALESCE(u.address, o.pickup_address) as address" : "COALESCE(u.address, '-') as address";
$pickup_dt_expr = isset($hasPickupCols['pickup_datetime']) ? "o.pickup_datetime" : "NULL as pickup_datetime";

$pickup_name_select = isset($hasPickupCols['pickup_name']) ? "o.pickup_name as pickup_name" : "NULL as pickup_name";
$pickup_phone_select = isset($hasPickupCols['pickup_phone']) ? "o.pickup_phone as pickup_phone" : "NULL as pickup_phone";
$pickup_address_select = isset($hasPickupCols['pickup_address']) ? "o.pickup_address as pickup_address" : "NULL as pickup_address";

$sql = "SELECT o.*, {$customer_expr}, COALESCE(u.email, '') as email, {$phone_expr}, {$address_expr}, {$pickup_dt_expr}, {$pickup_name_select}, {$pickup_phone_select}, {$pickup_address_select} 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php?error=Pesanan tidak ditemukan');
    exit();
}

// Update status jika ada form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    
    header('Location: order-detail.php?id=' . $order_id . '&success=Status berhasil diupdate');
    exit();
}

$status_text = [
    'pending' => 'Menunggu Pickup',
    'process' => 'Sedang Diproses',
    'washing' => 'Sedang Dicuci',
    'drying' => 'Sedang Dikeringkan',
    'ironing' => 'Sedang Disetrika',
    'ready' => 'Siap Diambil',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

$services = [
    'cuci_setrika' => 'Cuci + Setrika',
    'cuci_kering' => 'Cuci Kering',
    'setrika' => 'Setrika Saja'
];
?>

<div class="admin-container">
    <div style="margin-bottom: 1rem; display:flex; align-items:center; gap:12px;">
        <a href="orders.php" style="color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            ← Kembali ke Daftar Pesanan
        </a>
        <div style="margin-left:auto;">
            <a href="receipt_pdf.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn btn-animate-admin">Cetak Struk (PDF)</a>
        </div>
    </div>

    <h1 class="page-title">Detail Pesanan #<?php echo $order['id']; ?></h1>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✅ <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Detail Pesanan -->
        <div class="content-card">
            <div class="card-header">
                <h2>📋 Informasi Pesanan</h2>
                <span class="status-badge status-<?php echo $order['status']; ?>">
                    <?php echo $status_text[$order['status']]; ?>
                </span>
            </div>

            <div style="display: grid; gap: 1rem;">
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <strong>ID Pesanan:</strong>
                    <span>#<?php echo $order['id']; ?></span>
                </div>

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <strong>Jenis Layanan:</strong>
                    <span><?php echo $services[$order['service_type']]; ?></span>
                </div>

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <strong>Berat Cucian:</strong>
                    <span><?php echo !empty($order['weight']) ? htmlspecialchars($order['weight']) . ' kg' : '-'; ?></span>
                </div>

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <strong>Harga per kg:</strong>
                    <?php if (!empty($order['weight'])): ?>
                        <span>Rp <?php echo number_format($order['total_price'] / $order['weight'], 0, ',', '.'); ?></span>
                    <?php else: ?>
                        <span>-</span>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1rem; padding: 1rem; background: #e8f5e9; border-radius: 8px;">
                    <strong>Total Harga:</strong>
                    <span style="font-size: 1.3rem; color: #4caf50; font-weight: 700;">
                        Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?>
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <strong>Tanggal Pesanan:</strong>
                    <span><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?> WIB</span>
                </div>

                <!-- Pickup Info -->
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1rem; padding: 1rem; background: #fff8e1; border-radius: 8px;">
                    <strong>Jadwal Penjemputan:</strong>
                    <div>
                        <div style="font-weight:700;"><?php echo htmlspecialchars($order['pickup_name'] ?? ($order['customer_name'] ?? '-')); ?></div>
                        <div><?php echo htmlspecialchars($order['pickup_phone'] ?? ($order['phone'] ?? '-')); ?></div>
                        <div style="margin-top:0.5rem; color:#444;"><?php echo nl2br(htmlspecialchars($order['pickup_address'] ?? ($order['address'] ?? '-'))); ?></div>
                        <?php if (!empty($order['pickup_datetime'])): ?>
                            <div style="margin-top:0.5rem; font-weight:600;"><?php echo date('d F Y, H:i', strtotime($order['pickup_datetime'])) . ' WIB'; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <strong>Terakhir Update:</strong>
                    <span><?php echo date('d F Y, H:i', strtotime($order['updated_at'])); ?> WIB</span>
                </div>
            </div>
        </div>

        <!-- Info Pelanggan & Update Status -->
        <div>
            <!-- Info Pelanggan -->
            <div class="content-card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h2>👤 Pelanggan</h2>
                </div>

                <div style="display: grid; gap: 0.8rem;">
                    <div>
                        <strong style="color: #666; font-size: 0.9rem;">Nama:</strong>
                        <p style="margin: 0.3rem 0 0 0; font-size: 1rem;"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                    </div>

                    <div>
                        <strong style="color: #666; font-size: 0.9rem;">Email:</strong>
                        <p style="margin: 0.3rem 0 0 0;"><?php echo htmlspecialchars($order['email']); ?></p>
                    </div>

                    <div>
                        <strong style="color: #666; font-size: 0.9rem;">Telepon:</strong>
                        <p style="margin: 0.3rem 0 0 0;">
                            <a href="tel:<?php echo $order['phone']; ?>" style="color: #667eea;">
                                <?php echo htmlspecialchars($order['phone']); ?>
                            </a>
                        </p>
                    </div>

                    <div>
                        <strong style="color: #666; font-size: 0.9rem;">Alamat:</strong>
                        <p style="margin: 0.3rem 0 0 0; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Update Status -->
            <?php if ($order['status'] != 'completed' && $order['status'] != 'cancelled'): ?>
            <div class="content-card">
                <div class="card-header">
                    <h2>🔄 Update Status</h2>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label>Status Baru:</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Menunggu Pickup</option>
                            <option value="process" <?php echo $order['status'] == 'process' ? 'selected' : ''; ?>>Sedang Diproses</option>
                            <option value="washing" <?php echo $order['status'] == 'washing' ? 'selected' : ''; ?>>Sedang Dicuci</option>
                            <option value="drying" <?php echo $order['status'] == 'drying' ? 'selected' : ''; ?>>Sedang Dikeringkan</option>
                            <option value="ironing" <?php echo $order['status'] == 'ironing' ? 'selected' : ''; ?>>Sedang Disetrika</option>
                            <option value="ready" <?php echo $order['status'] == 'ready' ? 'selected' : ''; ?>>Siap Diambil</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>

                    <button type="submit" name="update_status" class="btn-success" style="width: 100%;">
                        Update Status
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>