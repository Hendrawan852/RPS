<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT prodi_id, nip_nidn FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);
$user_prodi_id = $user_info['prodi_id'] ?? 1;
$user_nip = $user_info['nip_nidn'] ?? '';

// Find internal dosen_id from the central 'dosen' table using NIP
$stmt = $pdo->prepare("SELECT id FROM dosen WHERE nip = ?");
$stmt->execute([$user_nip]);
$internal_dosen_id = $stmt->fetchColumn() ?: 0;

// 2. Handle Actions
$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save_sub_cpmk') {
            $mk_id = $_POST['mk_id'];
            $minggus = $_POST['minggu'];
            $subs = $_POST['sub_cpmk'];
            $indikators = $_POST['indikator'];
            $bentuks = $_POST['bentuk_asesmen'];
            $materis = $_POST['materi'];
            $metodes = $_POST['metode_row'];
            $settings = $_POST['setting_row'];
            $bobots = $_POST['bobot'];

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("DELETE FROM sub_cpmk WHERE mk_id = ?");
                $stmt->execute([$mk_id]);

                $stmt = $pdo->prepare("INSERT INTO sub_cpmk (mk_id, minggu, sub_cpmk, indikator, bentuk_asesmen, materi, metode, setting, bobot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                for ($i = 0; $i < count($minggus); $i++) {
                    if (!empty($minggus[$i])) {
                        $stmt->execute([$mk_id, $minggus[$i], $subs[$i], $indikators[$i], $bentuks[$i], $materis[$i], $metodes[$i], $settings[$i], $bobots[$i]]);
                    }
                }
                $pdo->commit();
                $msg = "Data Rincian Mingguan berhasil disimpan!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Gagal menyimpan: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'ajukan_rps') {
            $mk_id = $_POST['mk_id'];
            $semester = $_POST['semester'] ?? 'Gasal 2024/2025';
            
            try {
                // Use session user_id for pengajuan_rps tracking (FK to users table)
                $check = $pdo->prepare("SELECT id FROM pengajuan_rps WHERE mk_id = ? AND dosen_id = ?");
                $check->execute([$mk_id, $user_id]);
                
                if ($check->fetch()) {
                    $stmt = $pdo->prepare("UPDATE pengajuan_rps SET status = 'Pending', tanggal_update = CURRENT_TIMESTAMP WHERE mk_id = ? AND dosen_id = ?");
                    $stmt->execute([$mk_id, $user_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO pengajuan_rps (mk_id, dosen_id, semester, status) VALUES (?, ?, ?, 'Pending')");
                    $stmt->execute([$mk_id, $user_id, $semester]);
                }
                $msg = "RPS berhasil diajukan ke Kaprodi!";
            } catch (Exception $e) {
                $error = "Gagal mengajukan: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'save_bobot_penilaian') {
            $mk_id = $_POST['mk_id'];
            $mk_cpmk_ids = $_POST['mk_cpmk_id'];
            $t1s = $_POST['t1'];
            $t2s = $_POST['t2'];
            $t3s = $_POST['t3'];
            $p1s = $_POST['p1'];
            $p2s = $_POST['p2'];

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE mk_cpmk SET tugas1 = ?, tugas2 = ?, tugas3 = ?, proyek1 = ?, proyek2 = ? WHERE id = ?");
                for ($i = 0; $i < count($mk_cpmk_ids); $i++) {
                    $stmt->execute([$t1s[$i], $t2s[$i], $t3s[$i], $p1s[$i], $p2s[$i], $mk_cpmk_ids[$i]]);
                }
                $pdo->commit();
                $msg = "Bobot Penilaian CPMK berhasil disimpan!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Gagal menyimpan bobot: " . $e->getMessage();
            }
        }
    }
}

