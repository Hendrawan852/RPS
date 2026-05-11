<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$prodi_id = $_SESSION['prodi_id'] ?? 1;

// Action Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'pilih_dosen') {
            $id = $_POST['id'];
            $pdo->prepare("UPDATE dosen SET prodi_id = ? WHERE id = ?")->execute([$prodi_id, $id]);
            $msg = "Dosen berhasil ditambahkan.";
        } elseif ($action === 'lepas_dosen') {
            $id = $_POST['id'];
            $pdo->prepare("UPDATE mata_kuliah SET dosen_id = NULL WHERE dosen_id = ? AND prodi_id = ?")->execute([$id, $prodi_id]);
            $pdo->prepare("UPDATE dosen SET prodi_id = NULL WHERE id = ?")->execute([$id]);
            $msg = "Dosen berhasil dilepas.";
        } elseif ($action === 'tambah_mk_assignment') {
            $mk_id = $_POST['mk_id'];
            $dosen_id = $_POST['dosen_id'] ?: null;
            $pdo->prepare("UPDATE mata_kuliah SET prodi_id = ?, dosen_id = ? WHERE id = ?")->execute([$prodi_id, $dosen_id, $mk_id]);
            $msg = "Mata kuliah & dosen berhasil dipetakan.";
        } elseif ($action === 'update_dosen_mk') {
            $assignments = $_POST['assignments'] ?? [];
            foreach ($assignments as $mk_id => $dosen_id) {
                $d_id = $dosen_id ?: null;
                $stmt = $pdo->prepare("UPDATE mata_kuliah SET dosen_id = ?, prodi_id = ? WHERE id = ? AND (prodi_id = ? OR prodi_id IS NULL)");
                $stmt->execute([$d_id, $prodi_id, $mk_id, $prodi_id]);
            }
            $msg = "Semua perubahan pengampu berhasil disimpan.";
        } elseif ($action === 'lepas_mk') {
            $id = $_POST['id'];
            $pdo->prepare("UPDATE mata_kuliah SET prodi_id = NULL, dosen_id = NULL WHERE id = ?")->execute([$id]);
            $msg = "Mata kuliah berhasil dilepas.";
        }
    }
}

// Fetch Data
$prodi_info = $pdo->prepare("SELECT nama_prodi FROM prodi WHERE id = ?");
$prodi_info->execute([$prodi_id]);
$prodi_name = $prodi_info->fetchColumn() ?: "Program Studi";

