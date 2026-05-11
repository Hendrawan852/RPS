<?php
include 'includes/header.php';
include 'includes/sidebar.php';
require_once '../config/database.php';

$message = '';
$error = '';

// Fetch prodi list for dropdown
$prodi_stmt = $pdo->query("SELECT * FROM prodi ORDER BY nama_prodi ASC");
$prodi_list = $prodi_stmt->fetchAll();

// Handle Actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;

    if ($action === 'delete' && $id) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $message = "User berhasil dihapus.";
    } elseif ($action === 'reset' && $id) {
        $new_password = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $id]);
        $message = "Password user telah direset menjadi: password123";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $role = $_POST['role'];
    $prodi_id = 1; // Default to the first prodi
    $password = $_POST['password'] ?? null;

    try {
        // Uniqueness Check
        if ($id) {
            $check = $pdo->prepare("SELECT id FROM users WHERE (nip_nidn = ? OR email = ?) AND id != ?");
            $check->execute([$nip, $email, $id]);
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE nip_nidn = ? OR email = ?");
            $check->execute([$nip, $email]);
        }

        if ($check->fetch()) {
            throw new Exception("NIP / NIDN atau Email sudah digunakan oleh pengguna lain.");
        }

        if ($id) {
            // Update
            $sql = "UPDATE users SET nip_nidn=?, nama_lengkap=?, email=?, no_hp=?, role=?, prodi_id=? WHERE id=?";
            $params = [$nip, $nama, $email, $no_hp, $role, $prodi_id, $id];
            
            if (!empty($password)) {
                $sql = "UPDATE users SET nip_nidn=?, nama_lengkap=?, email=?, no_hp=?, role=?, prodi_id=?, password=? WHERE id=?";
                $params = [$nip, $nama, $email, $no_hp, $role, $prodi_id, password_hash($password, PASSWORD_DEFAULT), $id];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "Data user berhasil diperbarui.";
        } else {
            // Create
            $hashed = password_hash($password ?: 'password123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nip_nidn, nama_lengkap, email, no_hp, password, role, prodi_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nip, $nama, $email, $no_hp, $hashed, $role, $prodi_id]);
            $message = "User baru berhasil ditambahkan.";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Gagal menyimpan: NIP atau Email sudah ada dalam sistem.";
        } else {
            $error = "Terjadi kesalahan database: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch users with Prodi name (LEFT JOIN to ensure all users show up)
$stmt = $pdo->query("SELECT users.*, prodi.nama_prodi FROM users LEFT JOIN prodi ON users.prodi_id = prodi.id ORDER BY users.created_at DESC");
$users = $stmt->fetchAll();
?>

<?php if ($message): ?>
    <div class="alert alert-success" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Manajemen User</h2>
            <p>Kelola data Dosen, Kaprodi, dan Admin dalam sistem.</p>
        </div>
        <div class="action-btns">
            <button class="btn btn-secondary"><i class="fas fa-file-import"></i> Impor Excel</button>
            <button class="btn btn-primary" onclick="openModal('add')"><i class="fas fa-user-plus"></i> Tambah Pengguna</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title"> Daftar Pengguna </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NIP / NIDN</th>
                    <th>Nama Lengkap</th>
                    <th>E-mail</th>
                    <th>No. HP</th>
                    <th>Peran</th>
                    <th>Password</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($user['nip_nidn']); ?></td>
                    <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['no_hp']); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($user['role']); ?>"><?php echo strtoupper($user['role']); ?></span></td>
                    <td><code title="<?php echo htmlspecialchars($user['password']); ?>" style="font-size: 0.8em;"><?php echo htmlspecialchars(substr($user['password'], 0, 10)) . '...'; ?></code></td>
                    <td><span class="status-indicator active"></span> Aktif</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-icon text-primary" title="Edit" 
                                    onclick="openModal('edit', <?php echo htmlspecialchars(json_encode($user)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?action=reset&id=<?php echo $user['id']; ?>" class="btn-icon text-warning" title="Reset Password" onclick="return confirm('Reset password ke password123?')">
                                <i class="fas fa-key"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $user['id']; ?>" class="btn-icon text-danger" title="Hapus" onclick="return confirm('Hapus user ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Pengguna</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="id" id="user_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>NIP / NIDN</label>
                    <input type="text" name="nip" id="user_nip" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="user_nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="user_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>No. HP</label>
                    <input type="text" name="no_hp" id="user_no_hp" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="user_role" class="form-control" required>
                        <option value="Dosen">Dosen</option>
                        <option value="Kaprodi">Kaprodi</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="form-group" id="pass_group">
                    <label>Password</label>
                    <input type="password" name="password" id="user_pass" class="form-control" placeholder="Default: password123">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(mode, data = null) {
    const modal = document.getElementById('userModal');
    const title = document.getElementById('modalTitle');
    const passGroup = document.getElementById('pass_group');
    
    modal.style.display = "block";
    
    if (mode === 'edit') {
        title.innerText = "Edit Pengguna";
        document.getElementById('user_id').value = data.id;
        document.getElementById('user_nip').value = data.nip_nidn;
        document.getElementById('user_nama').value = data.nama_lengkap;
        document.getElementById('user_email').value = data.email;
        document.getElementById('user_no_hp').value = data.no_hp;
        document.getElementById('user_role').value = data.role;
        document.getElementById('user_pass').placeholder = "Kosongkan jika tidak ubah password";
    } else {
        title.innerText = "Tambah Pengguna";
        document.getElementById('user_id').value = "";
        document.querySelector('form').reset();
        document.getElementById('user_pass').placeholder = "Default: password123";
    }
}

function closeModal() {
    document.getElementById('userModal').style.display = "none";
}

window.onclick = function(event) {
    const modal = document.getElementById('userModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
