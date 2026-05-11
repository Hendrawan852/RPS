<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
// Get internal_dosen_id
$stmt = $pdo->prepare("SELECT d.id FROM dosen d JOIN users u ON d.nip = u.nip_nidn WHERE u.id = ?");
$stmt->execute([$user_id]);
$internal_dosen_id = $stmt->fetchColumn() ?: 0;

// Fetch ALL Approved RPS (as references)
$stmt = $pdo->prepare("SELECT p.*, m.kode_mk, m.nama_mk, d.nama as nama_dosen 
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       JOIN dosen d ON p.dosen_id = d.id
                       WHERE p.status = 'Approved'
                       ORDER BY p.tanggal_update DESC");
$stmt->execute();
$archives = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Arsip & Referensi RPS</h2>
    <p>Gunakan RPS lama yang sudah disetujui sebagai referensi atau template resmi.</p>
</div>

<style>
    .archive-container { display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; }
    .card-modern { background: white; border-radius: 20px; padding: 25px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .card-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    
    .archive-list { list-style: none; padding: 0; margin: 0; }
    .archive-item { 
        display: flex; justify-content: space-between; align-items: center; 
        padding: 15px; border-radius: 12px; background: #f8fafc; margin-bottom: 12px; 
        border: 1px solid transparent; transition: 0.2s;
    }
    .archive-item:hover { border-color: #3b82f6; background: #fff; transform: translateX(5px); }
    .archive-info h4 { margin: 0; font-size: 14px; color: #1e293b; font-weight: 700; }
    .archive-info p { margin: 4px 0 0; font-size: 12px; color: #64748b; }
    
    .guide-item { 
        display: flex; gap: 15px; align-items: center; padding: 15px; 
        border-radius: 12px; border: 1px solid #f1f5f9; margin-bottom: 12px;
        transition: 0.2s; cursor: pointer;
    }
    .guide-item:hover { background: #f8fafc; border-color: #3b82f6; }
    .guide-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    
    .btn-download { 
        width: 36px; height: 36px; border-radius: 10px; border: none; 
        background: #eff6ff; color: #3b82f6; cursor: pointer; transition: 0.2s;
    }
    .btn-download:hover { background: #3b82f6; color: white; }
</style>

<div class="page-header" style="margin-bottom: 30px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #1e293b;">Arsip & Referensi</h2>
    <p style="color: #64748b;">Gunakan RPS yang sudah disetujui sebagai referensi atau unduh panduan resmi.</p>
</div>

<div class="archive-container">
    <div class="card-modern">
        <div class="card-title"><i class="fas fa-box-archive" style="color: #3b82f6;"></i> Arsip RPS Approved</div>
        <div class="archive-list">
            <?php if (empty($archives)): ?>
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-folder-open fa-3x" style="margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>Belum ada arsip RPS yang disetujui.</p>
                </div>
            <?php else: ?>
                <?php foreach ($archives as $arc): ?>
                    <div class="archive-item">
                        <div class="archive-info">
                            <h4><?php echo htmlspecialchars($arc['nama_mk']); ?> (<?php echo $arc['kode_mk']; ?>)</h4>
                            <p>Oleh: <strong><?php echo htmlspecialchars($arc['nama_dosen']); ?></strong> • Diupdate: <?php echo date('d M Y', strtotime($arc['tanggal_update'])); ?></p>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="cetak_rps.php?id=<?php echo $arc['id']; ?>" target="_blank" class="btn-download" title="Unduh / Cetak RPS" style="display: flex; align-items: center; justify-content: center;"><i class="fas fa-file-pdf"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-modern">
        <div class="card-title"><i class="fas fa-info-circle" style="color: #f59e0b;"></i> Template & Panduan</div>
        
        <div class="guide-item">
            <div class="guide-icon" style="background: #ecfdf5; color: #10b981;"><i class="fas fa-file-word"></i></div>
            <div style="flex: 1;">
                <h5 style="margin: 0; font-size: 14px; font-weight: 700;">Template RPS SN-DIKTI</h5>
                <p style="margin: 4px 0 0; font-size: 12px; color: #64748b;">Format standar sesuai standar nasional pendidikan.</p>
            </div>
            <i class="fas fa-download" style="color: #94a3b8;"></i>
        </div>

        <div class="guide-item">
            <div class="guide-icon" style="background: #fef2f2; color: #ef4444;"><i class="fas fa-file-pdf"></i></div>
            <div style="flex: 1;">
                <h5 style="margin: 0; font-size: 14px; font-weight: 700;">Panduan OBE (Outcome Based Education)</h5>
                <p style="margin: 4px 0 0; font-size: 12px; color: #64748b;">Langkah penyusunan CPL dan CPMK yang tepat.</p>
            </div>
            <i class="fas fa-download" style="color: #94a3b8;"></i>
        </div>

        <div class="guide-item">
            <div class="guide-icon" style="background: #eff6ff; color: #3b82f6;"><i class="fas fa-book-reader"></i></div>
            <div style="flex: 1;">
                <h5 style="margin: 0; font-size: 14px; font-weight: 700;">Manual Sistem RPS v1.0</h5>
                <p style="margin: 4px 0 0; font-size: 12px; color: #64748b;">Tata cara penggunaan aplikasi bagi dosen.</p>
            </div>
            <i class="fas fa-download" style="color: #94a3b8;"></i>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
