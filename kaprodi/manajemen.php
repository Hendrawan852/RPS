<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$prodi_id = $_SESSION['prodi_id'];

// Fetch all RPS for this prodi
$stmt = $pdo->prepare("SELECT p.*, m.kode_mk, m.nama_mk, u.nama_lengkap as dosen_name 
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       JOIN users u ON p.dosen_id = u.id
                       WHERE u.prodi_id = ?
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
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y', strtotime($row['tanggal_update'])); ?>/<?php echo date('Y', strtotime($row['tanggal_update'])) + 1; ?></td>
                            <td>
                                <div class="actions" style="justify-content: flex-end;">
                                    <button class="btn-action primary" title="Download PDF"><i class="fas fa-file-pdf"></i></button>
                                    <button class="btn-action success" title="Salin Versi"><i class="fas fa-copy"></i></button>
                                    <button class="btn-action info" title="Detail"><i class="fas fa-eye"></i></button>
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

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#archiveTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
