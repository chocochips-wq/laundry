<?php
session_start();

// Include database connection
require_once __DIR__ . '/../config/db.php';

// Note: allow simultaneous admin and user logins in different tabs by using role-specific session keys.
// Do not auto-redirect here so users can sign in as different roles in the same browser session.


$error_message = "";

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields!";
    } else {
        // Query dengan MySQLi
        $stmt = $conn->prepare("SELECT id, name, email, password, role, phone, address FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set role-specific session keys so admin and users can be logged in simultaneously
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_role'] = 'admin';
                    $_SESSION['admin_phone'] = $user['phone'] ?? '';
                    $_SESSION['admin_address'] = $user['address'] ?? '';

                    // Redirect ke dashboard admin
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = 'user';
                    $_SESSION['user_phone'] = $user['phone'] ?? '';
                    $_SESSION['user_address'] = $user['address'] ?? '';

                    // Redirect ke home user
                    header("Location: user/home.php");
                    exit();
                }
            } else {
                $error_message = "Invalid email or password!";
            }
        } else {
            $error_message = "Invalid email or password!";
        }
        $stmt->close();
    }
}
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
                <h2>Welcome Back</h2>
                <p>Login to your account</p>
            </div>

            <!-- Alert -->
            <?php if ($error_message): ?>
                <div class="alert error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="your@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-footer">
                    <label class="remember">
                        <input type="checkbox">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot">Forgot password?</a>
                </div>

                <button type="submit" name="login" class="btn-submit">Login</button>
            </form>

            <!-- Register Link -->
            <div class="auth-link">
                Don't have an account? <a href="register.php">Register</a>
            </div>

            <div class="back-link">
                <a href="user/home.php">← Back to Home</a>
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