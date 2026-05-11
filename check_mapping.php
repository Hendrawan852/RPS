<?php
try {
    require_once 'config/database.php';
    $stmt = $pdo->query("SELECT id, username, prodi_id, role FROM users");
    while($r = $stmt->fetch()) {
        echo "ID: {$r['id']}, User: {$r['username']}, Role: {$r['role']}, ProdiID: {$r['prodi_id']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
