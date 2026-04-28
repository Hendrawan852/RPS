<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$dosen_id = $_SESSION['user_id'];

// Fetch actual stats
$stmt = $pdo->prepare("SELECT 
    COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved,
    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending,
    COUNT(*) as total
    FROM pengajuan_rps WHERE dosen_id = ?");
$stmt->execute([$dosen_id]);
$stats = $stmt->fetch();
$stats['percentage'] = $stats['total'] > 0 ? round(($stats['approved'] / $stats['total']) * 100) : 0;

// Fetch recent RPS
$stmt = $pdo->prepare("SELECT p.*, m.nama_mk FROM pengajuan_rps p 
                       JOIN mata_kuliah m ON p.mk_id = m.id 
                       WHERE dosen_id = ? ORDER BY p.tanggal_update DESC LIMIT 5");
$stmt->execute([$dosen_id]);
$recent_rps = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h2>
    <p>Kelola Rencana Pembelajaran Semester Anda dengan mudah dan terstruktur.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon approved">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['approved']; ?></h3>
            <p>RPS Approved</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon draft">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['pending']; ?></h3>
            <p>RPS Pending</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon progress-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $stats['percentage']; ?>%</h3>
            <p>Penyelesaian</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-title">RPS Terbaru yang Dibuat</div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mata Kuliah</th>
                        <th>Status</th>
                        <th>Terakhir Diubah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_rps) > 0): ?>
                        <?php foreach ($recent_rps as $row): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['nama_mk']); ?></strong></td>
                                <td><span class="badge badge-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($row['tanggal_update'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center; color: #94a3b8;">Belum ada pengajuan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Notifikasi & Peringatan</div>
        <ul class="notif-list">
            <li class="notif-item revision">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="notif-body">
                    <p><strong>Revisi Diperlukan:</strong> RPS Struktur Data ditolak Kaprodi.</p>
                    <small>Kemarin, 14:00</small>
                </div>
            </li>
            <li class="notif-item update">
                <i class="fas fa-info-circle"></i>
                <div class="notif-body">
                    <p><strong>Info:</strong> Kurikulum CPL 2024 telah diperbarui.</p>
                    <small>2 hari yang lalu</small>
                </div>
            </li>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
