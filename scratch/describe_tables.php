<?php
require_once '../config/database.php';
$tables = ['users', 'mata_kuliah', 'dosen'];
foreach ($tables as $t) {
    echo "\nStructure of table: $t\n";
    $stmt = $pdo->query("DESCRIBE $t");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
