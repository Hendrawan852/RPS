<?php
include 'config/database.php';
$stmt = $pdo->query('DESCRIBE users');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
