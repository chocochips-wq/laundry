<?php
$page_title = 'Kelola Pelanggan';
include('../../includes/admin-header.php');
require_once('../../config/db.php');

// Ambil data pelanggan
$db = new Database();
$conn = $db->getConnection();

$search = $_GET['search'] ?? '';

$query = "SELECT u.*, 
          COUNT(o.id) as total_orders,
          SUM(CASE WHEN o.status = 'completed' THEN o.total_price ELSE 0 END) as total_spent
          FROM users u
          LEFT JOIN orders o ON u.id = o.user_id
          WHERE u.role = 'user'";

if ($search) {
    $query .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $conn->prepare($query);

if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}

$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-container">
    <h1 class="page-title">Kelola Pelanggan</h1>

    <div class="content-card">
        <div class="card-header">
            <h2>👥 Semua Pelanggan</h2>
            <span style="color: white; background: #667eea; padding: 0.5rem 1rem; border-radius: 8px;">
                Total: <?php echo count($customers); ?> Pelanggan
            </span>
        </div>

        <!-- Search -->
        <form method="GET" style="margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 1rem;">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Cari nama, email, atau telepon..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    style="flex: 1;"
                >
                <button type="submit" class="btn-primary">🔍 Cari</button>
                <?php if ($search): ?>
                <a href="customers.php" class="btn-warning">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (count($customers) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Total Pesanan</th>
                        <th>Total Belanja</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><strong>#<?php echo $customer['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td>
                            <a href="tel:<?php echo $customer['phone']; ?>" style="color: #667eea;">
                                <?php echo htmlspecialchars($customer['phone']); ?>
                            </a>
                        </td>
                        <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($customer['address']); ?>
                        </td>
                        <td>
                            <span style="background: #e3f2fd; color: #2196f3; padding: 0.3rem 0.8rem; border-radius: 12px; font-weight: 600;">
                                <?php echo $customer['total_orders']; ?> pesanan
                            </span>
                        </td>
                        <td>
                            <strong style="color: #4caf50;">
                                Rp <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?>
                            </strong>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                        <td>
                            <button 
                                class="btn-primary btn-sm" 
                                onclick="viewCustomerDetail(<?php echo $customer['id']; ?>)"
                            >
                                Detail
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <h3>Tidak ada pelanggan</h3>
            <p>Belum ada pelanggan yang terdaftar atau sesuai pencarian</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Statistics -->
    <?php
    $total_customers = count($customers);
    $total_spent = array_sum(array_column($customers, 'total_spent'));
    $avg_spent = $total_customers > 0 ? $total_spent / $total_customers : 0;
    $total_orders = array_sum(array_column($customers, 'total_orders'));
    ?>

    <div class="stats-grid" style="margin-top: 2rem;">
        <div class="stat-card">
            <div class="stat-icon blue">👥</div>
            <div class="stat-info">
                <h3>Total Pelanggan</h3>
                <div class="stat-value"><?php echo $total_customers; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">📦</div>
            <div class="stat-info">
                <h3>Total Pesanan</h3>
                <div class="stat-value"><?php echo $total_orders; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">💰</div>
            <div class="stat-info">
                <h3>Total Pendapatan</h3>
                <div class="stat-value">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">📊</div>
            <div class="stat-info">
                <h3>Rata-rata Belanja</h3>
                <div class="stat-value">Rp <?php echo number_format($avg_spent, 0, ',', '.'); ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Customer -->
<div id="customerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
    <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 600px; width: 90%; margin: 2rem auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0;">Detail Pelanggan</h2>
            <button onclick="closeCustomerModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999;">×</button>
        </div>
        
        <div id="customerDetailContent">
            <div class="loading">
                <div class="spinner"></div>
                <p>Memuat data...</p>
            </div>
        </div>
    </div>
</div>

<script>
function viewCustomerDetail(customerId) {
    document.getElementById('customerModal').style.display = 'flex';
    
    // Fetch customer detail via AJAX
    fetch(`get-customer-detail.php?id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div style="display: grid; gap: 1rem;">
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                            <strong>Nama:</strong>
                            <p style="margin: 0.5rem 0 0 0;">${data.customer.name}</p>
                        </div>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                            <strong>Email:</strong>
                            <p style="margin: 0.5rem 0 0 0;">${data.customer.email}</p>
                        </div>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                            <strong>Telepon:</strong>
                            <p style="margin: 0.5rem 0 0 0;">${data.customer.phone}</p>
                        </div>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                            <strong>Alamat:</strong>
                            <p style="margin: 0.5rem 0 0 0; line-height: 1.6;">${data.customer.address}</p>
                        </div>
                        <div style="background: #e8f5e9; padding: 1rem; border-radius: 8px;">
                            <strong>Total Pesanan:</strong>
                            <p style="margin: 0.5rem 0 0 0; font-size: 1.2rem; color: #4caf50;">${data.customer.total_orders} pesanan</p>
                        </div>
                        <div style="background: #e8f5e9; padding: 1rem; border-radius: 8px;">
                            <strong>Total Belanja:</strong>
                            <p style="margin: 0.5rem 0 0 0; font-size: 1.2rem; color: #4caf50;">Rp ${new Intl.NumberFormat('id-ID').format(data.customer.total_spent)}</p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1.5rem;">
                        <h3 style="margin-bottom: 1rem;">Riwayat Pesanan Terakhir</h3>
                        ${data.orders.length > 0 ? `
                            <div style="max-height: 300px; overflow-y: auto;">
                                ${data.orders.map(order => `
                                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 0.5rem;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <strong>#${order.id}</strong>
                                            <span class="status-badge status-${order.status}">${order.status_text}</span>
                                        </div>
                                        <div style="font-size: 0.9rem; color: #666;">
                                            ${order.service_type} - ${order.weight} kg
                                        </div>
                                        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                                            <span style="color: #4caf50; font-weight: 600;">Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</span>
                                            <span style="font-size: 0.85rem; color: #999;">${order.date}</span>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : '<p style="text-align: center; color: #999; padding: 2rem;">Belum ada pesanan</p>'}
                    </div>
                `;
                document.getElementById('customerDetailContent').innerHTML = html;
            } else {
                document.getElementById('customerDetailContent').innerHTML = '<p style="text-align: center; color: #f44336;">Gagal memuat data pelanggan</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('customerDetailContent').innerHTML = '<p style="text-align: center; color: #f44336;">Terjadi kesalahan saat memuat data</p>';
        });
}

function closeCustomerModal() {
    document.getElementById('customerModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('customerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCustomerModal();
    }
});
</script>

</body>
</html>