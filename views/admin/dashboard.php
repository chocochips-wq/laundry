<?php
$page_title = 'Dashboard';
include('../../includes/admin-header.php');
require_once('../../config/db.php');
require_once('../../config/Finance.php');

// Ambil data keuangan
$financeReady = Finance::hasFinancesTable();
$totals = ['total_income' => 0, 'total_expense' => 0];
$monthlyData = [];
$recentTransactions = [];

if ($financeReady) {
    // Total keseluruhan
    $totals = Finance::totals(null, null);
    
    // Data per bulan untuk grafik (6 bulan terakhir)
    $monthlyQuery = "
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
        FROM finances
        WHERE transaction_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month ASC
    ";
    $result = $conn->query($monthlyQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $monthlyData[] = $row;
        }
    }
    
    // Transaksi terbaru (10 terakhir)
    $recentQuery = "
        SELECT f.*, fc.name as category_name
        FROM finances f
        LEFT JOIN finance_categories fc ON f.category_id = fc.id
        ORDER BY f.transaction_date DESC
        LIMIT 10
    ";
    $result = $conn->query($recentQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentTransactions[] = $row;
        }
    }
}

// Total pelanggan
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_customers = $stmt ? $stmt->fetch_assoc()['total'] : 0;

// Hitung saldo
$balance = $totals['total_income'] - $totals['total_expense'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .stat-card.income-card {
            border-left: 4px solid #4caf50;
        }

        .stat-card.expense-card {
            border-left: 4px solid #ff4757;
        }

        .stat-card.balance-card {
            border-left: 4px solid #667eea;
        }

        .stat-card.customer-card {
            border-left: 4px solid #ffa502;
        }

        .stat-icon {
            font-size: 2.5rem;
            line-height: 1;
        }

        .stat-info h3 {
            font-size: 0.85rem;
            font-weight: 600;
            color: #666;
            margin: 0 0 0.5rem 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-value.green {
            color: #4caf50;
        }

        .stat-value.red {
            color: #ff4757;
        }

        .stat-value.blue {
            color: #667eea;
        }

        .stat-value.orange {
            color: #ffa502;
        }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .chart-container h2 {
            margin: 0 0 1.5rem 0;
            color: #2d3748;
            font-size: 1.3rem;
        }

        .chart-wrapper {
            position: relative;
            height: 350px;
        }

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

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-wrapper {
                height: 250px;
            }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1 class="page-title">📊 Dashboard</h1>

    <?php if (!$financeReady): ?>
        <div class="alert alert-error">
            ⚠️ Modul keuangan belum terpasang. Silakan jalankan migrasi 
            <a href="../../config/migrate_finances.php" target="_blank">di sini</a>.
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
        <div class="stat-card income-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>Total Pemasukan</h3>
                <div class="stat-value green">Rp <?php echo number_format($totals['total_income'], 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="stat-card expense-card">
            <div class="stat-icon">💸</div>
            <div class="stat-info">
                <h3>Total Pengeluaran</h3>
                <div class="stat-value red">Rp <?php echo number_format($totals['total_expense'], 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="stat-card balance-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3>Pendapatan</h3>
                <div class="stat-value blue">Rp <?php echo number_format($balance, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="stat-card customer-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3>Total Pelanggan</h3>
                <div class="stat-value orange"><?php echo $total_customers; ?></div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <?php if (!empty($monthlyData)): ?>
    <div class="chart-container">
        <h2>📈 Grafik Pemasukan & Pengeluaran (6 Bulan Terakhir)</h2>
        <div class="chart-wrapper">
            <canvas id="financeChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Transactions -->
    <div class="content-card">
        <div class="card-header">
            <h2>📋 Transaksi Terbaru</h2>
            <a href="finances.php" class="btn-primary">Lihat Semua</a>
        </div>

        <?php if (count($recentTransactions) > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentTransactions as $t): ?>
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
                        <td><strong>Rp <?php echo number_format($t['amount'], 0, ',', '.'); ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($t['transaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars(substr($t['note'] ?? '-', 0, 50)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>Belum ada transaksi</h3>
            <p>Tambahkan transaksi untuk mulai mencatat keuangan</p>
            <a href="finances-add.php" class="btn-primary">➕ Tambah Transaksi</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($monthlyData)): ?>
<script>
    // Prepare data for chart
    const labels = <?php echo json_encode(array_map(function($d) {
        return date('M Y', strtotime($d['month'] . '-01'));
    }, $monthlyData)); ?>;
    
    const incomeData = <?php echo json_encode(array_map(function($d) {
        return floatval($d['income']);
    }, $monthlyData)); ?>;
    
    const expenseData = <?php echo json_encode(array_map(function($d) {
        return floatval($d['expense']);
    }, $monthlyData)); ?>;

    // Create chart
    const ctx = document.getElementById('financeChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: incomeData,
                    backgroundColor: 'rgba(76, 175, 80, 0.7)',
                    borderColor: 'rgba(76, 175, 80, 1)',
                    borderWidth: 2
                },
                {
                    label: 'Pengeluaran',
                    data: expenseData,
                    backgroundColor: 'rgba(255, 71, 87, 0.7)',
                    borderColor: 'rgba(255, 71, 87, 1)',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14,
                            weight: '600'
                        },
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                notation: 'compact',
                                compactDisplay: 'short'
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
</script>
<?php endif; ?>

</body>
</html>