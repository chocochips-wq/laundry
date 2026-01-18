<?php
include('../../includes/header.php');
include_once '../../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($name) || empty($email)) {
        $error_message = "Name and email are required!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Email is already used!";
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success_message = "Profile updated successfully!";
                
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error_message = "Failed to update profile!";
            }
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password must be at least 6 characters!";
    } else {
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Failed to change password!";
            }
            $stmt->close();
        } else {
            $error_message = "Current password is incorrect!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Berkah Laundry</title>
    <link rel="stylesheet" href="../../assets/css/profile.css">
</head>
<body>
    <div class="profile-page">
        <!-- Header Section -->
        <div class="page-header">
            <h1>My Profile</h1>
            <p>Manage your account information</p>
        </div>

        <div class="content-wrapper">
            <!-- Alert Messages -->
            <?php if ($success_message): ?>
                <div class="alert success">
                    ✓ <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert error">
                    ✕ <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="profile-content">
                <!-- Profile Card -->
                <div class="card">
                    <div class="card-title">
                        <h2>Personal Information</h2>
                    </div>
                    <form method="POST" action="">
                        <div class="form-row">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="form-row">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-row">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="081234567890">
                        </div>

                        <div class="form-row">
                            <label>Address</label>
                            <textarea name="address" rows="3" placeholder="Your address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
                    </form>
                </div>

                <!-- Password Card -->
                <div class="card">
                    <div class="card-title">
                        <h2>Change Password</h2>
                    </div>
                    <form method="POST" action="">
                        <div class="form-row">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>

                        <div class="form-row">
                            <label>New Password</label>
                            <input type="password" name="new_password" placeholder="Min. 6 characters" required>
                        </div>

                        <div class="form-row">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>

                        <button type="submit" name="change_password" class="btn-secondary">Update Password</button>
                    </form>
                </div>

                <!-- Info Card -->
                <div class="card info-card">
                    <div class="card-title">
                        <h2>Account Info</h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">User ID</span>
                            <span class="value">#<?php echo str_pad($user['id'], 5, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Member Since</span>
                            <span class="value">
                                <?php 
                                    $date = new DateTime($user['created_at']);
                                    echo $date->format('M d, Y'); 
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="label">Status</span>
                            <span class="badge">Active</span>
                        </div>
                    </div>
                </div>
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

        // Password confirmation validation
        document.querySelector('form[name="change_password"]')?.addEventListener('submit', function(e) {
            const newPass = document.querySelector('input[name="new_password"]').value;
            const confirmPass = document.querySelector('input[name="confirm_password"]').value;

            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>