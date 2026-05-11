<?php
require 'config/database.php';

try {
    // Check if columns exist and add them if not
    $columns = $pdo->query("DESCRIBE sub_cpmk")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('indikator', $columns)) {
        $pdo->exec("ALTER TABLE sub_cpmk ADD COLUMN indikator TEXT AFTER sub_cpmk");
    }
    if (!in_array('bentuk_asesmen', $columns)) {
        $pdo->exec("ALTER TABLE sub_cpmk ADD COLUMN bentuk_asesmen TEXT AFTER indikator");
    }
    if (!in_array('bobot', $columns)) {
        $pdo->exec("ALTER TABLE sub_cpmk ADD COLUMN bobot INT AFTER metode");
    }
    
    echo "Table 'sub_cpmk' updated successfully with missing columns.\n";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage() . "\n";
}
?>
