<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$prodi_id = $_SESSION['prodi_id'];

// 1. Handle Status Update Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = $_POST['id'];
    $new_status = ($_POST['action'] === 'approve') ? 'Approved' : 'Rejected';
    $catatan = isset($_POST['catatan']) ? $_POST['catatan'] : null;
    
    try {
        // Ensure the RPS belongs to a mata kuliah in the same prodi as the Kaprodi
        $stmt = $pdo->prepare("UPDATE pengajuan_rps p
                               JOIN mata_kuliah m ON p.mk_id = m.id
                               SET p.status = ?, p.catatan_revisi = ? 
                               WHERE p.id = ? AND m.prodi_id = ?");
        $stmt->execute([$new_status, $catatan, $id, $prodi_id]);
        $_SESSION['msg'] = "Status RPS berhasil diperbarui menjadi $new_status.";
    } catch (PDOException $e) {
        $_SESSION['err'] = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// 2. Fetch submissions for Kaprodi's Prodi
$stmt = $pdo->prepare("SELECT p.*, m.kode_mk, m.nama_mk, d.nama as dosen_name 
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       JOIN dosen d ON m.dosen_id = d.id
                       WHERE m.prodi_id = ?
                       ORDER BY p.tanggal_update DESC");
$stmt->execute([$prodi_id]);
$submissions = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="header-content">
        <h2>Persetujuan RPS</h2>
        <p>Tinjau dan berikan persetujuan untuk RPS yang diajukan oleh dosen.</p>
    </div>
</div>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
    </div>
<?php endif; ?>

<div class="card filter-card">
    <div class="filter-grid">
        <select class="form-control">
            <option>Semua Dosen</option>
        </select>
        <select class="form-control">
            <option>Semua Mata Kuliah</option>
        </select>
        <select class="form-control">
            <option value="">Semua Semester</option>
            <option value="Gasal 2023/2024">Gasal 2023/2024</option>
            <option value="Genap 2023/2024">Genap 2023/2024</option>
            <option value="Gasal 2024/2025" selected>Gasal 2024/2025</option>
            <option value="Genap 2024/2025">Genap 2024/2025</option>
        </select>
        <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>MK / Kode</th>
                    <th>Dosen Pengampu</th>
                    <th>Status</th>
                    <th>Tanggal Update</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($submissions) > 0): ?>
                    <?php foreach ($submissions as $row): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['nama_mk']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['kode_mk']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['dosen_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_update'])); ?></td>
                            <td>
                                <div class="actions" style="justify-content: flex-end; display: flex; gap: 8px;">
                                    <button class="btn-action primary review-btn" 
                                            data-id="<?php echo $row['id']; ?>" 
                                            data-mk="<?php echo htmlspecialchars($row['nama_mk']); ?>"
                                            title="Tinjau Isi RPS">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                    <?php if ($row['status'] === 'Pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn-action success" title="Approve" onclick="return confirm('Setujui RPS ini?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button class="btn-action danger revisi-btn" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-mk="<?php echo htmlspecialchars($row['nama_mk']); ?>"
                                                title="Tolak / Beri Catatan Revisi">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                            Tidak ada pengajuan RPS untuk program studi ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Revisi -->
<div id="revisiModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Beri Catatan Revisi</h3>
            <span class="close">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="id" id="revisi_id">
            <input type="hidden" name="action" value="reject">
            <div class="form-group" style="margin-top: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Apa yang perlu diperbaiki pada RPS <span id="revisi_mk_name"></span>?</label>
                <textarea name="catatan" class="form-control" rows="5" placeholder="Contoh: Deskripsi MK kurang detail, materi minggu ke-4 perlu diperbarui..." required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;"></textarea>
            </div>
            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary close-modal">Batal</button>
                <button type="submit" class="btn btn-danger" style="background: #ef4444; color: white;">Kirim Catatan Revisi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Review (Tinjau Isi) -->
<div id="reviewModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3>Review Isi RPS: <span id="review_mk_name"></span></h3>
            <span class="close">&times;</span>
        </div>
        <div id="review_body" style="margin-top: 20px; max-height: 500px; overflow-y: auto;">
            <!-- Content will be loaded here via AJAX -->
            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-spinner fa-spin"></i> Memuat data...
            </div>
        </div>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
.modal-content { background: white; margin: 5% auto; padding: 25px; border-radius: 12px; width: 90%; max-width: 500px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
.close { cursor: pointer; font-size: 24px; color: #94a3b8; }
.btn-secondary { background: #f1f5f9; color: #64748b; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; }
.btn-danger { padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; }

/* Table style for review content */
.review-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.review-table th, .review-table td { border: 1px solid #e2e8f0; padding: 10px; text-align: left; font-size: 13px; }
.review-table th { background: #f8fafc; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const revisiModal = document.getElementById('revisiModal');
    const reviewModal = document.getElementById('reviewModal');
    
    // Open Revisi Modal
    document.querySelectorAll('.revisi-btn').forEach(btn => {
        btn.onclick = function() {
            document.getElementById('revisi_id').value = this.dataset.id;
            document.getElementById('revisi_mk_name').innerText = this.dataset.mk;
            revisiModal.style.display = 'block';
        }
    });

    // Open Review Modal & Load Content
    document.querySelectorAll('.review-btn').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            document.getElementById('review_mk_name').innerText = this.dataset.mk;
            reviewModal.style.display = 'block';
            document.getElementById('review_body').innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>';
            
            // Fetch RPS content via AJAX
            fetch('api/get_rps_content.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('review_body').innerHTML = html;
                });
        }
    });

    // Close Modals
    document.querySelectorAll('.close, .close-modal').forEach(el => {
        el.onclick = function() {
            revisiModal.style.display = 'none';
            reviewModal.style.display = 'none';
        }
    });

    window.onclick = function(event) {
        if (event.target == revisiModal) revisiModal.style.display = 'none';
        if (event.target == reviewModal) reviewModal.style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
