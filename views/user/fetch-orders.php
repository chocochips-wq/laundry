<?php
session_start();
include_once '../../config/db.php';
include_once '../../config/Order.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger'>User tidak ditemukan.</div>";
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = Order::getByUser($user_id);

if (empty($orders)) {
    echo "<div class='alert alert-info text-center'>Belum ada pesanan.</div>";
    exit();
}

echo '<table class="table table-striped table-bordered">';
echo '<thead class="table-dark">';
echo '<tr><th>ID Pesanan</th><th>Layanan</th><th>Berat (kg)</th><th>Total Harga</th><th>Status</th></tr>';
echo '</thead><tbody>';

foreach ($orders as $order) {
    echo '<tr>';
    echo '<td>'.htmlspecialchars($order['id']).'</td>';
    echo '<td>'.htmlspecialchars(ucwords(str_replace('_',' ',$order['service_type']))).'</td>';
    echo '<td>'.htmlspecialchars($order['weight']).'</td>';
    echo '<td>Rp '.number_format($order['total_price'],0,',','.').'</td>';
    echo '<td>'.htmlspecialchars($order['status'] ?? 'Pending').'</td>';
    echo '</tr>';
}

echo '</tbody></table>';
