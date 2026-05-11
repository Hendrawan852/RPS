<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

$prodi_id = $_SESSION['prodi_id'] ?? 1;

// Fetch Dosen and their RPS progress
$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.nama_lengkap, 
        u.nip_nidn,
        (SELECT COUNT(*) FROM pengajuan_rps p WHERE p.dosen_id = u.id) as total_mk,
        (SELECT COUNT(*) FROM pengajuan_rps p WHERE p.dosen_id = u.id AND p.status = 'Approved') as approved_mk
    FROM users u 
    WHERE u.prodi_id = ? AND u.role = 'Dosen'
    ORDER BY u.nama_lengkap ASC
");
$stmt->execute([$prodi_id]);
$dosen_list = $stmt->fetchAll();
?>

<div class="page-header">
    <h2>Manajemen Dosen Prodi</h2>
    <p>Pantau keterlibatan dan progress penyusunan RPS setiap dosen di Program Studi Anda.</p>
</div>

<div class="card">
    <div class="card-title">Status Penyusunan RPS Dosen</div>
    <div class="table-responsive">
        <table class="custom-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; text-align: left;">
                    <th style="padding: 15px;">DOSEN</th>
                    <th>NIP / NIDN</th>
                    <th>MK DIAJAR</th>
                    <th>PROGRESS RPS</th>
                    <th style="text-align: right;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dosen_list)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">Belum ada dosen yang terdaftar di Prodi ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($dosen_list as $d): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($d['nama_lengkap']); ?>&background=random" style="width: 35px; height: 35px; border-radius: 50%;">
                                <div>
                                    <strong style="display: block;"><?php echo htmlspecialchars($d['nama_lengkap']); ?></strong>
                                    <small style="color: #64748b;">Dosen Tetap</small>
                                </div>
                            </div>
                        </td>
                        <td><span style="font-family: monospace; color: #475569;"><?php echo htmlspecialchars($d['nip_nidn']); ?></span></td>
                        <td><span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-weight: 600;"><?php echo $d['total_mk']; ?> MK</span></td>
                        <td>
                            <?php 
                                $percent = $d['total_mk'] > 0 ? round(($d['approved_mk'] / $d['total_mk']) * 100) : 0;
                                $color = $percent == 100 ? '#10b981' : ($percent > 0 ? '#f59e0b' : '#ef4444');
                            ?>
                            <div style="width: 100px; background: #e2e8f0; height: 8px; border-radius: 4px; margin-bottom: 5px;">
                                <div style="width: <?php echo $percent; ?>%; background: <?php echo $color; ?>; height: 100%; border-radius: 4px;"></div>
                            </div>
                            <small style="color: <?php echo $color; ?>; font-weight: 700;"><?php echo $d['approved_mk']; ?>/<?php echo $d['total_mk']; ?> Selesai (<?php echo $percent; ?>%)</small>
                        </td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline-primary btn-sm" onclick="alert('Fitur notifikasi WhatsApp sedang dikembangkan.')"><i class="fas fa-paper-plane"></i> Kirim Notif</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .custom-table tr:hover { background: #f8fafc; }
    .btn-outline-primary {
        background: transparent;
        border: 1px solid #4f46e5;
        color: #4f46e5;
        padding: 5px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: 0.2s;
    }
    .btn-outline-primary:hover {
        background: #4f46e5;
        color: white;
    }
</style>

<?php include 'includes/footer.php'; ?>