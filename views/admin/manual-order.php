<?php
$page_title = 'Tambah Pesanan Manual';
include('../../includes/admin-header.php');
require_once('../../config/db.php');
require_once('../../config/Order.php');

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type = trim($_POST['service_type'] ?? '');
    $weight = trim($_POST['weight'] ?? '0');
    $total_price = trim($_POST['total_price'] ?? '0');
    $pickup_name = trim($_POST['pickup_name'] ?? '');
    $pickup_phone = trim($_POST['pickup_phone'] ?? '');
    $pickup_address = trim($_POST['pickup_address'] ?? '');
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $pickup_time = trim($_POST['pickup_time'] ?? '');
    $status = trim($_POST['status'] ?? 'pending');

    // Basic validation
    if (empty($service_type)) $errors[] = 'Silakan pilih jenis layanan.';
    if (empty($pickup_name)) $errors[] = 'Nama penerima wajib diisi.';
    if (empty($pickup_phone)) $errors[] = 'Nomor telepon wajib diisi.';

    // Combine date/time
    $pickup_datetime = null;
    if (!empty($pickup_date)) {
        $time_part = $pickup_time ?: '09:00';
        $dt = $pickup_date . ' ' . $time_part;
        $pickup_datetime = date('Y-m-d H:i:s', strtotime($dt));
    }

    // Convert numeric fields
    $weight = is_numeric($weight) ? floatval($weight) : 0;
    $total_price = is_numeric($total_price) ? floatval($total_price) : 0;

    if (empty($errors)) {
        $order = new Order(null, $service_type, $weight, $total_price, $status, $pickup_name, $pickup_phone, $pickup_address, $pickup_datetime);
        if ($order->save()) {
            // get last insert id from mysqli connection
            global $conn;
            $new_id = $conn->insert_id;
            header('Location: order-detail.php?id=' . intval($new_id) . '&success=Pesanan manual berhasil dibuat');
            exit();
        } else {
            $errors[] = 'Gagal menyimpan pesanan. Periksa log server.';
        }
    }
}

$services = [
    'cuci_setrika' => 'Cuci Setrika (Kiloan)',
    'cuci_kering' => 'Cuci Kering (Kiloan)',
    'setrika_pakaian' => 'Setrika Pakaian',
    'bedcover' => 'Bedcover',
    'selimut' => 'Selimut',
    'boneka' => 'Boneka',
    'balmut' => 'Bantal + Selimut (Balmut)',
    'sejadah_tebal' => 'Sejadah Tebal'
];
?>

<div class="admin-container">
    <h1 class="page-title">Tambah Pesanan Manual</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="content-card">
        <div class="card-header">
            <h2>📥 Form Input Pesanan (Manual)</h2>
        </div>

        <form method="POST">
            <div class="form-group mb-3">
                <label>Jenis Layanan</label>
                <select name="service_type" class="form-select" required>
                    <option value="">-- Pilih Layanan --</option>
                    <?php foreach ($services as $k=>$v): ?>
                        <option value="<?php echo $k; ?>" <?php echo (isset($service_type) && $service_type==$k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Berat (kg)</label>
                <input type="number" step="0.1" name="weight" class="form-control" value="<?php echo htmlspecialchars($weight ?? '0'); ?>">
            </div>

            <div class="form-group mb-3">
                <label>Total Harga (Rp)</label>
                <input type="number" name="total_price" class="form-control" value="<?php echo htmlspecialchars($total_price ?? '0'); ?>">
            </div>

            <hr>
            <h4>Informasi Penjemputan</h4>

            <div class="form-group mb-3">
                <label>Nama Penerima</label>
                <input type="text" name="pickup_name" class="form-control" value="<?php echo htmlspecialchars($pickup_name ?? ''); ?>" required>
            </div>

            <div class="form-group mb-3">
                <label>Telepon / WhatsApp</label>
                <input type="tel" name="pickup_phone" class="form-control" value="<?php echo htmlspecialchars($pickup_phone ?? ''); ?>" required>
            </div>

            <div class="form-group mb-3">
                <label>Alamat Penjemputan</label>
                <textarea name="pickup_address" class="form-control"><?php echo htmlspecialchars($pickup_address ?? ''); ?></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Tanggal Penjemputan</label>
                    <input type="date" name="pickup_date" class="form-control" value="<?php echo htmlspecialchars($pickup_date ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label>Waktu Penjemputan</label>
                    <input type="time" name="pickup_time" class="form-control" value="<?php echo htmlspecialchars($pickup_time ?? '09:00'); ?>">
                </div>
            </div>

            <div class="form-group mb-3">
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="pending" <?php echo (isset($status) && $status=='pending') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="process" <?php echo (isset($status) && $status=='process') ? 'selected' : ''; ?>>Diproses</option>
                    <option value="ready" <?php echo (isset($status) && $status=='ready') ? 'selected' : ''; ?>>Siap Diambil</option>
                    <option value="completed" <?php echo (isset($status) && $status=='completed') ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-animate-admin">Simpan & Buka Detail</button>
                <a href="orders.php" class="btn btn-cta">Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>