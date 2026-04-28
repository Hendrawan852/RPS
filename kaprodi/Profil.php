<?php
include 'includes/header.php';
include 'includes/sidebar.php';
require_once '../config/database.php';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<div class="page-header">
    <h2>Profil Kaprodi</h2>
    <p>Kelola data diri dan autentikasi akun Anda.</p>
</div>

<div class="profil-layout">
    <div class="card p-sticky">
        <div class="profil-main">
            <div class="avatar-edit">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['nama_lengkap']); ?>&background=0f172a&color=fff&size=128" alt="Avatar">
                <button class="edit-badge"><i class="fas fa-camera"></i></button>
            </div>
            <h3><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
            <p><?php echo $user['role']; ?> Program Studi</p>
            <div class="signature-box">
                <span>Tanda Tangan Digital</span>
                <div class="sig-placeholder">Belum diunggah</div>
                <button class="btn-sig">Unggah Tanda Tangan</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Edit Personal Information</div>
        <form class="profil-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>NIP / NIDN</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nip_nidn']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
            </div>
            <button class="btn btn-primary">Save Changes</button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid var(--border-color);">

        <div class="card-title">Keamanan & Password</div>
        <form class="profil-form">
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" class="form-control">
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
            <button class="btn btn-secondary">Update Password</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
