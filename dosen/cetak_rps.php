<?php
require_once '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID Mata Kuliah tidak ditemukan.");
}

// 1. Fetch Data Utama
$stmt = $pdo->prepare("
    SELECT mk.*, p.nama_prodi, d.nama as nama_dosen, d.jabatan as jabatan_dosen,
           (SELECT nama_lengkap FROM users WHERE prodi_id = mk.prodi_id AND role = 'Kaprodi' LIMIT 1) as nama_kaprodi,
           (SELECT status FROM pengajuan_rps WHERE mk_id = mk.id ORDER BY id DESC LIMIT 1) as rps_status
    FROM mata_kuliah mk
    JOIN prodi p ON mk.prodi_id = p.id
    LEFT JOIN dosen d ON mk.dosen_id = d.id
    WHERE mk.id = ?
");
$stmt->execute([$id]);
$mk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mk) {
    die("Mata kuliah tidak ditemukan.");
}

// 2. Fetch CPL & CPMK
$cpl_stmt = $pdo->prepare("SELECT c.* FROM cpl c JOIN mk_cpl m ON c.id = m.cpl_id WHERE m.mk_id = ?");
$cpl_stmt->execute([$id]);
$cpl_list = $cpl_stmt->fetchAll(PDO::FETCH_ASSOC);

