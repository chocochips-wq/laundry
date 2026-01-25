<?php
$page_title = 'Keuangan';
include('../../includes/admin-header.php');
include_once __DIR__ . '/../../db.php';
require_once('../../config/Finance.php');

$filters = [];
$filters['type'] = $_GET['type'] ?? '';
$filters['from'] = $_GET['from'] ?? '';
$filters['to'] = $_GET['to'] ?? '';

// Defensive: check whether finance tables exist. If not, provide helpful instructions.
$transactions = [];
$totals = ['total_income' => 0, 'total_expense' => 0];
$categories = [];
$financeReady = Finance::hasFinancesTable();

if ($financeReady) {
    $transactions = Finance::all($filters);
    $totals = Finance::totals($filters['from'] ?: null, $filters['to'] ?: null);

    // Categories for filter
    $catRes = $conn->query("SELECT * FROM finance_categories ORDER BY name");
    $categories = $catRes ? $catRes->fetch_all(MYSQLI_ASSOC) : [];
}

// Handle deletion
if (isset($_GET['delete'])) {
    $did = intval($_GET['delete']);
    Finance::delete($did);
    header('Location: finances.php');
    exit;
}
?>

<style>
/* Finance Page Overrides */
.finance-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.finance-stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 1.2rem;
    transition: transform 0.2s;
}

.finance-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.finance-stat-card.stat-income {
    border-left: 4px solid #4caf50;
}

.finance-stat-card.stat-expense {
    border-left: 4px solid #ff4757;
}

.finance-stat-card.stat-balance {
    border-left: 4px solid #667eea;
}

.finance-stat-icon {
    font-size: 2.5rem;
    line-height: 1;
}

.finance-stat-info h3 {
    font-size: 0.85rem;
    font-weight: 600;
    color: #666;
    margin: 0 0 0.5rem 0;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.finance-stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
}

.finance-stat-value.income-color {
    color: #4caf50;
}

.finance-stat-value.expense-color {
    color: #ff4757;
}

.finance-stat-value.balance-color {
    color: #667eea;
}

/* Transaction Type Badge */
.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.9rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.type-badge.income {
    background: #e8f5e9;
    color: #2e7d32;
}

.type-badge.expense {
    background: #ffebee;
    color: #c62828;
}

/* Filter Header Enhancement */
.filter-header {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 1.5rem;
}

.filter-form {
    flex: 1;
    background: transparent;
    padding: 0;
    margin: 0;
}

.filter-actions-right {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.filter-actions-right .btn {
    white-space: nowrap;
}

/* Action Buttons Enhancement */
.action-buttons {
    display: flex;
    gap: 0.4rem;
    white-space: nowrap;
}

/* Responsive */
@media (max-width: 768px) {
    .finance-stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .finance-stat-card {
        padding: 1.2rem;
    }
    
    .finance-stat-icon {
        font-size: 2rem;
    }
    
    .finance-stat-value {
        font-size: 1.5rem;
    }
    
    .filter-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-actions-right {
        width: 100%;
        justify-content: stretch;
    }
    
    .filter-actions-right .btn {
        flex: 1;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn-sm {
        width: 100%;
    }
}
</style>

<div class="admin-container">
    <h1 class="page-title">💰 Keuangan</h1>

    <?php if (!$financeReady): ?>
        <div class="alert alert-error">
            ⚠️ Modul keuangan belum terpasang (tabel `finances` tidak ditemukan). <br>
            Silakan jalankan migrasi <a href="../../config/migrate_finances.php" target="_blank">di sini</a> lalu refresh halaman ini.
        </div>
    <?php endif; ?>

    <div class="content-card">
        <div class="card-header">
            <h2>📑 Daftar Transaksi</h2>
            <a href="finances-add.php" class="btn-oval-add">➕ Tambah</a>
        </div>

        <div class="filter-header">
            <form method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Tipe Transaksi</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Tipe</option>
                            <option value="income" <?php echo ($filters['type']==='income')? 'selected':''; ?>>💰 Pemasukan</option>
                            <option value="expense" <?php echo ($filters['type']==='expense')? 'selected':''; ?>>💸 Pengeluaran</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($filters['from']); ?>" onchange="this.form.submit()">
                    </div>

                    <div class="filter-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($filters['to']); ?>" onchange="this.form.submit()">
                    </div>
                </div>
            </form>
            
        </div>

        <div class="finance-stats-grid">
            <div class="finance-stat-card stat-income">
                <div class="finance-stat-icon">💰</div>
                <div class="finance-stat-info">
                    <h3>Total Pemasukan</h3>
                    <div class="finance-stat-value income-color">Rp <?php echo number_format($totals['total_income'] ?? 0,0,',','.'); ?></div>
                </div>
            </div>
            <div class="finance-stat-card stat-expense">
                <div class="finance-stat-icon">💸</div>
                <div class="finance-stat-info">
                    <h3>Total Pengeluaran</h3>
                    <div class="finance-stat-value expense-color">Rp <?php echo number_format($totals['total_expense'] ?? 0,0,',','.'); ?></div>
                </div>
            </div>
            <div class="finance-stat-card stat-balance">
                <div class="finance-stat-icon">📊</div>
                <div class="finance-stat-info">
                    <h3>Saldo Akhir</h3>
                    <div class="finance-stat-value balance-color">Rp <?php echo number_format(($totals['total_income'] - $totals['total_expense']),0,',','.'); ?></div>
                </div>
            </div>
        </div>

        <?php if (count($transactions) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Ref</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><strong>#<?php echo $t['id']; ?></strong></td>
                            <td>
                                <?php if ($t['type'] === 'income'): ?>
                                    <span class="type-badge income">💰 Pemasukan</span>
                                <?php else: ?>
                                    <span class="type-badge expense">💸 Pengeluaran</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($t['category_name'] ?? '-'); ?></td>
                            <td><strong>Rp <?php echo number_format($t['amount'],0,',','.'); ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($t['transaction_date'])); ?></td>
                            <td><?php echo htmlspecialchars($t['reference'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars(substr($t['note'] ?? '-',0,60)); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="finances-add.php?id=<?php echo $t['id']; ?>" class="btn-sm btn-warning">✏️ Edit</a>
                                    <a href="finances.php?delete=<?php echo $t['id']; ?>" onclick="return confirm('Hapus transaksi ini?')" class="btn-sm btn-danger">🗑️ Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>Tidak ada transaksi</h3>
                <p>Tambahkan transaksi baru untuk mulai mencatat pemasukan dan pengeluaran.</p>
            </div>
        <?php endif; ?>
    </div>
</div>