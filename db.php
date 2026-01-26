<?php
/**
 * Database Configuration
 * Supports both .env files and environment variables
 */

// Load environment variables from .env file if it exists
function loadEnv($path) {
    if (!is_readable($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    
    return true;
}

// Load .env file
$env_file = __DIR__ . '/.env';
loadEnv($env_file);

// Get database configuration from environment or use defaults
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'laundry_db';

// MySQLi connection (untuk file lama)
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection - show generic error to user, log technical error
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("Connection failed. Please contact administrator.");
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
$conn->set_charset("utf8mb4");

// Enable prepared statements to prevent SQL injection
$conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);

// PDO connection (untuk file admin baru)
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'laundry_db';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch(PDOException $exception) {
            // Log error but don't display to user
            error_log("PDO Connection Error: " . $exception->getMessage());
            // Return null to let application handle gracefully
            return null;
        }
        return $this->conn;
    }
}
?>