<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nip_nidn FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_nip = $stmt->fetchColumn() ?: '';

// Fetch RPS submissions for this lecturer with filter support
$where_clauses = ["p.dosen_id = ?"];
$params = [$user_id];

if (!empty($_GET['semester'])) {
    $where_clauses[] = "p.semester = ?";
    $params[] = $_GET['semester'];
}
if (!empty($_GET['status'])) {
    $where_clauses[] = "p.status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['search'])) {
    $where_clauses[] = "(m.nama_mk LIKE ? OR m.kode_mk LIKE ?)";
    $params[] = "%".$_GET['search']."%";
    $params[] = "%".$_GET['search']."%";
}

$where_sql = implode(" AND ", $where_clauses);

$stmt = $pdo->prepare("SELECT p.*, m.kode_mk, m.nama_mk 
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       WHERE $where_sql
                       ORDER BY p.tanggal_update DESC");
$stmt->execute($params);
$submissions = $stmt->fetchAll();
?>

<style>
    :root {
        --primary: #4f46e5;
        --primary-light: rgba(79, 70, 229, 0.1);
        --secondary: #64748b;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --background: #f8fafc;
        --card-bg: #ffffff;
        --text-dark: #1e293b;
        --border: #e2e8f0;
    }

    .dashboard-container { padding: 30px; background: var(--background); min-height: 100vh; }
    
    .page-header { margin-bottom: 30px; }
    .page-title { font-size: 28px; font-weight: 800; color: #1e293b; letter-spacing: -0.025em; margin: 0; }
    .page-subtitle { color: var(--secondary); font-size: 15px; margin-top: 5px; }

    .card-modern {
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid var(--border);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1.5fr auto;
        gap: 15px;
        padding: 20px;
        align-items: center;
    }

    .form-control-custom {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid var(--border);
        border-radius: 12px;
        font-size: 14px;
        transition: 0.2s;
        background: #fcfcfc;
    }
    .form-control-custom:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px var(--primary-light);
    }

    .btn-primary-custom {
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-primary-custom:hover { background: #4338ca; transform: translateY(-1px); }

    .table-modern { width: 100%; border-collapse: collapse; }
    .table-modern th { background: #f1f5f9; padding: 15px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--secondary); font-weight: 700; border-bottom: 1px solid var(--border); }
    .table-modern td { padding: 18px 15px; border-bottom: 1px solid var(--border); font-size: 14px; color: var(--text-dark); vertical-align: middle; }
    .table-modern tr:hover { background: #f8fafc; }

    .badge-tag { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
    .tag-pending { background: #fef3c7; color: #92400e; }
    .tag-approved { background: #d1fae5; color: #065f46; }
    .tag-rejected { background: #fee2e2; color: #991b1b; }
    .tag-slate { background: #f1f5f9; color: #475569; }

    .btn-action-group { display: flex; gap: 8px; justify-content: flex-end; }
    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        font-size: 14px;
    }
    .btn-view { background: #eff6ff; color: #3b82f6; }
    .btn-view:hover { background: #dbeafe; }
    .btn-pdf { background: #fef2f2; color: #ef4444; }
    .btn-pdf:hover { background: #fee2e2; }
    .btn-edit { background: #f0fdf4; color: #22c55e; }
    .btn-edit:hover { background: #dcfce7; }

    .revision-box {
        margin-top: 10px;
        padding: 12px;
        background: #fff5f5;
        border-radius: 10px;
        border-left: 4px solid #ef4444;
        font-size: 12px;
    }
</style>

<div class="dashboard-container">
    <div class="page-header">
        <h1 class="page-title">Daftar RPS Saya</h1>
        <p class="page-subtitle">Kelola dan pantau status seluruh Rencana Pembelajaran Semester yang telah Anda buat.</p>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
        <div style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 16px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="card-modern">
        <form method="GET" class="filter-grid">
            <select class="form-control-custom" name="semester">
                <option value="">Semua Semester / TA</option>
                <?php
                // Generate semester options dynamically based on your TA requirements
                $semesters = ['Gasal 2023/2024', 'Genap 2023/2024', 'Gasal 2024/2025', 'Genap 2024/2025'];
                foreach($semesters as $sem) {
                    $sel = ($_GET['semester'] ?? '') === $sem ? 'selected' : '';
                    echo "<option value=\"$sem\" $sel>$sem</option>";
                }
                ?>
            </select>
            <select class="form-control-custom" name="status">
                <option value="">Semua Status</option>
                <option value="Pending" <?php echo ($_GET['status']??'')==='Pending'?'selected':''; ?>>Pending</option>
                <option value="Approved" <?php echo ($_GET['status']??'')==='Approved'?'selected':''; ?>>Approved</option>
                <option value="Rejected" <?php echo ($_GET['status']??'')==='Rejected'?'selected':''; ?>>Rejected</option>
            </select>
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--secondary); font-size: 13px;"></i>
                <input type="text" name="search" placeholder="Cari Mata Kuliah..." class="form-control-custom" style="padding-left: 35px;" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn-primary-custom">
                <i class="fas fa-filter"></i> Saring
            </button>
        </form>
    </div>

    <div class="card-modern">
        <div style="overflow-x: auto;">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th style="width: 300px;">Mata Kuliah / Kode</th>
                        <th style="width: 150px;">Tahun Ajaran</th>
                        <th style="width: 150px;">Status</th>
                        <th style="width: 150px;">Update Terakhir</th>
                        <th style="text-align: right; padding-right: 25px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($submissions) > 0): ?>
                        <?php foreach ($submissions as $row): 
                            $status_class = 'tag-slate';
                            $status_icon = 'fa-info-circle';
                            if ($row['status'] === 'Pending') { $status_class = 'tag-pending'; $status_icon = 'fa-clock'; }
                            elseif ($row['status'] === 'Approved') { $status_class = 'tag-approved'; $status_icon = 'fa-check-circle'; }
                            elseif ($row['status'] === 'Rejected') { $status_class = 'tag-rejected'; $status_icon = 'fa-times-circle'; }
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: #1e293b; margin-bottom: 4px;"><?php echo htmlspecialchars($row['nama_mk']); ?></div>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <span class="badge-tag tag-slate" style="padding: 2px 8px; font-size: 10px;"><?php echo $row['kode_mk']; ?></span>
                                        <span style="font-size: 11px; color: var(--secondary); font-style: italic;">Dibuat: <?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-tag tag-slate" style="background: #eff6ff; color: #1e40af;"><?php echo htmlspecialchars($row['semester']); ?></span>
                                </td>
                                <td>
                                    <span class="badge-tag <?php echo $status_class; ?>">
                                        <i class="fas <?php echo $status_icon; ?>"></i>
                                        <?php echo $row['status']; ?>
                                    </span>
                                    <?php if ($row['status'] === 'Rejected' && !empty($row['catatan_revisi'])): ?>
                                        <div class="revision-box">
                                            <div style="font-weight: 800; color: #991b1b; margin-bottom: 3px;"><i class="fas fa-exclamation-triangle"></i> CATATAN REVISI:</div>
                                            <?php echo htmlspecialchars($row['catatan_revisi']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 13px;"><?php echo date('d M Y', strtotime($row['tanggal_update'])); ?></div>
                                    <div style="font-size: 11px; color: var(--secondary);"><?php echo date('H:i', strtotime($row['tanggal_update'])); ?> WIB</div>
                                </td>
                                <td style="padding-right: 25px;">
                                    <div class="btn-action-group">
                                        <a href="data_master.php" class="btn-action btn-view" title="Lihat Detail & Kelola">
                                            <i class="fas fa-search-plus"></i>
                                        </a>
                                        <?php if ($row['status'] === 'Rejected'): ?>
                                            <a href="data_master.php" class="btn-action btn-edit" title="Perbaiki RPS">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($row['status'] === 'Approved'): ?>
                                            <a href="cetak_rps.php?id=<?php echo $row['mk_id']; ?>" target="_blank" class="btn-action btn-pdf" title="Cetak PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div style="text-align: center; padding: 60px 20px;">
                                    <i class="fas fa-folder-open" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                                    <h3 style="font-size: 16px; color: var(--secondary); margin-bottom: 5px;">Tidak Ada Data Ditemukan</h3>
                                    <p style="font-size: 13px; color: #94a3b8;">Cobalah untuk mengubah filter atau kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
