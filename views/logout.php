<?php
// logout.php
session_start();

$scope = $_GET['scope'] ?? null;

if ($scope === 'admin') {
    // Hapus session khusus admin
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role'], $_SESSION['admin_phone'], $_SESSION['admin_address']);
    header('Location: ../login.php');
    exit();
} elseif ($scope === 'user') {
    // Hapus session khusus user
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['user_phone'], $_SESSION['user_address']);
    header('Location: ../user/home.php');
    exit();
} else {
    // Full logout
    session_unset(); 
    session_destroy(); 
    header("Location: ../user/home.php"); 
    exit();
}

