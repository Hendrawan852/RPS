<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Always read prodi_id fresh from the database to prevent stale session issues
$stmt = $pdo->prepare("SELECT prodi_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$prodi_id = $stmt->fetchColumn();

// 1. Handle Status Update Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];
    $new_status = 'Pending';
    if ($action === 'approve') $new_status = 'Approved';
    if ($action === 'reject') $new_status = 'Rejected';
    if ($action === 'cancel') $new_status = 'Pending';
    
    $catatan = $_POST['catatan'] ?? null;
    
    try {
        if ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE pengajuan_rps 
                                   SET status = 'Rejected', catatan_revisi = ? 
                                   WHERE id = ? AND mk_id IN (SELECT id FROM mata_kuliah WHERE prodi_id = ?)");
            $stmt->execute([$catatan, $id, $prodi_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE pengajuan_rps 
                                   SET status = ?, catatan_revisi = NULL 
                                   WHERE id = ? AND mk_id IN (SELECT id FROM mata_kuliah WHERE prodi_id = ?)");
            $stmt->execute([$new_status, $id, $prodi_id]);
        }
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['msg'] = "Status RPS berhasil diperbarui.";
        } else {
            $_SESSION['err'] = "Gagal memperbarui: RPS tidak ditemukan atau bukan milik Prodi Anda.";
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['err'] = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// 2. Filter Params
$f_dosen = $_GET['dosen'] ?? '';
$f_mk = $_GET['mk'] ?? '';
$f_semester = $_GET['semester'] ?? '';

// 3. Fetch submissions for Kaprodi's Prodi with Filtering
$query = "SELECT p.*, m.kode_mk, m.nama_mk, m.tahun_ajaran, d.nama as dosen_name 
          FROM pengajuan_rps p
          JOIN mata_kuliah m ON p.mk_id = m.id
          JOIN dosen d ON m.dosen_id = d.id
          WHERE m.prodi_id = ?";

$params = [$prodi_id];

if (!empty($f_dosen)) {
    $query .= " AND d.id = ?";
    $params[] = $f_dosen;
}
if (!empty($f_mk)) {
    $query .= " AND m.id = ?";
    $params[] = $f_mk;
}
if (!empty($f_semester)) {
    $query .= " AND p.semester = ?";
    $params[] = $f_semester;
}

$query .= " ORDER BY p.tanggal_update DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

// 4. Fetch unique filter data
$filter_dosens = $pdo->prepare("SELECT DISTINCT d.id, d.nama FROM dosen d JOIN mata_kuliah m ON d.id = m.dosen_id WHERE m.prodi_id = ? ORDER BY d.nama");
$filter_dosens->execute([$prodi_id]);
$dosens = $filter_dosens->fetchAll();

$filter_mks = $pdo->prepare("SELECT id, kode_mk, nama_mk FROM mata_kuliah WHERE prodi_id = ? ORDER BY nama_mk");
$filter_mks->execute([$prodi_id]);
$mks = $filter_mks->fetchAll();
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

<div class="card filter-card" style="margin-bottom: 25px; padding: 20px; background: white; border-radius: 16px; border: 1px solid #e2e8f0;">
    <form method="GET">
        <div class="filter-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
            <select name="dosen" class="form-control" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <option value="">Semua Dosen</option>
                <?php foreach($dosens as $d): ?>
                    <option value="<?php echo $d['id']; ?>" <?php echo $f_dosen == $d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['nama']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="mk" class="form-control" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <option value="">Semua Mata Kuliah</option>
                <?php foreach($mks as $m): ?>
                    <option value="<?php echo $m['id']; ?>" <?php echo $f_mk == $m['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['nama_mk']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="semester" class="form-control" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <option value="">Semua Semester</option>
                <option value="Gasal 2023/2024" <?php echo $f_semester === 'Gasal 2023/2024' ? 'selected' : ''; ?>>Gasal 2023/2024</option>
                <option value="Genap 2023/2024" <?php echo $f_semester === 'Genap 2023/2024' ? 'selected' : ''; ?>>Genap 2023/2024</option>
                <option value="Gasal 2024/2025" <?php echo $f_semester === 'Gasal 2024/2025' ? 'selected' : ''; ?>>Gasal 2024/2025</option>
                <option value="Genap 2024/2025" <?php echo $f_semester === 'Genap 2024/2025' ? 'selected' : ''; ?>>Genap 2024/2025</option>
            </select>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; background: #10b981; color: white; border: none; padding: 10px; border-radius: 10px; font-weight: 700; cursor: pointer;">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="persetujuan_rps.php" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #64748b; text-decoration: none; padding: 10px; border-radius: 10px; font-weight: 700;">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>MK / Kode</th>
                    <th>Dosen Pengampu</th>
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
                                <strong><?php echo htmlspecialchars($row['nama_mk']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['kode_mk']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['dosen_name']); ?></td>
                            <td><span class="badge-tag tag-slate"><?php echo htmlspecialchars($row['tahun_ajaran'] ?? '2024/2025'); ?></span></td>
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
                                            <button type="submit" name="action" value="approve" class="btn-action success" title="Setujui RPS" onclick="return confirm('Setujui RPS ini?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button class="btn-action danger revisi-btn" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-mk="<?php echo htmlspecialchars($row['nama_mk']); ?>"
                                                title="Minta Revisi">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php elseif ($row['status'] === 'Approved'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="cancel" class="btn-action warning" style="background: #fff7ed; color: #f59e0b;" title="Batalkan Persetujuan" onclick="return confirm('Batalkan persetujuan RPS ini?')">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                        </form>
                                        <button class="btn-action danger revisi-btn" 
                                                data-id="<?php echo $row['id']; ?>" 
                                                data-mk="<?php echo htmlspecialchars($row['nama_mk']); ?>"
                                                title="Tarik & Minta Revisi">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    <?php elseif ($row['status'] === 'Rejected'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="cancel" class="btn-action warning" style="background: #fff7ed; color: #f59e0b;" title="Batalkan Permintaan Revisi" onclick="return confirm('Batalkan permintaan revisi ini?')">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
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
