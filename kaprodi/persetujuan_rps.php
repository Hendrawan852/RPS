<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$prodi_id = $_SESSION['prodi_id'];

// 1. Handle Status Update Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = $_POST['id'];
    $new_status = ($_POST['action'] === 'approve') ? 'Approved' : 'Rejected';
    
    try {
        // Ensure the RPS belongs to a lecturer in the same prodi as the Kaprodi
        $stmt = $pdo->prepare("UPDATE pengajuan_rps p
                               JOIN users u ON p.dosen_id = u.id
                               SET p.status = ? 
                               WHERE p.id = ? AND u.prodi_id = ?");
        $stmt->execute([$new_status, $id, $prodi_id]);
        $_SESSION['msg'] = "Status RPS berhasil diperbarui menjadi $new_status.";
    } catch (PDOException $e) {
        $_SESSION['err'] = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// 2. Fetch submissions for Kaprodi's Prodi
$stmt = $pdo->prepare("SELECT p.*, m.kode_mk, m.nama_mk, u.nama_lengkap as dosen_name 
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       JOIN users u ON p.dosen_id = u.id
                       WHERE u.prodi_id = ?
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
                                    <button class="btn-action primary" title="Tinjau"><i class="fas fa-search-plus"></i></button>
                                    <?php if ($row['status'] === 'Pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn-action success" title="Approve"><i class="fas fa-check"></i></button>
                                            <button type="submit" name="action" value="reject" class="btn-action danger" title="Reject / Revisi"><i class="fas fa-times"></i></button>
                                        </form>
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

<?php include 'includes/footer.php'; ?>
