<?php
$page_title = 'Dashboard';
include('../../includes/admin-header.php');
require_once('../../config/db.php');

// Ambil statistik
$db = new Database();
$conn = $db->getConnection();

// Total pesanan
$stmt = $conn->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total pelanggan
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pesanan pending
$stmt = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total pendapatan
$stmt = $conn->query("SELECT SUM(total_price) as total FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Pesanan terbaru
$stmt = $conn->query("SELECT o.*, u.name as customer_name 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC 
                       LIMIT 10");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-container">
    <h1 class="page-title">Dashboard</h1>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">📦</div>
            <div class="stat-info">
                <h3>Total Pesanan</h3>
                <div class="stat-value"><?php echo $total_orders; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">👥</div>
            <div class="stat-info">
                <h3>Total Pelanggan</h3>
                <div class="stat-value"><?php echo $total_customers; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">⏳</div>
            <div class="stat-info">
                <h3>Pesanan Pending</h3>
                <div class="stat-value"><?php echo $pending_orders; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">💰</div>
            <div class="stat-info">
                <h3>Total Pendapatan</h3>
                <div class="stat-value">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="content-card">
        <div class="card-header">
            <h2>📋 Pesanan Terbaru</h2>
            <a href="orders.php" class="btn-primary">Lihat Semua</a>
        </div>

        <?php if (count($recent_orders) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Berat</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
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
                        <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
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
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>">
                                <button class="btn-primary btn-sm">Detail</button>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>Belum ada pesanan</h3>
            <p>Pesanan dari pelanggan akan muncul di sini</p>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>