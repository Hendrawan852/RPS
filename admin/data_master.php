<?php
session_start();
require_once '../config/database.php';

// Handle Deletions
if (isset($_GET['delete'])) {
    $type = $_GET['type'];
    $id = $_GET['id'];
    try {
        if ($type === 'mk') {
            // Delete relations first
            $pdo->prepare("DELETE FROM mk_cpl WHERE mk_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM mk_cpmk WHERE mk_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM pengajuan_rps WHERE mk_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM mata_kuliah WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($type === 'cpl') {
            $pdo->prepare("DELETE FROM mk_cpl WHERE cpl_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM cpl WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($type === 'cpmk') {
            $pdo->prepare("DELETE FROM mk_cpmk WHERE cpmk_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM cpmk WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($type === 'dosen') {
            $stmt = $pdo->prepare("DELETE FROM dosen WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        $_SESSION['msg'] = "Data berhasil dihapus.";
        header("Location: data_master.php" . (isset($_GET['tab']) ? "?tab=" . $_GET['tab'] : ""));
        exit();
    } catch (PDOException $e) {
        $_SESSION['err'] = "Gagal menghapus: " . $e->getMessage();
        header("Location: data_master.php");
        exit();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';

// Handle CPL Submission (Add & Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_cpl'])) {
    $id = $_POST['cpl_id'] ?? null;
    $kode = $_POST['kode_cpl'];
    $deskripsi = $_POST['deskripsi_cpl'];
    try {
        // Check if kode_cpl already exists
        $check = $pdo->prepare("SELECT id FROM cpl WHERE kode_cpl = ? AND id != ?");
        $check->execute([$kode, $id ?: 0]);
        if ($check->fetch()) {
            throw new Exception("Kode CPL '$kode' sudah tersedia. Silakan gunakan kode lain.");
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE cpl SET kode_cpl = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$kode, $deskripsi, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cpl (kode_cpl, deskripsi) VALUES (?, ?)");
            $stmt->execute([$kode, $deskripsi]);
        }
        $_SESSION['msg'] = "CPL berhasil disimpan!";
        header("Location: data_master.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['err'] = $e->getMessage();
        header("Location: data_master.php");
        exit();
    }
}

// Handle CPMK Submission (Add & Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_cpmk'])) {
    $id = $_POST['cpmk_id'] ?? null;
    $kode = $_POST['kode_cpmk'];
    $deskripsi = $_POST['deskripsi_cpmk'];
    try {
        // Check if kode_cpmk already exists
        $check = $pdo->prepare("SELECT id FROM cpmk WHERE kode_cpmk = ? AND id != ?");
        $check->execute([$kode, $id ?: 0]);
        if ($check->fetch()) {
            throw new Exception("Kode CPMK '$kode' sudah tersedia. Silakan gunakan kode lain.");
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE cpmk SET kode_cpmk = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$kode, $deskripsi, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cpmk (kode_cpmk, deskripsi) VALUES (?, ?)");
            $stmt->execute([$kode, $deskripsi]);
        }
        $_SESSION['msg'] = "CPMK berhasil disimpan!";
        header("Location: data_master.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['err'] = $e->getMessage();
        header("Location: data_master.php");
        exit();
    }
}

// Handle Mata Kuliah Submission (Add & Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_mk'])) {
    $id = $_POST['mk_id'] ?? null;
    $kode = $_POST['kode_mk'];
    $nama = $_POST['nama_mk'];
    $sks = $_POST['sks'];
    $semester = $_POST['semester'];
    $tahun_ajaran = $_POST['tahun_ajaran'];
    $deskripsi = $_POST['deskripsi_mk'];
    $prodi_id = $_POST['prodi_id'] ?: null;
    
    $selected_cpl = $_POST['selected_cpl'] ?? [];
    $selected_cpmk = $_POST['selected_cpmk'] ?? [];

    try {
        $pdo->beginTransaction();
        
        // Check if kode_mk already exists
        $check = $pdo->prepare("SELECT id FROM mata_kuliah WHERE kode_mk = ? AND id != ?");
        $check->execute([$kode, $id ?: 0]);
        if ($check->fetch()) {
            throw new Exception("Kode Mata Kuliah '$kode' sudah tersedia. Silakan gunakan kode lain.");
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE mata_kuliah SET kode_mk = ?, nama_mk = ?, sks = ?, semester_default = ?, tahun_ajaran = ?, deskripsi = ?, prodi_id = ? WHERE id = ?");
            $stmt->execute([$kode, $nama, $sks, $semester, $tahun_ajaran, $deskripsi, $prodi_id, $id]);
            $mk_id = $id;
        } else {
            $stmt = $pdo->prepare("INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester_default, tahun_ajaran, deskripsi, prodi_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$kode, $nama, $sks, $semester, $tahun_ajaran, $deskripsi, $prodi_id]);
            $mk_id = $pdo->lastInsertId();
        }
        
        // Update CPL mappings
        $pdo->prepare("DELETE FROM mk_cpl WHERE mk_id = ?")->execute([$mk_id]);
        foreach ($selected_cpl as $cpl_id) {
            $pdo->prepare("INSERT INTO mk_cpl (mk_id, cpl_id) VALUES (?, ?)")->execute([$mk_id, $cpl_id]);
        }
        
        // Update CPMK mappings
        $pdo->prepare("DELETE FROM mk_cpmk WHERE mk_id = ?")->execute([$mk_id]);
        foreach ($selected_cpmk as $cpmk_id) {
            $pdo->prepare("INSERT INTO mk_cpmk (mk_id, cpmk_id) VALUES (?, ?)")->execute([$mk_id, $cpmk_id]);
        }
        
        $pdo->commit();
        $_SESSION['msg'] = "Mata Kuliah berhasil disimpan!";
        header("Location: data_master.php?tab=mk");
        exit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['err'] = $e->getMessage();
        header("Location: data_master.php?tab=mk");
        exit();
    }
}

// Handle Dosen Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_dosen'])) {
    $id = $_POST['dosen_id'] ?? null;
    $nip = $_POST['nip_dosen'];
    $nama = $_POST['nama_dosen'];
    $jabatan = $_POST['jabatan_dosen'];
    $prodi_id = $_POST['prodi_id'] ?: null;
    
    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE dosen SET nip = ?, nama = ?, jabatan = ?, prodi_id = ? WHERE id = ?");
            $stmt->execute([$nip, $nama, $jabatan, $prodi_id, $id]);
            $_SESSION['msg'] = "Data Dosen berhasil diperbarui!";
        } else {
            // Check if NIP already exists
            $check = $pdo->prepare("SELECT id FROM dosen WHERE nip = ?");
            $check->execute([$nip]);
            if ($check->fetch()) {
                throw new Exception("Dosen dengan NIP ini sudah terdaftar.");
            }
            
            $stmt = $pdo->prepare("INSERT INTO dosen (nip, nama, jabatan, prodi_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nip, $nama, $jabatan, $prodi_id]);
            $_SESSION['msg'] = "Dosen baru berhasil ditambahkan!";
        }
        header("Location: data_master.php?tab=dosen");
        exit();
    } catch (Exception $e) {
        $_SESSION['err'] = "Gagal menyimpan Dosen: " . $e->getMessage();
        header("Location: data_master.php?tab=dosen");
        exit();
    }
}





// Fetch Dropdown Data
$prodis = $pdo->query("SELECT * FROM prodi ORDER BY nama_prodi ASC")->fetchAll(PDO::FETCH_ASSOC);
$mk_list = $pdo->query("SELECT mk.*, p.nama_prodi FROM mata_kuliah mk LEFT JOIN prodi p ON mk.prodi_id = p.id ORDER BY mk.kode_mk ASC")->fetchAll(PDO::FETCH_ASSOC);
$cpl_list = $pdo->query("SELECT * FROM cpl ORDER BY kode_cpl ASC")->fetchAll(PDO::FETCH_ASSOC);
$cpmk_list = $pdo->query("SELECT * FROM cpmk ORDER BY kode_cpmk ASC")->fetchAll(PDO::FETCH_ASSOC);
$dosen_list = $pdo->query("SELECT d.*, p.nama_prodi FROM dosen d LEFT JOIN prodi p ON d.prodi_id = p.id ORDER BY d.nama ASC")->fetchAll(PDO::FETCH_ASSOC);

// Helper function to get current mappings (for checklists)
function getLinkedCPL($mk_id, $pdo) {
    $stmt = $pdo->prepare("SELECT cpl_id FROM mk_cpl WHERE mk_id = ?");
    $stmt->execute([$mk_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getLinkedCPMK($mk_id, $pdo) {
    $stmt = $pdo->prepare("SELECT cpmk_id FROM mk_cpmk WHERE mk_id = ?");
    $stmt->execute([$mk_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getLinkedCPLData($mk_id, $pdo) {
    $stmt = $pdo->prepare("SELECT c.kode_cpl, c.deskripsi FROM cpl c JOIN mk_cpl mc ON c.id = mc.cpl_id WHERE mc.mk_id = ?");
    $stmt->execute([$mk_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLinkedCPMKData($mk_id, $pdo) {
    $stmt = $pdo->prepare("SELECT c.kode_cpmk, c.deskripsi FROM cpmk c JOIN mk_cpmk mc ON c.id = mc.cpmk_id WHERE mc.mk_id = ?");
    $stmt->execute([$mk_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="page-header">
    <h2>Data Master</h2>
    <p>Kelola referensi data yang digunakan dalam penyusunan RPS.</p>
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

<div class="master-tabs">
    <button class="tab-btn active" data-tab="cpl">CPL & CPMK</button>
    <button class="tab-btn" data-tab="mk">Mata Kuliah</button>
    <button class="tab-btn" data-tab="dosen">Dosen</button>
</div>

<div class="tab-content active" id="cpl">
    <!-- CPL Section -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-title">
            <span><i class="fas fa-graduation-cap" style="margin-right: 10px; color: #10b981;"></i> Capaian Pembelajaran Lulusan (CPL)</span>
            <button class="btn btn-primary btn-sm" style="background: #10b981;" id="btnTambahCPL"><i class="fas fa-plus"></i> Tambah CPL</button>
        </div>
        <div class="table-responsive">
            <table class="custom-table" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="width: 120px;">Kode</th>
                        <th>Deskripsi Capaian Pembelajaran Lulusan</th>
                        <th style="width: 280px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cpl_list)): ?>
                        <tr><td colspan="3" style="text-align: center; padding: 30px; color: var(--text-muted);">Belum ada data CPL.</td></tr>
                    <?php else: ?>
                        <?php foreach ($cpl_list as $cpl): ?>
                        <tr>
                            <td><span class="mk-code" style="background: #ecfdf5; color: #065f46;"><?php echo htmlspecialchars($cpl['kode_cpl']); ?></span></td>
                            <td style="line-height: 1.6;"><?php echo htmlspecialchars($cpl['deskripsi']); ?></td>
                            <td style="text-align: right;">
                                <div class="table-actions">
                                    <button class="btn-icon text-info" title="Lihat Detail CPL" onclick='openModalInfoGeneric("CPL", <?php echo json_encode($cpl); ?>)'><i class="fas fa-eye"></i> Info</button>
                                    <button class="btn-icon text-primary" onclick='openModalCPL(<?php echo json_encode($cpl); ?>)'><i class="fas fa-pen-to-square"></i> Edit</button>
                                    <a href="?delete=1&type=cpl&id=<?php echo $cpl['id']; ?>" class="btn-icon text-danger" onclick="return confirm('Hapus CPL ini?')"><i class="fas fa-trash-can"></i> Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CPMK Section -->
    <div class="card">
        <div class="card-title">
            <span><i class="fas fa-award" style="margin-right: 10px; color: #3b82f6;"></i> Capaian Pembelajaran Mata Kuliah (CPMK)</span>
            <button class="btn btn-primary btn-sm" style="background: #3b82f6;" id="btnTambahCPMK"><i class="fas fa-plus"></i> Tambah CPMK</button>
        </div>
        <div class="table-responsive">
            <table class="custom-table" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="width: 120px;">Kode</th>
                        <th>Deskripsi Capaian Pembelajaran Mata Kuliah</th>
                        <th style="width: 280px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cpmk_list)): ?>
                        <tr><td colspan="3" style="text-align: center; padding: 30px; color: var(--text-muted);">Belum ada data CPMK.</td></tr>
                    <?php else: ?>
                        <?php foreach ($cpmk_list as $cpmk): ?>
                        <tr>
                            <td><span class="mk-code" style="background: #eff6ff; color: #1e40af;"><?php echo htmlspecialchars($cpmk['kode_cpmk']); ?></span></td>
                            <td style="line-height: 1.6;"><?php echo htmlspecialchars($cpmk['deskripsi']); ?></td>
                            <td style="text-align: right;">
                                <div class="table-actions">
                                    <button class="btn-icon text-info" title="Lihat Detail CPMK" onclick='openModalInfoGeneric("CPMK", <?php echo json_encode($cpmk); ?>)'><i class="fas fa-eye"></i> Info</button>
                                    <button class="btn-icon text-primary" onclick='openModalCPMK(<?php echo json_encode($cpmk); ?>)'><i class="fas fa-pen-to-square"></i> Edit</button>
                                    <a href="?delete=1&type=cpmk&id=<?php echo $cpmk['id']; ?>" class="btn-icon text-danger" onclick="return confirm('Hapus CPMK ini?')"><i class="fas fa-trash-can"></i> Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card tab-content" id="mk">
    <div class="card-title">
        <span><i class="fas fa-book" style="margin-right: 10px; color: var(--primary-color);"></i> Data Mata Kuliah</span>
        <button class="btn btn-primary btn-sm" onclick="openModalMK()"><i class="fas fa-plus"></i> Tambah MK</button>
    </div>
    <div class="table-responsive">
        <table class="custom-table" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 120px;">Kode MK</th>
                    <th style="width: 35%;">Nama Mata Kuliah</th>
                    <th style="width: 100px;">Bobot</th>
                    <th style="width: 120px;">Semester</th>
                    <th style="width: 150px;">Tahun Ajaran</th>
                    <th style="width: 280px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mk_list as $mk): ?>
                <tr>
                    <td><span class="mk-code"><?php echo htmlspecialchars($mk['kode_mk']); ?></span></td>
                    <td><strong><?php echo htmlspecialchars($mk['nama_mk']); ?></strong></td>
                    <td><span class="sks-badge"><?php echo htmlspecialchars($mk['sks']); ?> SKS</span></td>
                    <td><span class="semester-badge" translate="no">SEM <?php echo htmlspecialchars($mk['semester_default']); ?></span></td>
                    <td><span class="badge" style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars($mk['tahun_ajaran'] ?? '2024/2025'); ?></span></td>
                    <td style="text-align: right;">
                          <div class="table-actions">
                               <button class="btn-icon text-info" title="Lihat Info Lengkap" 
                                  data-nama="<?php echo htmlspecialchars($mk['nama_mk']); ?>"
                                  data-kode="<?php echo htmlspecialchars($mk['kode_mk']); ?>"
                                  data-sks="<?php echo $mk['sks']; ?>"
                                  data-semester="<?php echo $mk['semester_default']; ?>"
                                  data-tahun_ajaran="<?php echo htmlspecialchars($mk['tahun_ajaran'] ?? '2024/2025'); ?>"
                                  data-deskripsi="<?php echo htmlspecialchars($mk['deskripsi'] ?? 'Tidak ada deskripsi.'); ?>"
                                  data-cpl='<?php echo htmlspecialchars(json_encode(getLinkedCPLData($mk['id'], $pdo)), ENT_QUOTES); ?>'
                                  data-cpmk='<?php echo htmlspecialchars(json_encode(getLinkedCPMKData($mk['id'], $pdo)), ENT_QUOTES); ?>'
                                  onclick="openModalInfoMK(this)">
                                  <i class="fas fa-eye"></i> Info
                               </button>

                               <button class="btn-icon text-primary" title="Edit Data" 
                                  onclick='openModalMK(<?php 
                                      $mk_data = $mk;
                                      $mk_data["cpl_ids"] = getLinkedCPL($mk["id"], $pdo);
                                      $mk_data["cpmk_ids"] = getLinkedCPMK($mk["id"], $pdo);
                                      echo htmlspecialchars(json_encode($mk_data)); 
                                  ?>)'>
                                  <i class="fas fa-pen-to-square"></i> Edit
                               </button>

                               <a href="?delete=1&type=mk&id=<?php echo $mk['id']; ?>" class="btn-icon text-danger" title="Hapus" onclick="return confirm('Hapus mata kuliah ini?')">
                                  <i class="fas fa-trash-can"></i> Hapus
                               </a>
                          </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card tab-content" id="dosen">
    <div class="card-title">
        <span><i class="fas fa-user-tie" style="margin-right: 10px; color: #f59e0b;"></i> Data Dosen Master</span>
        <button class="btn btn-primary btn-sm" style="background: #f59e0b;" onclick="openModalDosen()"><i class="fas fa-plus"></i> Tambah Dosen</button>
    </div>
    <div class="table-responsive">
        <table class="custom-table" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 180px;">NIP / NIDN</th>
                    <th>Nama Dosen</th>
                    <th>Jabatan</th>
                    <th style="width: 280px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dosen_list)): ?>
                    <tr><td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">Belum ada data Dosen.</td></tr>
                <?php else: ?>
                    <?php foreach ($dosen_list as $dosen): ?>
                    <tr>
                        <td><span class="mk-code" style="background: #fffbeb; color: #92400e;"><?php echo htmlspecialchars($dosen['nip']); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($dosen['nama']); ?></strong></td>
                        <td><span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars($dosen['jabatan'] ?: 'Dosen Tetap'); ?></span></td>
                        <td style="text-align: right;">
                            <div class="table-actions">
                                <button class="btn-icon text-info" title="Detail Dosen" onclick='openModalInfoGeneric("Dosen", <?php echo json_encode($dosen); ?>)'><i class="fas fa-eye"></i> Detail</button>
                                <button class="btn-icon text-primary" onclick='openModalDosen(<?php echo json_encode($dosen); ?>)'><i class="fas fa-pen-to-square"></i> Edit</button>
                                <a href="?delete=1&type=dosen&id=<?php echo $dosen['id']; ?>&tab=dosen" class="btn-icon text-danger" onclick="return confirm('Hapus Dosen ini?')"><i class="fas fa-trash-can"></i> Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Modal Tambah/Edit Dosen -->
<div id="modalDosen" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="dosen_modal_title">Tambah Dosen</h3>
            <span class="close" onclick="closeModal('modalDosen')">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="dosen_id" id="dosen_id">
            <div class="form-group">
                <label>NIP / NIDN</label>
                <input type="text" name="nip_dosen" id="dosen_nip" class="form-control" placeholder="Masukkan NIP atau NIDN" required>
            </div>
            <div class="form-group">
                <label>Nama Lengkap Dosen</label>
                <input type="text" name="nama_dosen" id="dosen_nama" class="form-control" placeholder="Masukkan Nama Lengkap Beserta Gelar" required>
            </div>
            <div class="form-group">
                <label>Jabatan / Fungsional</label>
                <input type="text" name="jabatan_dosen" id="dosen_jabatan" class="form-control" placeholder="Contoh: Lektor Kepala / Asisten Ahli">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDosen')">Batal</button>
                <button type="submit" name="simpan_dosen" class="btn btn-primary" style="background: #f59e0b;">Simpan Dosen</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Tambah CPL -->
<div id="modalCPL" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah CPL Baru</h3>
            <span class="close" onclick="closeModal('modalCPL')">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="cpl_id" id="cpl_id">
            <div class="form-group">
                <label>Kode CPL</label>
                <input type="text" name="kode_cpl" id="cpl_kode" class="form-control" placeholder="Contoh: CPL-01" required>
            </div>
            <div class="form-group">
                <label>Deskripsi Capaian Pembelajaran Lulusan</label>
                <textarea name="deskripsi_cpl" id="cpl_deskripsi" class="form-control" rows="4" placeholder="Masukkan deskripsi lengkap CPL..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCPL')">Batal</button>
                <button type="submit" name="simpan_cpl" class="btn btn-primary" style="background: #10b981;">Simpan CPL</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Tambah CPMK -->
<div id="modalCPMK" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah CPMK Baru</h3>
            <span class="close" onclick="closeModal('modalCPMK')">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="cpmk_id" id="cpmk_id">
            <div class="form-group">
                <label>Kode CPMK</label>
                <input type="text" name="kode_cpmk" id="cpmk_kode" class="form-control" placeholder="Contoh: CPMK-01" required>
            </div>
            <div class="form-group">
                <label>Deskripsi Capaian Pembelajaran Mata Kuliah</label>
                <textarea name="deskripsi_cpmk" id="cpmk_deskripsi" class="form-control" rows="4" placeholder="Masukkan deskripsi lengkap CPMK..." required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCPMK')">Batal</button>
                <button type="submit" name="simpan_cpmk" class="btn btn-primary" style="background: #3b82f6;">Simpan CPMK</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah/Edit MK -->
<div id="modalMK" class="modal">
    <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 id="mk_modal_title">Tambah Mata Kuliah</h3>
            <span class="close" onclick="closeModal('modalMK')">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="mk_id" id="mk_id">
            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Kode MK</label>
                    <input type="text" name="kode_mk" id="mk_kode" class="form-control" placeholder="Contoh: TIF101" required>
                </div>
                <div class="form-group">
                    <label>Nama Mata Kuliah</label>
                    <input type="text" name="nama_mk" id="mk_nama" class="form-control" placeholder="Nama Lengkap MK" required>
                </div>
                <div class="form-group">
                    <label>SKS</label>
                    <input type="number" name="sks" id="mk_sks" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <input type="number" name="semester" id="mk_semester" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tahun Ajaran</label>
                    <select name="tahun_ajaran" id="mk_tahun_ajaran" class="form-control" required>
                        <option value="2023/2024">2023/2024</option>
                        <option value="2024/2025" selected>2024/2025</option>
                        <option value="2025/2026">2025/2026</option>
                        <option value="2026/2027">2026/2027</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top: 15px;">
                <label>Deskripsi Singkat</label>
                <textarea name="deskripsi_mk" id="mk_deskripsi" class="form-control" rows="3" placeholder="Deskripsi mata kuliah..."></textarea>
            </div>

            <!-- Mapping Selection Directly in MK Form -->
            <div class="mapping-selection-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                <div class="mapping-section">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #10b981; margin-bottom: 12px;">
                        <i class="fas fa-graduation-cap"></i> Pilih CPL Terkait
                    </label>
                    <div class="checklist-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; max-height: 200px; overflow-y: auto;">
                        <?php if (empty($cpl_list)): ?>
                            <p style="font-size: 12px; color: #94a3b8; text-align: center;">Belum ada data CPL.</p>
                        <?php else: ?>
                            <?php foreach ($cpl_list as $cpl): ?>
                            <div class="checklist-item" style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 10px; font-size: 13px;">
                                <input type="checkbox" name="selected_cpl[]" value="<?php echo $cpl['id']; ?>" id="mk_cpl_<?php echo $cpl['id']; ?>" style="margin-top: 3px;">
                                <label for="mk_cpl_<?php echo $cpl['id']; ?>" style="cursor: pointer;">
                                    <span style="font-weight: 700; color: #065f46;"><?php echo htmlspecialchars($cpl['kode_cpl']); ?></span>: 
                                    <span style="color: #64748b;"><?php echo htmlspecialchars($cpl['deskripsi']); ?></span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mapping-section">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #3b82f6; margin-bottom: 12px;">
                        <i class="fas fa-award"></i> Pilih CPMK Terkait
                    </label>
                    <div class="checklist-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; max-height: 200px; overflow-y: auto;">
                        <?php if (empty($cpmk_list)): ?>
                            <p style="font-size: 12px; color: #94a3b8; text-align: center;">Belum ada data CPMK.</p>
                        <?php else: ?>
                            <?php foreach ($cpmk_list as $cpmk): ?>
                            <div class="checklist-item" style="margin-bottom: 8px; display: flex; align-items: flex-start; gap: 10px; font-size: 13px;">
                                <input type="checkbox" name="selected_cpmk[]" value="<?php echo $cpmk['id']; ?>" id="mk_cpmk_<?php echo $cpmk['id']; ?>" style="margin-top: 3px;">
                                <label for="mk_cpmk_<?php echo $cpmk['id']; ?>" style="cursor: pointer;">
                                    <span style="font-weight: 700; color: #1e40af;"><?php echo htmlspecialchars($cpmk['kode_cpmk']); ?></span>: 
                                    <span style="color: #64748b;"><?php echo htmlspecialchars($cpmk['deskripsi']); ?></span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="margin-top: 25px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalMK')">Batal</button>
                <button type="submit" name="simpan_mk" class="btn btn-primary">Simpan Mata Kuliah</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Detail MK -->
<div id="modalDetailMK" class="modal">
    <div class="modal-content" style="max-width: 650px; padding: 0; overflow: hidden;">
        <!-- Banner Header -->
        <div style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0ea5e9 100%); padding: 28px 30px; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -30px; right: 60px; width: 80px; height: 80px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; position: relative;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <div style="background: rgba(255,255,255,0.2); padding: 8px; border-radius: 10px;">
                            <i class="fas fa-book-open" style="color: white; font-size: 18px;"></i>
                        </div>
                        <span id="info_kode_badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 20px; font-family: monospace; letter-spacing: 0.5px;"></span>
                    </div>
                    <h2 id="info_nama" style="color: white; font-size: 20px; font-weight: 700; margin: 0; line-height: 1.3;"></h2>
                </div>
                <span onclick="closeModal('modalDetailMK')" style="color: rgba(255,255,255,0.7); font-size: 22px; cursor: pointer; line-height: 1; padding: 4px 8px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='transparent'">&times;</span>
            </div>
            <!-- Stat Cards -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-top: 20px;">
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 12px 16px;">
                    <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Bobot SKS</div>
                    <div style="color: white; font-size: 22px; font-weight: 700; display: flex; align-items: baseline; gap: 4px;">
                        <span id="info_sks"></span>
                        <span style="font-size: 13px; font-weight: 500; opacity: 0.8;">SKS</span>
                    </div>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 12px 16px;">
                    <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Semester</div>
                    <div style="color: white; font-size: 22px; font-weight: 700; display: flex; align-items: baseline; gap: 4px;">
                        <span id="info_semester"></span>
                        <span style="font-size: 13px; font-weight: 500; opacity: 0.8;"></span>
                    </div>
                </div>
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border-radius: 12px; padding: 12px 16px;">
                    <div style="color: rgba(255,255,255,0.7); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Tahun Ajaran</div>
                    <div style="color: white; font-size: 18px; font-weight: 700; display: flex; align-items: baseline; gap: 4px; padding-top: 4px;">
                        <span id="info_tahun_ajaran"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div style="padding: 24px 28px; max-height: 55vh; overflow-y: auto;">
            <!-- Deskripsi -->
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                    <i class="fas fa-align-left" style="color: #6366f1; font-size: 14px;"></i>
                    <span style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px;">Deskripsi</span>
                </div>
                <p id="info_deskripsi" style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 0; background: #f8fafc; border-left: 3px solid #6366f1; padding: 12px 16px; border-radius: 0 8px 8px 0;"></p>
            </div>

            <!-- CPL -->
            <div id="section_cpl" style="margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <i class="fas fa-graduation-cap" style="color: #10b981; font-size: 14px;"></i>
                    <span style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px;">CPL Terkait</span>
                    <span id="info_cpl_count" style="background: #d1fae5; color: #065f46; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px;"></span>
                </div>
                <div id="info_cpl" style="display: flex; flex-direction: column; gap: 8px;"></div>
            </div>

            <!-- CPMK -->
            <div id="section_cpmk">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <i class="fas fa-award" style="color: #3b82f6; font-size: 14px;"></i>
                    <span style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px;">CPMK Terkait</span>
                    <span id="info_cpmk_count" style="background: #dbeafe; color: #1e40af; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px;"></span>
                </div>
                <div id="info_cpmk" style="display: flex; flex-direction: column; gap: 8px;"></div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 16px 28px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeModal('modalDetailMK')" style="background: #f1f5f9; color: #374151; border: none; padding: 10px 24px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                <i class="fas fa-times" style="margin-right: 6px;"></i>Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Info Generic -->
<div id="modalInfoGeneric" class="modal">
    <div class="modal-content" style="max-width: 500px; padding: 0; overflow: hidden; border-radius: 20px;">
        <!-- Dynamic Header Banner -->
        <div id="generic_header_banner" style="padding: 30px; position: relative; overflow: hidden; color: white;">
            <div style="position: absolute; top: -15px; right: -15px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; position: relative; z-index: 1;">
                <div>
                    <div id="generic_type_badge" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: inline-block;"></div>
                    <h3 id="generic_info_title" style="margin: 0; font-size: 22px; font-weight: 800;"></h3>
                </div>
                <span class="close" onclick="closeModal('modalInfoGeneric')" style="color: rgba(255,255,255,0.8); font-size: 24px; cursor: pointer;">&times;</span>
            </div>
        </div>
        
        <div class="info-body" id="generic_info_body" style="padding: 25px; background: white;">
            <!-- Dynamic content will be injected here -->
        </div>
        
        <div class="modal-footer" style="padding: 15px 25px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalInfoGeneric')" style="background: #e2e8f0; color: #475569; border: none; padding: 8px 20px; border-radius: 10px; font-weight: 600; font-size: 14px;">Tutup</button>
        </div>
    </div>
</div>

<script>
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            tab.classList.add('active');
            document.getElementById(target).classList.add('active');
        });
    });

    // Auto-activate tab from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab) {
        const tabBtn = document.querySelector(`.tab-btn[data-tab="${activeTab}"]`);
        if (tabBtn) tabBtn.click();
    }

    // Modal Logic
    function openModal(id) {
        document.getElementById(id).style.display = "flex";
    }

    function closeModal(id) {
        document.getElementById(id).style.display = "none";
    }

    function openModalInfoMK(btn) {
        const data = btn.dataset;

        // Header
        document.getElementById('info_nama').innerText = data.nama;
        document.getElementById('info_kode_badge').innerText = data.kode;
        document.getElementById('info_sks').innerText = data.sks;
        document.getElementById('info_semester').innerText = 'Semester ' + data.semester;
        if (document.getElementById('info_tahun_ajaran')) {
            document.getElementById('info_tahun_ajaran').innerText = data.tahun_ajaran;
        }
        document.getElementById('info_deskripsi').innerText = data.deskripsi || 'Tidak ada deskripsi.';

        // Populate CPL
        const cplContainer = document.getElementById('info_cpl');
        const cplCount = document.getElementById('info_cpl_count');
        cplContainer.innerHTML = '';
        try {
            const cpls = JSON.parse(data.cpl);
            cplCount.innerText = cpls.length + ' item';
            if (cpls.length > 0) {
                cpls.forEach((cpl, i) => {
                    cplContainer.innerHTML += `
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; display: flex; gap: 12px; align-items: flex-start;">
                            <span style="background: #10b981; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; white-space: nowrap; margin-top: 1px;">${cpl.kode_cpl}</span>
                            <span style="color: #374151; font-size: 13px; line-height: 1.5;">${cpl.deskripsi}</span>
                        </div>`;
                });
            } else {
                cplCount.innerText = '0 item';
                cplContainer.innerHTML = '<div style="color: #94a3b8; font-size: 13px; font-style: italic; padding: 10px 0;">Belum ada CPL yang dipetakan.</div>';
            }
        } catch(e) {
            cplCount.innerText = '-';
            cplContainer.innerHTML = '<div style="color: #ef4444; font-size: 13px;">Gagal memuat data CPL.</div>';
        }

        // Populate CPMK
        const cpmkContainer = document.getElementById('info_cpmk');
        const cpmkCount = document.getElementById('info_cpmk_count');
        cpmkContainer.innerHTML = '';
        try {
            const cpmks = JSON.parse(data.cpmk);
            cpmkCount.innerText = cpmks.length + ' item';
            if (cpmks.length > 0) {
                cpmks.forEach((cpmk, i) => {
                    cpmkContainer.innerHTML += `
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 12px 14px; display: flex; gap: 12px; align-items: flex-start;">
                            <span style="background: #3b82f6; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; white-space: nowrap; margin-top: 1px;">${cpmk.kode_cpmk}</span>
                            <span style="color: #374151; font-size: 13px; line-height: 1.5;">${cpmk.deskripsi}</span>
                        </div>`;
                });
            } else {
                cpmkCount.innerText = '0 item';
                cpmkContainer.innerHTML = '<div style="color: #94a3b8; font-size: 13px; font-style: italic; padding: 10px 0;">Belum ada CPMK yang dipetakan.</div>';
            }
        } catch(e) {
            cpmkCount.innerText = '-';
            cpmkContainer.innerHTML = '<div style="color: #ef4444; font-size: 13px;">Gagal memuat data CPMK.</div>';
        }

        openModal('modalDetailMK');
    }


    function openModalMK(data = null) {
        // Reset all checkboxes first
        document.querySelectorAll('#modalMK input[type="checkbox"]').forEach(cb => cb.checked = false);

        if (data) {
            document.getElementById('mk_modal_title').innerText = "Edit Mata Kuliah";
            document.getElementById('mk_id').value = data.id;
            document.getElementById('mk_kode').value = data.kode_mk;
            document.getElementById('mk_nama').value = data.nama_mk;
            document.getElementById('mk_sks').value = data.sks;
            document.getElementById('mk_semester').value = data.semester_default;
            document.getElementById('mk_tahun_ajaran').value = data.tahun_ajaran || '2024/2025';
            document.getElementById('mk_deskripsi').value = data.deskripsi || '';

            // Populate CPL checkboxes
            if (data.cpl_ids) {
                data.cpl_ids.forEach(id => {
                    const cb = document.getElementById(`mk_cpl_${id}`);
                    if (cb) cb.checked = true;
                });
            }

            // Populate CPMK checkboxes
            if (data.cpmk_ids) {
                data.cpmk_ids.forEach(id => {
                    const cb = document.getElementById(`mk_cpmk_${id}`);
                    if (cb) cb.checked = true;
                });
            }
        } else {
            document.getElementById('mk_modal_title').innerText = "Tambah Mata Kuliah";
            document.getElementById('mk_id').value = "";
            document.getElementById('mk_kode').value = "";
            document.getElementById('mk_nama').value = "";
            document.getElementById('mk_sks').value = "";
            document.getElementById('mk_semester').value = "";
            document.getElementById('mk_tahun_ajaran').value = "2024/2025";
            document.getElementById('mk_deskripsi').value = "";
        }
        openModal('modalMK');
    }

    function openModalCPL(data = null) {
        if (data) {
            document.getElementById('cpl_id').value = data.id;
            document.getElementById('cpl_kode').value = data.kode_cpl;
            document.getElementById('cpl_deskripsi').value = data.deskripsi;
        } else {
            document.getElementById('cpl_id').value = "";
            document.getElementById('cpl_kode').value = "";
            document.getElementById('cpl_deskripsi').value = "";
        }
        openModal('modalCPL');
    }

    function openModalCPMK(data = null) {
        if (data) {
            document.getElementById('cpmk_id').value = data.id;
            document.getElementById('cpmk_kode').value = data.kode_cpmk;
            document.getElementById('cpmk_deskripsi').value = data.deskripsi;
        } else {
            document.getElementById('cpmk_id').value = "";
            document.getElementById('cpmk_kode').value = "";
            document.getElementById('cpmk_deskripsi').value = "";
        }
        openModal('modalCPMK');
    }

    function openModalDosen(data = null) {
        if (data) {
            document.getElementById('dosen_modal_title').innerText = "Edit Dosen";
            document.getElementById('dosen_id').value = data.id;
            document.getElementById('dosen_nip').value = data.nip;
            document.getElementById('dosen_nama').value = data.nama;
            document.getElementById('dosen_jabatan').value = data.jabatan || "";
        } else {
            document.getElementById('dosen_modal_title').innerText = "Tambah Dosen";
            document.getElementById('dosen_id').value = "";
            document.getElementById('dosen_nip').value = "";
            document.getElementById('dosen_nama').value = "";
            document.getElementById('dosen_jabatan').value = "";
        }
        openModal('modalDosen');
    }


    function openModalInfoGeneric(type, data) {
        const title = document.getElementById('generic_info_title');
        const body = document.getElementById('generic_info_body');
        const banner = document.getElementById('generic_header_banner');
        const badge = document.getElementById('generic_type_badge');
        
        badge.innerText = type;
        body.innerHTML = '';

        if (type === 'CPL') {
            banner.style.background = 'linear-gradient(135deg, #059669 0%, #10b981 100%)';
            title.innerText = data.kode_cpl;
            body.innerHTML = `
                <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; border-radius: 0 10px 10px 0; margin-bottom: 20px;">
                    <div style="font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase; margin-bottom: 5px;">Deskripsi Capaian</div>
                    <div style="color: #374151; font-size: 14px; line-height: 1.7;">${data.deskripsi}</div>
                </div>
            `;
        } else if (type === 'CPMK') {
            banner.style.background = 'linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%)';
            title.innerText = data.kode_cpmk;
            body.innerHTML = `
                <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; border-radius: 0 10px 10px 0; margin-bottom: 20px;">
                    <div style="font-size: 11px; font-weight: 700; color: #1e40af; text-transform: uppercase; margin-bottom: 5px;">Deskripsi Capaian</div>
                    <div style="color: #374151; font-size: 14px; line-height: 1.7;">${data.deskripsi}</div>
                </div>
            `;
        } else if (type === 'Dosen') {
            banner.style.background = 'linear-gradient(135deg, #4338ca 0%, #6366f1 100%)';
            title.innerText = data.nama;
            body.innerHTML = `
                <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 5px;">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(data.nama)}&size=100&background=6366f1&color=fff" style="border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                    <div>
                        <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;">NIP / NIDN</div>
                        <div style="font-size: 18px; font-weight: 700; color: #1f2937;">${data.nip}</div>
                        <div style="display: inline-block; background: #e0e7ff; color: #4338ca; font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 20px; margin-top: 8px;">${data.jabatan || 'Dosen Pengampu'}</div>
                    </div>
                </div>
            `;
        }
        
        openModal('modalInfoGeneric');
    }

    // Real-time Kode MK Validation
    const mkKodeInput = document.getElementById('mk_kode');
    if (mkKodeInput) {
        mkKodeInput.addEventListener('change', async function() {
            const kode = this.value;
            const id = document.getElementById('mk_id').value;
            if (!kode) return;

            try {
                const response = await fetch(`api_check_kode.php?kode=${encodeURIComponent(kode)}&id=${id}`);
                const result = await response.json();
                if (!result.available) {
                    alert(`Perhatian! Kode Mata Kuliah '${kode}' sudah tersedia di database. Silakan gunakan kode lain.`);
                    this.value = '';
                    this.focus();
                }
            } catch (e) {
                console.error("Gagal memeriksa kode:", e);
            }
        });
    }

    // Validation for CPL and CPMK
    const cplKodeInput = document.getElementById('cpl_kode');
    if (cplKodeInput) {
        cplKodeInput.addEventListener('change', async function() {
            const kode = this.value;
            const id = document.getElementById('cpl_id').value;
            if (!kode) return;
            try {
                const response = await fetch(`api_check_kode.php?type=cpl&kode=${encodeURIComponent(kode)}&id=${id}`);
                const result = await response.json();
                if (!result.available) {
                    alert(`Perhatian! Kode CPL '${kode}' sudah tersedia. Silakan gunakan kode lain.`);
                    this.value = '';
                    this.focus();
                }
            } catch (e) {}
        });
    }

    const cpmkKodeInput = document.getElementById('cpmk_kode');
    if (cpmkKodeInput) {
        cpmkKodeInput.addEventListener('change', async function() {
            const kode = this.value;
            const id = document.getElementById('cpmk_id').value;
            if (!kode) return;
            try {
                const response = await fetch(`api_check_kode.php?type=cpmk&kode=${encodeURIComponent(kode)}&id=${id}`);
                const result = await response.json();
                if (!result.available) {
                    alert(`Perhatian! Kode CPMK '${kode}' sudah tersedia. Silakan gunakan kode lain.`);
                    this.value = '';
                    this.focus();
                }
            } catch (e) {}
        });
    }

    const btnCPL = document.getElementById('btnTambahCPL');
    const btnCPMK = document.getElementById('btnTambahCPMK');

    if(btnCPL) btnCPL.onclick = () => openModalCPL();
    if(btnCPMK) btnCPMK.onclick = () => openModalCPMK();

    // Close when clicking outside
    window.onclick = (event) => {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }
</script>


<?php include 'includes/footer.php'; ?>
