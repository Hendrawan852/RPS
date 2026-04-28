<?php
require_once __DIR__ . '/../config/database.php';

try {
    // 1. Create mata_kuliah table
    $pdo->exec("CREATE TABLE IF NOT EXISTS mata_kuliah (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_mk VARCHAR(20) NOT NULL UNIQUE,
        nama_mk VARCHAR(100) NOT NULL,
        sks INT NOT NULL,
        semester_default INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create pengajuan_rps table
    $pdo->exec("CREATE TABLE IF NOT EXISTS pengajuan_rps (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mk_id INT NOT NULL,
        dosen_id INT NOT NULL,
        semester VARCHAR(50) NOT NULL,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        tanggal_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mk_id) REFERENCES mata_kuliah(id),
        FOREIGN KEY (dosen_id) REFERENCES users(id)
    )");

    // 3. Seed mata_kuliah
    $pdo->exec("INSERT IGNORE INTO mata_kuliah (kode_mk, nama_mk, sks, semester_default) VALUES 
    ('TIF101', 'Pemrograman Dasar', 3, 1),
    ('TIF205', 'Sistem Basis Data', 4, 3),
    ('TIF302', 'Pemrograman Web', 3, 5),
    ('TIF401', 'Kecerdasan Buatan', 3, 7)");

    // 4. Seed pengajuan_rps
    // Get Budi Santoso (Admin ID 1) and maybe other users if they exist.
    // For now, let's just use the Admin as a dummy lecturer if no others exist.
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $user_id = $stmt->fetchColumn();

    if ($user_id) {
        $pdo->exec("INSERT IGNORE INTO pengajuan_rps (mk_id, dosen_id, semester, status) VALUES 
        (3, $user_id, 'Gasal 2023/2024', 'Pending'),
        (4, $user_id, 'Gasal 2023/2024', 'Approved')");
    }

    echo "Migration successful!";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
