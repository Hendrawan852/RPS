<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$dosen_id = $_SESSION['user_id'];

// Fetch RPS submissions for this lecturer
$stmt = $pdo->prepare("SELECT p.*, m.kode_mk, m.nama_mk 
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       WHERE p.dosen_id = ?
                       ORDER BY p.tanggal_update DESC");
$stmt->execute([$dosen_id]);
$submissions = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Daftar RPS Saya</h2>
            <p>Kelola dan pantau status seluruh Rencana Pembelajaran Semester yang telah Anda buat.</p>
        </div>
        <a href="buat_rps_baru.php" class="btn btn-primary"><i class="fas fa-plus"></i> Buat RPS Baru</a>
    </div>
</div>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
    </div>
<?php endif; ?>

<div class="card filter-card">
    <form method="GET" class="filter-grid">
        <select class="form-control-sm" name="semester">
            <option value="">Semua Semester</option>
            <option value="Gasal 2023/2024">Gasal 2023/2024</option>
            <option value="Genap 2023/2024">Genap 2023/2024</option>
            <option value="Gasal 2024/2025">Gasal 2024/2025</option>
            <option value="Genap 2024/2025">Genap 2024/2025</option>
        </select>
        <select class="form-control-sm" name="status">
            <option value="">Semua Status</option>
            <option>Pending</option>
            <option>Approved</option>
            <option>Rejected</option>
        </select>
        <div class="search-box">
            <input type="text" name="search" placeholder="Cari Mata Kuliah..." class="form-control-sm">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode - Mata Kuliah</th>
                    <th>TA / Sem</th>
                    <th>Status</th>
                    <th>Tanggal Update</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($submissions) > 0): ?>
                    <?php foreach ($submissions as $row): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['kode_mk'] . ' - ' . $row['nama_mk']); ?></strong><br>
                                <small>Dibuat: <?php echo date('d M Y', strtotime($row['created_at'])); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['tanggal_update'])); ?></td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn-icon" title="Detail"><i class="fas fa-eye"></i></button>
                                    <?php if ($row['status'] === 'Rejected'): ?>
                                        <button class="btn-icon" title="Edit"><i class="fas fa-edit"></i></button>
                                    <?php endif; ?>
                                    <button class="btn-icon" title="Download PDF"><i class="fas fa-file-pdf"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                            Belum ada RPS yang dibuat.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
