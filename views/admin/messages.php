<?php
$page_title='Pesan Dari Pengguna';
include('../../includes/admin-header.php');
include_once __DIR__ . '/../../db.php';

// Initialize database connection
$db = new Database();
$conn = $db->getConnection();

// Handle delete message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $message_id = intval($_POST['message_id']);
    try {
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $delete_success = "Pesan berhasil dihapus!";
    } catch (Exception $e) {
        $delete_error = "Gagal menghapus pesan!";
    }
}

// Get search/filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest'; // newest atau oldest

// Build query
$query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)";
    $search_param = '%' . $search . '%';
    $params = [$search_param, $search_param, $search_param, $search_param];
}

// Add sorting
if ($sort === 'oldest') {
    $query .= " ORDER BY created_at ASC";
} else {
    $query .= " ORDER BY created_at DESC";
}

// Execute query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts
$count_stmt = $conn->query("SELECT COUNT(*) as total FROM contact_messages");
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count messages from last 7 days
$recent_stmt = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$recent_count = $recent_stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="admin-container">
    <h1 class="page-title">📬 Pesan Dari Pengguna</h1>

    <!-- Success/Error Messages -->
    <?php if (!empty($delete_success)): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem; padding: 1rem; background: #d4edda; border-left: 4px solid #28a745; border-radius: 8px; color: #155724;">
            ✅ <?php echo $delete_success; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($delete_error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.5rem; padding: 1rem; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: 8px; color: #721c24;">
            ❌ <?php echo $delete_error; ?>
        </div>
    <?php endif; ?>

    <div class="content-card">
        <div class="card-header">
            <h2>💬 Semua Pesan</h2>
            <div style="display: flex; gap: 1rem;">
                <span style="background: #667eea; color: #fff; padding: 0.5rem 1rem; border-radius: 8px;">
                    Total: <?php echo $total_count; ?>
                </span>
                <span style="background: #28a745; color: #fff; padding: 0.5rem 1rem; border-radius: 8px;">
                    7 Hari Terakhir: <?php echo $recent_count; ?>
                </span>
            </div>
        </div>

        <!-- Search & Filter -->
        <form method="GET" style="margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari nama, email, atau pesan..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    style="flex: 1; min-width: 200px;">
                
                <select name="sort" class="form-control" style="min-width: 150px;">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                </select>

                <button type="submit" style="padding: 0.75rem 1.5rem; background: #667eea; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    🔍 Cari
                </button>
                
                <?php if ($search): ?>
                    <a href="messages.php" style="padding: 0.75rem 1.5rem; background: #6c757d; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block;">
                        ✕ Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Messages Table -->
        <?php if (empty($messages)): ?>
            <div style="text-align: center; padding: 2rem; color: #6c757d;">
                <p>📭 Tidak ada pesan.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                            <th style="padding: 1rem; text-align: left;">Nama</th>
                            <th style="padding: 1rem; text-align: left;">Email</th>
                            <th style="padding: 1rem; text-align: left;">Telepon</th>
                            <th style="padding: 1rem; text-align: left;">Pesan (Preview)</th>
                            <th style="padding: 1rem; text-align: left;">Tanggal</th>
                            <th style="padding: 1rem; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr style="border-bottom: 1px solid #e9ecef;">
                                <td style="padding: 1rem; font-weight: 600; color: #2c3e50;">
                                    <?php echo htmlspecialchars($msg['name']); ?>
                                </td>
                                <td style="padding: 1rem; color: #6c757d;">
                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: #667eea; text-decoration: none;">
                                        <?php echo htmlspecialchars($msg['email'] ?? '-'); ?>
                                    </a>
                                </td>
                                <td style="padding: 1rem; color: #6c757d;">
                                    <a href="tel:<?php echo htmlspecialchars($msg['phone']); ?>" style="color: #667eea; text-decoration: none;">
                                        <?php echo htmlspecialchars($msg['phone']); ?>
                                    </a>
                                </td>
                                <td style="padding: 1rem; color: #6c757d; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . (strlen($msg['message']) > 50 ? '...' : ''); ?>
                                </td>
                                <td style="padding: 1rem; color: #6c757d; font-size: 0.9rem;">
                                    <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <a href="#" onclick="showMessage(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars(addslashes($msg['name'])); ?>', '<?php echo htmlspecialchars(addslashes($msg['email'])); ?>', '<?php echo htmlspecialchars(addslashes($msg['phone'])); ?>', '<?php echo htmlspecialchars(addslashes($msg['message'])); ?>', '<?php echo htmlspecialchars($msg['created_at']); ?>')" 
                                       style="padding: 0.5rem 1rem; background: #667eea; color: #fff; text-decoration: none; border-radius: 6px; margin-right: 0.5rem; display: inline-block;">
                                        👁️ Lihat
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus pesan ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" style="padding: 0.5rem 1rem; background: #dc3545; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Message Detail Modal -->
<div id="messageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 15px; padding: 2rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 2px solid #e9ecef; padding-bottom: 1rem;">
            <h3 id="modalTitle" style="margin: 0; color: #2c3e50;">Detail Pesan</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6c757d;">✕</button>
        </div>
        
        <div id="modalContent">
            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; color: #2c3e50;">Nama:</label>
                <p id="msgName" style="color: #6c757d; margin: 0.5rem 0;"></p>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; color: #2c3e50;">Email:</label>
                <p id="msgEmail" style="color: #6c757d; margin: 0.5rem 0;"></p>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; color: #2c3e50;">Telepon:</label>
                <p id="msgPhone" style="color: #6c757d; margin: 0.5rem 0;"></p>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label style="font-weight: 600; color: #2c3e50;">Tanggal:</label>
                <p id="msgDate" style="color: #6c757d; margin: 0.5rem 0;"></p>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600; color: #2c3e50;">Pesan:</label>
                <div id="msgContent" style="background: #667eea; padding: 1rem; border-radius: 8px; color: #fff; line-height: 1.6; border-left: 4px solid #764ba2; margin-top: 0.5rem;">
                </div>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button onclick="closeModal()" style="flex: 1; padding: 0.75rem; background: #6c757d; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.admin-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: white;
    margin-bottom: 2rem;
}

.content-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.card-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.5rem;
}

.form-control {
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.admin-table tbody tr:hover {
    background: #f8f9fa;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .admin-table {
        font-size: 0.9rem;
    }

    .admin-table th,
    .admin-table td {
        padding: 0.75rem !important;
    }
}
</style>

<script>
function showMessage(id, name, email, phone, message, date) {
    document.getElementById('msgName').textContent = name;
    document.getElementById('msgEmail').innerHTML = '<a href="mailto:' + email + '" style="color: #667eea; text-decoration: none;">' + email + '</a>';
    document.getElementById('msgPhone').innerHTML = '<a href="tel:' + phone + '" style="color: #667eea; text-decoration: none;">' + phone + '</a>';
    document.getElementById('msgContent').textContent = message;
    
    const dateObj = new Date(date);
    const formattedDate = dateObj.toLocaleDateString('id-ID') + ' ' + dateObj.toLocaleTimeString('id-ID');
    document.getElementById('msgDate').textContent = formattedDate;
    
    document.getElementById('messageModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('messageModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
