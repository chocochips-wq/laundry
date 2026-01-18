<?php
// migrate_finances.php - safe migration helper for finances tables
require_once __DIR__ . '/db.php';

$dbName = 'laundry_db';
try {
    $conn->query("CREATE TABLE IF NOT EXISTS finance_categories (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(128) NOT NULL,
        type ENUM('income','expense') NOT NULL DEFAULT 'expense',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_finance_cat_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $conn->query("CREATE TABLE IF NOT EXISTS finances (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        type ENUM('income','expense') NOT NULL,
        category_id INT UNSIGNED NULL,
        amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        transaction_date DATETIME NOT NULL,
        order_id INT UNSIGNED NULL,
        reference VARCHAR(255) NULL,
        note TEXT NULL,
        created_by INT UNSIGNED NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (transaction_date),
        INDEX (category_id),
        CONSTRAINT fk_finances_category FOREIGN KEY (category_id) REFERENCES finance_categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // seed a few categories if table is empty
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM finance_categories");
    $row = $res->fetch_assoc();
    if ($row && intval($row['cnt']) === 0) {
        $conn->query("INSERT INTO finance_categories (name, type) VALUES
            ('Pewangi','expense'),
            ('Deterjen','expense'),
            ('Plastik/Bek','expense'),
            ('Pendapatan Cuci','income')");
    }

    echo "Finances migration completed.";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage();
}
