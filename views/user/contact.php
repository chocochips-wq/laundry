<?php
include_once __DIR__ . '/../../db.php';
include('../../includes/header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message']);

    if ($name && $phone && $message) {
        $stmt = $conn->prepare(
            "INSERT INTO contact_messages (name, phone, email, message) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $phone, $email, $message);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

// ========== NOMOR WHATSAPP - HARDCODED ==========
$store_phone = '081319265466'; // Ganti dengan nomor Anda
$biz = preg_replace('/\D+/', '', $store_phone);
if (strlen($biz) > 0 && $biz[0] === '0') {
    $biz = '62' . substr($biz, 1);
}
$wa_link = "https://wa.me/{$biz}";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - Berkah Laundry</title>
    <meta name="description" content="Hubungi Berkah Laundry untuk layanan laundry profesional di Cibitung, Bekasi. Tersedia via WhatsApp, telepon, dan email.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/kontak.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../assets/css/home.css">
    <style>
        /* Header Section (Seperti Pricelist) */
        .contact-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 0 60px;
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }

        .contact-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .contact-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Inline CSS jika kontak.css tidak ada */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .contact-content {
            background: white;
            padding: 60px 20px;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        @media (max-width: 992px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
        }
        .info-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .info-card h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .info-card .icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .info-card p {
            margin: 8px 0;
            color: #555;
        }
        .info-card .muted {
            color: #999;
            font-size: 0.9rem;
        }
        .wa-link {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white !important;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .wa-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
        }
        .map-box {
            border-radius: 15px;
            overflow: hidden;
            height: 400px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .map-box iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }
        .contact-form {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .contact-form h3 {
            color: #333;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .contact-form textarea {
            min-height: 150px;
            resize: vertical;
        }
        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>

    <!-- Header Section (Seperti Pricelist) -->
    <div class="contact-header">
        <h1>Kontak Kami</h1>
        <p>Kami siap melayani kebutuhan laundry Anda dengan profesional dan ramah</p>
    </div>

    <!-- Contact Section -->
    <div class="contact-section">
        <!-- White Content Section -->
        <div class="contact-content">
            <div class="container">
                <!-- Flash messages -->
                <?php if (!empty($_SESSION['contact_success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['contact_success']); ?></div>
                    <?php unset($_SESSION['contact_success']); ?>
                <?php endif; ?>
                <?php if (!empty($_SESSION['contact_error'])): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['contact_error']); ?></div>
                    <?php unset($_SESSION['contact_error']); ?>
                <?php endif; ?>

                <div class="contact-grid">
                    <!-- Left Side: Contact Information -->
                    <div class="contact-left">
                        <div class="info-grid">
                            <div class="info-card">
                                <h4><span class="icon">📍</span> Alamat</h4>
                                <p>Cluster Mandala, Blok B No.15<br>Rt.11/Rw.26 Wanasari<br>Cibitung, Bekasi</p>
                                <p class="muted">Jam buka: Senin - Sabtu, 08:00 - 17:00</p>
                            </div>

                            <div class="info-card">
                                <h4><span class="icon">📞</span> Telepon & WhatsApp</h4>
                                <p><strong><?php echo htmlspecialchars($store_phone); ?></strong></p>
                                <p class="muted">Untuk konfirmasi cepat, hubungi admin via WhatsApp.</p>
                                <a href="<?php echo htmlspecialchars($wa_link); ?>?text=Halo%20Berkah%20Laundry%2C%20saya%20ingin%20bertanya" target="_blank" rel="noopener noreferrer" class="wa-link">
                                    💬 Hubungi via WhatsApp
                                </a>
                            </div>

                            <div class="info-card">
                                <h4><span class="icon">✉️</span> Email</h4>
                                <p><strong>lala.berkahlaundry@gmail.com</strong></p>
                                <p class="muted">Atau isi formulir di samping untuk pertanyaan non-darurat.</p>
                            </div>
                        </div>

                        <!-- Map -->
                        <div class="map-box">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.0!2d107.0!3d-6.3!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTgnMDAuMCJTIDEwN8KwMDAnMDAuMCJF!5e0!3m2!1sid!2sid!4v1234567890" 
                                allowfullscreen 
                                loading="lazy"
                                title="Lokasi Berkah Laundry di Google Maps">
                            </iframe>
                        </div>
                    </div>

                    <!-- Right Side: Contact Form -->
                    <div class="contact-right">
                        <form class="contact-form" method="POST" id="contactForm">
                            <h3>📝 Kirim Pesan</h3>
                            
                            <div class="form-row">
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    placeholder="Nama lengkap" 
                                    required
                                    minlength="3"
                                    maxlength="100">
                                <input 
                                    type="tel" 
                                    name="phone" 
                                    id="phone" 
                                    placeholder="Nomor WhatsApp (08xx)" 
                                    required
                                    pattern="[0-9]{9,15}"
                                    maxlength="15">
                            </div>
                            
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                placeholder="Email (opsional)"
                                maxlength="100">
                            
                            <textarea 
                                name="message" 
                                id="message" 
                                placeholder="Tulis pesan Anda di sini..." 
                                required
                                minlength="10"
                                maxlength="1000"></textarea>
                            
                            <div class="form-actions">
                                <button type="submit" id="submitBtn" class="btn-primary">
                                    ✉️ Kirim Pesan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../../includes/footer.php'); ?>

    <script>
        // Form handling
        (function() {
            const form = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            const phoneInput = document.getElementById('phone');

            // Phone number formatting - only numbers
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 15) {
                        value = value.substring(0, 15);
                    }
                    e.target.value = value;
                });
            }

            // Form submission - Redirect ke WhatsApp
            if (form) {
form.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append('ajax', '1');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Mengirim...';

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'ok') {
            // buat pesan WhatsApp
            let waMessage = `Halo Berkah Laundry,\n\n`;
            waMessage += `Nama: ${form.name.value}\n`;
            waMessage += `Telepon: ${form.phone.value}\n`;
            if (form.email.value) {
                waMessage += `Email: ${form.email.value}\n`;
            }
            waMessage += `\nPesan:\n${form.message.value}\n\nTerima kasih.`;

            const waUrl = `<?php echo $wa_link; ?>?text=${encodeURIComponent(waMessage)}`;
            window.location.href = waUrl;
        } else {
            alert('Gagal menyimpan pesan.');
            submitBtn.disabled = false;
            submitBtn.textContent = '✉️ Kirim Pesan';
        }
    })
    .catch(() => {
        alert('Pesan berhasil dirikim.');
        submitBtn.disabled = false;
        submitBtn.textContent = '✉️ Kirim Pesan';
    });
});
            }
        })();
    </script>
</body>
</html>