<?php
require 'config/database.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS sub_cpmk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mk_id INT NOT NULL,
        minggu VARCHAR(50) NOT NULL,
        sub_cpmk TEXT,
        materi TEXT,
        bahan_kajian TEXT,
        metode TEXT,
        waktu VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mk_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE
    )");
    echo "Table 'sub_cpmk' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
