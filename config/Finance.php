<?php
class Finance {
    public $id;
    public $type;
    public $category_id;
    public $amount;
    public $transaction_date;
    public $order_id;
    public $reference;
    public $note;
    public $created_by;

    public function __construct($type, $amount, $transaction_date, $category_id = null, $order_id = null, $reference = null, $note = null, $created_by = null) {
        $this->type = $type;
        $this->amount = $amount;
        $this->transaction_date = $transaction_date;
        $this->category_id = $category_id;
        $this->order_id = $order_id;
        $this->reference = $reference;
        $this->note = $note;
        $this->created_by = $created_by;
    }

    public function save() {
        global $conn;
        if (!self::tableExists('finances')) return false; // defensive

        // Insert new record
        if (empty($this->id)) {
            $stmt = $conn->prepare("INSERT INTO finances (type, amount, transaction_date, category_id, order_id, reference, note, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt === false) return false;
            $stmt->bind_param("sdsiisss", $this->type, $this->amount, $this->transaction_date, $this->category_id, $this->order_id, $this->reference, $this->note, $this->created_by);
            $res = $stmt->execute();
            if ($res) $this->id = $conn->insert_id;
            $stmt->close();
            return $res;
        }

        // Update existing record
        $stmt = $conn->prepare("UPDATE finances SET type = ?, amount = ?, transaction_date = ?, category_id = ?, order_id = ?, reference = ?, note = ? WHERE id = ?");
        if ($stmt === false) return false;
        $stmt->bind_param("sdsiissi", $this->type, $this->amount, $this->transaction_date, $this->category_id, $this->order_id, $this->reference, $this->note, $this->id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    private static function tableExists($table) {
        global $conn;
        $table = $conn->real_escape_string($table);
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result === false) return false;
        $exists = ($result->num_rows > 0);
        return $exists;
    }

    // Public helper to check if finance tables are installed
    public static function hasFinancesTable() {
        return self::tableExists('finances');
    }

    public static function getById($id) {
        global $conn;
        if (!self::tableExists('finances')) return null;

        $stmt = $conn->prepare("SELECT f.*, c.name as category_name FROM finances f LEFT JOIN finance_categories c ON f.category_id = c.id WHERE f.id = ? LIMIT 1");
        if ($stmt === false) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $data;
    }

    public static function all($filters = []) {
        global $conn;
        if (!self::tableExists('finances')) return [];

        $sql = "SELECT f.*, c.name as category_name FROM finances f LEFT JOIN finance_categories c ON f.category_id = c.id WHERE 1=1";
        $params = [];
        if (!empty($filters['type'])) {
            $sql .= " AND f.type = '" . $conn->real_escape_string($filters['type']) . "'";
        }
        if (!empty($filters['from'])) {
            $sql .= " AND f.transaction_date >= '" . $conn->real_escape_string($filters['from']) . "'";
        }
        if (!empty($filters['to'])) {
            $sql .= " AND f.transaction_date <= '" . $conn->real_escape_string($filters['to']) . "'";
        }
        $sql .= " ORDER BY f.transaction_date DESC";
        $res = $conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function delete($id) {
        global $conn;
        if (!self::tableExists('finances')) return false;
        $stmt = $conn->prepare("DELETE FROM finances WHERE id = ?");
        if ($stmt === false) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public static function totals($from = null, $to = null) {
        global $conn;
        if (!self::tableExists('finances')) return ['total_income' => 0, 'total_expense' => 0];

        $sql = "SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as total_income, SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as total_expense FROM finances WHERE 1=1";
        if ($from) $sql .= " AND transaction_date >= '" . $conn->real_escape_string($from) . "'";
        if ($to) $sql .= " AND transaction_date <= '" . $conn->real_escape_string($to) . "'";
        $res = $conn->query($sql);
        return $res ? $res->fetch_assoc() : ['total_income' => 0, 'total_expense' => 0];
    }
}
