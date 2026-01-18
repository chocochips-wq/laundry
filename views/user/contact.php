<?php
// contact.php
include_once '../../config/db.php';
include('../../includes/header.php');

// Prepare WhatsApp/store contact fallback
if (session_status() === PHP_SESSION_NONE) session_start();
$store_phone = '';
$res = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'store_phone' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $store_phone = $row['setting_value'];
}
if (empty($store_phone)) {
    $store_phone = '081319265466';
}
$biz = preg_replace('/\D+/', '', $store_phone);
if (strlen($biz) > 0 && $biz[0] === '0') $biz = '62' . substr($biz, 1);
$wa_link = "https://wa.me/{$biz}";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungi Kami - Berkah Laundry</title>
    <meta name="description" content="Hubungi Berkah Laundry untuk layanan laundry profesional di Cibitung, Bekasi. Tersedia via WhatsApp, telepon, dan email.">
    <link rel="stylesheet" href="../../assets/css/kontak.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Contact Section -->
    <div class="contact-section">
        <!-- Hero Header -->
        <div class="contact-hero">
            <div class="container">
                <h2>Kontak Kami</h2>
                <p class="subtitle">Kami siap melayani kebutuhan laundry Anda dengan profesional dan ramah</p>
            </div>
        </div>

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
                                <p><strong>0815-8624-4181</strong></p>
                                <p class="muted">Untuk konfirmasi cepat, hubungi admin via WhatsApp.</p>
                                <a href="<?php echo $wa_link; ?>?text=Halo%20Berkah%20Laundry%2C%20saya%20ingin%20bertanya" target="_blank" rel="noopener noreferrer" class="btn btn-success wa-link">
                                    💬 Hubungi via WhatsApp
                                </a>
                            </div>

                            <div class="info-card">
                                <h4><span class="icon">✉️</span> Email</h4>
                                <p><strong>support@berkahlaundry.com</strong></p>
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
                        <form class="contact-form" action="contact_submit.php" method="POST" id="contactForm">
                            <h3>📝 kirim pesan </h1>
                            
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
                                <button type="submit" id="submitBtn" class="btn btn-primary">
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

            // Form submission validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    const name = document.getElementById('name').value.trim();
                    const phone = document.getElementById('phone').value.trim();
                    const message = document.getElementById('message').value.trim();

                    // Validate name
                    if (name.length < 3) {
                        e.preventDefault();
                        alert('⚠️ Nama harus minimal 3 karakter!');
                        document.getElementById('name').focus();
                        return false;
                    }

                    // Validate phone
                    const phoneDigits = phone.replace(/\D/g, '');
                    if (phoneDigits.length < 9 || phoneDigits.length > 15) {
                        e.preventDefault();
                        alert('⚠️ Nomor telepon harus 9-15 digit!');
                        document.getElementById('phone').focus();
                        return false;
                    }

                    // Validate message
                    if (message.length < 10) {
                        e.preventDefault();
                        alert('⚠️ Pesan minimal 10 karakter!');
                        document.getElementById('message').focus();
                        return false;
                    }

                    if (message.length > 1000) {
                        e.preventDefault();
                        alert('⚠️ Pesan maksimal 1000 karakter!');
                        document.getElementById('message').focus();
                        return false;
                    }

                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.classList.add('loading');
                    submitBtn.textContent = 'Mengirim...';
                });
            }
        })();
    </script>
</body>
</html>