<?php
// Safe migration script to add pickup columns to 'orders' table
// Run this file from browser or CLI: php migrate_add_pickup_columns.php
// IMPORTANT: BACKUP YOUR DATABASE BEFORE RUNNING.

require_once __DIR__ . '/db.php'; // provides $conn (mysqli)

$columns = [
    'pickup_name' => "VARCHAR(255) NULL",
    'pickup_phone' => "VARCHAR(50) NULL",
    'pickup_address' => "TEXT NULL",
    'pickup_datetime' => "DATETIME NULL"
];

$dbname = $conn->real_escape_string((new ReflectionProperty($conn, 'connect_errno'))->getDeclaringClass()->name) ?? '';
// Simpler: use DB name from db.php
$dbname = 'laundry_db';

$added = [];
$skipped = [];
$errors = [];

foreach ($columns as $col => $type) {
    $checkSql = "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'orders' AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('ss', $dbname, $col);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row && $row['cnt'] > 0) {
        $skipped[] = $col;
        continue;
    }

    $sql = "ALTER TABLE `orders` ADD COLUMN `{$col}` {$type};";
    if ($conn->query($sql) === TRUE) {
        $added[] = $col;
    } else {
        $errors[$col] = $conn->error;
    }
}

echo "<h2>Migration result</h2>";
echo "<p>Added: " . implode(', ', $added) . "</p>";
echo "<p>Skipped (already exist): " . implode(', ', $skipped) . "</p>";
if (!empty($errors)) {
    echo "<p>Errors:</p><pre>" . print_r($errors, true) . "</pre>";
} else {
    echo "<p>No errors.</p>";
}

echo "<p><strong>Note:</strong> Backup DB before running migrations; you can also run the SQL file in <code>db/migrations/20251224_add_pickup_columns.sql</code>.</p>";

?>