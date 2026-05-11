<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

if (isset($_GET['mk_id'])) {
    $mk_id = $_GET['mk_id'];
    
    // Fetch CPL
    $stmt = $pdo->prepare("SELECT c.id, c.kode_cpl, c.deskripsi FROM cpl c JOIN mk_cpl mc ON c.id = mc.cpl_id WHERE mc.mk_id = ?");
    $stmt->execute([$mk_id]);
    $cpls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch CPMK
    $stmt = $pdo->prepare("SELECT c.id, c.kode_cpmk, c.deskripsi FROM cpmk c JOIN mk_cpmk mc ON c.id = mc.cpmk_id WHERE mc.mk_id = ?");
    $stmt->execute([$mk_id]);
    $cpmks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'cpl' => $cpls,
        'cpmk' => $cpmks
    ]);
} else {
    echo json_encode(['error' => 'No MK ID provided']);
}
?>
