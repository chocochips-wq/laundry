<?php
$page_title = 'Kelola Harga';
include('../../includes/admin-header.php');
require_once('../../config/db.php');

// Update harga
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $service_id = $_POST['service_id'];
    $new_price = $_POST['price'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE services SET price = ? WHERE id = ?");
    $stmt->execute([$new_price, $service_id]);
    
    header('Location: pricelist.php?success=Harga berhasil diupdate');
    exit();
}

// Tambah layanan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $service_name = $_POST['service_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("INSERT INTO services (name, price, description) VALUES (?, ?, ?)");
    $stmt->execute([$service_name, $price, $description]);
    
    header('Location: pricelist.php?success=Layanan baru berhasil ditambahkan');
    exit();
}

// Hapus layanan
if (isset($_GET['delete'])) {
    $service_id = $_GET['delete'];
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    
    header('Location: pricelist.php?success=Layanan berhasil dihapus');
    exit();
}

// Ambil semua layanan
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT * FROM services ORDER BY id ASC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Jika tabel services belum ada data, buat data default
if (count($services) == 0) {
    $default_services = [
        ['name' => 'Cuci + Setrika', 'price' => 6000, 'description' => 'Layanan cuci dan setrika lengkap'],
        ['name' => 'Cuci Kering', 'price' => 4000, 'description' => 'Layanan cuci tanpa setrika'],
        ['name' => 'Setrika Saja', 'price' => 4000, 'description' => 'Layanan setrika saja']
    ];
    
    foreach ($default_services as $service) {
        $stmt = $conn->prepare("INSERT INTO services (name, price, description) VALUES (?, ?, ?)");
        $stmt->execute([$service['name'], $service['price'], $service['description']]);
    }
    
    header('Location: pricelist.php');
    exit();
}
?>

<div class="admin-container">
    <h1 class="page-title">Kelola Harga Layanan</h1>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✅ <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
    <?php endif; ?>

    <!-- Tambah Layanan Baru -->
    <div class="content-card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <h2>➕ Tambah Layanan Baru</h2>
        </div>

        <form method="POST" class="form-add-service">
            <div class="form-group">
                <label>Nama Layanan:</label>
                <input type="text" name="service_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Harga (Rp/kg):</label>
                <input type="number" name="price" class="form-control" min="0" step="500" required>
            </div>

            <div class="form-group">
                <label>Deskripsi:</label>
                <input type="text" name="description" class="form-control" required>
            </div>

            <button type="submit" name="add_service" class="btn-primary">Tambah</button>
        </form>
    </div>

    <!-- Daftar Harga -->
    <div class="content-card">
        <div class="card-header">
            <h2>💰 Daftar Harga Layanan</h2>
        </div>

        <div class="price-list-grid">
            <?php foreach ($services as $service): ?>
            <div class="price-item">
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p style="color: #666; font-size: 0.9rem; margin: 0.5rem 0;">
                    <?php echo htmlspecialchars($service['description']); ?>
                </p>
                
                <div class="price">
                    Rp <?php echo number_format($service['price'], 0, ',', '.'); ?>/kg
                </div>

                <form method="POST" style="margin-top: 1rem;">
                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                    <div class="form-group">
                        <label>Update Harga:</label>
                        <input 
                            type="number" 
                            name="price" 
                            class="form-control" 
                            value="<?php echo $service['price']; ?>"
                            min="0" 
                            step="500"
                            required
                        >
                    </div>

                    <div class="price-actions">
                        <button type="submit" name="update_price" class="btn-success">
                            💾 Update
                        </button>
                        <button 
                            type="button" 
                            onclick="if(confirm('Yakin hapus layanan ini?')) window.location='pricelist.php?delete=<?php echo $service['id']; ?>'" 
                            class="btn-danger"
                        >
                            🗑️ Hapus
                        </button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Info Tambahan -->
    <div class="content-card" style="margin-top: 2rem; background: #e3f2fd;">
        <h3 style="color: #1976d2; margin-bottom: 1rem;">💡 Tips Pengaturan Harga</h3>
        <ul style="color: #333; line-height: 1.8;">
            <li>Pastikan harga sudah termasuk biaya operasional (deterjen, listrik, dll)</li>
            <li>Pertimbangkan harga kompetitor di area Anda</li>
            <li>Berikan harga khusus untuk pelanggan setia atau pesanan dalam jumlah besar</li>
            <li>Update harga secara berkala mengikuti inflasi dan biaya operasional</li>
        </ul>
    </div>
</div>

</body>
</html>