$cpmk_stmt = $pdo->prepare("
    SELECT c.*, 
           (SELECT GROUP_CONCAT(cpl.kode_cpl SEPARATOR ', ') 
            FROM cpl 
            JOIN mk_cpl mc ON cpl.id = mc.cpl_id 
            WHERE mc.mk_id = ?) as cpl_supported 
    FROM cpmk c 
    JOIN mk_cpmk m ON c.id = m.cpmk_id 
    WHERE m.mk_id = ?
");
$cpmk_stmt->execute([$id, $id]);
$cpmk_list = $cpmk_stmt->fetchAll(PDO::FETCH_ASSOC);

$sub_stmt = $pdo->prepare("SELECT * FROM sub_cpmk WHERE mk_id = ? ORDER BY CAST(minggu AS UNSIGNED) ASC");
$sub_stmt->execute([$id]);
$sub_list = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak RPS - <?php echo $mk['nama_mk']; ?></title>
    <style>
        @page { size: A4; margin: 0.5in; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.2; background: #fff; }
        .rps-container { width: 100%; border: none; background: white; }
        
        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 0; }
        th, td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; word-wrap: break-word; }
        
        .header-box { text-align: center; background: #fff; }
        .header-title { font-size: 12px; font-weight: bold; text-transform: uppercase; }
        
        .bg-gray { background: #f3f4f6; font-weight: bold; }
        .text-center { text-align: center; }
        
        .logo-img { width: 60px; height: auto; }
        
        .page-break { page-break-after: always; height: 0; display: block; clear: both; }
        
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
        }
        
        .btn-print {
            position: fixed; top: 20px; right: 20px; padding: 10px 20px;
            background: #4f46e5; color: white; border: none; border-radius: 8px;
            cursor: pointer; font-weight: bold; z-index: 1000;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">Cetak PDF / Print</button>

    <div class="rps-container" id="rps-content">
        <!-- PAGE 1: IDENTITY -->
        <table>
            <tr>
                <td rowspan="2" class="text-center" style="width: 80px;">
                    <img src="../logo/logo-unsri.webp" alt="Logo" class="logo-img" onerror="this.src='https://upload.wikimedia.org/wikipedia/id/thumb/0/00/Universitas_Sriwijaya_Logo.svg/1200px-Universitas_Sriwijaya_Logo.svg.png'">
                </td>
                <td colspan="6" class="header-box">
                    <div class="header-title">RENCANA PEMBELAJARAN SEMESTER</div>
                    <div class="header-title">PROGRAM STUDI <?php echo strtoupper($mk['nama_prodi']); ?></div>
                    <div class="header-title">FAKULTAS ILMU KOMPUTER</div>
                    <div class="header-title">UNIVERSITAS SRIWIJAYA</div>
                </td>
            </tr>
            <tr class="bg-gray text-center" style="font-size: 9px;">
                <td style="width: 15%;">NAMA MK</td>
                <td style="width: 10%;">KODE MK</td>
                <td style="width: 20%;">RUMPUN MATA KULIAH</td>
                <td colspan="2" style="width: 15%;">BOBOT(SKS)</td>
                <td style="width: 10%;">SEMESTER</td>
                <td style="width: 15%;">Direvisi</td>
            </tr>
            <tr class="text-center">
                <td class="bg-gray" style="text-align: left;">Identitas Mata Kuliah</td>
                <td><?php echo $mk['nama_mk']; ?></td>
                <td><?php echo $mk['kode_mk']; ?></td>
                <td><?php echo ($mk['prodi_id'] == 1 ? 'Ilmu Komputer' : 'Sistem Informasi'); ?></td>
                <td>T=<?php echo $mk['sks']; ?></td>
                <td>P=0</td>
                <td><?php echo $mk['semester_default']; ?></td>
                <td><?php echo date('d M Y', strtotime($mk['created_at'])); ?></td>
            </tr>
            <tr>
                <td rowspan="2" class="bg-gray">Otoritas</td>
                <td colspan="3" class="bg-gray text-center">Pengembang RPS</td>
                <td colspan="2" class="bg-gray text-center">Ketua Kelompok Keahlian</td>
                <td class="bg-gray text-center">Ka PRODI</td>
            </tr>
            <tr class="text-center">
                <td colspan="3" style="height: 35px; vertical-align: bottom;">
                    <u><?php echo $mk['nama_dosen'] ?: '-'; ?></u>
                </td>
                <td colspan="2" style="height: 35px; vertical-align: bottom;">-</td>
                <td style="height: 35px; vertical-align: bottom;">
                    <u><?php echo $mk['nama_kaprodi'] ?: 'Apriansyah Putra, S.Kom., M.Kom.'; ?></u>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="bg-gray" style="width: 20%;">Deskripsi Mata Kuliah</td>
                <td><?php echo $mk['deskripsi'] ?: 'Tidak ada deskripsi.'; ?></td>
            </tr>
        </table>

        <table>
            <tr>
                <td rowspan="<?php echo count($cpl_list) + count($cpmk_list) + 3; ?>" class="bg-gray" style="width: 20%;">Capaian Pembelajaran Lulusan & Capaian Pembelajaran Mata Kuliah</td>
                <td colspan="3" class="bg-gray">Capaian Pembelajaran Lulusan (CPL) PRODI</td>
            </tr>
            <?php foreach($cpl_list as $cpl): ?>
            <tr>
                <td style="width: 15%;" class="text-center"><?php echo $cpl['kode_cpl']; ?></td>
                <td colspan="2"><?php echo $cpl['deskripsi']; ?></td>
            </tr>
            <?php endforeach; ?>
            
            <tr class="bg-gray">
                <td class="text-center">Capaian Pembelajaran Mata Kuliah (CPMK)</td>
                <td>Diskripsi CPMK</td>
                <td style="width: 15%;" class="text-center">CPL yang didukung</td>
            </tr>
            <?php foreach($cpmk_list as $cpmk): ?>
            <tr>
                <td class="text-center"><?php echo $cpmk['kode_cpmk']; ?></td>
                <td><?php echo $cpmk['deskripsi']; ?></td>
                <td style="width: 15%;" class="text-center"><?php echo $cpmk['cpl_supported'] ?: '-'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div class="page-break"></div>

        <!-- PAGE 2: ASSESSMENT & REFS -->
        <table>
            <tr class="bg-gray">
                <td rowspan="7" style="width: 20%;">Penilaian</td>
                <td rowspan="2" class="text-center">Id CPMK</td>
                <td colspan="5" class="text-center">Bobot per Bentuk Penilaian</td>
                <td rowspan="2" class="text-center">TOTAL</td>
            </tr>
            <tr class="bg-gray text-center">
                <td>T1</td>
                <td>T2</td>
                <td>T3</td>
                <td>P1</td>
                <td>P2</td>
            </tr>
            <?php 
            $total_t1 = 0;
            foreach($cpmk_list as $index => $cpmk): 
                $val = ($index == 0 ? 20 : 0); 
                $total_t1 += $val;
            ?>
            <tr class="text-center">
                <td><?php echo $cpmk['kode_cpmk']; ?></td>
                <td><?php echo $val; ?></td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td><?php echo $val; ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="bg-gray text-center">
                <td>Total</td>
                <td><?php echo $total_t1; ?></td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>100</td>
            </tr>
        </table>

        <table>
            <tr>
                <td rowspan="4" class="bg-gray" style="width: 20%;">Pustaka</td>
                <td class="bg-gray">Utama:</td>
            </tr>
            <tr>
                <td>1. Buku Ajar Mata Kuliah <?php echo $mk['nama_mk']; ?> (2023)</td>
            </tr>
            <tr>
                <td class="bg-gray">Pendukung:</td>
            </tr>
            <tr>
                <td>- Artikel Ilmiah Terkait <?php echo $mk['nama_mk']; ?></td>
            </tr>
        </table>

        <table>
            <tr>
                <td rowspan="2" class="bg-gray" style="width: 20%;">Media Pembelajaran</td>
                <td class="bg-gray">Software:</td>
                <td class="bg-gray">Hardware:</td>
            </tr>
            <tr>
                <td>Browser, VS Code, Database Manager</td>
                <td>Komputer/Laptop, Proyektor</td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="bg-gray" style="width: 20%;">Team Teaching</td>
                <td><?php echo $mk['nama_dosen'] ?: '-'; ?></td>
            </tr>
        </table>

        <table>
            <tr>
                <td class="bg-gray" style="width: 20%;">Matakuliah Syarat</td>
                <td>-</td>
            </tr>
        </table>

        <div class="page-break"></div>

        <!-- WEEKLY PLAN -->
        <table style="font-size: 9px;">
            <tr class="bg-gray text-center">
                <td style="width: 40px;">MINGGU</td>
                <td style="width: 50px;">ID SUB CPMK</td>
                <td>DESKRIPSI SUB-CPMK</td>
                <td>INDIKATOR KETERCAPAIAN CPMK</td>
                <td style="width: 80px;">BENTUK ASESMEN</td>
                <td>MATERI</td>
                <td style="width: 80px;">METODE</td>
                <td style="width: 70px;">L/D JARINGAN</td>
                <td style="width: 40px;">BOBOT</td>
            </tr>
            <?php 
            $sub_map = [];
            foreach($sub_list as $s) $sub_map[$s['minggu']] = $s;

            for($i = 1; $i <= 16; $i++): 
                if($i == 8): ?>
                    <tr class="bg-gray text-center">
                        <td>8</td>
                        <td colspan="7">UTS (Ujian Tengah Semester)</td>
                        <td>30</td>
                    </tr>
                <?php continue; endif; 

                if($i == 16): ?>
                    <tr class="bg-gray text-center">
                        <td>16</td>
                        <td colspan="7">UAS (Ujian Akhir Semester)</td>
                        <td>40</td>
                    </tr>
                <?php continue; endif; 

                $data = $sub_map[$i] ?? null;
            ?>
            <tr>
                <td class="text-center"><?php echo $i; ?></td>
                <td class="text-center">-</td>
                <td><?php echo $data ? $data['sub_cpmk'] : '-'; ?></td>
                <td><?php echo $data ? $data['indikator'] : '-'; ?></td>
                <td><?php echo $data ? $data['bentuk_asesmen'] : 'Kriteria: Ketepatan'; ?></td>
                <td><?php echo $data ? $data['materi'] : '-'; ?></td>
                <td><?php echo $data ? $data['metode'] : 'Diskusi'; ?></td>
                <td class="text-center"><?php echo $data ? (($data['setting'] == 'Daring' ? 'Online' : $data['setting']) ?: 'Tatap Muka') : 'Tatap Muka'; ?></td>
                <td class="text-center"><?php echo $data ? $data['bobot'] : '2'; ?></td>
            </tr>
            <?php endfor; ?>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        window.onload = function() {
            const element = document.getElementById('rps-content');
            const opt = {
                margin:       [0.4, 0.4, 0.4, 0.4],
                filename:     'RPS_<?php echo $mk['kode_mk']; ?>_<?php echo str_replace(' ', '_', $mk['nama_mk']); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
