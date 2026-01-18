<?php
$page_title = 'Buat Struk';
include('../../includes/admin-header.php');
require_once('../../config/db.php');

$errors = [];
$invoice_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $service_type = $_POST['service_type'] ?? '';
    $weight = floatval($_POST['weight'] ?? 0);
    $price_per_kg = floatval($_POST['price_per_kg'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    // Validasi
    if (empty($customer_name)) $errors[] = 'Nama pelanggan harus diisi';
    if (empty($customer_phone)) $errors[] = 'Nomor telepon harus diisi';
    if (empty($service_type)) $errors[] = 'Jenis layanan harus dipilih';
    if ($weight <= 0) $errors[] = 'Berat harus lebih dari 0';
    
    if (empty($errors)) {
        $total = $weight * $price_per_kg;
        $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        
        $invoice_data = [
            'invoice_number' => $invoice_number,
            'date' => date('d/m/Y H:i'),
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'service_type' => $service_type,
            'weight' => $weight,
            'price_per_kg' => $price_per_kg,
            'total' => $total,
            'notes' => $notes
        ];
    }
}

$service_prices = [
    'cuci_setrika' => 6000,
    'cuci_kering' => 4000,
    'setrika' => 4000
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        .invoice-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .form-section-invoice {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .form-group-invoice {
            margin-bottom: 1.2rem;
        }

        .form-group-invoice label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .form-group-invoice input,
        .form-group-invoice select,
        .form-group-invoice textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group-invoice input:focus,
        .form-group-invoice select:focus,
        .form-group-invoice textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .required {
            color: #ff4757;
        }

        /* Struk Preview */
        .struk-preview {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .struk-wrapper {
            background: #f7fafc;
            padding: 1.5rem;
            border-radius: 8px;
            display: flex;
            justify-content: center;
        }

        #strukContent {
            width: 300px; /* 80mm thermal printer */
            background: white;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .struk-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .struk-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .struk-header p {
            margin: 3px 0;
            font-size: 11px;
        }

        .struk-body {
            margin-bottom: 15px;
        }

        .struk-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .struk-row.bold {
            font-weight: bold;
        }

        .struk-divider {
            border-bottom: 1px dashed #666;
            margin: 10px 0;
        }

        .struk-total {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 8px 0;
            margin: 10px 0;
            font-weight: bold;
            font-size: 14px;
        }

        .struk-footer {
            text-align: center;
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 15px;
            font-size: 11px;
        }

        .btn-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn-download {
            flex: 1;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-jpg {
            background: #4caf50;
            color: white;
        }

        .btn-jpg:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .btn-pdf {
            background: #ff4757;
            color: white;
        }

        .btn-pdf:hover {
            background: #e84343;
            transform: translateY(-2px);
        }

        .btn-print {
            background: #667eea;
            color: white;
        }

        .btn-print:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .empty-struk {
            text-align: center;
            padding: 3rem 1rem;
            color: #a0aec0;
        }

        .empty-struk-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 968px) {
            .invoice-container {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }
            #strukContent, #strukContent * {
                visibility: visible;
            }
            #strukContent {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
            }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1 class="page-title">🧾 Buat Struk Manual</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>⚠️ Kesalahan:</strong>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="invoice-container">
        <!-- Form Input -->
        <div class="form-section-invoice">
            <h2 style="margin-top: 0; color: #2d3748;">📝 Data Transaksi</h2>
            
            <form method="POST" id="invoiceForm">
                <div class="form-group-invoice">
                    <label>Nama Pelanggan <span class="required">*</span></label>
                    <input type="text" name="customer_name" value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>" placeholder="Masukkan nama pelanggan" required>
                </div>

                <div class="form-group-invoice">
                    <label>Nomor Telepon <span class="required">*</span></label>
                    <input type="tel" name="customer_phone" value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>" placeholder="08xxxxxxxxxx" required>
                </div>

                <div class="form-group-invoice">
                    <label>Jenis Layanan <span class="required">*</span></label>
                    <input
                        type="text"
                        name="service_type"
                        placeholder="Contoh: Cuci Karpet / Bedcover"
                        required
                        value="<?php echo htmlspecialchars($_POST['service_type'] ?? ''); ?>"
                    >
                </div>

                <div class="form-grid-2">
                    <div class="form-group-invoice">
                        <label>Berat (kg) <span class="required">*</span></label>
                        <input type="number" name="weight" id="weight" step="0.5" min="0.5" value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>" placeholder="0" required>
                    </div>

                    <div class="form-group-invoice">
                        <label>Harga/kg (Rp) <span class="required">*</span></label>
                        <input
                            type="number"
                            name="price_per_kg"
                            placeholder="Contoh: 6000"
                            required
                            value="<?php echo htmlspecialchars($_POST['price_per_kg'] ?? ''); ?>"
                        >

                    </div>
                </div>

                <div class="form-group-invoice">
                    <label>Catatan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan tambahan (opsional)"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem; font-size: 1rem; margin-top: 0.5rem;">
                    📋 Buat Struk
                </button>
            </form>
        </div>

        <!-- Preview Struk -->
        <div class="struk-preview">
            <h2 style="margin-top: 0; color: #2d3748;">👁️ Preview Struk</h2>
            
            <div class="struk-wrapper">
                <?php if ($invoice_data): ?>
                <div id="strukContent">
                    <div class="struk-header">
                        <h2>BERKAH LAUNDRY</h2>
                        <p>Jl. Contoh No. 123, Jakarta</p>
                        <p>Telp: 081234567890</p>
                    </div>

                    <div class="struk-body">
                        <div class="struk-row">
                            <span>No Invoice:</span>
                            <strong><?php echo $invoice_data['invoice_number']; ?></strong>
                        </div>
                        <div class="struk-row">
                            <span>Tanggal:</span>
                            <span><?php echo $invoice_data['date']; ?></span>
                        </div>
                        
                        <div class="struk-divider"></div>
                        
                        <div class="struk-row">
                            <span>Nama:</span>
                            <span><?php echo $invoice_data['customer_name']; ?></span>
                        </div>
                        <div class="struk-row">
                            <span>Telepon:</span>
                            <span><?php echo $invoice_data['customer_phone']; ?></span>
                        </div>
                        
                        <div class="struk-divider"></div>
                        
                        <div class="struk-row bold">
                            <span>LAYANAN:</span>
                        </div>
                        <div class="struk-row">
                        </div>
                        <div class="struk-row">
                            <span>Berat:</span>
                            <span><?php echo $invoice_data['weight']; ?> kg</span>
                        </div>
                        <div class="struk-row">
                            <span>Harga/kg:</span>
                            <span>Rp <?php echo number_format($invoice_data['price_per_kg'], 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="struk-total">
                            <div class="struk-row">
                                <span>TOTAL:</span>
                                <span>Rp <?php echo number_format($invoice_data['total'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($invoice_data['notes'])): ?>
                        <div class="struk-row">
                            <span>Catatan:</span>
                        </div>
                        <div style="margin-top: 5px; font-size: 11px;">
                            <?php echo nl2br(htmlspecialchars($invoice_data['notes'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="struk-footer">
                        <p>*** TERIMA KASIH ***</p>
                        <p>Barang diambil maks 7 hari</p>
                        <p>Barang hilang 10x lipat harga</p>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-struk">
                    <div class="empty-struk-icon">📄</div>
                    <h3>Belum Ada Struk</h3>
                    <p>Isi form di samping untuk membuat struk</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($invoice_data): ?>
            <div class="btn-actions">
                <button onclick="downloadAsJPG()" class="btn-download btn-jpg">
                    📷 Download JPG
                </button>
                <button onclick="downloadAsPDF()" class="btn-download btn-pdf">
                    📑 Download PDF
                </button>
                <button onclick="printStruk()" class="btn-download btn-print">
                    🖨️ Cetak
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Download as JPG
    function downloadAsJPG() {
        const struk = document.getElementById('strukContent');
        
        html2canvas(struk, {
            scale: 3,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'struk-' + Date.now() + '.jpg';
            link.href = canvas.toDataURL('image/jpeg', 0.95);
            link.click();
        });
    }

    // Download as PDF
    function downloadAsPDF() {
        const struk = document.getElementById('strukContent');
        
        html2canvas(struk, {
            scale: 3,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            const { jsPDF } = window.jspdf;
            const imgData = canvas.toDataURL('image/jpeg', 0.95);
            
            // A6 size (105 x 148 mm) for receipt
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [80, 200] // 80mm width thermal paper
            });
            
            const imgWidth = 80;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            
            pdf.addImage(imgData, 'JPEG', 0, 0, imgWidth, imgHeight);
            pdf.save('struk-' + Date.now() + '.pdf');
        });
    }

    // Print
    function printStruk() {
        window.print();
    }
</script>

</body>
</html>