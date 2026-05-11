<?php
/**
 * Dedicated AJAX handler for Kaprodi Data Master actions
 * Completely isolated from HTML output to ensure clean JSON responses
 */
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure clean output - no HTML whatsoever
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Silakan login kembali.']);
    exit;
}

// LOG every single request to this file (FULL POST data)
file_put_contents(
    'C:/laragon/www/RPS/scratch/ajax_log.txt',
    date('Y-m-d H:i:s') . " | User:" . ($_SESSION['user_id'] ?? 'N/A') .
    " | Action:" . ($_POST['action'] ?? 'NONE') .
    " | POST:" . json_encode($_POST) . "\n",
    FILE_APPEND
);

// Get prodi_id from DB
$stmt = $pdo->prepare("SELECT prodi_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$prodi_id = $stmt->fetchColumn();

if (!$prodi_id) {
    echo json_encode(['success' => false, 'message' => 'Akun belum terhubung dengan Program Studi.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'update_dosen_mk') {
        $assignments = $_POST['assignments'] ?? [];
        $updated = 0;
        foreach ($assignments as $mk_id => $dosen_id) {
            $d_id = ($dosen_id !== '' && $dosen_id !== null) ? (int)$dosen_id : null;
            $mk_id = (int)$mk_id;
            $stmt = $pdo->prepare("UPDATE mata_kuliah SET dosen_id = ?, prodi_id = ? WHERE id = ?");
            $stmt->execute([$d_id, $prodi_id, $mk_id]);
            $updated += $stmt->rowCount();
        }
        echo json_encode([
            'success' => true,
            'message' => "Berhasil disimpan. $updated baris diperbarui.",
            'updated' => $updated
        ]);

    } elseif ($action === 'lepas_mk') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE mata_kuliah SET prodi_id = NULL, dosen_id = NULL WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Mata kuliah berhasil dilepas.']);

    } elseif ($action === 'lepas_dosen') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE mata_kuliah SET dosen_id = NULL WHERE dosen_id = ? AND prodi_id = ?")->execute([$id, $prodi_id]);
        $pdo->prepare("UPDATE dosen SET prodi_id = NULL WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Dosen berhasil dilepas.']);

    } else {
        echo json_encode(['success' => false, 'message' => "Action '$action' tidak dikenal."]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
exit;
