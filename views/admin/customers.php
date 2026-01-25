<?php
$page_title='Pengguna Terdaftar';
include('../../includes/admin-header.php');
include_once __DIR__ . '/../../db.php';

$db=new Database();
$conn=$db->getConnection();

$search=$_GET['search']??'';

$query="SELECT u.*
        FROM users u
        WHERE u.role='user'";

if($search){
    $query.=" AND (u.name LIKE :search
               OR u.email LIKE :search
               OR u.phone LIKE :search)";
}

$query.=" ORDER BY u.created_at DESC";

$stmt=$conn->prepare($query);

if($search){
    $stmt->bindValue(':search','%'.$search.'%');
}

$stmt->execute();
$users=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-container">
    <h1 class="page-title">Pengguna Terdaftar</h1>

    <div class="content-card">
        <div class="card-header">
            <h2>👤 Semua Pengguna</h2>
            <span style="background:#667eea;color:#fff;padding:0.5rem 1rem;border-radius:8px;">
                Total: <?php echo count($users); ?> Pengguna
            </span>
        </div>

        <!-- Search -->
        <form method="GET" style="margin-bottom:1.5rem;">
            <div style="display:flex;gap:1rem;">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Cari nama, email, atau telepon..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    style="flex:1;"
                >
                <button type="submit" class="btn-primary">🔍 Cari</button>
                <?php if($search): ?>
                    <a href="users.php" class="btn-warning">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if(count($users)>0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Status Akun</th>
                        <th>Keterangan</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td><strong>#<?php echo $user['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <a href="tel:<?php echo $user['phone']; ?>" style="color:#667eea;">
                                <?php echo htmlspecialchars($user['phone']); ?>
                            </a>
                        </td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($user['address']); ?>
                        </td>
                        <td>
                            <span style="background:#e8f5e9;color:#2e7d32;padding:0.3rem 0.8rem;border-radius:12px;font-weight:600;">
                                Aktif
                            </span>
                        </td>
                        <td style="color:#666;">
                            Terdaftar
                        </td>
                        <td><?php echo date('d/m/Y',strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="btn-primary btn-sm" onclick="viewUserDetail(<?php echo $user['id']; ?>)">
                                Detail
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">👤</div>
            <h3>Tidak ada pengguna</h3>
            <p>Belum ada pengguna yang terdaftar atau sesuai pencarian</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Statistik -->
    <?php
    $total_users=count($users);
    ?>

    <div class="stats-grid" style="margin-top:2rem;">
        <div class="stat-card">
            <div class="stat-icon blue">👤</div>
            <div class="stat-info">
                <h3>Total Pengguna</h3>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div class="stat-info">
                <h3>Akun Aktif</h3>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">🗓️</div>
            <div class="stat-info">
                <h3>Terdaftar Hari Ini</h3>
                <div class="stat-value">-</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">🌐</div>
            <div class="stat-info">
                <h3>Total Akun Website</h3>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div id="userModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:2rem;border-radius:12px;max-width:500px;width:90%;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h2>Detail Pengguna</h2>
            <button onclick="closeUserModal()" style="border:none;background:none;font-size:1.5rem;cursor:pointer;">×</button>
        </div>
        <div id="userDetailContent" style="margin-top:1rem;">
            <p>Memuat data...</p>
        </div>
    </div>
</div>

<script>
function viewUserDetail(id){
    document.getElementById('userModal').style.display='flex';
    document.getElementById('userDetailContent').innerHTML='Memuat data...';

    fetch('get-user-detail.php?id='+id)
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            document.getElementById('userDetailContent').innerHTML=`
                <p><strong>Nama:</strong> ${data.user.name}</p>
                <p><strong>Email:</strong> ${data.user.email}</p>
                <p><strong>Telepon:</strong> ${data.user.phone}</p>
                <p><strong>Alamat:</strong> ${data.user.address}</p>
            `;
        }else{
            document.getElementById('userDetailContent').innerHTML='Gagal memuat data';
        }
    });
}

function closeUserModal(){
    document.getElementById('userModal').style.display='none';
}
</script>

</body>
</html>
