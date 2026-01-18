<?php
// pricelist.php
include_once '../../config/db.php';  // Koneksi database
include('../../includes/header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricelist - Berkah Laundry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/price.css">

</head>
<body>
    <!-- Header Section -->
    <div class="pricelist-header">
        <h1>Daftar Harga</h1>
        <p>Harga transparan untuk layanan bantuan berkualitas</p>
    </div>

    <!-- Pricing Container -->
    <div class="pricing-container">
        <!-- Regular Services -->
        <div class="pricing-section">
            <h2 class="section-title">Layanan Reguler</h2>
            <div class="price-cards">
                <div class="price-card">
                    <h3>Cuci Setrika</h3>
                    <p class="description">Cuci dan setrika pakaian Anda dengan hasil yang rapi dan harga terjangkau</p>
                    <div class="price">Rp 6.000</div>
                    <div class="unit">per Kg</div>
                </div>

                <div class="price-card">
                    <h3>Cuci Kering</h3>
                    <p class="description">Layanan cuci pakaian tanpa perlu dijemur, cocok untuk pakaian yang sensitif</p>
                    <div class="price">Rp 4.000</div>
                    <div class="unit">per Kg</div>
                </div>

                <div class="price-card">
                    <h3>Setrika Pakaian</h3>
                    <p class="description">Layanan setrika pakaian dengan hasil yang rapi dan cepat</p>
                    <div class="price">Rp 4.000</div>
                    <div class="unit">per Kg</div>
                </div>
            </div>
        </div>

        <!-- Special Items -->
        <div class="pricing-section">
            <h2 class="section-title">Harga Barang Khusus</h2>
            <div class="pricing-table">
                <table>
                    <thead>
                        <tr>
                            <th>Special Service</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Bedcover</td>
                            <td>Mulai Rp 20.000 - Rp 30.000</td>
                        </tr>
                        <tr>
                            <td>Selimut</td>
                            <td>Mulai Rp 10.000</td>
                        </tr>
                        <tr>
                            <td>Boneka</td>
                            <td>Rp 20.000 - Rp 30.000</td>
                        </tr>
                        <tr>
                            <td>Balmut</td>
                            <td>Rp 18.000</td>
                        </tr>
                        <tr>
                            <td>Sejadah Tebal</td>
                            <td>Rp 8.000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="info-box">
            <h4>Siap Untuk Pesan?</h4>
            <p>Dapatkan layanan laundry berkualitas dengan harga terjangkau. Proses cepat dan hasil memuaskan!</p>
            <a href="order.php" class="btn-order">Pesan Sekarang</a>
        </div>
    </div>

    <?php include('../../includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>