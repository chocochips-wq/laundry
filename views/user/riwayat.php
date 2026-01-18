<?php

include('../../includes/header.php');
include_once '../../config/db.php';
include_once '../../config/Order.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil semua pesanan user (Order::getByUser di Order.php sudah menggunakan JOIN users)
$orders = Order::getByUser($user_id);

// mapping badge (Bootstrap 5) dan label (Bahasa Indonesia)
$status_badges = [
    'pending'   => 'warning',
    'process'   => 'info',
    'washing'   => 'info',
    'drying'    => 'info',
    'ironing'   => 'info',
    'ready'     => 'primary',
    'completed' => 'success',
    'cancelled' => 'danger'
];

$status_labels = [
    'pending'   => 'Menunggu',
    'process'   => 'Diproses',
    'washing'   => 'Mencuci',
    'drying'    => 'Pengeringan',
    'ironing'   => 'Menyetrika',
    'ready'     => 'Siap',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
?>

<link rel="stylesheet" href="../../assets/css/order-history.css">

<div class="container mt-5 mb-5">
    <div class="order-header text-center">
        <h2 class="mb-2">📋 Riwayat & Status Pesanan</h2>
        <p class="mb-0 opacity-75">Lacak dan kelola pesanan laundry Anda</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✅ Berhasil!</strong> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error!</strong> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🧺</div>
            <h4 class="mb-3">Belum Ada Pesanan</h4>
            <p class="text-muted mb-4">Anda belum memiliki riwayat pesanan. Mulai pesan layanan laundry sekarang!</p>
            <a href="order.php" class="btn-order-now">🧺 Pesan Sekarang</a>
        </div>
    <?php else: ?>
        <div class="orders-container">
            <?php foreach ($orders as $order): ?>
                <?php
                    // Ambil data aman dengan fallback
                    $orderId     = htmlspecialchars($order['id'] ?? '-');
                    $name        = htmlspecialchars($order['name'] ?? $order['full_name'] ?? '-');
                    $address     = htmlspecialchars($order['address'] ?? '-');
                    $phone       = htmlspecialchars($order['phone'] ?? $order['phone_number'] ?? '-');
                    $serviceType = htmlspecialchars(ucwords(str_replace('_', ' ', $order['service_type'] ?? '-')));

                    // berat & harga
                    $weightRaw = $order['weight'] ?? null;
                    $weight = is_numeric($weightRaw) ? rtrim(rtrim(number_format((float)$weightRaw, 2, ',', '.'), '0'), ',') : '-';
                    $totalPrice = isset($order['total_price']) && is_numeric($order['total_price'])
                        ? number_format((float)$order['total_price'], 0, ',', '.')
                        : '-';

                    // Status: terima 'status' dulu, kalau tidak ada coba 'order_status' (alias)
                    $statusRaw = $order['status'] ?? $order['order_status'] ?? 'pending';
                    $statusKey = strtolower($statusRaw);
                    $statusBadge = $status_badges[$statusKey] ?? 'secondary';
                    $statusLabel = $status_labels[$statusKey] ?? ucfirst($statusKey);

                    // Tanggal aman
                    $createdAtRaw = $order['created_at'] ?? $order['created'] ?? null;
                    $createdAt = $createdAtRaw ? date('d M Y, H:i', strtotime($createdAtRaw)) : '-';
                ?>
                
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-id">Order #<?= $orderId ?></div>
                        <div class="order-date">📅 <?= $createdAt ?></div>
                    </div>
                    
                    <div class="order-card-body">
                        <div class="info-row">
                            <div class="info-item">
                                <div class="info-icon">👤</div>
                                <div class="info-content">
                                    <div class="info-label">Nama Pelanggan</div>
                                    <div class="info-value"><?= $name ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">📞</div>
                                <div class="info-content">
                                    <div class="info-label">No. Telepon</div>
                                    <div class="info-value"><?= $phone ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-item">
                                <div class="info-icon">📍</div>
                                <div class="info-content">
                                    <div class="info-label">Alamat</div>
                                    <div class="info-value"><?= $address ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-item">
                                <div class="info-icon">🧺</div>
                                <div class="info-content">
                                    <div class="info-label">Layanan</div>
                                    <div class="info-value"><?= $serviceType ?></div>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">⚖️</div>
                                <div class="info-content">
                                    <div class="info-label">Berat</div>
                                    <div class="info-value"><?= $weight ?><?= is_numeric(str_replace(',', '.', $weightRaw)) ? ' kg' : '' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-footer">
                        <div>
                            <span class="price-label">Total Harga:</span>
                            <span class="price-tag">Rp <?= $totalPrice ?></span>
                        </div>
                        <div>
                            <span class="status-badge bg-<?= $statusBadge ?>">
                                <?php
                                $statusIcons = [
                                    'pending' => '⏳',
                                    'process' => '🔄',
                                    'washing' => '🧼',
                                    'drying' => '💨',
                                    'ironing' => '👔',
                                    'ready' => '✅',
                                    'completed' => '✔️',
                                    'cancelled' => '❌'
                                ];
                                echo $statusIcons[$statusKey] ?? '📦';
                                ?>
                                <?= htmlspecialchars($statusLabel) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="order.php" class="btn-order-now">🧺 Pesan Lagi</a>
        </div>
    <?php endif; ?>
</div>

<?php include('../../includes/footer.php'); ?>