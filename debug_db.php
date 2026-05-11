<?php
require_once 'config/database.php';
function desc($table, $pdo) {
    echo "Table: $table\n";
    $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
}
desc('cpmk', $pdo);
desc('mk_cpmk', $pdo);
desc('sub_cpmk', $pdo);