// 3. Fetch Mata Kuliah with Status
$mk_list = $pdo->prepare("
    SELECT mk.*, d.nama as nama_dosen, d.jabatan as jabatan_dosen, p.status as rps_status, p.tanggal_update as rps_update
    FROM mata_kuliah mk
    LEFT JOIN dosen d ON mk.dosen_id = d.id
    LEFT JOIN pengajuan_rps p ON mk.id = p.mk_id AND p.dosen_id = ?
    WHERE mk.prodi_id = ? OR mk.dosen_id = ?
    ORDER BY mk.semester_default ASC, mk.nama_mk ASC
");
$mk_list->execute([$user_id, $user_prodi_id, $internal_dosen_id]);
$mk_list = $mk_list->fetchAll(PDO::FETCH_ASSOC);

function getMappingData($mk_id, $pdo) {
    $cpl = $pdo->prepare("SELECT c.* FROM cpl c JOIN mk_cpl m ON c.id = m.cpl_id WHERE m.mk_id = ?");
    $cpl->execute([$mk_id]);
    $cpmk = $pdo->prepare("
        SELECT c.*, m.tugas1, m.tugas2, m.tugas3, m.proyek1, m.proyek2, m.id as mk_cpmk_id
        FROM cpmk c 
        JOIN mk_cpmk m ON c.id = m.cpmk_id 
        WHERE m.mk_id = ?
    ");
    $cpmk->execute([$mk_id]);
    $sub = $pdo->prepare("SELECT * FROM sub_cpmk WHERE mk_id = ? ORDER BY CAST(minggu AS UNSIGNED) ASC, id ASC");
    $sub->execute([$mk_id]);
    
    return [
        'cpl' => $cpl->fetchAll(PDO::FETCH_ASSOC),
        'cpmk' => $cpmk->fetchAll(PDO::FETCH_ASSOC),
        'sub' => $sub->fetchAll(PDO::FETCH_ASSOC)
    ];
}
?>

<style>
    :root {
        --primary: #4f46e5;
        --primary-dark: #4338ca;
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
    
    .tab-container {
        display: flex;
        gap: 5px;
        margin-bottom: 25px;
        background: #e2e8f0;
        padding: 5px;
        border-radius: 14px;
        width: fit-content;
    }
    .tab-btn {
        padding: 10px 24px;
        border: none;
        background: transparent;
        color: var(--secondary);
        font-weight: 600;
        cursor: pointer;
        border-radius: 10px;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .tab-btn.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.4s ease; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .card-modern {
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid var(--border);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .table-modern { width: 100%; border-collapse: collapse; }
    .table-modern th { background: #f1f5f9; padding: 15px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--secondary); font-weight: 700; border-bottom: 1px solid var(--border); }
    .table-modern td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 13.5px; color: var(--text-dark); }
    .table-modern tr:hover { background: #f8fafc; }

    .badge-tag { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
    .tag-blue { background: #e0e7ff; color: #4338ca; }
    .tag-amber { background: #fef3c7; color: #92400e; }
    .tag-emerald { background: #d1fae5; color: #065f46; }
    .tag-slate { background: #f1f5f9; color: #475569; }
    .tag-red { background: #fee2e2; color: #991b1b; }

    .btn-action { width: 34px; height: 34px; border-radius: 10px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; color: white; text-decoration: none; }
    .btn-view { background: var(--primary); }
    .btn-edit { background: var(--success); }
    .btn-delete { background: var(--danger); }

    .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; }
    .modal-content { background: white; width: 95%; max-width: 1200px; border-radius: 24px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }

    .form-control-custom { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; transition: 0.2s; }
    .form-control-custom:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }

    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state i { font-size: 48px; color: var(--secondary); opacity: 0.3; margin-bottom: 20px; }
    .empty-state h3 { font-size: 18px; color: var(--secondary); margin-bottom: 10px; }
</style>

<div class="dashboard-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b; letter-spacing: -0.025em;">Manajemen RPS & Kurikulum</h1>
            <p style="color: var(--secondary); font-size: 15px;">Kelola rincian materi mingguan dan ajukan RPS ke Kaprodi.</p>
        </div>
    </div>

    <?php if ($msg): ?>
        <div style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 16px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 16px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="tab-container">
        <button class="tab-btn active" onclick="switchTab('list')"><i class="fas fa-list"></i> Daftar Mata Kuliah</button>
        <button class="tab-btn" id="tab-detail-btn" onclick="switchTab('detail')"><i class="fas fa-file-alt"></i> Detail & Penyusunan RPS</button>
    </div>

    <div id="tab-list" class="tab-content active">
        <div class="card-modern">
            <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 16px; font-weight: 700; color: var(--text-dark);">Daftar Program Studi Mata Kuliah</h2>
                <div style="position: relative; width: 300px;">
                    <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--secondary); font-size: 13px;"></i>
                    <input type="text" id="mkSearch" placeholder="Cari Kode atau Nama MK..." onkeyup="filterMK()" style="width: 100%; padding: 10px 15px 10px 40px; border: 1px solid var(--border); border-radius: 12px; font-size: 13px;">
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th style="width: 100px;">SKS</th>
                            <th style="width: 100px;">Semester</th>
                            <th style="width: 130px;">Tahun Ajaran</th>
                            <th style="width: 130px;">Status RPS</th>
                            <th>Dosen Pengampu</th>
                            <th style="width: 120px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mk_list as $mk): 
                            $mk['mapping'] = getMappingData($mk['id'], $pdo);
                            $status_class = 'tag-slate';
                            if ($mk['rps_status'] === 'Approved') $status_class = 'tag-emerald';
                            if ($mk['rps_status'] === 'Pending') $status_class = 'tag-amber';
                            if ($mk['rps_status'] === 'Rejected') $status_class = 'tag-red';
                        ?>
                        <tr class="mk-row" data-search="<?php echo strtolower($mk['kode_mk'] . ' ' . $mk['nama_mk']); ?>">
                            <td><span class="badge-tag tag-blue"><?php echo $mk['kode_mk']; ?></span></td>
                            <td style="font-weight: 700; color: #0f172a;"><?php echo $mk['nama_mk']; ?></td>
                            <td><span class="badge-tag tag-slate"><?php echo $mk['sks']; ?> SKS</span></td>
                            <td><span class="badge-tag tag-amber" translate="no">SEM <?php echo $mk['semester_default']; ?></span></td>
                            <td><span class="badge-tag tag-blue" translate="no"><?php echo htmlspecialchars($mk['tahun_ajaran'] ?? '2024/2025'); ?></span></td>
                            <td>
                                <span class="badge-tag <?php echo $status_class; ?>">
                                    <i class="fas <?php 
                                        if($mk['rps_status'] === 'Approved') echo 'fa-check-circle';
                                        elseif($mk['rps_status'] === 'Pending') echo 'fa-clock';
                                        elseif($mk['rps_status'] === 'Rejected') echo 'fa-times-circle';
                                        else echo 'fa-info-circle';
                                    ?>"></i>
                                    <?php echo $mk['rps_status'] ?: 'Belum Ada'; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($mk['nama_dosen'] ?? 'No Name'); ?>&background=random&size=24" style="border-radius: 6px;">
                                    <span style="font-size: 13px;"><?php echo $mk['nama_dosen'] ?: '<span style="color:#94a3b8; font-style:italic;">Belum ada</span>'; ?></span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <button class="btn-action btn-view" title="Kelola & Detail" onclick='selectMK(<?php echo json_encode($mk); ?>)'>
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-detail" class="tab-content">
        <div id="detail-placeholder" class="card-modern empty-state">
            <i class="fas fa-mouse-pointer"></i>
            <h3>Pilih Mata Kuliah</h3>
            <p>Klik tombol panah pada daftar mata kuliah untuk menyusun dan melihat detail RPS.</p>
        </div>
        <div id="detail-active" style="display: none;"></div>
    </div>
</div>

<div class="modal-overlay" id="modalInput">
    <!-- ... existing input modal content ... -->
    <div class="modal-content" style="max-width: 1300px;">
        <div style="padding: 20px 24px; background: #1e293b; color: white; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 18px; font-weight: 700; margin: 0;">Input Rincian Materi Per Minggu</h3>
                <p id="input-subtitle" style="font-size: 12px; opacity: 0.8; margin-top: 4px;"></p>
            </div>
            <button onclick="closeModal('modalInput')" style="background: none; border: none; color: white; cursor: pointer; font-size: 20px;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="subCpmkForm">
            <input type="hidden" name="action" value="save_sub_cpmk">
            <input type="hidden" name="mk_id" id="input-mk-id">
            <div style="padding: 0; overflow-y: auto; max-height: 65vh; background: #ffffff;">
                <table class="table-modern" style="border-collapse: separate; border-spacing: 0;">
                    <thead style="position: sticky; top: 0; z-index: 20; background: #f1f5f9; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <tr>
                            <th style="width: 60px; padding: 12px; font-size: 10px; text-align: center; border-right: 1px solid #e2e8f0;">Minggu</th>
                            <th style="padding: 12px; font-size: 10px;">Sub-CPMK</th>
                            <th style="padding: 12px; font-size: 10px;">Indikator</th>
                            <th style="padding: 12px; font-size: 10px;">Asesmen</th>
                            <th style="padding: 12px; font-size: 10px;">Materi</th>
                            <th style="padding: 12px; font-size: 10px;">Metode</th>
                            <th style="padding: 12px; font-size: 10px;">Setting</th>
                            <th style="padding: 12px; font-size: 10px; width: 70px; text-align: center;">Bobot</th>
                            <th style="width: 40px; border-left: 1px solid #e2e8f0;"></th>
                        </tr>
                    </thead>
                    <tbody id="input-rows"></tbody>
                </table>
            </div>
            <div style="padding: 20px 24px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <button type="button" class="badge-tag tag-slate" style="border: 1px solid #cbd5e1; cursor: pointer; padding: 10px 20px; font-size: 12px;" onclick="addNewRow()">
                    <i class="fas fa-plus-circle" style="margin-right: 8px;"></i> Tambah Minggu
                </button>
                <div style="display: flex; gap: 12px;">
                    <button type="button" onclick="closeModal('modalInput')" style="padding: 12px 24px; border-radius: 12px; border: 1px solid var(--border); background: white; cursor: pointer; font-weight: 600; color: #64748b;">Batal</button>
                    <button type="submit" style="padding: 12px 32px; border-radius: 12px; border: none; background: var(--primary); color: white; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">Simpan Rincian</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalBobot">
    <div class="modal-content" style="max-width: 900px;">
        <div style="padding: 20px 24px; background: #1e293b; color: white; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 18px; font-weight: 700; margin: 0;">Edit Bobot Penilaian per CPMK</h3>
            <button onclick="closeModal('modalBobot')" style="background: none; border: none; color: white; cursor: pointer; font-size: 20px;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="save_bobot_penilaian">
            <input type="hidden" name="mk_id" id="bobot-mk-id">
            <div style="padding: 24px; background: white;">
                <table class="table-modern">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th>CPMK</th>
                            <th style="width: 80px; text-align: center;">Tugas 1</th>
                            <th style="width: 80px; text-align: center;">Tugas 2</th>
                            <th style="width: 80px; text-align: center;">Tugas 3</th>
                            <th style="width: 80px; text-align: center;">Proyek 1</th>
                            <th style="width: 80px; text-align: center;">Proyek 2</th>
                        </tr>
                    </thead>
                    <tbody id="bobot-rows"></tbody>
                </table>
            </div>
            <div style="padding: 20px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 12px; background: #f8fafc;">
                <button type="button" onclick="closeModal('modalBobot')" style="padding: 10px 20px; border-radius: 10px; border: 1px solid var(--border); background: white; cursor: pointer; font-weight: 600;">Batal</button>
                <button type="submit" style="padding: 10px 30px; border-radius: 10px; border: none; background: var(--primary); color: white; font-weight: 700; cursor: pointer;">Simpan Bobot</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentSelectedMK = null;

function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
    document.querySelector(`[onclick="switchTab('${tabId}')"]`).classList.add('active');

    if (tabId === 'list') {
        document.getElementById('detail-active').style.display = 'none';
        document.getElementById('detail-active').innerHTML = '';
        document.getElementById('detail-placeholder').style.display = 'block';
        currentSelectedMK = null;
    }
}

function selectMK(data) {
    currentSelectedMK = data;
    renderDetailView(data);
    switchTab('detail');
}

function filterMK() {
    const filter = document.getElementById('mkSearch').value.toLowerCase();
    document.querySelectorAll('.mk-row').forEach(row => {
        row.style.display = row.dataset.search.includes(filter) ? "" : "none";
    });
}

function renderDetailView(data) {
    const container = document.getElementById('detail-active');
    document.getElementById('detail-placeholder').style.display = 'none';
    container.style.display = 'block';

    const statusTag = data.rps_status === 'Approved' ? 'tag-emerald' : 
                    (data.rps_status === 'Pending' ? 'tag-amber' : 
                    (data.rps_status === 'Rejected' ? 'tag-red' : 'tag-slate'));
    
    const statusIcon = data.rps_status === 'Approved' ? 'fa-check-circle' : 
                     (data.rps_status === 'Pending' ? 'fa-clock' : 
                     (data.rps_status === 'Rejected' ? 'fa-times-circle' : 'fa-info-circle'));

    let html = `
        <div class="card-modern" style="padding: 0; position: relative;">
            <button onclick="switchTab('list')" style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.1); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="padding: 25px; background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; border-radius: 20px 20px 0 0; display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 style="font-size: 24px; font-weight: 800; margin: 0;">${data.nama_mk}</h2>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 8px;">
                        <span class="badge-tag tag-blue" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);">${data.kode_mk}</span>
                        <span style="opacity: 0.8; font-size: 13px;">${data.sks} SKS • Semester ${data.semester_default}</span>
                        <span class="badge-tag ${statusTag}" style="margin-left: 10px;">
                            <i class="fas ${statusIcon}"></i> Status: ${data.rps_status || 'Belum Diajukan'}
                        </span>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-right: 40px;">
                    <button onclick="switchTab('list')" class="tab-btn" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); padding: 8px 20px;"><i class="fas fa-arrow-left"></i> Kembali ke Daftar</button>
                </div>
            </div>

            <div style="padding: 30px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <div>
                        <div style="margin-bottom: 25px;">
                            <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary); text-transform: uppercase; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-align-left" style="color: var(--primary);"></i> Deskripsi Mata Kuliah
                            </h4>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid var(--border); line-height: 1.6; color: #475569;">
                                ${data.deskripsi || '<span style="font-style:italic; color:#94a3b8;">Tidak ada deskripsi tersedia.</span>'}
                            </div>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary); text-transform: uppercase; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-graduation-cap" style="color: var(--warning);"></i> Capaian Pembelajaran (CPL & CPMK)
                            </h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div style="background: white; padding: 15px; border-radius: 16px; border: 1px solid var(--border);">
                                    <span style="font-size: 11px; font-weight: 800; color: var(--secondary); display: block; margin-bottom: 10px;">CPL TERKAIT</span>
                                    ${data.mapping.cpl.length ? data.mapping.cpl.map(c => `<div style="padding: 8px; border-left: 3px solid var(--primary); background: #f1f5f9; border-radius: 4px; margin-bottom: 8px; font-size: 11px;"><strong>${c.kode_cpl}</strong>: ${c.deskripsi}</div>`).join('') : '<p style="font-size:12px; color:#94a3b8;">Belum ada.</p>'}
                                </div>
                                <div style="background: white; padding: 15px; border-radius: 16px; border: 1px solid var(--border);">
                                    <span style="font-size: 11px; font-weight: 800; color: var(--secondary); display: block; margin-bottom: 10px;">CPMK TERKAIT</span>
                                    ${data.mapping.cpmk.length ? data.mapping.cpmk.map(c => `<div style="padding: 8px; border-left: 3px solid var(--warning); background: #f1f5f9; border-radius: 4px; margin-bottom: 8px; font-size: 11px;"><strong>${c.kode_cpmk}</strong>: ${c.deskripsi}</div>`).join('') : '<p style="font-size:12px; color:#94a3b8;">Belum ada.</p>'}
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary); text-transform: uppercase; margin: 0; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-chart-pie" style="color: var(--danger);"></i> Bobot Penilaian per CPMK
                                </h4>
                                <button onclick='openWeightModal(${JSON.stringify(data)})' class="badge-tag tag-blue" style="border: none; cursor: pointer; padding: 6px 12px; font-weight: 700;">
                                    <i class="fas fa-edit"></i> Edit Bobot
                                </button>
                            </div>
                            <div style="overflow-x: auto; border-radius: 16px; border: 1px solid var(--border); background: white;">
                                <table class="table-modern" style="font-size: 12px;">
                                    <thead>
                                        <tr style="background: #f8fafc;">
                                            <th>CPMK</th>
                                            <th style="text-align: center;">T1</th>
                                            <th style="text-align: center;">T2</th>
                                            <th style="text-align: center;">T3</th>
                                            <th style="text-align: center;">P1</th>
                                            <th style="text-align: center;">P2</th>
                                            <th style="text-align: center; font-weight: 800;">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.mapping.cpmk.length ? data.mapping.cpmk.map(c => {
                                            const total = (parseInt(c.tugas1)||0) + (parseInt(c.tugas2)||0) + (parseInt(c.tugas3)||0) + (parseInt(c.proyek1)||0) + (parseInt(c.proyek2)||0);
                                            return `
                                            <tr>
                                                <td style="font-weight: 700;">${c.kode_cpmk}</td>
                                                <td style="text-align: center;">${c.tugas1}%</td>
                                                <td style="text-align: center;">${c.tugas2}%</td>
                                                <td style="text-align: center;">${c.tugas3}%</td>
                                                <td style="text-align: center;">${c.proyek1}%</td>
                                                <td style="text-align: center;">${c.proyek2}%</td>
                                                <td style="text-align: center; font-weight: 800; color: ${total === 0 ? 'var(--danger)' : 'var(--success)'};">${total}%</td>
                                            </tr>`;
                                        }).join('') : '<tr><td colspan="7" style="text-align: center; color: #94a3b8;">Belum ada CPMK.</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div style="background: #f1f5f9; padding: 25px; border-radius: 20px; border: 1px solid var(--border); height: fit-content;">
                        <h4 style="font-size: 13px; font-weight: 700; color: var(--text-dark); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-info-circle" style="color: var(--primary);"></i> Informasi Dosen
                        </h4>
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(data.nama_dosen || 'No Name')}&background=random&size=48" style="border-radius: 12px;">
                            <div>
                                <div style="font-weight: 800; color: #1e293b;">${data.nama_dosen || 'Belum ditugaskan'}</div>
                                <div style="font-size: 12px; color: var(--secondary); font-weight: 600;">${data.jabatan_dosen || 'Dosen Pengampu'}</div>
                            </div>
                        </div>
                        <hr style="border: none; border-top: 1px dashed #cbd5e1; margin: 20px 0;">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <form method="POST" onsubmit="return confirm('Ajukan RPS ini ke Kaprodi untuk disetujui?')">
                                <input type="hidden" name="action" value="ajukan_rps">
                                <input type="hidden" name="mk_id" value="${data.id}">
                                <button type="submit" ${data.rps_status === 'Pending' || data.rps_status === 'Approved' ? 'disabled' : ''} style="width: 100%; padding: 14px; border-radius: 12px; border: none; background: ${data.rps_status === 'Pending' || data.rps_status === 'Approved' ? '#94a3b8' : 'var(--primary)'}; color: white; font-weight: 700; cursor: ${data.rps_status === 'Pending' || data.rps_status === 'Approved' ? 'not-allowed' : 'pointer'}; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: ${data.rps_status === 'Pending' || data.rps_status === 'Approved' ? 'none' : '0 4px 12px rgba(79, 70, 229, 0.25)'};">
                                    <i class="fas ${data.rps_status === 'Rejected' ? 'fa-redo' : 'fa-paper-plane'}"></i> 
                                    ${data.rps_status === 'Rejected' ? 'AJUKAN ULANG' : (data.rps_status === 'Pending' ? 'MENUNGGU PERSETUJUAN' : (data.rps_status === 'Approved' ? 'SUDAH DISETUJUI' : 'AJUKAN KE KAPRODI'))}
                                </button>
                            </form>

                            <a href="cetak_rps.php?id=${data.id}" target="_blank" 
                               style="width: 100%; padding: 14px; border-radius: 12px; border: 2px solid ${data.rps_status === 'Approved' ? '#10b981' : '#e2e8f0'}; 
                                      background: ${data.rps_status === 'Approved' ? '#ecfdf5' : '#f8fafc'}; 
                                      color: ${data.rps_status === 'Approved' ? '#059669' : '#94a3b8'}; 
                                      font-weight: 700; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; 
                                      cursor: ${data.rps_status === 'Approved' ? 'pointer' : 'not-allowed'}; 
                                      pointer-events: ${data.rps_status === 'Approved' ? 'auto' : 'none'};"
                               title="${data.rps_status === 'Approved' ? 'Cetak RPS' : 'RPS harus disetujui untuk dicetak'}">
                                <i class="fas fa-file-pdf"></i> CETAK PDF (RPS)
                            </a>

                            <p style="font-size: 11px; color: var(--secondary); text-align: center; line-height: 1.4;">
                                ${data.rps_status === 'Pending' ? 'RPS Anda sedang dalam proses peninjauan oleh Kaprodi.' : 
                                 (data.rps_status === 'Approved' ? 'RPS ini telah disetujui dan siap untuk dicetak.' : 
                                 (data.rps_status === 'Rejected' ? 'RPS ditolak. Silakan perbaiki rincian materi dan ajukan ulang.' : 'Setelah diajukan, status RPS akan menjadi "Pending" dan menunggu persetujuan dari Kaprodi.'))}
                            </p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f1f5f9;">
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--secondary); text-transform: uppercase; margin: 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-calendar-alt" style="color: var(--success);"></i> Rincian Materi Per Minggu
                        </h4>
                        <div style="display: flex; gap: 10px;">
                            <button onclick='openInputSubCpmk(${JSON.stringify(data)})' class="badge-tag" style="background: var(--success); color: white; border: none; cursor: pointer; padding: 10px 20px; border-radius: 10px; font-weight: 700; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                                <i class="fas fa-tasks"></i> KELOLA DATA MINGGUAN
                            </button>
                        </div>
                    </div>
                    <div style="overflow-x: auto; border-radius: 16px; border: 1px solid var(--border); background: white;">
                        <table class="table-modern" style="min-width: 1000px;">
                            <thead>
                                <tr>
                                    <th style="width: 60px; text-align: center;">Minggu</th>
                                    <th>Sub-CPMK</th>
                                    <th>Indikator</th>
                                    <th>Asesmen</th>
                                    <th>Materi</th>
                                    <th>Metode</th>
                                    <th style="width: 100px; text-align: center;">Setting</th>
                                    <th style="width: 60px; text-align: center;">Bobot</th>
                                </tr>
                            </thead>
                            <tbody id="weekly-detail-tbody">
                                ${data.mapping.sub.length ? data.mapping.sub.map(s => `
                                    <tr>
                                        <td style="text-align: center; font-weight: 800; color: var(--primary); background: #f8fafc;">${s.minggu}</td>
                                        <td style="font-size: 12.5px; line-height: 1.5;">${s.sub_cpmk}</td>
                                        <td style="font-size: 12px; color: #475569;">${s.indikator || '-'}</td>
                                        <td style="font-size: 12px; color: #475569;">${s.bentuk_asesmen || '-'}</td>
                                        <td style="font-size: 12.5px; color: #475569;">${s.materi}</td>
                                        <td style="font-size: 12px; color: #475569;">${s.metode || '-'}</td>
                                        <td style="font-size: 11px; text-align: center;"><span class="badge-tag tag-slate">${s.setting === 'Daring' ? 'Online' : (s.setting || 'Tatap Muka')}</span></td>
                                        <td style="text-align: center; font-weight: 700; color: var(--primary);">${s.bobot || 0}</td>
                                    </tr>
                                `).join('') : '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">Belum ada data materi mingguan.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.innerHTML = html;
}

function updateSettingHidden(radio, index) {
    document.getElementById('setting_hidden_' + index).value = radio.value;
}

function openInputSubCpmk(data) {
    document.getElementById('input-mk-id').value = data.id;
    document.getElementById('input-subtitle').innerText = `${data.kode_mk} • ${data.nama_mk}`;
    const tbody = document.getElementById('input-rows');
    tbody.innerHTML = '';
    if (data.mapping.sub.length > 0) {
        data.mapping.sub.forEach((s, idx) => addRow(s, idx));
    } else {
        addRow({minggu: 1, sub_cpmk: '', indikator: '', bentuk_asesmen: '', materi: '', metode: '', bobot: 2}, 0);
    }
    document.getElementById('modalInput').style.display = 'flex';
}

function addRow(data, index) {
    const tbody = document.getElementById('input-rows');
    const tr = document.createElement('tr');
    let bobotOptions = '';
    for (let i = 1; i <= 9; i++) bobotOptions += `<option value="${i}" ${data.bobot == i ? 'selected' : ''}>${i}</option>`;
    tr.innerHTML = `
        <td style="background: #f8fafc; border-right: 1px solid #e2e8f0; width: 70px; text-align: center;">
            <input type="text" name="minggu[]" value="${data.minggu || '1'}" class="form-control-custom" style="text-align:center; border:none; border-bottom: 1px dashed #cbd5e1; background:transparent; font-weight: 800; font-size: 16px; color: #4f46e5; width: 50px; padding: 4px;">
        </td>
        <td style="padding: 10px;"><textarea name="sub_cpmk[]" class="form-control-custom" style="min-height: 80px;">${data.sub_cpmk}</textarea></td>
        <td style="padding: 10px;"><textarea name="indikator[]" class="form-control-custom" style="min-height: 80px;">${data.indikator || ''}</textarea></td>
        <td style="padding: 10px;"><textarea name="bentuk_asesmen[]" class="form-control-custom" style="min-height: 80px;">${data.bentuk_asesmen || ''}</textarea></td>
        <td style="padding: 10px;"><textarea name="materi[]" class="form-control-custom" style="min-height: 80px;">${data.materi || ''}</textarea></td>
        <td style="padding: 10px;"><input type="text" name="metode_row[]" value="${data.metode || ''}" class="form-control-custom"></td>
        <td style="padding: 10px;">
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; cursor: pointer; color: #475569;">
                    <input type="radio" name="setting_radio_${index}" value="Tatap Muka" ${!data.setting || data.setting == 'Tatap Muka' ? 'checked' : ''} onchange="updateSettingHidden(this, ${index})"> Tatap Muka
                </label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; cursor: pointer; color: #475569;">
                    <input type="radio" name="setting_radio_${index}" value="Online" ${data.setting == 'Online' || data.setting == 'Daring' ? 'checked' : ''} onchange="updateSettingHidden(this, ${index})"> Online
                </label>
                <input type="hidden" name="setting_row[]" id="setting_hidden_${index}" value="${data.setting || 'Tatap Muka'}">
            </div>
        </td>
        <td style="padding: 10px;"><select name="bobot[]" class="form-control-custom">${bobotOptions}</select></td>
        <td style="text-align: center; border-left: 1px solid #e2e8f0; vertical-align: middle;">
            <button type="button" onclick="if(confirm('Hapus baris minggu ini?')) this.closest('tr').remove()" style="background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; display: flex; align-items: center; gap: 5px; margin: 0 auto;">
                <i class="fas fa-trash-alt"></i> Hapus
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function addNewRow() {
    const tbody = document.getElementById('input-rows');
    let nextNum = tbody.rows.length + 1;
    let index = Date.now(); // Use unique timestamp for new row radios
    addRow({minggu: nextNum, sub_cpmk: '', indikator: '', bentuk_asesmen: '', materi: '', metode: '', bobot: 2}, index);
}

function openWeightModal(data) {
    document.getElementById('bobot-mk-id').value = data.id;
    const tbody = document.getElementById('bobot-rows');
    tbody.innerHTML = '';
    
    data.mapping.cpmk.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="font-weight: 700; font-size: 13px;">${c.kode_cpmk}
                <input type="hidden" name="mk_cpmk_id[]" value="${c.mk_cpmk_id}">
            </td>
            <td><input type="number" name="t1[]" value="${c.tugas1 || 0}" class="form-control-custom" style="padding: 8px; text-align: center;"></td>
            <td><input type="number" name="t2[]" value="${c.tugas2 || 0}" class="form-control-custom" style="padding: 8px; text-align: center;"></td>
            <td><input type="number" name="t3[]" value="${c.tugas3 || 0}" class="form-control-custom" style="padding: 8px; text-align: center;"></td>
            <td><input type="number" name="p1[]" value="${c.proyek1 || 0}" class="form-control-custom" style="padding: 8px; text-align: center;"></td>
            <td><input type="number" name="p2[]" value="${c.proyek2 || 0}" class="form-control-custom" style="padding: 8px; text-align: center;"></td>
        `;
        tbody.appendChild(tr);
    });
    
    document.getElementById('modalBobot').style.display = 'flex';
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>

<?php include 'includes/footer.php'; ?>
