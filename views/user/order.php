<?php

include_once '../../config/db.php';
include_once '../../config/Order.php';

// ========== PROSES FORM POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type = $_POST['service_type'] ?? 'cuci_setrika';

    // Estimator: jika user memilih estimasi berdasarkan jumlah barang
    $estimate_mode = isset($_POST['estimate']) && $_POST['estimate'] === '1';

    // Jika tidak memakai estimasi, user harus memilih paket
    $order_package = trim($_POST['order_package'] ?? '');

    if ($estimate_mode) {
        $shirt = intval($_POST['item_shirt'] ?? 0);
        $pant = intval($_POST['item_pant'] ?? 0);
        $sheet = intval($_POST['item_sheet'] ?? 0);

        // Berat standar perkiraan (kg)
        $weight = $shirt * 0.3 + $pant * 0.6 + $sheet * 1.5;
    } elseif (!empty($order_package)) {
        // Map package types to a default weight where applicable
        $package_defaults = [
            'cuci_setrika' => 2,
            'cuci_kering' => 2,
            'setrika_pakaian' => 1,
            'bedcover' => 0,
            'selimut' => 0,
            'boneka' => 0,
            'balmut' => 0,
            'sejadah_tebal' => 0
        ];
        $weight = $package_defaults[$order_package] ?? 0;
    } else {
        $_SESSION['error'] = 'Pilih jenis pesanan atau gunakan estimasi berdasarkan jumlah barang.';
        header('Location: order.php');
        exit();
    }

    // Enforce minimum order of 2 kg only for kiloan/cuci packages
    $min_weight_packages = ['cuci_setrika', 'cuci_kering'];
    if (in_array($order_package, $min_weight_packages) && $weight < 2) {
        $_SESSION['error'] = 'Minimal order adalah 2 kg untuk paket kiloan atau cuci setrika.';
        header('Location: order.php');
        exit();
    }

    // Calculate price based on service type
    $prices = [
        'cuci_setrika' => 6000,
        'cuci_kering' => 4000,
        'setrika' => 4000
    ];

    $price_per_kg = $prices[$service_type] ?? 6000;
    $total_price = round($weight * $price_per_kg);

    $pickup_name = trim($_POST['pickup_name'] ?? ($_SESSION['user_name'] ?? ''));
    $pickup_phone = trim($_POST['pickup_phone'] ?? ($_SESSION['user_phone'] ?? ''));
    $pickup_address = trim($_POST['pickup_address'] ?? ($_SESSION['user_address'] ?? ''));

    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $pickup_time = trim($_POST['pickup_time'] ?? '');
    $pickup_datetime = null;

    if (!empty($pickup_date) && !empty($pickup_time)) {
        $dt = DateTime::createFromFormat('Y-m-d H:i', $pickup_date . ' ' . $pickup_time);
        if ($dt) {
            $now = new DateTime();
            if ($dt < $now) {
                $dt = $now->modify('+1 hour');
            }
            $pickup_datetime = $dt->format('Y-m-d H:i:00');
        }
    } else {
        $_SESSION['error'] = 'Silakan pilih tanggal dan waktu penjemputan.';
        header('Location: order.php');
        exit();
    }

    // Tentukan user_id: jika login, gunakan SESSION; jika tidak, buat user guest
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        // Guest checkout
        $guest_name = trim($_POST['guest_name'] ?? $pickup_name);
        $guest_phone = trim($_POST['guest_phone'] ?? $pickup_phone);
        $guest_address = trim($_POST['guest_address'] ?? $pickup_address);
        $guest_email = trim($_POST['guest_email'] ?? '');

        if (empty($guest_name) || empty($guest_phone)) {
            $_SESSION['error'] = 'Untuk memesan sebagai tamu, isi nama dan nomor telepon';
            header('Location: order.php');
            exit();
        }

        $user_id = null;
        if (!empty($guest_email) && filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $guest_email);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $user_id = $row['id'];
            }
            $stmt->close();
        }

        if ($user_id === null) {
            $password_hash = password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT);
            $role = 'guest';
            $email_param = !empty($guest_email) && filter_var($guest_email, FILTER_VALIDATE_EMAIL) 
                ? $guest_email 
                : ('guest_' . time() . '_' . uniqid() . '@example.local');

            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $guest_name, $email_param, $guest_phone, $password_hash, $role, $guest_address);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
            }
            $stmt->close();
        }
    }

    // Create and save order
    $order = new Order($user_id, $service_type, $weight, $total_price, 'pending', $pickup_name, $pickup_phone, $pickup_address, $pickup_datetime);

    if ($order->save()) {
        $order_id = $conn->insert_id;

        // ========== NOMOR WHATSAPP - HARDCODED ==========
        $biz = '6281319265466'; // +62 813-1926-5466

        // Service name mapping
        $service_names = [
            'cuci_setrika' => 'Cuci + Setrika',
            'cuci_kering' => 'Cuci Kering',
            'setrika' => 'Setrika Saja'
        ];
        $service_name = $service_names[$service_type] ?? $service_type;

        // Package labels
        $package_labels = [
            'cuci_setrika' => 'Cuci Setrika',
            'cuci_kering' => 'Cuci Kering',
            'setrika_pakaian' => 'Setrika Pakaian',
            'bedcover' => 'Bedcover',
            'selimut' => 'Selimut',
            'boneka' => 'Boneka',
            'balmut' => 'Balmut',
            'sejadah_tebal' => 'Sejadah Tebal'
        ];

        $package_label = !empty($order_package) ? ($package_labels[$order_package] ?? $order_package) : '';

        if ($estimate_mode) {
            $shirt = intval($_POST['item_shirt'] ?? 0);
            $pant = intval($_POST['item_pant'] ?? 0);
            $sheet = intval($_POST['item_sheet'] ?? 0);
            $method_desc = "Estimasi: ${shirt} kemeja, ${pant} celana, ${sheet} seprai";
        } else {
            $method_desc = $package_label;
        }
        
        $pickup_dt_str = $pickup_datetime ? date('d/m/Y H:i', strtotime($pickup_datetime)) : '-';

        // Buat pesan WhatsApp
        $message_text = "Halo, saya ingin memesan layanan *{$service_name}*.\n\n" .
                       "Nama: {$pickup_name}\n" .
                       "Telepon: {$pickup_phone}\n" .
                       "Alamat: {$pickup_address}\n" .
                       "Jenis Pesanan: {$method_desc}\n" .
                       "Jadwal Penjemputan: {$pickup_dt_str}\n" .
                       "ID Pesanan: #{$order_id}\n\n" .
                       "Terima kasih.";

        $message = rawurlencode($message_text);
        $wa_url = "https://wa.me/{$biz}?text={$message}";

        // ========== LANGSUNG REDIRECT KE WHATSAPP ==========
        // Tidak pakai session, langsung output HTML dengan meta refresh
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="0;url=<?= htmlspecialchars($wa_url) ?>">
            <title>Redirect ke WhatsApp...</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .loading-container {
                    text-align: center;
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                }
                .loading-icon {
                    font-size: 80px;
                    animation: pulse 1s infinite;
                }
                @keyframes pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
                h2 { color: #25D366; margin: 20px 0; }
                p { color: #666; }
                .btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 15px 30px;
                    background: #25D366;
                    color: white;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: bold;
                }
            </style>
        </head>
            <script>
                // Fallback jika meta refresh gagal
                setTimeout(function() {
                    window.location.href = <?= json_encode($wa_url) ?>;
                }, 500);
            </script>
        </body>
        </html>
        <?php
        exit();
    } else {
        $_SESSION['error'] = 'Gagal membuat pesanan. Silakan coba lagi.';
        header('Location: order.php');
        exit();
    }
}

