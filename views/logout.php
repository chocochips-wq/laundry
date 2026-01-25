<?php
// logout.php
// Cegah output sebelum header
ob_start();

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scope = $_GET['scope'] ?? null;

if ($scope === 'admin') {
    // Hapus session khusus admin
    if (isset($_SESSION['admin_id'])) {
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role'], $_SESSION['admin_phone'], $_SESSION['admin_address']);
    }
    // Redirect ke login dengan path konsisten
    ob_end_clean();
    header('Location: /laundry/views/login.php');
    exit();
} elseif ($scope === 'user') {
    // Hapus session khusus user
    if (isset($_SESSION['user_id'])) {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['user_phone'], $_SESSION['user_address']);
    }
    // Redirect ke home
    ob_end_clean();
    header('Location: /laundry/index.php');
    exit();
} else {
    // Full logout - hapus semua session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset(); 
        session_destroy();
    }
    // Redirect ke home
    ob_end_clean();
    header("Location: /laundry/index.php"); 
    exit();
}

