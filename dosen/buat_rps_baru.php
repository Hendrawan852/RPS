<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nip_nidn FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_nip = $stmt->fetchColumn() ?: '';

// Find internal dosen_id from the central 'dosen' table using NIP
$stmt = $pdo->prepare("SELECT id FROM dosen WHERE nip = ?");
$stmt->execute([$user_nip]);
$internal_dosen_id = $stmt->fetchColumn() ?: 0;

// 1. Fetch available Mata Kuliah (Only assigned to this lecturer)
$stmt = $pdo->prepare("SELECT mk.*, p.nama_prodi FROM mata_kuliah mk LEFT JOIN prodi p ON mk.prodi_id = p.id WHERE mk.dosen_id = ? ORDER BY mk.nama_mk ASC");
$stmt->execute([$internal_dosen_id]);
$courses = $stmt->fetchAll();

// Fetch Lecturer Prodi
$stmt = $pdo->prepare("SELECT p.nama_prodi FROM users u LEFT JOIN prodi p ON u.prodi_id = p.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user_prodi = $stmt->fetchColumn() ?: "Program Studi";

// 1.5 Fetch existing data if editing
$is_edit = false;
$edit_data = [];
if (isset($_GET['edit_id'])) {
    $is_edit = true;
    $stmt = $pdo->prepare("SELECT * FROM pengajuan_rps WHERE id = ? AND dosen_id = ?");
    $stmt->execute([$_GET['edit_id'], $user_id]);
    $edit_data = $stmt->fetch();
    
    if (!$edit_data) {
        $is_edit = false; // Invalid ID or not owned by user
    }
}

// 2. Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rps'])) {
    $mk_id = $_POST['mk_id'];
    $semester_ta = "Smt " . $_POST['semester'] . " - " . $_POST['ta'];
    $sks = $_POST['sks'];
    $metode = $_POST['metode'];
    $penilaian = $_POST['penilaian'];
    $referensi = $_POST['referensi'];
    
    try {
        $pdo->beginTransaction();

        if ($is_edit) {
            // Update Main RPS Data
            $stmt = $pdo->prepare("UPDATE pengajuan_rps SET mk_id = ?, semester = ?, sks = ?, status = 'Pending', metode = ?, penilaian = ?, referensi = ? WHERE id = ?");
            $stmt->execute([$mk_id, $semester_ta, $sks, $metode, $penilaian, $referensi, $_GET['edit_id']]);
            $rps_id = $_GET['edit_id'];
        } else {
            // Save New RPS Data
            $stmt = $pdo->prepare("INSERT INTO pengajuan_rps (mk_id, dosen_id, semester, sks, status, metode, penilaian, referensi) VALUES (?, ?, ?, ?, 'Pending', ?, ?, ?)");
            $stmt->execute([$mk_id, $user_id, $semester_ta, $sks, $metode, $penilaian, $referensi]);
            $rps_id = $pdo->lastInsertId();
        }

        // Save Weekly Details (Sub-CPMK)
        // First clear existing if any for this MK (optional, but keep it clean)
        $stmt = $pdo->prepare("DELETE FROM sub_cpmk WHERE mk_id = ?");
        $stmt->execute([$mk_id]);

        if (isset($_POST['minggu'])) {
            foreach ($_POST['minggu'] as $index => $mg) {
                $stmt = $pdo->prepare("INSERT INTO sub_cpmk (mk_id, minggu, sub_cpmk, indikator, bentuk_asesmen, materi, bahan_kajian, metode, bobot, waktu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $mk_id, 
                    "Minggu " . $mg, 
                    $_POST['sub_cpmk'][$index], 
                    $_POST['indikator'][$index], 
                    $_POST['bentuk_asesmen'][$index], 
                    $_POST['materi'][$index], 
                    $_POST['bahan_kajian'][$index], 
                    $_POST['metode_step3'][$index], 
                    $_POST['bobot'][$index], 
                    $_POST['waktu'][$index]
                ]);
            }
        }

        // Save CPL & CPMK Mappings (Update mk_cpl and mk_cpmk)
        $stmt = $pdo->prepare("DELETE FROM mk_cpl WHERE mk_id = ?");
        $stmt->execute([$mk_id]);
        if (isset($_POST['cpl_ids'])) {
            foreach ($_POST['cpl_ids'] as $cpl_id) {
                $stmt = $pdo->prepare("INSERT INTO mk_cpl (mk_id, cpl_id) VALUES (?, ?)");
                $stmt->execute([$mk_id, $cpl_id]);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM mk_cpmk WHERE mk_id = ?");
        $stmt->execute([$mk_id]);
        if (isset($_POST['cpmk_ids'])) {
            foreach ($_POST['cpmk_ids'] as $cpmk_id) {
                $stmt = $pdo->prepare("INSERT INTO mk_cpmk (mk_id, cpmk_id) VALUES (?, ?)");
                $stmt->execute([$mk_id, $cpmk_id]);
            }
        }

        $pdo->commit();
        $_SESSION['msg'] = "RPS berhasil diajukan ke Kaprodi.";
        header("Location: daftar_rps.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Gagal mengajukan RPS: " . $e->getMessage();
    }
}
?>

<div class="page-header">
    <h2>Buat RPS Baru</h2>
    <p>Ikuti langkah-langkah di bawah untuk menyusun Rencana Pembelajaran Semester.</p>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<form method="POST" id="rpsForm">
<div class="wizard-container card">
    <div class="wizard-steps">
        <div class="step active" data-step="1">
            <div class="step-num">1</div>
            <div class="step-label">Identitas MK</div>
        </div>
        <div class="step" data-step="2">
            <div class="step-num">2</div>
            <div class="step-label">CPL & CPMK</div>
        </div>
        <div class="step" data-step="3">
            <div class="step-num">3</div>
            <div class="step-label">Materi & Sub</div>
        </div>
        <div class="step" data-step="4">
            <div class="step-num">4</div>
            <div class="step-label">Metode</div>
        </div>
        <div class="step" data-step="5">
            <div class="step-num">5</div>
            <div class="step-label">Penilaian</div>
        </div>
        <div class="step" data-step="6">
            <div class="step-num">6</div>
            <div class="step-label">Referensi</div>
        </div>
    </div>

    <div class="wizard-content">
        <!-- Step 1: Identitas -->
        <div class="step-content active" id="step1">


            <div class="content-header">
                <h3>Langkah 1: Identitas Mata Kuliah</h3>
                <p>Silakan lengkapi informasi dasar mata kuliah untuk dokumen RPS.</p>
            </div>

            <div class="form-horizontal">
                <div class="form-group">
                    <label>Pilih Mata Kuliah</label>
                    <div class="form-control-wrapper">
                        <select class="form-control" name="mk_id" required onchange="updateCourseFields(this)">
                            <option value="">Pilih MK yang diampu...</option>
                            <?php foreach ($courses as $mk): ?>
                                <option value="<?php echo $mk['id']; ?>" 
                                        <?php echo ($is_edit && $edit_data['mk_id'] == $mk['id']) ? 'selected' : ''; ?>
                                        data-sks="<?php echo $mk['sks']; ?>" 
                                        data-kode="<?php echo $mk['kode_mk']; ?>"
                                        data-semester="<?php echo $mk['semester_default']; ?>"
                                        data-deskripsi="<?php echo htmlspecialchars((string)$mk['deskripsi']); ?>">
                                    <?php echo $mk['kode_mk'] . ' - ' . $mk['nama_mk']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($courses)): ?>
                            <small style="color: #ef4444; margin-top: 5px; display: block;">
                                <i class="fas fa-exclamation-triangle"></i> Anda belum memiliki mata kuliah yang diampu. Silakan hubungi Kaprodi.
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kode MK</label>
                    <div class="form-control-wrapper">
                        <input type="text" id="kodeMkInput" class="form-control" placeholder="Otomatis..." readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label>Bobot SKS</label>
                    <div class="form-control-wrapper">
                        <input type="number" id="sksInput" class="form-control" name="sks" placeholder="0" required readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label>Semester / Tahun Ajaran</label>
                    <div class="form-control-wrapper">
                        <div style="display: flex; gap: 15px;">
                            <select class="form-control" name="semester" id="semesterSelect" style="flex: 1;">
                                <option value="">Semester...</option>
                                <?php 
                                $current_smt = $is_edit ? explode(' ', $edit_data['semester'])[1] : '';
                                for($i=1; $i<=8; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($current_smt == $i) ? 'selected' : ''; ?>>Semester <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <select class="form-control" name="ta" style="flex: 2;">
                                <?php 
                                $current_ta = $is_edit ? explode(' - ', $edit_data['semester'])[1] : '';
                                $options = ["Gasal 2025/2026", "Genap 2024/2025", "Gasal 2024/2025"];
                                foreach($options as $opt): ?>
                                    <option value="<?php echo $opt; ?>" <?php echo ($current_ta == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Rumpun MK</label>
                    <div class="form-control-wrapper">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_prodi); ?>" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi Mata Kuliah</label>
                    <div class="form-control-wrapper">
                        <textarea id="deskripsiTextarea" class="form-control" rows="4" placeholder="Deskripsi akan muncul otomatis..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: CPL & CPMK -->
        <div class="step-content" id="step2">
             <div class="content-header">
                <h3>CPL & CPMK</h3>
                <p>Petakan capaian pembelajaran lulusan ke capaian mata kuliah.</p>
            </div>
            <div class="mapping-container" id="cplCpmkContainer">
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Memuat data CPL & CPMK...</p>
                </div>
            </div>
        </div>

        <!-- Step 3: Materi & Sub-CPMK -->
        <div class="step-content" id="step3">
            <div class="content-header">
                <h3>Langkah 3: Materi & Sub-CPMK</h3>
                <p>Informasi detail untuk materi pembelajaran dan kesesuaian dengan Sub-CPMK.</p>
            </div>
            <div class="table-card">
                <table class="minimal-table" id="materiTable">
                    <thead>
                        <tr>
                            <th>Minggu</th>
                            <th>Sub-CPMK</th>
                            <th>Indikator</th>
                            <th>Asesmen</th>
                            <th>Materi</th>
                            <th>Bahan Kajian</th>
                            <th>Metode</th>
                            <th>Bobot</th>
                            <th style="text-align: right;">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically -->
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px; color: #94a3b8;">
                                Silakan pilih mata kuliah di langkah pertama untuk mulai mengisi materi.
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="padding: 15px; text-align: center;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="addMateriRow()"><i class="fas fa-plus"></i> Tambah Baris Minggu</button>
                </div>
            </div>
        </div>
        <div class="step-content" id="step4">
            <div class="content-header">
                <h3>Langkah 4: Metode & Pengalaman Belajar</h3>
                <p>Tentukan metode pengajaran (Ceramah, Diskusi, Case Study, dll).</p>
            </div>
            <div class="form-group">
                <label>Metode Pembelajaran</label>
                <textarea class="form-control" name="metode" rows="5" placeholder="Contoh: Ceramah, Diskusi Kelompok, Small Group Discussion..."><?php echo $is_edit ? htmlspecialchars((string)$edit_data['metode']) : ''; ?></textarea>
            </div>
        </div>
        <div class="step-content" id="step5">
            <div class="content-header">
                <h3>Langkah 5: Penilaian & Bobot</h3>
                <p>Tentukan kriteria penilaian dan rubrik.</p>
            </div>
            <div class="form-group">
                <label>Kriteria Penilaian</label>
                <textarea class="form-control" name="penilaian" rows="5" placeholder="Contoh: Keaktifan (10%), Tugas (20%), UTS (30%), UAS (40%)..."><?php echo $is_edit ? htmlspecialchars((string)$edit_data['penilaian']) : ''; ?></textarea>
            </div>
        </div>
        <div class="step-content" id="step6">
            <div class="content-header">
                <h3>Langkah 6: Referensi & Lampiran</h3>
                <p>Daftar pustaka utama dan pendukung.</p>
            </div>
            <div class="form-group">
                <label>Daftar Referensi</label>
                <textarea class="form-control" name="referensi" rows="5" placeholder="Masukkan daftar pustaka di sini..."><?php echo $is_edit ? htmlspecialchars((string)$edit_data['referensi']) : ''; ?></textarea>
            </div>
        </div>
    </div>

    </div>

    <div class="wizard-footer">
        <button type="button" class="btn btn-outline" id="prevBtn" disabled>Sebelumnya</button>
        <div class="right-buttons">
            <button type="button" class="btn btn-secondary">Simpan Draft</button>
            <button type="button" class="btn btn-primary" id="nextBtn">Selanjutnya</button>
            <input type="hidden" name="submit_rps" value="1">
        </div>
    </div>
</div>
</form>

<script>
let currentStep = 1;
const totalSteps = 6;
const rpsForm = document.getElementById('rpsForm');

function updateCourseFields(select) {
    const option = select.options[select.selectedIndex];
    if (!option.value) {
        document.getElementById('kodeMkInput').value = '';
        document.getElementById('sksInput').value = '';
        document.getElementById('semesterSelect').value = '';
        document.getElementById('deskripsiTextarea').value = '';
        return;
    }

    const sks = option.getAttribute('data-sks');
    const kode = option.getAttribute('data-kode');
    const semester = option.getAttribute('data-semester');
    const deskripsi = option.getAttribute('data-deskripsi');

    document.getElementById('sksInput').value = sks;
    document.getElementById('kodeMkInput').value = kode;
    document.getElementById('semesterSelect').value = semester;
    document.getElementById('deskripsiTextarea').value = deskripsi;

    // Fetch CPL & CPMK
    fetchCplCpmk(option.value);
}

function fetchCplCpmk(mkId) {
    const container = document.getElementById('cplCpmkContainer');
    container.innerHTML = '<div style="text-align: center; padding: 40px; color: #94a3b8;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i><p>Memuat data CPL & CPMK...</p></div>';

    fetch(`api/get_mk_details.php?mk_id=${mkId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }

            let html = '<div class="mapping-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">';
            
            // CPL Section
            html += '<div><h4 style="margin-bottom: 15px; color: var(--primary); font-size: 14px;">CPL Terkait</h4>';
            if (data.cpl.length > 0) {
                data.cpl.forEach(c => {
                    html += `<div class="mapping-item" style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 10px;">
                                <label style="display: flex; gap: 10px; cursor: pointer;">
                                    <input type="checkbox" name="cpl_ids[]" value="${c.id}" checked> 
                                    <div><strong>${c.kode_cpl}:</strong> <span style="font-size: 13px; color: #64748b;">${c.deskripsi}</span></div>
                                </label>
                            </div>`;
                });
            } else {
                html += '<p style="font-size: 13px; color: #94a3b8; font-style: italic;">Tidak ada CPL yang dipetakan ke mata kuliah ini.</p>';
            }
            html += '</div>';

            // CPMK Section
            html += '<div><h4 style="margin-bottom: 15px; color: #f59e0b; font-size: 14px;">CPMK Terkait</h4>';
            if (data.cpmk.length > 0) {
                data.cpmk.forEach(c => {
                    html += `<div class="mapping-item" style="background: #fffbeb; padding: 12px; border-radius: 8px; border: 1px solid #fef3c7; margin-bottom: 10px;">
                                <label style="display: flex; gap: 10px; cursor: pointer;">
                                    <input type="checkbox" name="cpmk_ids[]" value="${c.id}" checked> 
                                    <div><strong>${c.kode_cpmk}:</strong> <span style="font-size: 13px; color: #92400e;">${c.deskripsi}</span></div>
                                </label>
                            </div>`;
                });
            } else {
                html += '<p style="font-size: 13px; color: #94a3b8; font-style: italic;">Tidak ada CPMK yang dipetakan ke mata kuliah ini.</p>';
            }
            html += '</div></div>';

            container.innerHTML = html;

            // Also clear step 3 placeholder
            const tbody = document.querySelector('#materiTable tbody');
            tbody.innerHTML = '';
            addMateriRow(); // Add first row
        })
        .catch(err => {
            container.innerHTML = `<div class="alert alert-danger">Gagal memuat data: ${err.message}</div>`;
        });
}

let weekCount = 0;
function addMateriRow() {
    weekCount++;
    const tbody = document.querySelector('#materiTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="minggu[]" value="${weekCount}" class="form-control" style="width: 50px; text-align: center;"></td>
        <td><textarea name="sub_cpmk[]" class="form-control" rows="2" placeholder="Sub-CPMK..."></textarea></td>
        <td><textarea name="indikator[]" class="form-control" rows="2" placeholder="Indikator..."></textarea></td>
        <td><input type="text" name="bentuk_asesmen[]" class="form-control" placeholder="Asesmen..."></td>
        <td><textarea name="materi[]" class="form-control" rows="2" placeholder="Materi..."></textarea></td>
        <td><input type="text" name="bahan_kajian[]" class="form-control" placeholder="Bahan Kajian..."></td>
        <td><input type="text" name="metode_step3[]" class="form-control" placeholder="Metode..."></td>
        <td><input type="number" name="bobot[]" class="form-control" value="0" style="width: 60px;"></td>
        <td><input type="text" name="waktu[]" class="form-control" value="150m" style="width: 80px; text-align: right;"></td>
    `;
    tbody.appendChild(tr);
}

document.getElementById('nextBtn').addEventListener('click', () => {
    if(currentStep < totalSteps) {
        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('completed');
        
        currentStep++;
        
        document.getElementById(`step${currentStep}`).classList.add('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
        
        document.getElementById('prevBtn').disabled = false;
        if(currentStep === totalSteps) {
            document.getElementById('nextBtn').innerText = "Submit ke Kaprodi";
            document.getElementById('nextBtn').classList.replace('btn-primary', 'btn-success');
        }
    } else {
        // Trigger actual form submission on last step
        rpsForm.submit();
    }
});

document.getElementById('prevBtn').addEventListener('click', () => {
    if(currentStep > 1) {
        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
        
        currentStep--;
        
        document.getElementById(`step${currentStep}`).classList.add('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('completed');
        
        if(currentStep === 1) document.getElementById('prevBtn').disabled = true;
        document.getElementById('nextBtn').innerText = "Selanjutnya";
        document.getElementById('nextBtn').classList.replace('btn-success', 'btn-primary');
    }
});

// Initial Load if Editing
<?php if ($is_edit): ?>
window.onload = function() {
    const select = document.querySelector('select[name="mk_id"]');
    updateCourseFields(select);
    
    // Fetch weekly data and populate Step 3
    fetch(`api/get_rps_content.php?id=<?php echo $_GET['edit_id']; ?>&raw=1`)
        .then(response => response.json())
        .then(data => {
            if (data.weeks && data.weeks.length > 0) {
                const tbody = document.querySelector('#materiTable tbody');
                tbody.innerHTML = '';
                weekCount = 0;
                data.weeks.forEach(w => {
                    addMateriRow();
                    const lastRow = tbody.lastElementChild;
                    lastRow.querySelector('input[name="minggu[]"]').value = w.minggu.replace('Minggu ', '');
                    lastRow.querySelector('textarea[name="sub_cpmk[]"]').value = w.sub_cpmk;
                    lastRow.querySelector('textarea[name="indikator[]"]').value = w.indikator;
                    lastRow.querySelector('input[name="bentuk_asesmen[]"]').value = w.bentuk_asesmen;
                    lastRow.querySelector('textarea[name="materi[]"]').value = w.materi;
                    lastRow.querySelector('input[name="bahan_kajian[]"]').value = w.bahan_kajian;
                    lastRow.querySelector('input[name="metode_step3[]"]').value = w.metode;
                    lastRow.querySelector('input[name="bobot[]"]').value = w.bobot;
                    lastRow.querySelector('input[name="waktu[]"]').value = w.waktu;
                });
            }
        });
}
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
