<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nip_nidn FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_nip = $stmt->fetchColumn() ?: '';

// Find internal dosen_id from the central 'dosen' table using NIP
$stmt = $pdo->prepare("SELECT id FROM dosen WHERE nip = ?");
$stmt->execute([$user_nip]);
$internal_dosen_id = $stmt->fetchColumn() ?: 0;

// Fetch actual stats
$stmt = $pdo->prepare("SELECT 
    COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved,
    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected,
    COUNT(*) as total
    FROM pengajuan_rps WHERE dosen_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Fetch Assigned Courses (to know what to work on)
$stmt = $pdo->prepare("SELECT mk.*, (SELECT status FROM pengajuan_rps WHERE mk_id = mk.id AND dosen_id = mk.dosen_id LIMIT 1) as rps_status 
                       FROM mata_kuliah mk WHERE mk.dosen_id = ?");
$stmt->execute([$internal_dosen_id]);
$assigned_mk = $stmt->fetchAll();

$completed_rps = 0;
foreach($assigned_mk as $amk) {
    if($amk['rps_status'] === 'Approved') $completed_rps++;
}
$stats['percentage'] = count($assigned_mk) > 0 ? round(($completed_rps / count($assigned_mk)) * 100) : 0;

// Fetch recent RPS
$stmt = $pdo->prepare("SELECT p.*, m.nama_mk FROM pengajuan_rps p 
                       JOIN mata_kuliah m ON p.mk_id = m.id 
                       WHERE p.dosen_id = ? ORDER BY p.tanggal_update DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_rps = $stmt->fetchAll();
?>

<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 24px; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 20px; border: 1px solid #f1f5f9; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-4px); }
    .stat-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .stat-icon.approved { background: #ecfdf5; color: #10b981; }
    .stat-icon.pending { background: #fff7ed; color: #f59e0b; }
    .stat-icon.rejected { background: #fef2f2; color: #ef4444; }
    .stat-icon.total { background: #eff6ff; color: #3b82f6; }
    .stat-info h3 { font-size: 24px; font-weight: 800; color: #1e293b; margin: 0; }
    .stat-info p { font-size: 13px; color: #64748b; margin: 4px 0 0; font-weight: 500; }

    .dashboard-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 25px; }
    .card { background: white; border-radius: 20px; padding: 25px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); height: 100%; }
    .card-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
    
    .table-modern { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .table-modern th { text-align: left; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; padding: 0 15px 10px; }
    .table-modern td { background: #f8fafc; padding: 15px; first-child { border-radius: 12px 0 0 12px; } last-child { border-radius: 0 12px 12px 0; } }
    
    .badge { padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }

    .notif-item { padding: 15px; border-radius: 15px; border: 1px solid #f1f5f9; margin-bottom: 12px; display: flex; gap: 15px; transition: 0.2s; }
    .notif-item:hover { border-color: #3b82f6; background: #f0f7ff; }
</style>

<div class="page-header" style="margin-bottom: 30px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #1e293b;">Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>!</h2>
    <p style="color: #64748b;">Kelola Rencana Pembelajaran Semester Anda dengan mudah dan terstruktur.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon approved"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info"><h3><?php echo $stats['approved']; ?></h3><p>RPS Disetujui</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
        <div class="stat-info"><h3><?php echo $stats['pending']; ?></h3><p>RPS Pending</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rejected"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-info"><h3><?php echo $stats['rejected']; ?></h3><p>Perlu Revisi</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon total"><i class="fas fa-percentage"></i></div>
        <div class="stat-info"><h3><?php echo $stats['percentage']; ?>%</h3><p>Total Progres</p></div>
    </div>
</div>

<div class="dashboard-grid">
    <div style="display: flex; flex-direction: column; gap: 25px;">
        <div class="card">
            <div class="card-title">Mata Kuliah Diampu (Sesuai Kurikulum)</div>
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th>Status RPS</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($assigned_mk) > 0): ?>
                            <?php foreach ($assigned_mk as $mk): ?>
                                <tr>
                                    <td><span style="font-family: monospace; font-weight: 700; color: #3b82f6;"><?php echo $mk['kode_mk']; ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($mk['nama_mk']); ?></strong></td>
                                    <td>
                                        <?php if ($mk['rps_status']): ?>
                                            <span class="badge badge-<?php echo strtolower($mk['rps_status']); ?>"><?php echo $mk['rps_status']; ?></span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #94a3b8;">Belum Dibuat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><a href="data_master.php" class="btn btn-sm btn-primary" style="padding: 5px 15px; border-radius: 8px; font-size: 12px; text-decoration: none;">Kelola <i class="fas fa-arrow-right"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada penugasan mata kuliah.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Aktivitas Pengajuan Terbaru</div>
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_rps) > 0): ?>
                            <?php foreach ($recent_rps as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars((string)$row['nama_mk']); ?></strong></td>
                                    <td><span class="badge badge-<?php echo strtolower((string)$row['status']); ?>"><?php echo (string)$row['status']; ?></span></td>
                                    <td><span style="font-size: 12px; color: #64748b;"><?php echo date('d M Y', strtotime((string)$row['tanggal_update'])); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada riwayat aktivitas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Notifikasi & Peringatan</div>
        <div class="notif-list">
            <?php 
            $notif_count = 0;
            foreach ($recent_rps as $row) {
                if ($row['status'] === 'Rejected') {
                    $notif_count++;
                    ?>
                    <div class="notif-item">
                        <div style="background: #fef2f2; color: #ef4444; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <p style="font-size: 13px; font-weight: 700; margin: 0; color: #1e293b;">Revisi RPS Diperlukan</p>
                            <p style="font-size: 12px; color: #64748b; margin: 4px 0 8px;"><?php echo htmlspecialchars((string)$row['nama_mk']); ?> ditolak oleh Kaprodi.</p>
                            <div style="font-size: 11px; background: #f8fafc; padding: 8px; border-radius: 8px; border: 1px dashed #e2e8f0; color: #475569; font-style: italic;">
                                "<?php echo htmlspecialchars((string)$row['catatan_revisi']); ?>"
                            </div>
                            <small style="color: #94a3b8; font-size: 10px; margin-top: 8px; display: block;"><?php echo date('d M Y', strtotime((string)$row['tanggal_update'])); ?></small>
                        </div>
                    </div>
                    <?php
                }
            }
            if ($notif_count === 0): ?>
                <div class="notif-item">
                    <div style="background: #eff6ff; color: #3b82f6; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <p style="font-size: 13px; font-weight: 700; margin: 0; color: #1e293b;">Semua Aman</p>
                        <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">Tidak ada revisi atau tugas mendesak saat ini.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="notif-item" style="margin-top: 20px; opacity: 0.8;">
                <div style="background: #f1f5f9; color: #475569; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <p style="font-size: 13px; font-weight: 700; margin: 0;">Informasi Kurikulum</p>
                    <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;">Pembaruan CPL 2024 telah tersedia untuk dipetakan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php include 'includes/footer.php'; ?>
