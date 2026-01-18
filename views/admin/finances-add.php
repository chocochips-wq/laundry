<?php
$page_title = 'Tambah / Edit Transaksi';
include('../../includes/admin-header.php');
require_once('../../config/db.php');
require_once('../../config/Finance.php');

$categories = [];
$catRes = $conn->query("SELECT * FROM finance_categories ORDER BY name");
if ($catRes) $categories = $catRes->fetch_all(MYSQLI_ASSOC);

$errors = [];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$existing = $id ? Finance::getById($id) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'expense';
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $amount = floatval(str_replace(',', '', $_POST['amount'] ?? 0));
    $transaction_date = $_POST['transaction_date'] ?? date('Y-m-d H:i:s');
    $order_id = !empty($_POST['order_id']) ? intval($_POST['order_id']) : null;
    $reference = trim($_POST['reference'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if ($amount <= 0) $errors[] = 'Jumlah harus lebih dari 0.';
    if (!in_array($type, ['income','expense'])) $errors[] = 'Tipe tidak valid.';

    if (empty($errors)) {
        $finance = new Finance($type, $amount, $transaction_date, $category_id, $order_id, $reference, $note, $_SESSION['admin_id'] ?? null);
        if ($id) {
            $finance->id = $id;
        }
        if ($finance->save()) {
            header('Location: finances.php?success=Transaksi tersimpan');
            exit;
        } else {
            $errors[] = 'Gagal menyimpan transaksi.';
        }
    }
}

?>

<div class="admin-container">
    <h1 class="page-title"><?php echo $id ? '✏️ Edit Transaksi' : '➕ Tambah Transaksi'; ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>⚠️ Kesalahan:</strong>
            <ul>
                <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
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
                        <select name="type" id="type" class="form-select form-control" required>
                            <option value="income" <?php echo (!empty($existing) && $existing['type']=='income')? 'selected':''; ?>>💰 Pemasukan</option>
                            <option value="expense" <?php echo (!empty($existing) && $existing['type']=='expense')? 'selected':''; ?>>💸 Pengeluaran</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Kategori <span class="required">*</span></label>
                        <select name="category_id" id="category_id" class="form-select form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo (!empty($existing) && $existing['category_id']==$c['id'])? 'selected':''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amount">Jumlah (Rp) <span class="required">*</span></label>
                        <input type="number" id="amount" name="amount" step="0.01" class="form-control" value="<?php echo htmlspecialchars($existing['amount'] ?? '0'); ?>" required placeholder="0,00">
                    </div>
                </div>
            </div>

            <!-- Date & Reference Section -->
            <div class="form-section">
                <h3 class="section-title">Detail Transaksi</h3>
                
                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label for="transaction_date">Tanggal Transaksi <span class="required">*</span></label>
                        <input type="datetime-local" id="transaction_date" name="transaction_date" class="form-control" value="<?php echo (!empty($existing['transaction_date']))? date('Y-m-d\TH:i', strtotime($existing['transaction_date'])): date('Y-m-d\TH:i'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="reference">Referensi / Order ID</label>
                        <input type="text" id="reference" name="reference" class="form-control" value="<?php echo htmlspecialchars($existing['reference'] ?? ''); ?>" placeholder="contoh: INV-001 atau ORD-123">
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="form-section">
                <h3 class="section-title">Catatan</h3>
                
                <div class="form-group">
                    <label for="note">Keterangan Tambahan</label>
                    <textarea id="note" name="note" class="form-control textarea-large" placeholder="Masukkan catatan atau deskripsi transaksi..."><?php echo htmlspecialchars($existing['note'] ?? ''); ?></textarea>
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