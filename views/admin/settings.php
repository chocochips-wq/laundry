<?php
$page_title = 'Pengaturan';
include('../../includes/admin-header.php');
require_once('../../config/db.php');

$db = new Database();
$conn = $db->getConnection();

// Update informasi toko
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $store_name = $_POST['store_name'];
    $store_phone = $_POST['store_phone'];
    $store_address = $_POST['store_address'];
    $store_email = $_POST['store_email'];
    $open_hours = $_POST['open_hours'];
    
    // Simpan ke database atau file konfigurasi
    // Untuk contoh ini, kita simpan ke tabel settings
    $settings = [
        'store_name' => $store_name,
        'store_phone' => $store_phone,
        'store_address' => $store_address,
        'store_email' => $store_email,
        'open_hours' => $open_hours
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    
    header('Location: settings.php?success=Informasi toko berhasil diupdate');
    exit();
}

// Update password admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        header('Location: settings.php?error=Password baru tidak cocok');
        exit();
    }
    
    $admin_id = $_SESSION['admin_id'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($current_password, $admin['password'])) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $admin_id]);
        
        header('Location: settings.php?success=Password berhasil diupdate');
        exit();
    } else {
        header('Location: settings.php?error=Password lama tidak sesuai');
        exit();
    }
}

$stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
$settings_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$store_name = $settings_data['store_name'] ?? 'Berkah Laundry';
$store_phone = $settings_data['store_phone'] ?? '0812-3456-7890';
$store_address = $settings_data['store_address'] ?? 'Jl. Contoh No. 123';
$store_email = $settings_data['store_email'] ?? 'info@berkahlaundry.com';
$open_hours = $settings_data['open_hours'] ?? 'Senin - Sabtu: 08.00 - 20.00';
?>

<div class="admin-container">
    <h1 class="page-title">Pengaturan</h1>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        ✅ <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        ❌ <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
    <?php endif; ?>

    <div style="display: grid; gap: 1.5rem;">
        <!-- Informasi Toko -->
        <div class="content-card">
            <div class="card-header">
                <h2>🏪 Informasi Toko</h2>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Nama Toko:</label>
                    <input 
                        type="text" 
                        name="store_name" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($store_name); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Nomor Telepon:</label>
                    <input 
                        type="tel" 
                        name="store_phone" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($store_phone); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input 
                        type="email" 
                        name="store_email" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($store_email); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Alamat:</label>
                    <textarea 
                        name="store_address" 
                        class="form-control" 
                        rows="3"
                        required
                    ><?php echo htmlspecialchars($store_address); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Jam Operasional:</label>
                    <input 
                        type="text" 
                        name="open_hours" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($open_hours); ?>"
                        placeholder="Contoh: Senin - Sabtu: 08.00 - 20.00"
                        required
                    >
                </div>

                <button type="submit" name="update_info" class="btn-success">
                    💾 Simpan Perubahan
                </button>
            </form>
        </div>

        <!-- Ganti Password -->
        <div class="content-card">
            <div class="card-header">
                <h2>🔐 Ganti Password</h2>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Password Lama:</label>
                    <input 
                        type="password" 
                        name="current_password" 
                        class="form-control" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Password Baru:</label>
                    <input 
                        type="password" 
                        name="new_password" 
                        class="form-control" 
                        minlength="6"
                        required
                    >
                    <small style="color: #666;">Minimal 6 karakter</small>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password Baru:</label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        class="form-control" 
                        minlength="6"
                        required
                    >
                </div>

                <button type="submit" name="update_password" class="btn-success">
                    🔒 Update Password
                </button>
            </form>
        </div>

        <!-- Informasi Sistem -->
        <div class="content-card">
            <div class="card-header">
                <h2>ℹ️ Informasi Sistem</h2>
            </div>

            <div style="display: grid; gap: 1rem;">
                <div class="settings-info-grid">
                    <strong>Versi Sistem:</strong>
                    <span>1.0.0</span>
                </div>

                <div class="settings-info-grid">
                    <strong>Nama Admin:</strong>
                    <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                </div>

                <div class="settings-info-grid">
                    <strong>Email Admin:</strong>
                    <span><?php echo htmlspecialchars($_SESSION['admin_email'] ?? '-'); ?></span>
                </div>

                <div class="settings-info-grid">
                    <strong>Role:</strong>
                    <span style="background: #667eea; color: white; padding: 0.3rem 0.8rem; border-radius: 12px; display: inline-block; width: fit-content;">
                        Administrator
                    </span>
                </div>
            </div>
        </div>

        <!-- Backup & Maintenance -->
        <div class="content-card">
            <div class="card-header">
                <h2>🔧 Maintenance</h2>
            </div>

            <div style="display: grid; gap: 1rem;">
                <div class="maintenance-card" style="background: #fff3e0; border-left-color: #ff9800;">
                    <h4 style="color: #f57c00;">⚠️ Backup Database</h4>
                    <p>
                        Disarankan untuk melakukan backup database secara berkala untuk menghindari kehilangan data.
                    </p>
                    <button class="btn-warning" onclick="alert('Fitur backup akan segera hadir!')">
                        💾 Backup Sekarang
                    </button>
                </div>

                <div class="maintenance-card" style="background: #ffebee; border-left-color: #f44336;">
                    <h4 style="color: #c62828;">🗑️ Hapus Data Lama</h4>
                    <p>
                        Hapus data pesanan yang sudah lebih dari 1 tahun untuk mengoptimalkan database.
                    </p>
                    <button class="btn-danger" onclick="if(confirm('Yakin hapus data lama? Tindakan ini tidak dapat dibatalkan!')) alert('Fitur ini akan segera tersedia')">
                        🗑️ Hapus Data Lama
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>