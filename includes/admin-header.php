<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Ambil nama admin dari session
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - Berkah Laundry</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<header class="admin-header">
    <h1>🧺 Berkah Laundry Admin</h1>
    
    <button class="mobile-toggle" id="mobileToggle" onclick="toggleNav()">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <nav class="admin-nav" id="adminNav">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            📊 Dashboard
        </a>
        <a href="customers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
            👥 Pelanggan
        </a>
        <a href="invoice.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'invoice.php' ? 'active' : ''; ?>">
            🧾 Invoice
        </a>
        <a href="finances.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'finances.php' ? 'active' : ''; ?>">
            🧾 Keuangan
        </a>
    </nav>

    <div class="admin-user">
        <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
        <a href="../logout.php?scope=admin">
            <button class="btn-logout">Logout</button>
        </a> 
    </div>
</header>

<!-- Overlay untuk mobile sidebar -->
<div class="nav-overlay" id="navOverlay" onclick="closeNav()"></div>

<script>
function toggleNav() {
    const nav = document.getElementById('adminNav');
    nav.classList.toggle('active');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const nav = document.getElementById('adminNav');
    const toggle = document.querySelector('.mobile-toggle');
    
    if (!nav.contains(event.target) && !toggle.contains(event.target)) {
        nav.classList.remove('active');
    }
});

// Close menu when window is resized to desktop size
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        document.getElementById('adminNav').classList.remove('active');
    }
});
</script>

</body>
</html>