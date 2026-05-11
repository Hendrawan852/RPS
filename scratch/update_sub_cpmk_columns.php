<?php
require 'config/database.php';

try {
    // Add columns if they don't exist
    $pdo->exec("ALTER TABLE sub_cpmk ADD COLUMN IF NOT EXISTS indikator TEXT AFTER sub_cpmk");
    $pdo->exec("ALTER TABLE sub_cpmk ADD COLUMN IF NOT EXISTS bentuk_asesmen TEXT AFTER indikator");
    $pdo->exec("ALTER TABLE sub_cpmk ADD COLUMN IF NOT EXISTS media VARCHAR(100) AFTER metode");
    $pdo->exec("ALTER TABLE sub_cpmk ADD COLUMN IF NOT EXISTS bobot INT AFTER media");
    
    echo "Table 'sub_cpmk' updated successfully with new columns from PDF reference.\n";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage() . "\n";
}
?>
