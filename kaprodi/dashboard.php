<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$prodi_id = $_SESSION['prodi_id'];

// Fetch actual stats for this prodi
$stmt = $pdo->prepare("SELECT COUNT(*) as total_mk FROM mata_kuliah WHERE prodi_id = ?");
$stmt->execute([$prodi_id]);
$total_mk = $stmt->fetch()['total_mk'];

$stmt = $pdo->prepare("SELECT 
    COUNT(CASE WHEN p.status = 'Approved' THEN 1 END) as approved,
    COUNT(CASE WHEN p.status = 'Pending' THEN 1 END) as pending,
    COUNT(CASE WHEN p.status = 'Rejected' THEN 1 END) as rejected
    FROM pengajuan_rps p
    JOIN mata_kuliah m ON p.mk_id = m.id
    WHERE m.prodi_id = ?");
$stmt->execute([$prodi_id]);
$stats = $stmt->fetch();

$perc_approved = $total_mk > 0 ? round(($stats['approved'] / $total_mk) * 100) : 0;
$pending_count = $stats['pending'];
$rejected_count = $stats['rejected']; // Using this as 'Needs Update'

// Fetch recent notifications (recent submissions in prodi)
$stmt = $pdo->prepare("SELECT p.*, m.nama_mk, u.nama_lengkap as dosen_name FROM pengajuan_rps p 
                       JOIN mata_kuliah m ON p.mk_id = m.id 
                       JOIN users u ON p.dosen_id = u.id
                       WHERE m.prodi_id = ? 
                       ORDER BY p.tanggal_update DESC LIMIT 3");
$stmt->execute([$prodi_id]);
$notifications = $stmt->fetchAll();

// Fetch chart data: RPS counts per semester
$stmt = $pdo->prepare("SELECT m.semester_default as semester, 
                       COUNT(*) as total,
                       COUNT(CASE WHEN p.status = 'Approved' THEN 1 END) as approved
                       FROM mata_kuliah m
                       LEFT JOIN pengajuan_rps p ON m.id = p.mk_id
                       WHERE m.prodi_id = ?
                       GROUP BY m.semester_default
                       ORDER BY m.semester_default");
$stmt->execute([$prodi_id]);
$chart_data = $stmt->fetchAll();

$semesters = [];
$approved_counts = [];
foreach ($chart_data as $row) {
    $semesters[] = "Sem " . $row['semester'];
    $approved_counts[] = $row['approved'];
}
?>

<div class="page-header">
    <h2>Dashboard Kaprodi</h2>
    <p>Monitoring capaian RPS dan persetujuan prodi Anda.</p>
</div>

<div class="stats-grid">
    <a href="manajemen.php" class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0.05)); color: #0ea5e9;">
            <i class="fas fa-chart-pie"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $perc_approved; ?>%</h3>
            <p>RPS Terisi</p>
        </div>
    </a>
    <a href="persetujuan_rps.php" class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05)); color: #f59e0b;">
            <i class="fas fa-file-signature"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_count; ?></h3>
            <p>Menunggu Persetujuan</p>
        </div>
    </a>
    <a href="persetujuan_rps.php?filter=rejected" class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.05)); color: #ef4444;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $rejected_count; ?></h3>
            <p>Butuh Revisi</p>
        </div>
    </a>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-title">Grafik Capaian RPS</div>
        <div class="chart-container" style="position: relative; height:250px; width:100%">
            <canvas id="rpsChart"></canvas>
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
                            <p><strong><?php echo $notif['status']; ?>:</strong> <?php echo htmlspecialchars($notif['nama_mk']); ?> 
                                <span style="color: #94a3b8; font-weight: 400;">oleh</span> <?php echo htmlspecialchars($notif['dosen_name']); ?>
                            </p>
                            <small><i class="far fa-clock"></i> <?php echo date('d M Y, H:i', strtotime($notif['tanggal_update'])); ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li style="color: #94a3b8; font-size: 14px;">Tidak ada aktivitas terbaru.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('rpsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($semesters); ?>,
            datasets: [{
                label: 'RPS Disetujui',
                data: <?php echo json_encode($approved_counts); ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgba(59, 130, 246, 1)',
                hoverBackgroundColor: '#2563eb',
                borderWidth: 0,
                borderRadius: 8,
                barThickness: 25
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
