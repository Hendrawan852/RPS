<?php
require 'config/database.php';
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "Structure of $table:\n";
        $stmt2 = $pdo->query("DESCRIBE $table");
        while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            echo "  " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
