<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// 1. Handle Status Update Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = $_POST['id'];
    $new_status = ($_POST['action'] === 'approve') ? 'Approved' : 'Rejected';
    
    try {
        $stmt = $pdo->prepare("UPDATE pengajuan_rps SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        $_SESSION['msg'] = "Status RPS berhasil diperbarui menjadi $new_status.";
    } catch (PDOException $e) {
        $_SESSION['err'] = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// 2. Prepare Data Fetching with Filtering
$filter_status = isset($_GET['status']) && $_GET['status'] !== 'Semua Status' ? $_GET['status'] : null;
$filter_semester = isset($_GET['semester']) && $_GET['semester'] !== 'Semua Semester' ? $_GET['semester'] : null;

$sql = "SELECT p.*, m.kode_mk, m.nama_mk, u.nama_lengkap as dosen_name 
        FROM pengajuan_rps p
        JOIN mata_kuliah m ON p.mk_id = m.id
        JOIN users u ON p.dosen_id = u.id
        WHERE 1=1";

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
    <h2>Manajemen RPS</h2>
    <p>Monitoring dan approval Rencana Pembelajaran Semester (RPS) dari seluruh Dosen.</p>
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
            <select class="form-select-sm" onchange="const url = new URL(window.location.href); url.searchParams.set('semester', this.value); window.location.href=url.href;">
                <option <?php echo $filter_semester === null ? 'selected' : ''; ?>>Semua Semester</option>
                <option value="Gasal 2023/2024" <?php echo $filter_semester === 'Gasal 2023/2024' ? 'selected' : ''; ?>>Gasal 2023/2024</option>
                <option value="Genap 2023/2024" <?php echo $filter_semester === 'Genap 2023/2024' ? 'selected' : ''; ?>>Genap 2023/2024</option>
                <option value="Gasal 2024/2025" <?php echo $filter_semester === 'Gasal 2024/2025' ? 'selected' : ''; ?>>Gasal 2024/2025</option>
                <option value="Genap 2024/2025" <?php echo $filter_semester === 'Genap 2024/2025' ? 'selected' : ''; ?>>Genap 2024/2025</option>
            </select>
            <select class="form-select-sm" onchange="const url = new URL(window.location.href); url.searchParams.set('status', this.value); window.location.href=url.href;">
                <option <?php echo $filter_status === null ? 'selected' : ''; ?>>Semua Status</option>
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
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_update'])); ?></td>
                            <td>
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <?php if ($row['status'] === 'Pending'): ?>
                                        <form method="POST" style="display: flex; gap: 8px;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-primary btn-sm" title="Approve">
                                                <i class="fas fa-check-circle"></i> Approve
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-secondary btn-sm" title="Reject">
                                                <i class="fas fa-times-circle"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn-icon-only" title="Lihat Detail"><i class="fas fa-search-plus"></i></button>
                                    <button class="btn-icon-only" title="Riwayat Revisi"><i class="fas fa-history"></i></button>
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

<?php include 'includes/footer.php'; ?>
