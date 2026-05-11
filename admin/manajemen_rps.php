<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Admin role is now for monitoring only. Approval is handled by Kaprodi.

// 2. Prepare Data Fetching with Filtering
$filter_status = isset($_GET['status']) && $_GET['status'] !== 'Semua Status' ? $_GET['status'] : null;
$filter_semester = isset($_GET['semester']) && $_GET['semester'] !== 'Semua Semester' ? $_GET['semester'] : null;

$sql = "SELECT p.*, m.kode_mk, m.nama_mk, m.tahun_ajaran, u.nama_lengkap as dosen_name 
        FROM pengajuan_rps p
        JOIN mata_kuliah m ON p.mk_id = m.id
        JOIN users u ON p.dosen_id = u.id
        WHERE 1=1"; // Admin monitors all RPS statuses

if ($filter_status) {
    $sql .= " AND p.status = :status";
}
if ($filter_semester) {
    $sql .= " AND p.semester = :semester";
}

$sql .= " ORDER BY p.tanggal_update DESC";

$stmt = $pdo->prepare($sql);
$params = [];
if ($filter_status) $params['status'] = $filter_status;
if ($filter_semester) $params['semester'] = $filter_semester;

$stmt->execute($params);
$submissions = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Monitoring RPS</h2>
    <p>Monitoring Rencana Pembelajaran Semester (RPS) yang telah ditinjau oleh Kaprodi.</p>
</div>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['err'])): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['err']; unset($_SESSION['err']); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-title">
        <span style="color: #f8fafc; font-size: 18px; font-weight: 700;">Pengajuan RPS Terbaru</span>
        <div class="filters" style="display: flex; gap: 10px;">
            <select class="form-select-sm" onchange="applyFilter('semester', this.value)">
                <option value="Semua Semester" <?php echo $filter_semester === null ? 'selected' : ''; ?>>Semua Semester</option>
                <option value="Gasal 2023/2024" <?php echo $filter_semester === 'Gasal 2023/2024' ? 'selected' : ''; ?>>Gasal 2023/2024</option>
                <option value="Genap 2023/2024" <?php echo $filter_semester === 'Genap 2023/2024' ? 'selected' : ''; ?>>Genap 2023/2024</option>
                <option value="Gasal 2024/2025" <?php echo $filter_semester === 'Gasal 2024/2025' ? 'selected' : ''; ?>>Gasal 2024/2025</option>
                <option value="Genap 2024/2025" <?php echo $filter_semester === 'Genap 2024/2025' ? 'selected' : ''; ?>>Genap 2024/2025</option>
            </select>
            <select class="form-select-sm" onchange="applyFilter('status', this.value)">
                <option value="Semua Status" <?php echo $filter_status === null ? 'selected' : ''; ?>>Semua Status</option>
                <option value="Pending" <?php echo $filter_status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Approved" <?php echo $filter_status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="Rejected" <?php echo $filter_status === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
    </div>
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>MK / Kode</th>
                    <th>Dosen Pengampu</th>
                    <th>Semester</th>
                    <th>Tahun Ajaran</th>
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
                                <div class="mk-info">
                                    <span class="mk-title"><?php echo htmlspecialchars($row['nama_mk']); ?></span>
                                    <span class="mk-code"><?php echo htmlspecialchars($row['kode_mk']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="dosen-info">
                                    <div class="dosen-avatar-sm"><?php echo strtoupper(substr($row['dosen_name'], 0, 1)); ?></div>
                                    <span class="dosen-name"><?php echo htmlspecialchars($row['dosen_name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                            <td><span class="badge-tag tag-slate"><?php echo htmlspecialchars($row['tahun_ajaran'] ?? '2024/2025'); ?></span></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_update'])); ?></td>
                            <td>
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <button class="btn-icon-only detail-btn" 
                                            data-id="<?php echo $row['id']; ?>" 
                                            data-mk="<?php echo htmlspecialchars($row['nama_mk']); ?>" 
                                            title="Lihat Detail">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            Tidak ada pengajuan RPS ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail RPS -->
<div id="detailModal" class="modal">
    <div class="modal-content" style="max-width: 900px; width: 95%;">
        <div class="modal-header">
            <h3>Detail RPS: <span id="detail_mk_name"></span></h3>
            <span class="close">&times;</span>
        </div>
        <div id="detail_body" style="margin-top: 20px; max-height: 70vh; overflow-y: auto; padding-right: 10px;">
            <div style="text-align: center; padding: 40px;">
                <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i>
                <p style="margin-top: 15px; color: var(--text-muted);">Memuat konten RPS...</p>
            </div>
        </div>
    </div>
</div>

<style>
.modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); }
.modal-content { background: white; margin: 3% auto; padding: 30px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid rgba(255,255,255,0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; }
.modal-header h3 { font-size: 20px; font-weight: 800; color: #1e293b; margin: 0; }
.close { cursor: pointer; font-size: 28px; color: #94a3b8; transition: 0.2s; }
.close:hover { color: #1e293b; }

/* Review Table styles */
.review-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
.review-table th, .review-table td { border: 1px solid #e2e8f0; padding: 12px; text-align: left; font-size: 12.5px; }
.review-table th { background: #f8fafc; font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('detailModal');
    const closeBtn = modal.querySelector('.close');

    document.querySelectorAll('.detail-btn').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            const mkName = this.dataset.mk;
            document.getElementById('detail_mk_name').innerText = mkName;
            modal.style.display = 'block';
            
            // Fetch content from Kaprodi's API (authorized for Admin now)
            fetch('../kaprodi/api/get_rps_content.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('detail_body').innerHTML = html;
                })
                .catch(err => {
                    document.getElementById('detail_body').innerHTML = '<div style="text-align: center; color: var(--danger); padding: 20px;"><i class="fas fa-exclamation-triangle"></i> Gagal memuat data.</div>';
                });
        }
    });

    closeBtn.onclick = function() { modal.style.display = 'none'; }
    window.onclick = function(event) { if (event.target == modal) modal.style.display = 'none'; }
});

function applyFilter(key, value) {
    const url = new URL(window.location.href);
    if (value === 'Semua Semester' || value === 'Semua Status' || value === '') {
        url.searchParams.delete(key);
    } else {
        url.searchParams.set(key, value);
    }
    window.location.href = url.href;
}
</script>

<?php include 'includes/footer.php'; ?>
