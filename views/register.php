<?php
session_start();
require_once __DIR__ . '/../db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ./index.php");
    exit();
}

$error_message = "";
$success_message = "";

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all required fields!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email address!";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Email is already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
            
            if ($stmt->execute()) {
                $success_message = "Registration successful! Redirecting...";
                echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $error_message = "Registration failed!";
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Berkah Laundry</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <!-- Debug: CSS path -->
    <?php 
    $css_path = __DIR__ . '/../assets/css/auth.css';
    if (!file_exists($css_path)) {
        echo "<!-- CSS FILE NOT FOUND: " . $css_path . " -->";
    } else {
        echo "<!-- CSS FILE EXISTS -->";
    }
    ?>
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
                <h2>Create Account</h2>
                <p>Sign up to get started</p>
            </div>

            <!-- Alerts -->
            <?php if ($success_message): ?>
                <div class="alert success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="" required>
                </div>

                <div class="input-group">
                    <label>Phone (Optional)</label>
                    <input type="tel" name="phone" placeholder="">
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="" required>
                </div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="" required>
                </div>

                <div class="form-footer">
                    <label class="remember">
                        <input type="checkbox" required>
                        <span>I agree to Terms & Conditions</span>
                    </label>
                </div>

                <button type="submit" name="register" class="btn-submit">Create Account</button>
            </form>

            <!-- Login Link -->
            <div class="auth-link">
                Already have an account? <a href="login.php">Login</a>
            </div>

            <div class="back-link">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });

        // Password match validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirm = document.querySelector('input[name="confirm_password"]').value;

            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>