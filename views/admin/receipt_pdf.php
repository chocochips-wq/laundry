<?php
// receipt_pdf.php - generate PDF (if dompdf available) or show print-friendly HTML
require_once('../../config/db.php');

$order_id = $_GET['id'] ?? 0;
if (!$order_id) {
    echo "Invalid order ID";
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Defensive pickup column check (reuse pattern)
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
    echo "Order not found";
    exit;
}

// Render receipt HTML (buffer)
ob_start();
include __DIR__ . '/receipt.php';
$html = ob_get_clean();

// Try to generate PDF using Dompdf if available
if (class_exists('Dompdf\Dompdf')) {
    try {
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A6', 'portrait'); // small receipt-like paper
        $dompdf->render();
        $output = $dompdf->output();
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="struk_' . $order_id . '.pdf"');
        echo $output;
        exit;
    } catch (Exception $e) {
        // fallback to HTML
        echo $html;
        exit;
    }
} else {
    // Dompdf not installed; show HTML with a clear note and a print button
    echo "<div style='max-width: 720px; margin: 12px auto; font-family: sans-serif;'>";
    echo "<div style='background:#fff;padding:12px;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,0.08);'>";
    echo "<p><strong>Note:</strong> Library <code>dompdf</code> tidak terpasang. Struk ditampilkan sebagai HTML; gunakan <em>Print</em> di browser atau pasang Dompdf untuk download PDF otomatis.</p>";
    echo "<div style='margin-top:10px;'>";
    echo "<a href='#' onclick='window.print();return false;' class='btn btn-animate-admin'>Print / Save as PDF</a> ";
    echo "<a href='receipt_pdf.php?id={$order_id}&raw=1' class='btn btn-cta' style='margin-left:8px;'>Buka HTML</a>";
    echo "</div>";
    echo "</div>";
    echo $html;
    echo "</div>";
    exit;
}
