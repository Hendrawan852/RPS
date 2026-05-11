<?php
include 'includes/header.php';
include 'includes/sidebar.php';

// Dummy data for stats (Real deal would use COUNT() queries)
$total_rps = 120;
$total_mk = 45;
$total_dosen = 28;
$rps_selesai = 85; // 85%
?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p>Selamat datang kembali di panel administrasi RPS System.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card premium">
        <div class="stat-icon" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5;">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_rps; ?></h3>
            <p>Total RPS</p>
        </div>
    </div>
    <div class="stat-card premium">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_mk; ?></h3>
            <p>Total MK</p>
        </div>
    </div>
    <div class="stat-card premium">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_dosen; ?></h3>
            <p>Total Dosen</p>
        </div>
    </div>
    <div class="stat-card premium">
        <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $rps_selesai; ?>%</h3>
            <p>RPS Selesai</p>
        </div>
    </div>
</div>

<!-- Premium Feature Showcase -->
<div class="page-header" style="margin-top: 50px;">
    <h2>Sistem Capabilities</h2>
    <p>Lihat fitur unggulan dan opsi peningkatan sistem Anda.</p>
</div>


<div class="dashboard-grid">
    <!-- Notifications -->
    <div class="card">
        <div class="card-title">
            <span>Notifikasi Terbaru</span>
            <i class="fas fa-bell"></i>
        </div>
        <ul class="notification-list">
            <li>
                <div class="notif-icon"><i class="fas fa-file-signature"></i></div>
                <div class="notif-content">
                    <p><strong>RPS Baru</strong> perlu approval: Pemrograman Web (Dr. Hendrawan)</p>
                    <span>2 jam yang lalu</span>
                </div>
            </li>
            <li>
                <div class="notif-icon"><i class="fas fa-user-plus"></i></div>
                <div class="notif-content">
                    <p><strong>User Baru</strong> terdaftar: Budi Santoso (Dosen)</p>
                    <span>5 jam yang lalu</span>
                </div>
            </li>
            <li>
                <div class="notif-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="notif-content">
                    <p><strong>Deadline</strong> revisi RPS MK Sistem Basis Data besok!</p>
                    <span>10 jam yang lalu</span>
                </div>
            </li>
        </ul>
    </div>

    <!-- Quick Access -->
    <div class="card">
        <div class="card-title">
            <span>Akses Cepat</span>
            <i class="fas fa-bolt"></i>
        </div>
        <div class="quick-access-grid">
            <a href="manajemen_user.php" class="qa-item">
                <i class="fas fa-user-plus"></i>
                <span>Tambah User</span>
            </a>
            <a href="data_master.php" class="qa-item">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah MK</span>
            </a>
            <a href="manajemen_rps.php" class="qa-item">
                <i class="fas fa-check-double"></i>
                <span>Monitoring RPS</span>
            </a>
            <a href="laporan.php" class="qa-item">
                <i class="fas fa-file-pdf"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
