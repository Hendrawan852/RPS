<?php
require 'config/database.php';
try {
    $pdo->exec("ALTER TABLE sub_cpmk ADD COLUMN setting VARCHAR(50) DEFAULT 'Tatap Muka' AFTER metode");
    echo "Column 'setting' added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
