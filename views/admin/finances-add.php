<?php
$page_title = 'Tambah / Edit Transaksi';
include('../../includes/admin-header.php');
require_once('../../config/db.php');
require_once('../../config/Finance.php');

$errors = [];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$existing = $id ? Finance::getById($id) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'expense';
    $category_name = trim($_POST['category_name'] ?? '');
    $amount = floatval(str_replace(',', '', $_POST['amount'] ?? 0));
    $transaction_date = $_POST['transaction_date'] ?? date('Y-m-d H:i:s');
    $note = trim($_POST['note'] ?? '');

    // Validasi
    if ($amount <= 0) {
        $errors[] = 'Jumlah harus lebih dari 0.';
    }
    if (empty($category_name)) {
        $errors[] = 'Kategori harus diisi.';
    }
    if (!in_array($type, ['income','expense'])) {
        $errors[] = 'Tipe tidak valid.';
    }

    if (empty($errors)) {
        // Cek/buat kategori
        $category_id = null;
        $checkCat = $conn->prepare("SELECT id FROM finance_categories WHERE name = ?");
        $checkCat->bind_param('s', $category_name);
        $checkCat->execute();
        $catResult = $checkCat->get_result();
        
        if ($catResult->num_rows > 0) {
            $category_id = $catResult->fetch_assoc()['id'];
        } else {
            // Buat kategori baru
            $insertCat = $conn->prepare("INSERT INTO finance_categories (name) VALUES (?)");
            $insertCat->bind_param('s', $category_name);
            if ($insertCat->execute()) {
                $category_id = $conn->insert_id;
            }
            $insertCat->close();
        }
        $checkCat->close();

        // Simpan transaksi
        $finance = new Finance($type, $amount, $transaction_date, $category_id, null, null, $note, $_SESSION['admin_id'] ?? null);
        if ($id) {
            $finance->id = $id;
        }
        
        if ($finance->save()) {
            $_SESSION['success'] = 'Transaksi berhasil disimpan!';
            header('Location: finances.php');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan transaksi.';
        }
    }
}

// Ambil kategori existing untuk autocomplete
$categoriesQuery = $conn->query("SELECT DISTINCT name FROM finance_categories ORDER BY name");
$existingCategories = [];
if ($categoriesQuery) {
    while ($row = $categoriesQuery->fetch_assoc()) {
        $existingCategories[] = $row['name'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .form-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0 0 1.2rem 0;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-grid {
            display: grid;
            gap: 1.5rem;
        }

        .form-grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .form-grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .required {
            color: #ff4757;
        }

        .form-control, .form-select {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .textarea-large {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-start;
            margin-top: 2rem;
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #ff4757;
        }

        .alert ul {
            margin: 0.5rem 0 0 1.5rem;
        }

        .category-suggestions {
            position: relative;
        }

        .suggestions-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            display: none;
        }

        .suggestions-list.active {
            display: block;
        }

        .suggestion-item {
            padding: 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .suggestion-item:hover {
            background: #f7fafc;
        }

        @media (max-width: 768px) {
            .form-grid-2, .form-grid-3 {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-lg {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1 class="page-title"><?php echo $id ? '✏️ Edit Transaksi' : '➕ Tambah Transaksi'; ?></h1>

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

    <div class="content-card">
        <form method="POST" class="transaction-form">
            
            <!-- Tipe & Kategori Section -->
            <div class="form-section">
                <h3 class="section-title">Informasi Dasar</h3>
                
                <div class="form-grid form-grid-3">
                    <div class="form-group">
                        <label for="type">Tipe Transaksi <span class="required">*</span></label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="income" <?php echo (!empty($existing) && $existing['type']=='income')? 'selected':''; ?>>💰 Pemasukan</option>
                            <option value="expense" <?php echo (!empty($existing) && $existing['type']=='expense')? 'selected':''; ?>>💸 Pengeluaran</option>
                        </select>
                    </div>

                    <div class="form-group category-suggestions">
                        <label for="category_name">Kategori <span class="required">*</span></label>
                        <input type="text" id="category_name" name="category_name" class="form-control" 
                               value="<?php echo htmlspecialchars($existing['category_name'] ?? ''); ?>" 
                               placeholder="Contoh: Gaji, Listrik, Belanja" 
                               autocomplete="off"
                               required>
                        <div class="suggestions-list" id="suggestionsList"></div>
                        <small style="color: #718096; margin-top: 0.3rem; display: block;">
                            Ketik nama kategori atau pilih dari saran
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="amount">Jumlah (Rp) <span class="required">*</span></label>
                        <input type="number" id="amount" name="amount" step="0.01" class="form-control" 
                               value="<?php echo htmlspecialchars($existing['amount'] ?? ''); ?>" 
                               required 
                               placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Date Section -->
            <div class="form-section">
                <h3 class="section-title">Detail Transaksi</h3>
                
                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label for="transaction_date">Tanggal Transaksi <span class="required">*</span></label>
                        <input type="datetime-local" id="transaction_date" name="transaction_date" class="form-control" 
                               value="<?php echo (!empty($existing['transaction_date']))? date('Y-m-d\TH:i', strtotime($existing['transaction_date'])): date('Y-m-d\TH:i'); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="note">Keterangan Singkat</label>
                        <input type="text" id="note" name="note" class="form-control" 
                               value="<?php echo htmlspecialchars($existing['note'] ?? ''); ?>" 
                               placeholder="Contoh: Pembayaran listrik bulan Januari">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    💾 <?php echo $id ? 'Perbarui' : 'Simpan'; ?> Transaksi
                </button>
                <a href="finances.php" class="btn btn-secondary btn-lg">
                    ← Kembali
                </a>
            </div>

        </form>
    </div>
</div>

<script>
    // Autocomplete kategori
    const categories = <?php echo json_encode($existingCategories); ?>;
    const categoryInput = document.getElementById('category_name');
    const suggestionsList = document.getElementById('suggestionsList');

    categoryInput.addEventListener('input', function() {
        const value = this.value.toLowerCase();
        suggestionsList.innerHTML = '';
        
        if (value.length < 1) {
            suggestionsList.classList.remove('active');
            return;
        }

        const filtered = categories.filter(cat => 
            cat.toLowerCase().includes(value)
        );

        if (filtered.length > 0) {
            filtered.forEach(cat => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.textContent = cat;
                div.onclick = function() {
                    categoryInput.value = cat;
                    suggestionsList.classList.remove('active');
                };
                suggestionsList.appendChild(div);
            });
            suggestionsList.classList.add('active');
        } else {
            suggestionsList.classList.remove('active');
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.category-suggestions')) {
            suggestionsList.classList.remove('active');
        }
    });

    // Format input rupiah
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
</script>

</body>
</html>