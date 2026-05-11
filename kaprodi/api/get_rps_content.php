<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Kaprodi') {
    exit('Unauthorized');
}

if (!isset($_GET['id'])) {
    exit('Missing ID');
}

$id = $_GET['id'];

// Get basic info and course description
$stmt = $pdo->prepare("SELECT p.*, m.nama_mk, m.kode_mk, m.deskripsi, u.nama_lengkap as dosen_name
                       FROM pengajuan_rps p
                       JOIN mata_kuliah m ON p.mk_id = m.id
                       JOIN users u ON p.dosen_id = u.id
                       WHERE p.id = ?");
$stmt->execute([$id]);
$rps = $stmt->fetch();

if (!$rps) {
    exit('RPS not found');
}

// Get weekly details
$stmt = $pdo->prepare("SELECT * FROM sub_cpmk WHERE mk_id = ? ORDER BY CAST(REPLACE(minggu, 'Minggu ', '') AS UNSIGNED)");
$stmt->execute([$rps['mk_id']]);
$weeks = $stmt->fetchAll();

// Handle Raw JSON Request for Editing
if (isset($_GET['raw'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'rps' => $rps,
        'weeks' => $weeks
    ]);
    exit;
}

// Get CPL mapping
$stmt = $pdo->prepare("SELECT c.* FROM cpl c JOIN mk_cpl mc ON c.id = mc.cpl_id WHERE mc.mk_id = ?");
$stmt->execute([$rps['mk_id']]);
$cpls = $stmt->fetchAll();

// Get CPMK mapping
$stmt = $pdo->prepare("SELECT c.* FROM cpmk c JOIN mk_cpmk mc ON c.id = mc.cpmk_id WHERE mc.mk_id = ?");
$stmt->execute([$rps['mk_id']]);
$cpmks = $stmt->fetchAll();
?>

<div class="review-section" style="background: #f8fafc; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #e2e8f0;">
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
        <div>
            <small style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 10px;">Dosen Pengampu</small>
            <p style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars((string)$rps['dosen_name']); ?></p>
        </div>
        <div>
            <small style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 10px;">Semester / TA</small>
            <p style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars((string)$rps['semester']); ?></p>
        </div>
        <div>
            <small style="color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 10px;">Bobot SKS</small>
            <p style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars((string)$rps['sks']); ?> SKS</p>
        </div>
    </div>
</div>

<div class="review-section">
    <h4 style="color: #0f172a; margin-bottom: 10px;"><i class="fas fa-info-circle"></i> Deskripsi Mata Kuliah</h4>
    <p style="font-size: 14px; line-height: 1.6; color: #475569; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9; border-left: 4px solid var(--accent-color);">
        <?php echo !empty($rps['deskripsi']) ? nl2br(htmlspecialchars((string)$rps['deskripsi'])) : '<span style="color: #94a3b8;">Belum ada deskripsi mata kuliah.</span>'; ?>
    </p>
</div>

<div class="review-section" style="margin-top: 25px;">
    <h4 style="color: #0f172a; margin-bottom: 10px;"><i class="fas fa-graduation-cap"></i> Capaian Pembelajaran (CPL & CPMK)</h4>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <p style="font-weight: 700; font-size: 13px; color: #64748b; margin-bottom: 8px; text-transform: uppercase;">CPL Prodi</p>
            <ul style="font-size: 12px; padding-left: 20px; color: #475569;">
                <?php if (count($cpls) > 0): ?>
                    <?php foreach ($cpls as $c): ?>
                        <li><strong><?php echo htmlspecialchars((string)$c['kode_cpl']); ?>:</strong> <?php echo htmlspecialchars((string)$c['deskripsi']); ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li style="list-style: none; margin-left: -20px; color: #94a3b8;">Tidak ada CPL yang ditautkan.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9;">
            <p style="font-weight: 700; font-size: 13px; color: #64748b; margin-bottom: 8px; text-transform: uppercase;">CPMK</p>
            <ul style="font-size: 12px; padding-left: 20px; color: #475569;">
                <?php if (count($cpmks) > 0): ?>
                    <?php foreach ($cpmks as $c): ?>
                        <li><strong><?php echo htmlspecialchars((string)$c['kode_cpmk']); ?>:</strong> <?php echo htmlspecialchars((string)$c['deskripsi']); ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li style="list-style: none; margin-left: -20px; color: #94a3b8;">Tidak ada CPMK yang ditautkan.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<div class="review-section" style="margin-top: 25px;">
    <h4 style="color: #0f172a; margin-bottom: 10px;"><i class="fas fa-calendar-alt"></i> Rencana Pembelajaran Mingguan</h4>
    <?php if (count($weeks) > 0): ?>
        <div style="overflow-x: auto;">
            <table class="review-table" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">Mg</th>
                        <th>Sub-CPMK</th>
                        <th>Indikator</th>
                        <th>Asesmen</th>
                        <th>Materi Pembelajaran</th>
                        <th>Bahan Kajian</th>
                        <th>Metode</th>
                        <th style="width: 60px; text-align: center;">Bobot</th>
                        <th style="width: 80px; text-align: center;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weeks as $w): ?>
                        <tr>
                            <td style="text-align: center; background: #f8fafc; font-weight: 700;">
                                <?php echo str_replace('Minggu ', '', (string)$w['minggu']); ?>
                            </td>
                            <td style="font-size: 12px;"><?php echo nl2br(htmlspecialchars((string)$w['sub_cpmk'])); ?></td>
                            <td style="font-size: 12px;"><?php echo nl2br(htmlspecialchars((string)$w['indikator'])); ?></td>
                            <td style="font-size: 12px;"><?php echo htmlspecialchars((string)$w['bentuk_asesmen']); ?></td>
                            <td style="font-size: 12px;"><?php echo nl2br(htmlspecialchars((string)$w['materi'])); ?></td>
                            <td style="font-size: 12px;"><?php echo htmlspecialchars((string)$w['bahan_kajian']); ?></td>
                            <td>
                                <span class="badge" style="background: #0f172a; color: #fff; font-size: 9px; display: block; margin-bottom: 4px;"><?php echo htmlspecialchars((string)$w['setting']); ?></span>
                                <span style="font-size: 11px;"><?php echo htmlspecialchars((string)$w['metode']); ?></span>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #3b82f6;"><?php echo htmlspecialchars((string)$w['bobot']); ?>%</td>
                            <td style="text-align: center; font-size: 11px; color: #64748b;"><?php echo htmlspecialchars((string)$w['waktu']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="text-align: center; padding: 20px; color: #94a3b8; background: #fff; border: 1px solid #f1f5f9; border-radius: 8px;">Belum ada rincian mingguan yang diinput.</p>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px;">
    <div class="review-section">
        <h4 style="color: #0f172a; margin-bottom: 10px;"><i class="fas fa-chalkboard-teacher"></i> Metode & Pengalaman Belajar</h4>
        <div style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9; font-size: 13px; color: #475569;">
            <?php echo !empty($rps['metode']) ? nl2br(htmlspecialchars((string)$rps['metode'])) : '<span style="color: #94a3b8;">Belum ditentukan.</span>'; ?>
        </div>
    </div>
    <div class="review-section">
        <h4 style="color: #0f172a; margin-bottom: 10px;"><i class="fas fa-tasks"></i> Kriteria Penilaian</h4>
        <div style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9; font-size: 13px; color: #475569;">
            <?php echo !empty($rps['penilaian']) ? nl2br(htmlspecialchars((string)$rps['penilaian'])) : '<span style="color: #94a3b8;">Belum ditentukan.</span>'; ?>
        </div>
    </div>
</div>

<div class="review-section" style="margin-top: 25px;">
    <h4 style="color: #0f172a; margin-bottom: 10px;"><i class="fas fa-book"></i> Daftar Referensi</h4>
    <div style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9; font-size: 13px; color: #475569;">
        <?php echo !empty($rps['referensi']) ? nl2br(htmlspecialchars((string)$rps['referensi'])) : '<span style="color: #94a3b8;">Belum ada daftar referensi.</span>'; ?>
    </div>
</div>
