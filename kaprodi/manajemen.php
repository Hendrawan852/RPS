<?php
require_once '../config/database.php';
session_start();

// Always read prodi_id fresh from the database to prevent stale session issues
$stmt = $pdo->prepare("SELECT prodi_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$prodi_id = $stmt->fetchColumn();

include 'includes/header.php';
include 'includes/sidebar.php';


// 2. Fetch all RPS for this prodi
$stmt = $pdo->prepare("SELECT p.*, m.kode_mk, m.nama_mk, m.tahun_ajaran, d.nama as dosen_name 
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       LEFT JOIN dosen d ON m.dosen_id = d.id
                       WHERE m.prodi_id = ?
                       ORDER BY p.tanggal_update DESC");
$stmt->execute([$prodi_id]);
$archives = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="header-content">
        <h2>Manajemen RPS Prodi</h2>
        <p>Arsip dan pencarian keseluruhan Rencana Pembelajaran Semester di program studi Anda.</p>
    </div>
    <div class="header-actions">
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Cari MK atau Dosen...">
        </div>
    </div>
</div>

<div class="card">
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="card-title">
        <span>Semua Arsip RPS</span>
        <div class="card-actions">
            <button class="btn btn-outline-primary btn-sm"><i class="fas fa-download"></i> Export Excel</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="archiveTable">
            <thead>
                <tr>
                    <th>Mata Kuliah / Kode</th>
                    <th>Dosen Pengampu</th>
                    <th>Status</th>
                    <th>Tahun Ajaran</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($archives) > 0): ?>
                    <?php foreach ($archives as $row): ?>
                        <tr>
                            <td>
                                <div class="mk-info">
                                    <strong><?php echo htmlspecialchars($row['nama_mk']); ?></strong>
                                    <code><?php echo htmlspecialchars($row['kode_mk']); ?></code>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['dosen_name']); ?></td>
                            <td class="status-cell">
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y', strtotime($row['tanggal_update'])); ?>/<?php echo date('Y', strtotime($row['tanggal_update'])) + 1; ?></td>
                            <td>
                                <div class="actions" style="justify-content: flex-end; display: flex; gap: 8px;">
                                    <a href="../dosen/cetak_rps.php?id=<?php echo $row['mk_id']; ?>" target="_blank" class="btn-action primary" style="background: #eff6ff; color: #3b82f6;" title="Download PDF"><i class="fas fa-file-pdf"></i></a>
                                    
                                    <button class="btn-action info detail-btn" 
                                            data-id="<?php echo $row['id']; ?>" 
                                            data-mk="<?php echo htmlspecialchars($row['nama_mk']); ?>"
                                            style="background: #f1f5f9; color: #64748b;" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 60px; color: #94a3b8;">
                            <i class="fas fa-folder-open fa-3x" style="margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                            Belum ada arsip RPS untuk program studi ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



<!-- Modal Review (Tinjau Isi) -->
<div id="reviewModal" class="modal">
    <div class="modal-content" style="max-width: 800px; width: 90%;">
        <div class="modal-header">
            <h3>Arsip RPS: <span id="review_mk_name"></span></h3>
            <span class="close">&times;</span>
        </div>
        <div id="review_body" style="margin-top: 20px; max-height: 500px; overflow-y: auto;">
            <div style="text-align: center; padding: 20px;">
                <i class="fas fa-spinner fa-spin"></i> Memuat data...
            </div>
        </div>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
.modal-content { background: white; margin: 5% auto; padding: 25px; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
.modal-header h3 { font-size: 18px; font-weight: 800; color: #1e293b; margin: 0; }
.close { cursor: pointer; font-size: 24px; color: #94a3b8; }

.review-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.review-table th, .review-table td { border: 1px solid #e2e8f0; padding: 10px; text-align: left; font-size: 13px; }
.review-table th { background: #f8fafc; font-weight: 700; color: #64748b; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const reviewModal = document.getElementById('reviewModal');
    const revisiModal = document.getElementById('revisiModal');
    
    // Search logic
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#archiveTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Detail modal logic
    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            document.getElementById('review_mk_name').innerText = this.dataset.mk;
            reviewModal.style.display = 'block';
            document.getElementById('review_body').innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>';
            
            fetch('api/get_rps_content.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('review_body').innerHTML = html;
                });
        }
    });

    // Close Modals
    document.querySelectorAll('.close').forEach(el => {
        el.onclick = function() {
            reviewModal.style.display = 'none';
        }
    });

    window.onclick = function(event) {
        if (event.target == reviewModal) reviewModal.style.display = 'none';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
