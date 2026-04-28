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
    <h2>Profil Saya</h2>
    <p>Kelola informasi akun dan kata sandi Anda.</p>
</div>

<div class="profil-container">
    <div class="card profil-card">
        <div class="profil-header">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['nama_lengkap']); ?>&background=4f46e5&color=fff&size=128" alt="Avatar">
            <h3><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
            <span class="badge badge-admin"><?php echo $user['role']; ?></span>
        </div>
        <div class="profil-body">
            <div class="info-item">
                <label>NIP / NIDN</label>
                <p><?php echo htmlspecialchars($user['nip_nidn']); ?></p>
            </div>
            <div class="info-item">
                <label>Email</label>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="info-item">
                <label>Nomor HP</label>
                <p><?php echo htmlspecialchars($user['no_hp']); ?></p>
            </div>
        </div>
    </div>

    <div class="card form-card">
        <div class="card-title">Update Profil</div>
        <form>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <div class="form-group">
                <label>Nomor HP</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['no_hp']); ?>">
            </div>
            <button type="button" class="btn btn-primary">Simpan Perubahan</button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid var(--border-color);">

        <div class="card-title">Ganti Password</div>
        <form>
            <div class="form-group">
                <label>Password Lama</label>
                <input type="password" class="form-control" placeholder="Masukkan password saat ini">
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" class="form-control" placeholder="Minimal 8 karakter">
            </div>
            <button type="button" class="btn btn-secondary">Update Password</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
