<?php
// receipt.php - simple receipt template (expects $order array available)
if (!isset($order) || empty($order)) {
    echo "<p>Order data tidak tersedia.</p>";
    exit;
}

$company = [
    'name' => 'Berkah Laundry',
    'address' => "Cluster Mandala, Blok B No.15\nRt.11/Rw.26 Wanasari\nCibitung, Bekasi",
    'phone' => '0815-8624-4181'
];

$invoice = 'INV-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
$datetime = !empty($order['created_at']) ? date('d M Y H:i', strtotime($order['created_at'])) : '';
$pickup_dt = !empty($order['pickup_datetime']) ? date('d M Y H:i', strtotime($order['pickup_datetime'])) : '';

?><!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk - <?php echo $invoice; ?></title>
    <link rel="stylesheet" href="../../assets/css/receipt.css">
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h1><?php echo htmlspecialchars($company['name']); ?></h1>
            <div class="meta">
                <div><?php echo nl2br(htmlspecialchars($company['address'])); ?></div>
                <div>WA: <?php echo htmlspecialchars($company['phone']); ?></div>
            </div>
        </div>

        <div class="receipt-info">
            <div><strong>Nomor:</strong> <?php echo $invoice; ?></div>
            <div><strong>Tanggal:</strong> <?php echo $datetime; ?></div>
            <div><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></div>
        </div>

        <div class="receipt-customer">
            <h3>Pelanggan</h3>
            <div><strong>Nama:</strong> <?php echo htmlspecialchars($order['pickup_name'] ?? ($order['customer_name'] ?? '-')); ?></div>
            <div><strong>Telp:</strong> <?php echo htmlspecialchars($order['pickup_phone'] ?? ($order['phone'] ?? '-')); ?></div>
            <div><strong>Alamat:</strong> <?php echo nl2br(htmlspecialchars($order['pickup_address'] ?? ($order['address'] ?? '-'))); ?></div>
            <?php if ($pickup_dt): ?>
            <div><strong>Jadwal Jemput:</strong> <?php echo $pickup_dt; ?> WIB</div>
            <?php endif; ?>
        </div>

        <div class="receipt-items">
            <h3>Rincian</h3>
            <div class="row">
                <div class="label">Layanan</div>
                <div class="value"><?php echo htmlspecialchars($order['service_type'] ?? ''); ?></div>
            </div>
            <div class="row">
                <div class="label">Berat</div>
                <div class="value"><?php echo !empty($order['weight']) ? htmlspecialchars($order['weight']) . ' kg' : '-'; ?></div>
            </div>
            <div class="row total">
                <div class="label">Total</div>
                <div class="value">Rp <?php echo number_format($order['total_price'] ?? 0, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="receipt-footer">
            <p>Terima kasih telah menggunakan layanan kami.</p>
            <p>Hubungi kami di WA: <?php echo htmlspecialchars($company['phone']); ?></p>
        </div>
    </div>
</body>
</html>