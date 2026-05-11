<?php
session_start();
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? ''; // NIP or Email
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = "NIP/Email dan Password harus diisi.";
    } else {
        // Check user by NIP or Email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nip_nidn = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['prodi_id'] = $user['prodi_id'];
            $_SESSION['jabatan'] = $user['jabatan'];

            // Redirect based on role
            if ($user['role'] === 'Admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] === 'Kaprodi') {
                header("Location: ../kaprodi/dashboard.php");
            } else {
                header("Location: ../dosen/dashboard.php");
            }
            exit();
        } else {
            $error = "NIP/Email atau Password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RPS System</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <img src="../logo/logo-unsri.webp" alt="Logo Unsri" style="width: 80px; height: auto; margin-bottom: 20px;">
            <h1>Login RPS System</h1>
            <p>Silakan masuk menggunakan akun Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="identifier">NIP / Email</label>
                <input type="text" name="identifier" id="identifier" class="form-control" placeholder="Masukkan NIP atau Email" required value="<?php echo htmlspecialchars($identifier ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password" required>
            </div>

            <button type="submit" class="btn-auth">Masuk</button>
        </form>

        <div class="auth-footer">
            &copy; <?php echo date('Y'); ?> RPS System - All Rights Reserved
        </div>
    </div>
</body>
</html>
