<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$prodi_id = $_SESSION['prodi_id'];

// Fetch actual stats for this prodi
$stmt = $pdo->prepare("SELECT 
    COUNT(CASE WHEN p.status = 'Approved' THEN 1 END) as approved,
    COUNT(CASE WHEN p.status = 'Pending' THEN 1 END) as pending,
    COUNT(CASE WHEN p.status = 'Rejected' THEN 1 END) as rejected,
    COUNT(*) as total
    FROM pengajuan_rps p
    JOIN users u ON p.dosen_id = u.id
    WHERE u.prodi_id = ?");
$stmt->execute([$prodi_id]);
$stats = $stmt->fetch();

$perc_approved = $stats['total'] > 0 ? round(($stats['approved'] / $stats['total']) * 100) : 0;
$pending_count = $stats['pending'];
$rejected_count = $stats['rejected']; // Using this as 'Needs Update'

// Fetch recent notifications (recent submissions in prodi)
$stmt = $pdo->prepare("SELECT p.*, m.nama_mk, u.nama_lengkap as dosen_name FROM pengajuan_rps p 
                       JOIN mata_kuliah m ON p.mk_id = m.id 
                       JOIN users u ON p.dosen_id = u.id
                       WHERE u.prodi_id = ? 
                       ORDER BY p.tanggal_update DESC LIMIT 3");
$stmt->execute([$prodi_id]);
$notifications = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Dashboard Kaprodi</h2>
    <p>Monitoring capaian RPS dan persetujuan prodi Anda.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(56, 189, 248, 0.1); color: #0ea5e9;">
            <i class="fas fa-percent"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $perc_approved; ?>%</h3>
            <p>RPS Approved</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_count; ?></h3>
            <p>Pending Approval</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $expired_alert; ?></h3>
            <p>Perlu Diperbarui</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-title">Grafik Capaian RPS</div>
        <div class="chart-placeholder">
            <i class="fas fa-chart-area fa-3x"></i>
            <p>Grafik per semester akan tampil di sini</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-title">Notifikasi Terbaru</div>
        <ul class="notif-list">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notif): ?>
                    <?php 
                        $dot_class = 'blue';
                        if ($notif['status'] === 'Approved') $dot_class = 'green';
                        if ($notif['status'] === 'Rejected') $dot_class = 'red';
                    ?>
                    <li>
                        <div class="notif-dot <?php echo $dot_class; ?>"></div>
                        <div class="notif-text">
                            <p><strong><?php echo $notif['status']; ?>:</strong> <?php echo htmlspecialchars($notif['nama_mk']); ?> (<?php echo htmlspecialchars($notif['dosen_name']); ?>)</p>
                            <small><?php echo date('d M Y, H:i', strtotime($notif['tanggal_update'])); ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li style="color: #94a3b8; font-size: 14px;">Tidak ada aktivitas terbaru.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