// Include header setelah proses POST selesai
include('../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - Berkah Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/order.css">
    
    <style>
        /* Simple professional button styling */
        .btn-order-simple {
            width: 100%;
            padding: 12px 18px;
            font-size: 1rem;
            border-radius: 8px;
            font-weight: 600;
        }

        /* Button info text styling */
        .btn-info-text {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 5px solid #25D366;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .btn-info-text small {
            display: block;
            line-height: 2;
            color: #0c4a6e;
            font-size: 14px;
        }

        .btn-info-text .wa-number-badge {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border-radius: 25px;
            font-weight: 700;
            font-size: 16px;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
            transition: transform 0.3s ease;
        }

        .btn-info-text .wa-number-badge:hover {
            transform: scale(1.05);
        }

        .btn-info-text .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .btn-info-text .feature-icon {
            font-size: 20px;
            margin-right: 10px;
            display: inline-block;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-5px);
            }
            60% {
                transform: translateY(-3px);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .btn-order-simple {
                font-size: 16px;
                padding: 14px;
            }

            .btn-info-text {
                padding: 16px;
            }

            .btn-info-text .wa-number-badge {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="page-header">
            <h1>🧺 Order Laundry</h1>
            <p>Pesan layanan laundry dengan mudah dan cepat</p>
        </div>

        <div class="order-container">
            <!-- Alert Messages -->
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ❌ <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Info Box -->
            <div class="info-box">
                <h5>ℹ️ Informasi Penting:</h5>
                <ul>
                    <li>Minimal order 2 kg</li>
                    <li>Estimasi pengerjaan 1-2 hari kerja</li>
                    <li>Gratis antar jemput untuk pemesanan di atas 5 kg</li>
                    <li>Pembayaran bisa cash atau transfer</li>
                    <li>Tidak menerima pakaian dalam</li>
                </ul>
            </div>

            <!-- Order Form -->
            <div class="order-form-card">
                <h2 class="form-section-title">Detail Pemesanan</h2>
                
                <form action="order.php" method="POST" id="orderForm">
                    <!-- Service Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">Pilih Jenis Layanan</label>
                        <div class="service-options">
                            <div class="service-option">
                                <input type="radio" name="service_type" value="cuci_setrika" id="service1" required checked>
                                <label class="service-label" for="service1">
                                    <div class="service-name">Cuci Setrika</div>                        
                                </label>
                            </div>
                            
                            <div class="service-option">
                                <input type="radio" name="service_type" value="cuci_kering" id="service2" required>
                                <label class="service-label" for="service2">
                                    <div class="service-name">Cuci Kering</div>                        
                                </label>
                            </div>
                            
                            <div class="service-option">
                                <input type="radio" name="service_type" value="setrika" id="service3" required>
                                <label class="service-label" for="service3">
                                    <div class="service-name">Setrika Saja</div>                        
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup Details -->
                    <div class="mb-4 pickup-section">
                        <h5>📦 Data Penjemputan</h5>

                        <div class="mb-3">
                            <label class="form-label">Nama Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="pickup_name" id="pickup_name" class="form-control" placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon/WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" name="pickup_phone" id="pickup_phone" class="form-control" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>" required>
                            <small class="text-muted">Format: 08xxxxxxxxxx (minimal 10 digit)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Penjemputan Lengkap <span class="text-danger">*</span></label>
                            <textarea name="pickup_address" id="pickup_address" class="form-control" rows="3" placeholder="Contoh: Jl. Merdeka No. 123, Dekat Indomaret, Kelurahan..." required><?= htmlspecialchars($_SESSION['user_address'] ?? '') ?></textarea>
                            <small class="text-muted">Sertakan patokan/landmark agar mudah ditemukan</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Penjemputan <span class="text-danger">*</span></label>
                                <input type="date" name="pickup_date" id="pickup_date" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Penjemputan <span class="text-danger">*</span></label>
                                <input type="time" name="pickup_time" id="pickup_time" class="form-control" required>
                                <small class="text-muted">Default: besok jam 09:00</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Pesanan <span class="text-danger">*</span></label>
                            <select name="order_package" id="order_package" class="form-select" required>
                                <option value="">-- Pilih Jenis Pesanan --</option>
                                <option value="cuci_setrika">Cuci Setrika (Kiloan)</option>
                                <option value="cuci_kering">Cuci Kering (Kiloan)</option>
                                <option value="setrika_pakaian">Setrika Pakaian</option>
                                <option value="bedcover">Bedcover</option>
                                <option value="selimut">Selimut</option>
                                <option value="boneka">Boneka</option>
                                <option value="balmut">Bantal + Selimut (Balmut)</option>
                                <option value="sejadah_tebal">Sejadah Tebal</option>
                            </select>
                            <small class="text-muted">Lihat <a href="pricelist.php" target="_blank">Daftar Harga</a> untuk rincian lengkap.</small>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg btn-order-simple" id="btnSubmit">
                            <span class="spinner-border spinner-border-sm me-2" id="btnSpinner" role="status" style="display:none" aria-hidden="true"></span>
                            Pesan Sekarang
                        </button>
                    </div>

                    <div class="btn-info-text text-center">
                        <div style="font-size: 20px; margin-bottom: 12px;">
                            <span style="font-size: 24px;">✨</span> 
                            <strong style="color: #0c4a6e;">Proses Cepat & Mudah</strong> 
                            <span style="font-size: 24px;">✨</span>
                        </div>
                        <small>
                            <div class="feature-item">
                                <span class="feature-icon">✅</span>
                                <span><strong>Pesanan otomatis tersimpan</strong> ke sistem</span>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">📱</span>
                                <span><strong>Langsung terhubung</strong> ke WhatsApp admin</span>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">💬</span>
                                <span><strong>Konfirmasi real-time</strong> dalam hitungan detik</span>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">🚀</span>
                                <span><strong>Layanan 24/7</strong> siap melayani Anda</span>
                            </div>
                            <br>
                            <span class="wa-number-badge">
                                📞 +62 813-1926-5466
                            </span>
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ========== DEFAULT PICKUP DATETIME ==========
        function setDefaultPickupDatetime() {
            const dateEl = document.getElementById('pickup_date');
            const timeEl = document.getElementById('pickup_time');
            
            if (!dateEl || !timeEl) return;
            
            if (!dateEl.value) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const year = tomorrow.getFullYear();
                const month = String(tomorrow.getMonth() + 1).padStart(2, '0');
                const day = String(tomorrow.getDate()).padStart(2, '0');
                dateEl.value = `${year}-${month}-${day}`;
                
                const today = new Date();
                const minYear = today.getFullYear();
                const minMonth = String(today.getMonth() + 1).padStart(2, '0');
                const minDay = String(today.getDate()).padStart(2, '0');
                dateEl.min = `${minYear}-${minMonth}-${minDay}`;
            }
            
            if (!timeEl.value) {
                timeEl.value = '09:00';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setDefaultPickupDatetime();
        });

        // ========== FORM VALIDATION ==========
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const pickupName = document.getElementById('pickup_name')?.value.trim() || '';
            const pickupPhone = document.getElementById('pickup_phone')?.value.trim() || '';
            const pickupAddr = document.getElementById('pickup_address')?.value.trim() || '';
            const pickupDate = document.getElementById('pickup_date')?.value || '';
            const pickupTime = document.getElementById('pickup_time')?.value || '';
            const orderPackage = document.getElementById('order_package')?.value || '';

            if (!pickupName || !pickupPhone || !pickupAddr) {
                e.preventDefault();
                alert('❌ Mohon isi lengkap:\n- Nama Penerima\n- Nomor Telepon/WhatsApp\n- Alamat Penjemputan');
                return false;
            }

            const phoneDigits = pickupPhone.replace(/\D/g, '');
            if (phoneDigits.length < 10) {
                e.preventDefault();
                alert('❌ Nomor telepon tidak valid.\nMinimal 10 digit.\n\nContoh: 081234567890');
                document.getElementById('pickup_phone').focus();
                return false;
            }

            if (!pickupDate || !pickupTime) {
                e.preventDefault();
                alert('❌ Pilih tanggal dan waktu penjemputan');
                return false;
            }

            const pickupDateTime = new Date(pickupDate + 'T' + pickupTime);
            const now = new Date();
            if (pickupDateTime < now) {
                e.preventDefault();
                alert('❌ Tanggal dan waktu penjemputan harus di masa depan\n\nSilakan pilih tanggal besok atau setelahnya.');
                return false;
            }

            if (!orderPackage) {
                e.preventDefault();
                alert('❌ Pilih jenis pesanan');
                document.getElementById('order_package').focus();
                return false;
            }

            // Show loading state
            const btnSpinner = document.getElementById('btnSpinner');
            const btnSubmit = document.getElementById('btnSubmit');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                if (btnSpinner) btnSpinner.style.display = 'inline-block';
            }

            return true;
        });

        // ========== AUTO-FORMAT NOMOR TELEPON ==========
        document.getElementById('pickup_phone')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 15) {
                value = value.substring(0, 15);
            }
            e.target.value = value;
        });

        // ========== VALIDASI REAL-TIME ==========
        document.getElementById('pickup_phone')?.addEventListener('blur', function(e) {
            const phone = e.target.value.replace(/\D/g, '');
            if (phone.length > 0 && phone.length < 10) {
                alert('⚠️ Nomor telepon terlalu pendek.\nMinimal 10 digit.');
                e.target.focus();
            }
        });
    </script>
</body>
</html>