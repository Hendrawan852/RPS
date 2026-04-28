<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// 1. Fetch available Mata Kuliah
$stmt = $pdo->query("SELECT * FROM mata_kuliah ORDER BY nama_mk ASC");
$courses = $stmt->fetchAll();

// 2. Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rps'])) {
    $mk_id = $_POST['mk_id'];
    $semester_ta = $_POST['semester_ta'];
    $sks = $_POST['sks'];
    $dosen_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO pengajuan_rps (mk_id, dosen_id, semester, sks, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->execute([$mk_id, $dosen_id, $semester_ta, $sks]);
        $_SESSION['msg'] = "RPS berhasil diajukan ke Kaprodi.";
        header("Location: daftar_rps.php");
        exit;
    } catch (PDOException $e) {
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
                        <select class="form-control" name="mk_id" required onchange="updateSks(this)">
                            <option value="">Pilih MK yang diampu...</option>
                            <?php foreach ($courses as $mk): ?>
                                <option value="<?php echo $mk['id']; ?>" data-sks="<?php echo $mk['sks']; ?>" <?php echo ($mk['nama_mk'] == 'Pemrograman Web') ? 'selected' : ''; ?>>
                                    <?php echo $mk['kode_mk'] . ' - ' . $mk['nama_mk']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kode MK</label>
                    <div class="form-control-wrapper">
                        <input type="text" class="form-control" value="TI-203" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label>Bobot SKS</label>
                    <div class="form-control-wrapper">
                        <input type="number" id="sksInput" class="form-control" name="sks" value="3" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Semester / Tahun Ajaran</label>
                    <div class="form-control-wrapper">
                        <div style="display: flex; gap: 15px;">
                            <select class="form-control" name="semester" style="flex: 1;">
                                <option value="3" selected>Semester 3</option>
                                <option value="1">Semester 1</option>
                                <option value="5">Semester 5</option>
                            </select>
                            <select class="form-control" name="ta" style="flex: 2;">
                                <option value="Gasal 2025/2026" selected>Gasal 2025/2026</option>
                                <option value="Gasal 2024/2025">Gasal 2024/2025</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Rumpun MK</label>
                    <div class="form-control-wrapper">
                        <input type="text" class="form-control" value="MK Inti Prodi" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi Mata Kuliah</label>
                    <div class="form-control-wrapper">
                        <textarea class="form-control" rows="4" placeholder="Masukkan deskripsi singkat mengenai tujuan dan cakupan mata kuliah ini...">Mata kuliah ini memberikan pemahaman mendalam mengenai pengembangan aplikasi web modern menggunakan teknologi HTML5, CSS3, dan JavaScript Dasar hingga framework populer.</textarea>
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
            <div class="mapping-container">
                <div class="mapping-item">
                    <label><input type="checkbox"> <strong>CPL-01:</strong> Mampu menerapkan pemikiran logis...</label>
                </div>
                <div class="mapping-item">
                    <label><input type="checkbox"> <strong>CPL-02:</strong> Menunjukkan kinerja mandiri...</label>
                </div>
            </div>
        </div>

        <!-- Remaining steps would follow similar structure -->
        <div class="step-content" id="step3">
            <div class="content-header">
                <h3>Langkah 3: Materi & Sub-CPMK</h3>
                <p>Informasi detail untuk materi pembelajaran dan kesesuaian dengan Sub-CPMK.</p>
            </div>
            <div class="table-card">
                <table class="minimal-table">
                    <colgroup>
                        <col style="width: 80px;">       <!-- Minggu -->
                        <col style="width: 20%;">        <!-- Sub-CPMK -->
                        <col style="width: 25%;">        <!-- Materi -->
                        <col style="width: 15%;">        <!-- Bahan Kajian -->
                        <col style="width: 20%;">        <!-- Metode -->
                        <col style="width: 100px;">      <!-- Waktu -->
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Minggu</th>
                            <th>Sub-CPMK</th>
                            <th>Materi Pembelajaran</th>
                            <th>Bahan Kajian</th>
                            <th>Metode</th>
                            <th style="text-align: right;">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="week-badge">1</span></td>
                            <td>Mahasiswa memahami konsep dasar Web</td>
                            <td class="materi-text">Pengenalan World Wide Web, HTTP, Browser</td>
                            <td>Konsep Dasar Internet</td>
                            <td><div class="metode-tag"><i class="fas fa-chalkboard-teacher"></i> Ceramah & Diskusi</div></td>
                            <td><div class="waktu-tag" style="justify-content: flex-end;"><i class="far fa-clock"></i> 150m</div></td>
                        </tr>
                        <tr>
                            <td><span class="week-badge">2</span></td>
                            <td>Mampu menyusun struktur dokumen HTML</td>
                            <td class="materi-text">Tag HTML, Atribut, Head, Body, List, Link</td>
                            <td>HTML5 Standards</td>
                            <td><div class="metode-tag"><i class="fas fa-laptop-code"></i> Praktikum Terbimbing</div></td>
                            <td><div class="waktu-tag" style="justify-content: flex-end;"><i class="far fa-clock"></i> 150m</div></td>
                        </tr>
                        <tr>
                            <td><span class="week-badge">3-4</span></td>
                            <td>Mampu mendesain layout dengan CSS</td>
                            <td class="materi-text">Selectors, Box Model, Flexbox, Grid</td>
                            <td>CSS3 Layouting</td>
                            <td><div class="metode-tag"><i class="fas fa-vial"></i> Demo & Latihan</div></td>
                            <td><div class="waktu-tag" style="justify-content: flex-end;"><i class="far fa-clock"></i> 300m</div></td>
                        </tr>
                        <tr>
                            <td><span class="week-badge">5</span></td>
                            <td>Memahami konsep Responsive Design</td>
                            <td class="materi-text">Media Queries, Viewport, Framework CSS</td>
                            <td>Responsive Web Design</td>
                            <td><div class="metode-tag"><i class="fas fa-project-diagram"></i> Project Based</div></td>
                            <td><div class="waktu-tag"><i class="far fa-clock"></i> 150m</div></td>
                        </tr>
                        <tr>
                            <td><span class="week-badge">6-7</span></td>
                            <td>Mampu membuat form dan validasi</td>
                            <td class="materi-text">Input types, Form attributes, Intro to JS</td>
                            <td>Interactivity</td>
                            <td><div class="metode-tag"><i class="fas fa-tools"></i> Praktikum</div></td>
                            <td><div class="waktu-tag"><i class="far fa-clock"></i> 300m</div></td>
                        </tr>
                        <tr class="uts-row">
                            <td colspan="6">
                                <div class="uts-badge">
                                    <i class="fas fa-star"></i> <span>Evaluasi Tengah Semester (UTS)</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="step-content" id="step4"><h3>Langkah 4: Metode & Pengalaman Belajar</h3><p>Tentukan metode pengajaran (Ceramah, Diskusi, Case Study, dll).</p></div>
        <div class="step-content" id="step5"><h3>Langkah 5: Penilaian & Bobot</h3><p>Tentukan kriteria penilaian dan rubrik.</p></div>
        <div class="step-content" id="step6"><h3>Langkah 6: Referensi & Lampiran</h3><p>Daftar pustaka utama dan pendukung.</p></div>
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

function updateSks(select) {
    const selectedOption = select.options[select.selectedIndex];
    const sks = selectedOption.getAttribute('data-sks') || 0;
    document.getElementById('sksInput').value = sks;
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
</script>

<?php include 'includes/footer.php'; ?>
