<?php
require_once 'c:/laragon/www/RPS/config/database.php';
try {
    $count = $pdo->exec("UPDATE users SET prodi_id = 1 WHERE prodi_id IS NULL OR prodi_id = 0 OR prodi_id = ''");
    echo "Fixed $count users with missing prodi_id.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
