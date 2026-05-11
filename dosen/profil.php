<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch from central dosen table using NIP
$stmt = $pdo->prepare("SELECT * FROM dosen WHERE nip = ?");
$stmt->execute([$user['nip_nidn']]);
$dosen_profile = $stmt->fetch();

// Merge for display
if ($dosen_profile) {
    $user['nama_lengkap'] = $dosen_profile['nama'];
    $user['jabatan'] = $dosen_profile['jabatan'];
}

// Handle Update Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $jabatan = $_POST['jabatan'];
    
    try {
        $pdo->beginTransaction();
        
        // Update users table (login account)
        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, no_hp = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $no_hp, $user_id]);
        
        // Update dosen table (master profile)
        $stmt = $pdo->prepare("UPDATE dosen SET nama = ?, jabatan = ? WHERE nip = ?");
        $stmt->execute([$nama, $jabatan, $user['nip_nidn']]);
        
        $pdo->commit();
        $_SESSION['nama_lengkap'] = $nama; // Update session
        header("Location: profil.php?msg=success");
        exit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = "Gagal mengupdate profil.";
    }
}
?>

<style>
    .profil-grid { display: grid; grid-template-columns: 320px 1fr; gap: 30px; }
    .card-modern { background: white; border-radius: 20px; padding: 30px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    
    .avatar-section { text-align: center; padding-bottom: 25px; border-bottom: 1px solid #f1f5f9; margin-bottom: 25px; }
    .avatar-large { 
        width: 100px; height: 100px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); 
        color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; 
        font-size: 36px; font-weight: 800; margin: 0 auto 15px; 
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    }
    
    .signature-section { text-align: center; }
    .signature-preview { 
        background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 15px; 
        padding: 20px; margin: 15px 0; color: #94a3b8;
    }
    
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; }
    .form-control { 
        width: 100%; padding: 12px 15px; border-radius: 10px; border: 1.5px solid #e2e8f0; 
        font-size: 14px; transition: 0.2s; background: #fff;
    }
    .form-control:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    .form-control:disabled { background: #f1f5f9; cursor: not-allowed; }
    
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    
    .btn-save { 
        background: #1e293b; color: white; border: none; padding: 12px 25px; 
        border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s;
        display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-save:hover { background: #0f172a; transform: translateY(-2px); }
</style>

<div class="page-header" style="margin-bottom: 30px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #1e293b;">Profil & Pengaturan Akun</h2>
    <p style="color: #64748b;">Kelola informasi profil akademik dan keamanan akses sistem Anda.</p>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
    <div style="background: #ecfdf5; color: #065f46; padding: 15px 25px; border-radius: 15px; margin-bottom: 25px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-check-circle"></i> <span>Profil Anda telah berhasil diperbarui!</span>
    </div>
<?php endif; ?>

<div class="profil-grid">
    <div style="display: flex; flex-direction: column; gap: 25px;">
        <div class="card-modern">
            <div class="avatar-section" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 25px; margin-bottom: 25px;">
                <div class="avatar-large"><?php echo strtoupper(substr($user['nama_lengkap'], 0, 2)); ?></div>
                <h3 style="margin: 15px 0 0; font-size: 18px; color: #1e293b;"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
                <p style="margin: 5px 0 0; color: #64748b; font-size: 13px;">NIP: <?php echo htmlspecialchars($user['nip_nidn']); ?></p>
                <?php if ($user['jabatan']): ?>
                    <span style="background: #eff6ff; color: #3b82f6; padding: 5px 12px; border-radius: 50px; font-size: 11px; font-weight: 700; margin-top: 12px; display: inline-block; border: 1px solid #dbeafe;">
                        <i class="fas fa-award" style="margin-right: 5px;"></i> <?php echo htmlspecialchars($user['jabatan']); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="signature-section">
                <label style="font-size: 13px; font-weight: 700; color: #475569;">Tanda Tangan Digital</label>
                <div class="signature-preview">
                    <i class="fas fa-file-signature fa-3x" style="opacity: 0.3; margin-bottom: 10px; display: block;"></i>
                    <span style="font-size: 12px;">Belum Terunggah</span>
                </div>
                <button class="form-control" style="border-style: dashed; cursor: pointer; font-weight: 600; color: #3b82f6;">Unggah Baru</button>
            </div>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 25px;">
        <div class="card-modern">
            <h4 style="margin: 0 0 25px; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-id-card" style="color: #3b82f6;"></i> Informasi Personal
            </h4>
            <form action="profil.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap (Gelar)</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>NIP / NIDN (ID Pegawai)</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nip_nidn']); ?>" disabled>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email Institusi</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Nomor WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($user['no_hp']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Jabatan Fungsional Terakhir</label>
                    <input type="text" name="jabatan" class="form-control" value="<?php echo htmlspecialchars($user['jabatan']); ?>" placeholder="Contoh: Lektor / Asisten Ahli">
                </div>
                <div style="padding-top: 10px; border-top: 1px solid #f1f5f9;">
                    <button type="submit" name="update_profile" class="btn-save">
                        <i class="fas fa-save"></i> Perbarui Profil
                    </button>
                </div>
            </form>
        </div>

        <div class="card-modern">
            <h4 style="margin: 0 0 25px; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-lock" style="color: #f59e0b;"></i> Keamanan Akun
            </h4>
            <form action="profil.php" method="POST">
                <div class="form-group">
                    <label>Password Saat Ini</label>
                    <input type="password" name="old_password" class="form-control" placeholder="Masukkan password lama">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>
                </div>
                <div style="padding-top: 10px; border-top: 1px solid #f1f5f9;">
                    <button type="submit" name="update_password" class="btn-save" style="background: #64748b;">
                        <i class="fas fa-key"></i> Ganti Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
