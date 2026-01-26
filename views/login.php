<?php
session_start();

// Include security
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../db.php';

$error_message = "";

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Invalid request. Please try again.";
        logSecurityEvent('CSRF_TOKEN_MISMATCH', ['ip' => $_SERVER['REMOTE_ADDR']]);
    } else {
        // Check rate limiting
        $rate_limit_key = 'login_attempt_' . $_SERVER['REMOTE_ADDR'];
        if (!checkRateLimit($rate_limit_key, 5, 900)) { // 5 attempts per 15 minutes
            $error_message = "Too many login attempts. Please try again in 15 minutes.";
            logSecurityEvent('RATE_LIMIT_EXCEEDED', ['ip' => $_SERVER['REMOTE_ADDR']]);
        } else {
            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $error_message = "Please fill in all fields!";
                incrementRateLimit($rate_limit_key);
            } elseif (!validateEmail($email)) {
                $error_message = "Invalid email format!";
                incrementRateLimit($rate_limit_key);
            } else {
                // Use prepared statement to prevent SQL injection
                $stmt = $conn->prepare("SELECT id, name, email, password, role, phone, address FROM users WHERE email = ?");
                if (!$stmt) {
                    $error_message = displayError("An error occurred. Please try again.", $conn->error);
                    logSecurityEvent('DATABASE_ERROR', ['query' => 'login_select']);
                } else {
                    $stmt->bind_param("s", $email);
                    if (!$stmt->execute()) {
                        $error_message = displayError("An error occurred. Please try again.", $stmt->error);
                        logSecurityEvent('DATABASE_ERROR', ['query' => 'login_execute']);
                    } else {
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows === 1) {
                            $user = $result->fetch_assoc();
                            
                            if (password_verify($password, $user['password'])) {
                                // Regenerate session ID to prevent session fixation
                                session_regenerate_id(true);
                                
                                // Set role-specific session keys
                                if ($user['role'] === 'admin') {
                                    $_SESSION['admin_id'] = $user['id'];
                                    $_SESSION['admin_name'] = htmlspecialchars($user['name']);
                                    $_SESSION['admin_email'] = htmlspecialchars($user['email']);
                                    $_SESSION['admin_role'] = 'admin';
                                    $_SESSION['admin_phone'] = htmlspecialchars($user['phone'] ?? '');
                                    $_SESSION['admin_address'] = htmlspecialchars($user['address'] ?? '');
                                    
                                    logSecurityEvent('LOGIN_SUCCESS', ['user' => htmlspecialchars($user['email']), 'role' => 'admin']);
                                    
                                    // Redirect using safe redirect function
                                    header("Location: " . BASE_URL . "/views/admin/dashboard.php");
                                    exit();
                                } else {
                                    $_SESSION['user_id'] = $user['id'];
                                    $_SESSION['user_name'] = htmlspecialchars($user['name']);
                                    $_SESSION['user_email'] = htmlspecialchars($user['email']);
                                    $_SESSION['user_role'] = 'user';
                                    $_SESSION['user_phone'] = htmlspecialchars($user['phone'] ?? '');
                                    $_SESSION['user_address'] = htmlspecialchars($user['address'] ?? '');
                                    
                                    logSecurityEvent('LOGIN_SUCCESS', ['user' => htmlspecialchars($user['email']), 'role' => 'user']);
                                    
                                    // Redirect using safe redirect function
                                    header("Location: " . BASE_URL . "/index.php");
                                    exit();
                                }
                            } else {
                                $error_message = "Invalid email or password!";
                                incrementRateLimit($rate_limit_key);
                                logSecurityEvent('LOGIN_FAILED', ['email' => htmlspecialchars($email), 'reason' => 'invalid_password']);
                            }
                        } else {
                            $error_message = "Invalid email or password!";
                            incrementRateLimit($rate_limit_key);
                            logSecurityEvent('LOGIN_FAILED', ['email' => htmlspecialchars($email), 'reason' => 'user_not_found']);
                        }
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Berkah Laundry</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-box">
            <!-- Logo -->
            <div class="auth-logo">
                <span class="logo">🧺</span>
                <h1>Berkah Laundry</h1>
            </div>

            <!-- Header -->
            <div class="auth-header">
                <h2>Selamat Datang</h2>
                <p>Login untuk melanjutkan</p>
            </div>

            <!-- Alert -->
            <?php if ($error_message): ?>
                <div class="alert error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlEscape(getCSRFToken()); ?>">
                
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="your@email.com" required value="<?php echo isset($_POST['email']) ? htmlEscape(sanitizeInput($_POST['email'])) : ''; ?>">
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-footer">
                    <label class="remember">
                        <input type="checkbox">
                        <span>Ingat Saya</span>
                    </label>
                    <a href="#" class="forgot">Lupa password?</a>
                </div>

                <button type="submit" name="login" class="btn-submit">Login</button>
            </form>

            <!-- Register Link -->
            <div class="auth-link">
                Tidak Punya Akun? <a href="register.php">Daftar</a>
            </div>

            <div class="back-link">
                <a href="user/index.php">← Kembali Ke Home</a>
            </div>
    </div>

    <script>
        // Auto-hide alert
        const alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>