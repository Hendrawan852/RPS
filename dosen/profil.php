<?php
include 'includes/header.php';
include 'includes/sidebar.php';
require_once '../config/database.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="page-header">
    <h2>Profil Dosen</h2>
    <p>Kelola data personal dan pengaturan keamanan akun Anda.</p>
</div>

<div class="profil-grid">
    <div class="card side-profile">
        <div class="avatar-section">
            <div class="avatar-large"><?php echo strtoupper(substr($user['nama_lengkap'], 0, 2)); ?></div>
            <h3><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
            <p>NIP: <?php echo htmlspecialchars($user['nip_nidn']); ?></p>
        </div>
        <div class="signature-section">
            <label>Tanda Tangan Digital</label>
            <div class="signature-preview">
                <i class="fas fa-file-signature fa-3x"></i>
                <p>Belum ada tanda tangan</p>
            </div>
            <button class="btn btn-outline-primary btn-block">Unggah Tanda Tangan</button>
        </div>
    </div>

    <div class="form-section">
        <div class="card">
            <div class="card-title">Informasi Pribadi</div>
            <form class="p-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">
                    </div>
                    <div class="form-group">
                        <label>NIP / NIDN</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nip_nidn']); ?>" disabled>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>No. HP / WhatsApp</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['no_hp']); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>

        <div class="card">
            <div class="card-title">Ganti Password</div>
            <form class="p-form">
                <div class="form-group">
                    <label>Password Saat Ini</label>
                    <input type="password" class="form-control" placeholder="******">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary">Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
