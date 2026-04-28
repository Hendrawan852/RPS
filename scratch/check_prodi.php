<?php
require_once 'c:/laragon/www/RPS/config/database.php';
echo "Prodi list:\n";
foreach ($pdo->query("SELECT * FROM prodi") as $row) {
    print_r($row);
}
?>
