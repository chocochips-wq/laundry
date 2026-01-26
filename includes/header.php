<?php
// Start session with secure settings
ini_set('session.cookie_httponly', 1);

// Only set secure cookie on HTTPS
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

session_start();

// Include security module
require_once __DIR__ . '/../config/security.php';

// Auto-detect BASE_URL untuk compatibility dengan localhost dan production
if (empty($_SERVER['HTTP_HOST']) === false) {
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'];
    $script_path = dirname($_SERVER['SCRIPT_NAME']);
    
    // Untuk InfinityFree atau production - biasanya langsung di root public_html
    if (strpos($domain, 'localhost') === false && strpos($domain, '127.0.0.1') === false) {
        // Production environment - file ada di root public_html
        define('BASE_URL', '');
    } else {
        // Localhost environment - file ada di folder laundry
        define('BASE_URL', '/laundry');
    }
} else {
    define('BASE_URL', '/laundry');
}

// Session timeout (30 minutes)
$session_timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar Styles */
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 18px 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-custom.scrolled {
            padding: 12px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-size: 1.6rem;
            font-weight: 700;
            color: white !important;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand:hover {
            opacity: 0.9;
        }

        .brand-icon {
            font-size: 1.8rem;
        }

        /* Desktop Menu */
        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 500;
            font-size: 1rem;
            padding: 10px 24px !important;
            margin: 0 4px;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            letter-spacing: 0.3px;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.15);
        }

        .navbar-nav .nav-link.active {
            color: white !important;
            background: rgba(255, 255, 255, 0.25);
            font-weight: 600;
        }

        /* User Dropdown - Desktop */
        .navbar-nav .dropdown-menu {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
            padding: 8px;
            margin-top: 12px;
            min-width: 200px;
        }

        .navbar-nav .dropdown-item {
            padding: 12px 18px;
            color: #2c3e50;
            font-weight: 500;
            transition: all 0.2s ease;
            border-radius: 8px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-nav .dropdown-item:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .navbar-nav .dropdown-toggle::after {
            margin-left: 8px;
        }

        /* User Info in Dropdown */
        .user-info {
            padding: 14px 18px;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 6px;
        }

        .user-info .user-name {
            font-weight: 700;
            color: #667eea;
            font-size: 1rem;
            margin-bottom: 2px;
        }

        .user-info .user-email {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .dropdown-divider {
            margin: 6px 0;
            border-top: 1px solid #e9ecef;
        }

        /* Dropdown Icons */
        .dropdown-icon {
            font-size: 1.1rem;
        }

        /* Button Styles */
        .btn-auth {
            padding: 10px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            text-decoration: none;
        }

        .btn-login {
            background: white;
            color: #667eea;
            border: 2px solid white;
            margin-right: 8px;
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.4);
            color: #667eea;
        }

        /* Mobile Toggle */
        .navbar-toggler {
            border: 2px solid white;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .navbar-toggler:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }

        /* ====== MOBILE SIDEBAR MENU ====== */
        @media (max-width: 991px) {
            .navbar-custom {
                padding: 16px 0;
            }

            /* Sidebar Container */
            .navbar-collapse {
                position: fixed;
                top: 0;
                right: -100%;
                width: 300px;
                height: 100vh;
                background: white;
                box-shadow: -3px 0 20px rgba(0, 0, 0, 0.3);
                transition: right 0.35s ease;
                z-index: 999999;
                overflow-y: auto;
                padding: 0;
            }

            .navbar-collapse.show {
                right: 0;
            }

            /* Sidebar Header */
            .sidebar-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 24px 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .sidebar-brand {
                font-size: 1.3rem;
                font-weight: 700;
                color: white;
                text-transform: uppercase;
                letter-spacing: 1.5px;
            }

            .sidebar-close {
                background: transparent;
                border: 2px solid white;
                color: white;
                font-size: 1.8rem;
                width: 36px;
                height: 36px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                font-weight: 300;
                line-height: 1;
            }

            .sidebar-close:hover {
                background: white;
                color: #667eea;
                transform: rotate(90deg);
            }

            /* Mobile Menu Items */
            .navbar-nav {
                padding: 20px 0;
                flex-direction: column;
            }

            .navbar-nav .nav-item {
                margin: 0;
                width: 100%;
            }

            .navbar-nav .nav-link {
                color: #2c3e50 !important;
                padding: 16px 24px !important;
                margin: 4px 12px;
                border-radius: 8px;
                font-size: 1rem;
                font-weight: 500;
                text-align: left;
                transition: all 0.2s ease;
                background: transparent;
                cursor: pointer;
                display: block;
            }

            .navbar-nav .nav-link:hover,
            .navbar-nav .nav-link.active {
                background: #f8f9fa;
                color: #667eea !important;
                transform: translateX(4px);
            }

            /* Mobile Auth Section */
            .mobile-auth-section {
                padding: 20px;
                border-top: 1px solid #e9ecef;
                margin-top: 20px;
            }

            .mobile-auth-section .btn-auth {
                width: 100%;
                margin: 8px 0;
                padding: 14px 28px;
                font-size: 1rem;
                justify-content: center;
                cursor: pointer;
                display: flex;
            }

            .mobile-auth-section .btn-login {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
            }

            .mobile-auth-section .btn-login:hover {
                background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
                color: white;
            }

            .mobile-auth-section .btn-profile {
                background: transparent;
                color: #667eea;
                border: 2px solid #667eea;
            }

            .mobile-auth-section .btn-profile:hover {
                background: #667eea;
                color: white;
            }

            .navbar-brand {
                font-size: 1.4rem;
            }

            /* User Info in Mobile */
            .mobile-auth-section .user-info {
                padding: 16px 0;
                border-bottom: 1px solid #e9ecef;
                margin-bottom: 16px;
                background: transparent;
                border-radius: 0;
            }

            .mobile-auth-section .user-info .user-name {
                color: #2c3e50;
                font-size: 1.1rem;
            }

            .mobile-auth-section .user-info .user-email {
                color: #6c757d;
            }
        }

        /* NO BACKDROP AT ALL */
        .mobile-backdrop {
            display: none !important;
        }

        @media (min-width: 992px) {
            .mobile-backdrop {
                display: none !important;
            }
            
            .sidebar-header {
                display: none;
            }

            .mobile-auth-section {
                display: none;
            }

            .navbar-nav .dropdown-menu {
                position: absolute !important;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.2rem;
                letter-spacing: 1px;
            }

            .navbar-collapse {
                width: 280px;
            }
        }
    </style>
</head>
<body>
    <!-- NO BACKDROP -->

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
                <span class="brand-icon">🧺</span>
                Berkah Laundry
            </a>
            <button class="navbar-toggler" type="button" id="navbarToggler">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="navbarNav">
                <!-- Sidebar Header (Mobile Only) -->
                <div class="sidebar-header">
                    <span class="sidebar-brand">Menu</span>
                    <button class="sidebar-close" id="sidebarClose">×</button>
                </div>

                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/views/user/pricelist.php">Daftar Harga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/views/user/order.php">Pesan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/views/user/contact.php">Kontak Kami</a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- User is logged in - Desktop View -->
                        <li class="nav-item dropdown d-none d-lg-block">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li class="user-info">
                                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                                </li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/user/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/laundry/views/logout.php?scope=user">
                                    <span class="dropdown-icon">🚪</span> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- User is not logged in - Desktop View -->
                        <li class="nav-item d-none d-lg-block">
                            <a class="btn btn-auth btn-login" href="<?= BASE_URL ?>/views/login.php">👤</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Mobile Auth Section -->
                <div class="mobile-auth-section d-lg-none">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- User is logged in - Mobile View -->
                        <div class="user-info mb-3">
                            <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
                            <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                        </div>
                        <a class="btn btn-auth btn-profile" href="<?= BASE_URL ?>/views/user/profile.php">👤 Profile</a>
                        <a class="btn btn-auth btn-login" href="<?= BASE_URL ?>/views/logout.php?scope=user">🚪 Logout</a>
                    <?php else: ?>
                        <!-- User is not logged in - Mobile View -->
                        <a class="btn btn-auth btn-login" href="<?= BASE_URL ?>/views/login.php">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile Sidebar Control
        (function() {
            const toggler = document.getElementById('navbarToggler');
            const sidebarClose = document.getElementById('sidebarClose');
            const navbarCollapse = document.getElementById('navbarNav');
            
            function closeSidebar() {
                navbarCollapse.classList.remove('show');
                document.body.style.overflow = '';
            }
            
            if (toggler) {
                toggler.addEventListener('click', function(e) {
                    e.preventDefault();
                    navbarCollapse.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            }
            
            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebar);
            }
        })();

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Active link highlighting
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href.includes(currentPage)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>