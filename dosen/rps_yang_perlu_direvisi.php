<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nip_nidn FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_nip = $stmt->fetchColumn() ?: '';

// Find internal dosen_id
$stmt = $pdo->prepare("SELECT id FROM dosen WHERE nip = ?");
$stmt->execute([$user_nip]);
$internal_dosen_id = $stmt->fetchColumn() ?: 0;

// Fetch only Rejected RPS
$stmt = $pdo->prepare("SELECT p.*, m.kode_mk, m.nama_mk 
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       WHERE p.dosen_id = ? AND p.status = 'Rejected'
                       ORDER BY p.tanggal_update DESC");
$stmt->execute([$internal_dosen_id]);
$revisions = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>RPS yang Perlu Direvisi</h2>
    <p>Segera perbaiki RPS Anda berdasarkan catatan dari Kaprodi untuk mendapatkan persetujuan.</p>
</div>

<div class="revision-container">
    <?php if (count($revisions) > 0): ?>
        <?php foreach ($revisions as $row): ?>
            <div class="card revision-card" style="margin-bottom: 20px; border-left: 5px solid #ef4444;">
                <div class="revision-header" style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9; margin-bottom: 15px;">
                    <div class="mk-info">
                        <span class="badge badge-danger" style="background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; margin-bottom: 10px; display: inline-block;">Perlu Revisi</span>
                        <h3 style="margin: 0; color: #1e293b;"><?php echo htmlspecialchars((string)$row['kode_mk'] . ' - ' . (string)$row['nama_mk']); ?></h3>
                        <p style="margin: 5px 0 0; color: #64748b; font-size: 13px;"><?php echo htmlspecialchars((string)$row['semester']); ?></p>
                    </div>
                    <a href="buat_rps_baru.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary" style="background: #1e293b; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600;">
                        <i class="fas fa-edit"></i> Perbaiki Sekarang
                    </a>
                </div>
                <div class="revision-note" style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px dashed #cbd5e1;">
                    <div class="note-title" style="font-weight: 700; color: #475569; margin-bottom: 8px; font-size: 13px;">
                        <i class="fas fa-comment-dots" style="color: #3b82f6;"></i> Catatan Koreksi Kaprodi:
                    </div>
                    <p style="font-style: italic; color: #1e293b; line-height: 1.6; margin-bottom: 10px;">
                        "<?php echo nl2br(htmlspecialchars((string)$row['catatan_revisi'])); ?>"
                    </p>
                    <small style="color: #94a3b8; font-size: 11px;">Dikirim pada: <?php echo date('d M Y, H:i', strtotime((string)$row['tanggal_update'])); ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 50px; color: #e2e8f0; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 style="color: #64748b; margin-bottom: 10px;">Luar Biasa!</h3>
            <p style="color: #94a3b8;">Tidak ada RPS yang perlu direvisi saat ini. Semua pengajuan Anda dalam status aman.</p>
            <a href="daftar_rps.php" class="btn btn-outline" style="margin-top: 20px; border: 1px solid #e2e8f0; color: #64748b; display: inline-block; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Lihat Semua RPS</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
