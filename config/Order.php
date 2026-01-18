<?php
class Order {
    private $user_id;
    private $service_type;
    private $weight;
    private $total_price;
    private $status;

    // Pickup information
    private $pickup_name;
    private $pickup_phone;
    private $pickup_address;
    private $pickup_datetime;

    public function __construct($user_id, $service_type, $weight, $total_price, $status = 'pending', $pickup_name = null, $pickup_phone = null, $pickup_address = null, $pickup_datetime = null) {
        $this->user_id = $user_id;
        $this->service_type = $service_type;
        $this->weight = $weight;
        $this->total_price = $total_price;
        $this->status = $status;

        $this->pickup_name = $pickup_name;
        $this->pickup_phone = $pickup_phone;
        $this->pickup_address = $pickup_address;
        $this->pickup_datetime = $pickup_datetime;
    }

    public function save() {
        global $conn;

        // First attempt: try to insert with pickup columns (if DB supports them)
        $pickup_cols_sql = ', pickup_name, pickup_phone, pickup_address, pickup_datetime';
        $pickup_values_placeholders = ', ?, ?, ?, ?';

        try {
            if ($this->user_id === null) {
                $sql = "INSERT INTO orders (user_id, service_type, weight, total_price, status" . $pickup_cols_sql . ", created_at, updated_at) VALUES (NULL, ?, ?, ?, ?" . $pickup_values_placeholders . ", NOW(), NOW())";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sddssss", $this->service_type, $this->weight, $this->total_price, $this->status, $this->pickup_name, $this->pickup_phone, $this->pickup_address, $this->pickup_datetime);
                }
            } else {
                $sql = "INSERT INTO orders (user_id, service_type, weight, total_price, status" . $pickup_cols_sql . ", created_at, updated_at) VALUES (?, ?, ?, ?, ?" . $pickup_values_placeholders . ", NOW(), NOW())";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("isddssss", $this->user_id, $this->service_type, $this->weight, $this->total_price, $this->status, $this->pickup_name, $this->pickup_phone, $this->pickup_address, $this->pickup_datetime);
                }
            }

            if ($stmt && $stmt->execute()) {
                $stmt->close();
                return true;
            }
        } catch (Exception $e) {
            // ignore, we'll try fallback
        }

        // Fallback: insert without pickup columns (for older DB schema)
        if ($this->user_id === null) {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, service_type, weight, total_price, status, created_at, updated_at) VALUES (NULL, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmt === false) {
                return false;
            }
            $stmt->bind_param("sdds", $this->service_type, $this->weight, $this->total_price, $this->status);
        } else {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, service_type, weight, total_price, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            if ($stmt === false) {
                return false;
            }
            $stmt->bind_param("isdds", $this->user_id, $this->service_type, $this->weight, $this->total_price, $this->status);
        }

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    // Ambil semua pesanan
    public static function all() {
        global $conn;
        $result = $conn->query("SELECT * FROM orders ORDER BY id DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Ambil pesanan user tertentu (pakai JOIN ke users supaya dapat name/address/phone)
    public static function getByUser($user_id) {
        global $conn;

        $sql = "SELECT 
                    o.id,
                    o.user_id,
                    u.name,
                    u.address,
                    u.phone,
                    o.service_type,
                    o.weight,
                    o.total_price,
                    o.status,
                    o.created_at,
                    o.updated_at
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.user_id = ?
                ORDER BY o.id DESC";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            // jika prepare gagal, kembalikan array kosong (atau kamu bisa log error)
            return [];
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res === false) {
            $stmt->close();
            return [];
        }
        $data = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
}
?>