$dosen_list = $pdo->prepare("
    SELECT d.*, 
           (SELECT COUNT(*) FROM mata_kuliah mk WHERE mk.dosen_id = d.id AND mk.prodi_id = ?) as jml_mk,
           (SELECT GROUP_CONCAT(nama_mk SEPARATOR ', ') FROM mata_kuliah mk WHERE mk.dosen_id = d.id AND mk.prodi_id = ?) as daftar_mk
    FROM dosen d 
    WHERE (d.prodi_id = ? OR d.prodi_id IS NULL)
    ORDER BY d.nama ASC
");
$dosen_list->execute([$prodi_id, $prodi_id, $prodi_id]);
$dosens = $dosen_list->fetchAll();

$mk_list = $pdo->prepare("
    SELECT mk.*, d.nama as nama_dosen 
    FROM mata_kuliah mk 
    LEFT JOIN dosen d ON mk.dosen_id = d.id 
    WHERE (mk.prodi_id = ? OR mk.prodi_id IS NULL)
    ORDER BY mk.kode_mk ASC
");
$mk_list->execute([$prodi_id]);
$mks = $mk_list->fetchAll();

$mk_selection = $pdo->prepare("SELECT id, kode_mk, nama_mk, sks FROM mata_kuliah WHERE prodi_id IS NULL OR prodi_id != ?");
$mk_selection->execute([$prodi_id]);
$mk_pool = $mk_selection->fetchAll();

$dosen_selection = $pdo->prepare("SELECT id, nama as nama_lengkap, nip as nip_nidn FROM dosen WHERE (prodi_id IS NULL OR prodi_id != ?)");
$dosen_selection->execute([$prodi_id]);
$dosen_pool = $dosen_selection->fetchAll();

function getMappingData($mk_id, $pdo) {
    $cpl = $pdo->prepare("SELECT c.kode_cpl FROM cpl c JOIN mk_cpl mc ON c.id = mc.cpl_id WHERE mc.mk_id = ?");
    $cpl->execute([$mk_id]);
    $cpmk = $pdo->prepare("SELECT c.kode_cpmk FROM cpmk c JOIN mk_cpmk mc ON c.id = mc.cpmk_id WHERE mc.mk_id = ?");
    $cpmk->execute([$mk_id]);
    return ['cpl' => $cpl->fetchAll(PDO::FETCH_COLUMN), 'cpmk' => $cpmk->fetchAll(PDO::FETCH_COLUMN)];
}
?>

<style>
    :root {
        --primary: #4f46e5;
        --secondary: #64748b;
        --success: #10b981;
        --danger: #ef4444;
        --border: #e2e8f0;
        --bg-soft: #f8fafc;
        --text-dark: #0f172a;
    }

    body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }

    .page-header {
        background: white;
        padding: 20px 30px;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title h2 { font-size: 20px; font-weight: 700; color: var(--text-dark); margin: 0; }
    .page-title p { font-size: 13px; color: var(--secondary); margin: 4px 0 0; }

    .card-modern {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 25px;
        overflow: hidden;
        border: 1px solid var(--border);
    }

    .card-header-modern {
        padding: 16px 24px;
        background: var(--bg-soft);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .card-header-modern h3 { font-size: 15px; font-weight: 700; color: var(--text-dark); margin: 0; }

    .table-modern { width: 100%; border-collapse: collapse; }
    .table-modern th { 
        padding: 14px 24px; 
        text-align: left; 
        font-size: 11px; 
        font-weight: 700; 
        color: var(--secondary); 
        text-transform: uppercase;
        border-bottom: 1px solid var(--border);
    }

    .table-modern td { 
        padding: 16px 24px; 
        border-bottom: 1px solid var(--border); 
        font-size: 13.5px;
        vertical-align: middle;
    }

    .btn-action {
        width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
        border: none; cursor: pointer; transition: 0.2s; font-size: 14px;
    }

    .btn-red { background: #fee2e2; color: var(--danger); }
    .btn-red:hover { background: var(--danger); color: white; }

    .btn-indigo { 
        background: var(--primary); color: white; padding: 10px 24px; border-radius: 12px; 
        font-size: 14px; font-weight: 600; border: none; cursor: pointer; 
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: 0.2s;
    }

    .select-modern {
        padding: 10px 14px; border-radius: 12px; border: 2px solid var(--border); background: var(--bg-soft);
        font-size: 13.5px; color: var(--text-dark); outline: none; min-width: 220px; cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
    }

    .select-modern:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
    .select-modern.is-selected { border-color: #10b981; background-color: #f0fdf4; color: #065f46; font-weight: 600; }
    .select-modern.is-empty { border-color: #f59e0b; background-color: #fffbeb; color: #92400e; border-style: solid; }
    .select-modern.is-changed { border-color: #4f46e5; border-style: dashed; animation: pulse-border 2s infinite; }

    @keyframes pulse-border {
        0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
        100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        margin-top: 8px;
        letter-spacing: 0.5px;
        padding: 2px 8px;
        border-radius: 6px;
        width: fit-content;
    }
    .status-selected { background: #d1fae5; color: #065f46; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-changed { background: #e0e7ff; color: #4338ca; }

    .badge-tag { padding: 4px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid transparent; }
    .tag-blue { background: #eff6ff; color: #1e40af; border-color: #dbeafe; }
    .tag-green { background: #ecfdf5; color: #065f46; border-color: #d1fae5; }
    .tag-amber { background: #fffbeb; color: #92400e; border-color: #fde68a; }
    .tag-slate { background: #f8fafc; color: #475569; border-color: #e2e8f0; }

    .btn-blue { background: #e0f2fe; color: #0ea5e9; }
    .btn-blue:hover { background: #0ea5e9; color: white; }

    .search-input {
        width: 100%; padding: 10px 15px; border-radius: 10px; border: 1px solid var(--border);
        font-size: 13px; margin-bottom: 15px; outline: none; transition: 0.2s;
    }
    .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

    .modal-backdrop { background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
    .modal-box { 
        background: white; border-radius: 24px; width: 90%; max-width: 850px; 
        display: flex; flex-direction: column; overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .info-item { margin-bottom: 12px; display: flex; gap: 10px; align-items: flex-start; }
    .info-label { min-width: 100px; font-weight: 700; color: var(--secondary); font-size: 11px; text-transform: uppercase; margin-top: 2px; }
    .info-value { flex: 1; color: var(--text-dark); font-weight: 500; }

    .master-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 25px;
        background: #e2e8f0;
        padding: 6px;
        border-radius: 14px;
        width: fit-content;
    }

    .tab-btn {
        padding: 10px 24px;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 700;
        color: #64748b;
        border-radius: 10px;
        font-size: 13.5px;
        transition: all 0.3s ease;
    }

    .tab-btn.active {
        color: #4f46e5;
        background: white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-box {
        background: #dcfce7; color: #166534; padding: 12px 20px; border-radius: 12px; 
        margin-bottom: 20px; font-size: 13.5px; display: flex; align-items: center; gap: 10px;
    }
    .btn-save-pengampu {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #d1fae5;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-save-pengampu:hover {
        background: #10b981;
        color: white;
    }
    .btn-save-pengampu:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-bulk-save {
        background: #10b981;
        color: white;
        padding: 12px 30px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: 0.3s;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
    }
    .btn-bulk-save:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
    }
    .btn-bulk-save:active { transform: translateY(0); }
</style>

<div class="page-header">
    <div class="page-title">
        <h2>Data Master & Kurikulum</h2>
        <p>Manajemen Dosen dan Mata Kuliah untuk <strong><?php echo htmlspecialchars($prodi_name); ?></strong></p>
    </div>
</div>

<?php if (isset($msg)): ?>
    <div class="alert-box"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
<?php endif; ?>

<div class="master-tabs">
    <button class="tab-btn active" data-tab="dosen">Personalia Dosen</button>
    <button class="tab-btn" data-tab="mk">Mata Kuliah & Penugasan</button>
</div>

<div id="dosen" class="tab-content active">
    <div class="card-modern">
    <div class="card-header-modern">
        <i class="fas fa-users" style="color: var(--primary);"></i>
        <h3>Personalia Dosen Prodi</h3>
    </div>
    <table class="table-modern">
        <thead>
            <tr>
                <th style="width: 40%;">Nama Lengkap</th>
                <th>NIP / NIDN</th>
                <th>Jabatan</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($dosens)): ?>
                <tr><td colspan="3" style="text-align: center; color: var(--secondary); padding: 30px;">Belum ada dosen yang terdaftar.</td></tr>
            <?php else: ?>
                <?php foreach ($dosens as $d): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($d['nama']); ?>&background=random&size=32" style="border-radius: 8px;">
                            <strong><?php echo htmlspecialchars($d['nama']); ?></strong>
                        </div>
                    </td>
                    <td><span style="font-family: monospace; font-weight: 600;"><?php echo $d['nip']; ?></span></td>
                    <td><span class="badge-tag tag-blue"><?php echo htmlspecialchars($d['jabatan'] ?: 'Dosen Tetap'); ?></span></td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button class="btn-action btn-blue" title="Info Penugasan" onclick='openInfo("Dosen", <?php echo json_encode($d); ?>)'><i class="fas fa-eye"></i></button>
                            <form method="POST" onsubmit="return confirm('Lepas dosen?')">
                                <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                <input type="hidden" name="action" value="lepas_dosen">
                                <button type="submit" class="btn-action btn-red"><i class="fas fa-user-minus"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<div id="mk" class="tab-content">
<div class="card-modern">
    <div class="card-header-modern" style="justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-book" style="color: var(--success);"></i>
            <h3>Mata Kuliah & Penugasan</h3>
        </div>
        <div style="font-size: 12px; color: var(--secondary); font-style: italic;">
            <i class="fas fa-info-circle"></i> Ubah pengampu lalu klik Simpan di bawah
        </div>
    </div>
    <form id="bulkUpdateForm" method="POST">
        <input type="hidden" name="action" value="update_dosen_mk">
    <table class="table-modern">
        <thead>
            <tr>
                <th style="width: 100px;">Kode</th>
                <th>Mata Kuliah</th>
                <th style="width: 100px;">Semester</th>
                <th style="width: 120px;">Tahun Ajaran</th>
                <th>Dosen Pengampu</th>
                <th>CPL</th>
                <th>CPMK</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($mks)): ?>
                <tr><td colspan="7" style="text-align: center; color: var(--secondary); padding: 40px;">Belum ada mata kuliah yang dipilih.</td></tr>
<?php else: ?>
                <?php foreach ($mks as $mk): 
                    $map = getMappingData($mk['id'], $pdo);
                    $mk['mapping'] = $map;
                ?>
                <tr>
                    <td><span class="badge-tag tag-blue"><?php echo $mk['kode_mk']; ?></span></td>
                    <td>
                        <div style="font-weight: 700;"><?php echo $mk['nama_mk']; ?></div>
                    </td>
                    <td><span class="badge-tag tag-amber" translate="no">SEM <?php echo $mk['semester_default']; ?></span></td>
                    <td><span class="badge-tag tag-slate"><?php echo htmlspecialchars($mk['tahun_ajaran'] ?? '2024/2025'); ?></span></td>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                            <select name="assignments[<?php echo $mk['id']; ?>]" class="select-modern <?php echo $mk['dosen_id'] ? 'is-selected' : 'is-empty'; ?>" style="min-width: 220px;">
                                <option value="">-- Pilih Dosen --</option>
                                <?php foreach ($dosens as $d): ?>
                                    <option value="<?php echo $d['id']; ?>" <?php echo ((int)($mk['dosen_id'] ?? 0) === (int)$d['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($mk['dosen_id']): ?>
                                <div class="status-indicator status-selected">
                                    <i class="fas fa-check-circle"></i> Terpilih
                                </div>
                            <?php else: ?>
                                <div class="status-indicator status-pending">
                                    <i class="fas fa-exclamation-triangle"></i> Belum Dipilih
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                            <?php if (empty($map['cpl'])): ?>
                                <span style="color: #cbd5e1; font-size: 11px; font-style: italic;">None</span>
                            <?php else: ?>
                                <?php foreach($map['cpl'] as $c): ?><span class="badge-tag tag-green"><?php echo $c; ?></span><?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                            <?php if (empty($map['cpmk'])): ?>
                                <span style="color: #cbd5e1; font-size: 11px; font-style: italic;">None</span>
                            <?php else: ?>
                                <?php foreach($map['cpmk'] as $c): ?><span class="badge-tag tag-amber"><?php echo $c; ?></span><?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 5px; justify-content: flex-end;">
                            <button class="btn-action btn-blue" onclick='openInfo("MK", <?php echo json_encode($mk); ?>)'><i class="fas fa-eye"></i></button>
                            <form method="POST" onsubmit="return confirm('Lepas MK?')">
                                <input type="hidden" name="id" value="<?php echo $mk['id']; ?>">
                                <input type="hidden" name="action" value="lepas_mk">
                                <button type="submit" class="btn-action btn-red"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="padding: 24px; background: #f8fafc; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 15px; align-items: center;">
        <div id="unsavedCount" style="display: none; font-size: 13px; color: #4f46e5; font-weight: 600;">
            <i class="fas fa-edit"></i> <span id="change-count">0</span> perubahan belum disimpan
        </div>
        <button type="submit" class="btn-bulk-save" style="min-width: 150px;">
            <i class="fas fa-save"></i> Simpan
        </button>
    </div>
    </form>
</div>
</div>


<!-- Modal Info Detail (Premium Version) -->
<div id="modalInfo" class="modal modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1100; justify-content: center; align-items: center;">
    <div class="modal-box" style="max-width: 550px; padding: 0; overflow: hidden; border-radius: 20px;">
        <!-- Header Banner -->
        <div id="modal-banner" style="padding: 30px; color: white; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -15px; right: -15px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; position: relative; z-index: 1;">
                <div>
                    <div id="modal-type-badge" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: inline-block;"></div>
                    <h3 id="info-title" style="margin: 0; font-size: 22px; font-weight: 800;"></h3>
                </div>
                <span onclick="closeModal('modalInfo')" style="color: rgba(255,255,255,0.8); font-size: 24px; cursor: pointer;">&times;</span>
            </div>
        </div>

        <div id="info-content" style="padding: 25px; background: white; max-height: 60vh; overflow-y: auto;">
            <!-- Content dynamically loaded -->
        </div>

        <div style="padding: 16px 25px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button type="button" class="btn-indigo" onclick="closeModal('modalInfo')" style="background: #e2e8f0; color: #475569; border: none; padding: 10px 24px; border-radius: 12px; font-weight: 600;">Tutup</button>
        </div>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function filterList(input, containerId) {
    const filter = input.value.toLowerCase();
    const container = document.getElementById(containerId);
    const items = container.getElementsByClassName('pool-item');
    
    for (let i = 0; i < items.length; i++) {
        const searchTxt = items[i].dataset.search;
        if (searchTxt.indexOf(filter) > -1) {
            items[i].style.display = "";
        } else {
            items[i].style.display = "none";
        }
    }
}

function openInfo(type, data) {
    const title = document.getElementById('info-title');
    const content = document.getElementById('info-content');
    const banner = document.getElementById('modal-banner');
    const badge = document.getElementById('modal-type-badge');
    
    badge.innerText = type === 'MK' ? 'Mata Kuliah' : 'Dosen';
    content.innerHTML = '';

    if (type === 'Dosen') {
        banner.style.background = 'linear-gradient(135deg, #4338ca 0%, #6366f1 100%)';
        title.innerText = data.nama;
        content.innerHTML = `
            <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;">
                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(data.nama)}&size=100&background=6366f1&color=fff" style="border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                <div>
                    <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;">NIP / NIDN</div>
                    <div style="font-size: 18px; font-weight: 700; color: #1f2937;">${data.nip}</div>
                    <div style="display: inline-block; background: #e0e7ff; color: #4338ca; font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 20px; margin-top: 8px;">${data.jabatan || 'Dosen Pengampu'}</div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 20px;">
                <div style="background: #f0fdf4; padding: 15px; border-radius: 12px; border: 1px solid #dcfce7; display: flex; align-items: center; gap: 15px;">
                    <div style="width: 45px; height: 45px; background: #10b981; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800;">
                        ${data.jml_mk}
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #059669; font-weight: 700; text-transform: uppercase;">Total Penugasan</div>
                        <div style="font-size: 14px; font-weight: 600; color: #064e3b;">Mata Kuliah Diampu</div>
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #f1f5f9;">
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-list-ul"></i> Daftar Mata Kuliah
                </div>
                <div style="font-size: 13.5px; line-height: 1.6; color: #334155;">
                    ${data.daftar_mk ? data.daftar_mk.split(', ').map(mk => `<div style="padding: 4px 0; border-bottom: 1px dashed #e2e8f0; last-child { border: none }"><i class="fas fa-check-circle" style="color: #10b981; font-size: 12px; margin-right: 8px;"></i>${mk}</div>`).join('') : '<span style="color: #94a3b8; font-style: italic;">Belum ada penugasan mata kuliah.</span>'}
                </div>
            </div>
        `;
    } else {
        banner.style.background = 'linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%)';
        title.innerText = data.nama_mk;
        
        const cpls = data.mapping.cpl.map(c => `<span class="badge-tag tag-green" style="margin-bottom: 5px;">${c}</span>`).join(' ');
        const cpmks = data.mapping.cpmk.map(c => `<span class="badge-tag tag-amber" style="margin-bottom: 5px;">${c}</span>`).join(' ');

        content.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                <div style="background: #eff6ff; padding: 12px; border-radius: 12px; border: 1px solid #dbeafe; text-align: center;">
                    <div style="font-size: 10px; color: #1e40af; text-transform: uppercase; font-weight: 800;">Kode</div>
                    <div style="font-size: 14px; font-weight: 700; color: #1e40af;">${data.kode_mk}</div>
                </div>
                <div style="background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #f1f5f9; text-align: center;">
                    <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 800;">SKS</div>
                    <div style="font-size: 14px; font-weight: 700; color: #334155;">${data.sks} SKS</div>
                </div>
                <div style="background: #f8fafc; padding: 12px; border-radius: 12px; border: 1px solid #f1f5f9; text-align: center;">
                    <div style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 800;">Semester</div>
                    <div style="font-size: 14px; font-weight: 700; color: #334155;">Smt ${data.semester_default}</div>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 15px; border-radius: 12px; border-left: 4px solid #3b82f6; margin-bottom: 20px;">
                <div style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">Deskripsi Mata Kuliah</div>
                <div style="font-size: 14px; line-height: 1.6; color: #334155;">${data.deskripsi || 'Tidak ada deskripsi tersedia.'}</div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="background: white; border: 1px solid #e2e8f0; padding: 15px; border-radius: 15px;">
                    <div style="font-size: 11px; font-weight: 800; color: #10b981; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-graduation-cap"></i> CPL Terkait
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                        ${cpls || '<span style="color: #94a3b8; font-size: 12px;">Tidak ada CPL</span>'}
                    </div>
                </div>
                <div style="background: white; border: 1px solid #e2e8f0; padding: 15px; border-radius: 15px;">
                    <div style="font-size: 11px; font-weight: 800; color: #f59e0b; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-award"></i> CPMK Terkait
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                        ${cpmks || '<span style="color: #94a3b8; font-size: 12px;">Tidak ada CPMK</span>'}
                    </div>
                </div>
            </div>
        `;
    }
    
    openModal('modalInfo');
}

// Track changes
const updateIndicators = (select) => {
    const td = select.closest('td');
    const statusIndicator = td.querySelector('.status-indicator');
    const isInitialValue = select.value === select.dataset.initial;
    
    // Remove all states
    select.classList.remove('is-selected', 'is-empty', 'is-changed');
    
    if (!isInitialValue) {
        select.classList.add('is-changed');
        if (statusIndicator) {
            statusIndicator.className = 'status-indicator status-changed';
            statusIndicator.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Belum Disimpan';
        }
    } else if (select.value) {
        select.classList.add('is-selected');
        if (statusIndicator) {
            statusIndicator.className = 'status-indicator status-selected';
            statusIndicator.innerHTML = '<i class="fas fa-check-circle"></i> Terpilih';
        }
    } else {
        select.classList.add('is-empty');
        if (statusIndicator) {
            statusIndicator.className = 'status-indicator status-pending';
            statusIndicator.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Belum Dipilih';
        }
    }

    // Update global unsaved count
    const changedCount = Array.from(document.querySelectorAll('select[name^="assignments"]'))
        .filter(s => s.value !== s.dataset.initial).length;
    
    const countDisplay = document.getElementById('unsavedCount');
    if (changedCount > 0) {
        countDisplay.style.display = 'block';
        document.getElementById('change-count').innerText = changedCount;
    } else {
        countDisplay.style.display = 'none';
    }
};

// Initialize initial values and listeners
document.querySelectorAll('select[name^="assignments"]').forEach(select => {
    select.dataset.initial = select.value;
    select.addEventListener('change', () => updateIndicators(select));
});

// AJAX logic for bulk updating dosen pengampu
const bulkForm = document.getElementById('bulkUpdateForm');
if (bulkForm) {
    bulkForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('.btn-bulk-save');
        const originalHtml = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        
        const formData = new FormData(this);
        
        fetch('data_master.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Berhasil!';
            btn.style.background = '#059669';
            
            // Update initial values and indicators
            this.querySelectorAll('select[name^="assignments"]').forEach(select => {
                select.dataset.initial = select.value;
                updateIndicators(select);
            });
            
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                btn.style.background = '';
            }, 3000);
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = '<i class="fas fa-exclamation-circle"></i> Gagal';
            btn.style.background = '#ef4444';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                btn.style.background = '';
            }, 3000);
        });
    });
}

// Tab switching logic
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
        
        // Save active tab to localStorage
        localStorage.setItem('activeTab_dataMaster', this.dataset.tab);
    });
});

// Restore active tab on load
window.addEventListener('load', () => {
    const activeTab = localStorage.getItem('activeTab_dataMaster');
    if (activeTab) {
        const btn = document.querySelector(`.tab-btn[data-tab="${activeTab}"]`);
        if (btn) btn.click();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
