<?php
// ========== PROSES FORM POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type = $_POST['service_type'] ?? 'cuci_setrika';
    $order_package = trim($_POST['order_package'] ?? '');

    if (empty($order_package)) {
        $_SESSION['error'] = 'Pilih jenis pesanan.';
        header('Location: order.php');
        exit();
    }

    // Data penjemputan
    $pickup_name = trim($_POST['pickup_name'] ?? ($_SESSION['user_name'] ?? ''));
    $pickup_phone = trim($_POST['pickup_phone'] ?? ($_SESSION['user_phone'] ?? ''));
    $pickup_address = trim($_POST['pickup_address'] ?? ($_SESSION['user_address'] ?? ''));
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $pickup_time = trim($_POST['pickup_time'] ?? '');
    $pickup_datetime = null;

    // Validasi dan format datetime
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

    // Validasi data lengkap
    if (empty($pickup_name) || empty($pickup_phone) || empty($pickup_address)) {
        $_SESSION['error'] = 'Mohon isi lengkap data penjemputan (Nama, Telepon, Alamat)';
        header('Location: order.php');
        exit();
    }

    // Nomor WhatsApp
    $biz = '6281319265466';

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

    $method_desc = $package_label;
    
    $pickup_dt_str = $pickup_datetime ? date('d/m/Y H:i', strtotime($pickup_datetime)) : '-';
    $order_ref = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    if (isset($_SESSION['wa_sent'])) {
        exit();
    }
    $_SESSION['wa_sent'] = true;

    // Buat pesan WhatsApp
    $message_text =
        "Halo, saya ingin memesan layanan *{$service_name}*.\n\n" .
        "Nama: {$pickup_name}\n" .
        "Telepon: {$pickup_phone}\n" .
        "Alamat: {$pickup_address}\n" .
        "Jenis Pesanan: {$method_desc}\n" .
        "Jadwal Penjemputan: {$pickup_dt_str}\n" .
        "Referensi: {$order_ref}\n\n" .
        "Terima kasih.";

    $message = rawurlencode($message_text);
    $wa_url = "https://wa.me/{$biz}?text={$message}";

    // Set success message
    $_SESSION['success'] = 'Pesanan berhasil! Anda akan diarahkan ke WhatsApp.';

    // Langsung redirect ke WhatsApp tanpa halaman perantara
    header("Location: " . $wa_url);
    exit();
}

// ========== INCLUDE HEADER ==========
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
        .btn-order-simple {
            width: 100%;
            padding: 14px 20px;
            font-size: 1.1rem;
            border-radius: 10px;
            font-weight: 600;
            background: linear-gradient(135deg, #25D366, #128C7E);
            border: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        }
        
        .btn-order-simple:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
        }
        
        .btn-order-simple:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .btn-info-text {
            margin-top: 25px;
            padding: 25px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 5px solid #25D366;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .btn-info-text .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            color: #0c4a6e;
            font-size: 14px;
            line-height: 1.6;
        }

        .btn-info-text .feature-icon {
            font-size: 22px;
            margin-right: 12px;
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

        .wa-number-badge {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border-radius: 25px;
            font-weight: 700;
            font-size: 16px;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
            transition: transform 0.3s ease;
        }

        .wa-number-badge:hover {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .btn-order-simple {
                font-size: 1rem;
                padding: 12px 18px;
            }

            .btn-info-text {
                padding: 20px;
            }

            .btn-info-text .feature-item {
                font-size: 13px;
            }

            .wa-number-badge {
                font-size: 14px;
                padding: 10px 20px;
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
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ✅ <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

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
                    <li>Minimal order 2 kg untuk paket kiloan</li>
                    <li>Estimasi pengerjaan 1-2 hari kerja</li>
                    <li>Gratis antar jemput untuk pemesanan di atas 5 kg</li>
                    <li>Pembayaran bisa cash atau transfer</li>
                    <li>Tidak menerima pakaian dalam</li>
                </ul>
            </div>

            <!-- Order Form -->
            <div class="order-form-card">
                <h2 class="form-section-title">Detail Pemesanan</h2>
                
                <form action="order.php" method="POST" id="orderForm" novalidate>
                    <!-- Service Type Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Jenis Layanan <span class="text-danger">*</span></label>
                        <div class="service-options">
                            <div class="service-option">
                                <input type="radio" name="service_type" value="cuci_setrika" id="service1" required checked>
                                <label class="service-label" for="service1">
                                    <div class="service-name">Cuci Setrika</div>
                                    <div class="service-price">Rp 6.000/kg</div>
                                </label>
                            </div>
                            
                            <div class="service-option">
                                <input type="radio" name="service_type" value="cuci_kering" id="service2" required>
                                <label class="service-label" for="service2">
                                    <div class="service-name">Cuci Kering</div>
                                    <div class="service-price">Rp 4.000/kg</div>
                                </label>
                            </div>
                            
                            <div class="service-option">
                                <input type="radio" name="service_type" value="setrika" id="service3" required>
                                <label class="service-label" for="service3">
                                    <div class="service-name">Setrika Saja</div>
                                    <div class="service-price">Rp 4.000/kg</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Jenis Pesanan -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Jenis Pesanan <span class="text-danger">*</span></label>
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

                    <!-- Pickup Details -->
                    <div class="mb-4 pickup-section">
                        <h5 class="mb-3">📦 Data Penjemputan</h5>

                        <div class="mb-3">
                            <label class="form-label">Nama Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="pickup_name" id="pickup_name" class="form-control" 
                                   placeholder="Masukkan nama lengkap" 
                                   value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon/WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" name="pickup_phone" id="pickup_phone" class="form-control" 
                                   placeholder="Contoh: 081234567890" 
                                   value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>" required>
                            <small class="text-muted">Format: 08xxxxxxxxxx (minimal 10 digit)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Penjemputan Lengkap <span class="text-danger">*</span></label>
                            <textarea name="pickup_address" id="pickup_address" class="form-control" rows="3" 
                                      placeholder="Contoh: Jl. Merdeka No. 123, Dekat Indomaret, Kelurahan..." required><?= htmlspecialchars($_SESSION['user_address'] ?? '') ?></textarea>
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
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-order-simple" id="btnSubmit">
                            💬 Pesan Sekarang
                        </button>
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
            const btnSubmit = document.getElementById('btnSubmit');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menghubungkan...';
                
                // Reset button setelah 3 detik (jika user kembali dari WhatsApp)
                setTimeout(function() {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '💬 Pesan Sekarang';
                }, 3000);
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