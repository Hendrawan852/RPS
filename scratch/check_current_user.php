<?php
require_once '../config/database.php';
session_start();

echo "Current Session User ID: " . ($_SESSION['user_id'] ?? 'NONE') . "\n";
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id, role, prodi_id, nama_lengkap FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    print_r($user);
}
?>
