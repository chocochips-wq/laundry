<?php
// home.php
include_once '../../config/db.php';
include('../../includes/header.php'); 
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Laundry - Layanan Kami</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/home.css">
</head>
<body>

    <!-- Hero Section (Banner) -->
    <div class="hero-section">
        <!-- Banner hanya gambar, tanpa teks atau tombol -->
    </div>

    <!-- Services Section -->
    <div class="services-section">
        <div class="container">
            <h2 class="text-center">Layanan Kami</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <div class="col">
                    <div class="card service-card">
                        <img src="images/cuci-setrika.jpg" class="card-img-top" alt="Cuci Setrika">
                        <div class="card-body">
                            <h5 class="card-title">Cuci Setrika</h5>
                            <p class="card-text">Cuci dan setrika pakaian Anda dengan hasil yang rapi dan harga terjangkau.</p>
                            <p class="card-text"><strong>Price: Rp 6.000 / Kg</strong></p>
                            <a href="order.php?service=cuci_setrika" class="btn btn-animate">Order Now</a>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card service-card">
                        <img src="images/cuci-kering.jpg" class="card-img-top" alt="Cuci Kering">
                        <div class="card-body">
                            <h5 class="card-title">Cuci Kering</h5>
                            <p class="card-text">Layanan cuci pakaian tanpa perlu dijemur, cocok untuk pakaian yang sensitif.</p>
                            <p class="card-text"><strong>Price: Rp 4.000 / Kg</strong></p>
                            <a href="order.php?service=cuci_kering" class="btn btn-animate">Order Now</a>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card service-card">
                        <img src="../images/setrika.jpg" class="card-img-top" alt="Setrika Pakaian">
                        <div class="card-body">
                            <h5 class="card-title">Setrika Pakaian</h5>
                            <p class="card-text">Layanan setrika pakaian dengan hasil yang rapi dan cepat.</p>
                            <p class="card-text"><strong>Price: Rp 4.000 / Kg</strong></p>
                            <a href="order.php?service=setrika" class="btn btn-animate">Order Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Choose Us Section -->
    <div class="why-choose-section">
        <div class="container">
            <h2 class="text-center">Kenapa Pilih Berkah Laundry?</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon">⚡</div>
                        <h4>Pelayanan Cepat</h4>
                        <p>Layanan cepat dengan hasil maksimal dalam 1-2 hari</p>
                    </div>
                </div>
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon">✨</div>
                        <h4>Kualitas Terjamin</h4>
                        <p>Kualitas terjamin dengan peralatan modern dan detergen premium</p>
                    </div>
                </div>
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon">💰</div>
                        <h4>Harga Terjangkau</h4>
                        <p>Harga terjangkau untuk semua kalangan dengan promo menarik</p>
                    </div>
                </div>
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon">🚚</div>
                        <h4>Gratis Jemput</h4>
                        <p>Gratis antar jemput untuk area tertentu</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
        <div class="container">
            <h2>Ready to Try Our Service?</h2>
            <p>Dapatkan pakaian bersih dan wangi tanpa repot!</p>
            <div>
                <a href="order.php" class="btn-cta me-3">Order Now</a>
                <a href="pricelist.php" class="btn-cta">View Pricelist</a>
            </div>
        </div>
    </div>

   <?php include('../../includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
