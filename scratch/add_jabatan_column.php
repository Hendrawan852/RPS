<?php
include 'config/database.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN jabatan VARCHAR(100) NULL AFTER role");
    echo "Column 'jabatan' added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
