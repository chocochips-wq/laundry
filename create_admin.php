php<?php
// create_admin.php - File untuk membuat admin baru
include_once 'config/db.php';

// ========== KONFIGURASI ADMIN BARU ==========
$admin_name = 'Admin Berkah';
$admin_email = 'admin@berkahlaundry.com';
$admin_phone = '081234567890';
$admin_password = 'admin123'; // Password default, nanti bisa diganti
$admin_address = 'Kantor Berkah Laundry';
$admin_role = 'admin';

// Hash password
$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

// Cek apakah email sudah ada
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $admin_email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo "❌ <b>ERROR:</b> Email <b>{$admin_email}</b> sudah terdaftar!<br>";
    echo "Silakan hapus user lama atau gunakan email berbeda.";
    $check_stmt->close();
    exit();
}
$check_stmt->close();

// Insert admin baru
$stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssss", $admin_name, $admin_email, $admin_phone, $password_hash, $admin_role, $admin_address);

if ($stmt->execute()) {
    $admin_id = $stmt->insert_id;
    echo "
    <!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Admin Created Successfully</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .success-container {
                background: white;
                padding: 50px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 600px;
                text-align: center;
            }
            .success-icon {
                font-size: 80px;
                margin-bottom: 20px;
            }
            h2 {
                color: #28a745;
                margin-bottom: 30px;
            }
            .info-box {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 10px;
                margin: 20px 0;
                text-align: left;
            }
            .info-box p {
                margin: 10px 0;
                color: #495057;
            }
            .info-box strong {
                color: #212529;
            }
            .btn-login {
                display: inline-block;
                padding: 15px 40px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                text-decoration: none;
                border-radius: 50px;
                font-weight: 700;
                margin-top: 20px;
                transition: all 0.3s ease;
            }
            .btn-login:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
                color: white;
            }
            .warning {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin-top: 20px;
                border-radius: 5px;
                text-align: left;
            }
            .warning strong {
                color: #856404;
            }
        </style>
    </head>
    <body>
        <div class='success-container'>
            <div class='success-icon'>✅</div>
            <h2>Admin Berhasil Dibuat!</h2>
            
            <div class='info-box'>
                <p><strong>ID Admin:</strong> #{$admin_id}</p>
                <p><strong>Nama:</strong> {$admin_name}</p>
                <p><strong>Email:</strong> {$admin_email}</p>
                <p><strong>Password:</strong> {$admin_password}</p>
                <p><strong>Role:</strong> {$admin_role}</p>
                <p><strong>Telepon:</strong> {$admin_phone}</p>
            </div>

            <div class='warning'>
                <strong>⚠️ PENTING:</strong><br>
                1. Segera <strong>hapus file create_admin.php</strong> ini setelah selesai!<br>
                2. Ubah password setelah login pertama kali<br>
                3. Jangan bagikan informasi ini ke orang lain
            </div>

            <a href='views/login.php' class='btn-login'>Login Sekarang →</a>
        </div>
    </body>
    </html>
    ";
} else {
    echo "❌ <b>ERROR:</b> Gagal membuat admin<br>";
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>