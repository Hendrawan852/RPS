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
    SELECT c.*, m.tugas1, m.tugas2, m.tugas3, m.proyek1, m.proyek2,
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            background: #f1f5f9;
            line-height: 1.4;
        }
        .rps-container {
            width: 1100px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000;
        }
        td, th {
            border: 1px solid #000;
            padding: 6px 10px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .header-box { text-align: center; vertical-align: middle; }
        .header-title { font-size: 13pt; font-weight: bold; text-transform: uppercase; }
        .bg-gray {
            background-color: #e2e8f0 !important;
            font-weight: bold;
            vertical-align: middle;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .logo-img { width: 80px; display: block; margin: auto; }
        .page-break { page-break-after: always; }
        .val-input {
            width: 90%;
            border: 1px solid #ccc;
            padding: 2px;
            text-align: center;
            font-family: inherit;
            font-size: 10pt;
        }
        .col-minggu  { width: 40px; }
        .col-id      { width: 50px; }
        .col-desc    { width: 180px; }
        .col-indikator { width: 150px; }
        .col-asesmen { width: 110px; }
        .col-materi  { width: 150px; }
        .col-metode  { width: 105px; }
        .col-jaringan { width: 110px; }
        .col-bobot   { width: 50px; }
        .btn-print {
            position: fixed; top: 15px; right: 15px;
            padding: 10px 24px;
            background: #1d4ed8; color: #fff;
            border: none; border-radius: 6px;
            cursor: pointer; font-weight: bold; font-size: 13px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 999;
        }
        .btn-print:hover { background: #1e40af; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .rps-container { width: 100%; padding: 0; box-shadow: none; }
            .val-input { border: none; background: transparent; }
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div id="loading-overlay" style="
        position: fixed; top:0; left:0; width:100%; height:100%;
        background: #4c1d95;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        z-index: 9999; color: white;
        font-family: 'Segoe UI', sans-serif;
    ">
        <div style="font-size: 54px; margin-bottom: 20px;">📄</div>
        <div style="font-size: 24px; font-weight: bold; margin-bottom: 10px;">GENERASI FILE PDF...</div>
        <div style="font-size: 14px; opacity: 0.9;">Halaman ini akan menutup otomatis setelah download selesai.</div>
        <div style="margin-top: 35px; width: 350px; height: 8px; background: rgba(255,255,255,0.2); border-radius: 4px; overflow: hidden;">
            <div id="progress-bar" style="height:100%; width:0%; background:#fff; border-radius:4px; transition: width 4s ease;"></div>
        </div>
    </div>

    <div class="rps-container" id="rps-content">
    <table>
            <tr>
                <td rowspan="2" class="text-center" style="width: 100px; vertical-align: middle;">
                    <img src="../logo/logo-unsri.webp" alt="Logo" class="logo-img" onerror="this.src='https://upload.wikimedia.org/wikipedia/id/thumb/0/00/Universitas_Sriwijaya_Logo.svg/1200px-Universitas_Sriwijaya_Logo.svg.png'">
                </td>
                <td colspan="8" class="header-box" style="padding: 10px;">
                    <div class="header-title">RENCANA PEMBELAJARAN SEMESTER</div>
                    <div class="header-title">PROGRAM STUDI <?php echo strtoupper($mk['nama_prodi']); ?></div>
                    <div class="header-title">FAKULTAS ILMU KOMPUTER</div>
                    <div class="header-title">UNIVERSITAS SRIWIJAYA</div>
                </td>
            </tr>
            <!-- Row 2: MK Details Headers -->
            <tr class="bg-gray">
                <td colspan="2" style="width: 18%;">NAMA MK</td>
                <td style="width: 10%;">KODE MK</td>
                <td style="width: 15%;">RUMPUN MATA KULIAH</td>
                <td colspan="2" style="width: 10%;">BOBOT(SKS)</td>
                <td style="width: 10%;">SEMESTER</td>
                <td style="width: 12%;">Direvisi</td>
            </tr>
            <!-- Row 3: Identity Data -->
            <tr>
                <td class="bg-gray" style="text-align: left;">Identitas Mata Kuliah</td>
                <td colspan="2" class="text-center"><?php echo $mk['nama_mk']; ?></td>
                <td class="text-center"><?php echo $mk['kode_mk']; ?></td>
                <td class="text-center"><?php echo ($mk['prodi_id'] == 1 ? 'Ilmu Komputer' : 'Sistem Informasi'); ?></td>
                <td colspan="2" class="text-center">T=<?php echo $mk['sks']; ?> P=0</td>
                <td class="text-center"><?php echo $mk['semester_default'] ?: $mk['semester']; ?></td>
                <td class="text-center"><?php echo date('d-m-Y', strtotime($mk['created_at'])); ?></td>
            </tr>
            <!-- Row 4-5: Otoritas -->
            <tr>
                <td rowspan="2" class="bg-gray" style="text-align: left;">Otoritas</td>
                <td colspan="3" class="bg-gray">Pengembang RPS</td>
                <td colspan="3" class="bg-gray">Ketua Kelompok Keahlian</td>
                <td colspan="2" class="bg-gray">Ka PRODI</td>
            </tr>
            <tr class="text-center">
                <td colspan="3" style="height: 45px; vertical-align: bottom; padding-bottom: 5px;">
                    <u><?php echo $mk['nama_dosen'] ?: '-'; ?></u>
                </td>
                <td colspan="3" style="height: 45px; vertical-align: bottom; padding-bottom: 5px;">-</td>
                <td colspan="2" style="height: 45px; vertical-align: bottom; padding-bottom: 5px;">
                    <u><?php echo $mk['nama_kaprodi'] ?: 'Apriansyah Putra, S.Kom., M.Kom.'; ?></u>
                </td>
            </tr>
            <!-- Row 6: Deskripsi -->
            <tr>
                <td class="bg-gray" style="text-align: left;">Deskripsi Mata Kuliah</td>
                <td colspan="8" style="text-align: justify;"><?php echo $mk['deskripsi'] ?: 'Tidak ada deskripsi.'; ?></td>
            </tr>
            <!-- Section: CPL/CPMK -->
            <tr class="bg-gray">
                <td rowspan="<?php echo count($cpl_list) + count($cpmk_list) + 2; ?>" style="text-align: left;">CPL & CPMK</td>
                <td colspan="8" style="text-align: left;">Capaian Pembelajaran Lulusan (CPL) PRODI</td>
            </tr>
            <?php foreach($cpl_list as $cpl): ?>
            <tr>
                <td style="width: 10%;" class="text-center text-bold"><?php echo $cpl['kode_cpl']; ?></td>
                <td colspan="7"><?php echo $cpl['deskripsi']; ?></td>
            </tr>
            <?php endforeach; ?>
            
            <tr class="bg-gray">
                <td colspan="7" style="text-align: left;">Capaian Pembelajaran Mata Kuliah (CPMK)</td>
                <td style="width: 20%;">CPL yang didukung</td>
            </tr>
            <?php foreach($cpmk_list as $cpmk): ?>
            <tr>
                <td colspan="7" class="text-bold"><?php echo $cpmk['kode_cpmk']; ?>: <?php echo $cpmk['deskripsi']; ?></td>
                <td class="text-center"><?php echo $cpmk['cpl_supported'] ?: '-'; ?></td>
            </tr>
            <?php endforeach; ?>

            <!-- Section: Penilaian -->
            <tr class="bg-gray">
                <td rowspan="<?php echo count($cpmk_list) + 3; ?>" style="text-align: left; vertical-align: middle;">Penilaian</td>
                <td rowspan="2" class="text-center" style="vertical-align: middle;">Id CPMK</td>
                <td colspan="5" class="text-center">Bobot per Bentuk Penilaian (%)</td>
                <td colspan="2" rowspan="2" class="text-center" style="vertical-align: middle; font-size: 9px; line-height: 1.1;">TOTAL BOBOT PER CPMK</td>
            </tr>
            <tr class="bg-gray" style="font-size: 9px; text-align: center;">
                <td>Tugas 1</td>
                <td>Tugas 2</td>
                <td>Tugas 3</td>
                <td>Proyek 1</td>
                <td>Proyek 2</td>
            </tr>
            <?php 
            $sum_t1 = $sum_t2 = $sum_t3 = $sum_p1 = $sum_p2 = 0;
            foreach($cpmk_list as $cpmk): 
                $row_total = $cpmk['tugas1'] + $cpmk['tugas2'] + $cpmk['tugas3'] + $cpmk['proyek1'] + $cpmk['proyek2'];
                $sum_t1 += $cpmk['tugas1']; $sum_t2 += $cpmk['tugas2']; $sum_t3 += $cpmk['tugas3'];
                $sum_p1 += $cpmk['proyek1']; $sum_p2 += $cpmk['proyek2'];
            ?>
            <tr class="text-center">
                <td class="text-bold"><?php echo $cpmk['kode_cpmk']; ?></td>
                <td><?php echo $cpmk['tugas1']; ?>%</td>
                <td><?php echo $cpmk['tugas2']; ?>%</td>
                <td><?php echo $cpmk['tugas3']; ?>%</td>
                <td><?php echo $cpmk['proyek1']; ?>%</td>
                <td><?php echo $cpmk['proyek2']; ?>%</td>
                <td colspan="2" class="text-bold"><?php echo $row_total; ?>%</td>
            </tr>
            <?php endforeach; ?>
            <tr class="bg-gray text-center" style="font-size: 10px;">
                <td class="text-bold">Total per penilaian</td>
                <td><?php echo $sum_t1; ?>%</td>
                <td><?php echo $sum_t2; ?>%</td>
                <td><?php echo $sum_t3; ?>%</td>
                <td><?php echo $sum_p1; ?>%</td>
                <td><?php echo $sum_p2; ?>%</td>
                <td colspan="2" class="text-bold"><?php echo ($sum_t1+$sum_t2+$sum_t3+$sum_p1+$sum_p2); ?>%</td>
            </tr>

            <!-- Section: References & Media -->
            <tr>
                <td rowspan="3" class="bg-gray" style="text-align: left;">Pustaka</td>
                <td colspan="1" class="bg-gray" style="text-align: left;">Utama:</td>
                <td colspan="7">1. Munir, Rinaldi, Algoritma & Pemrograman Dalam Bahasa Pascal dan C, Informatika, 2012.</td>
            </tr>
            <tr>
                <td colspan="1" class="bg-gray" style="text-align: left;">Pendukung:</td>
                <td colspan="7">- Jurnal dan Artikel Ilmiah Terkait</td>
            </tr>
            <tr>
                <td colspan="1" class="bg-gray" style="text-align: left;">Media:</td>
                <td colspan="7">Software: VS Code, Hardware: Laptop / Projector</td>
            </tr>

            <!-- Team & Syarat -->
            <tr>
                <td class="bg-gray" style="text-align: left;">Team Teaching</td>
                <td colspan="8"><?php echo $mk['nama_dosen'] ?: '-'; ?></td>
            </tr>
            <tr>
                <td class="bg-gray" style="text-align: left;">Mata Kuliah Syarat</td>
                <td colspan="8">-</td>
            </tr>
        </table>

        <div class="page-break"></div>

        <!-- WEEKLY PLAN -->
        <table style="font-size: 9pt;">
            <thead>
                <tr class="bg-gray">
                    <td class="col-minggu">MINGGU KE-</td>
                    <td class="col-id">ID SUB CPMK</td>
                    <td class="col-desc">DESKRIPSI SUB-CPMK</td>
                    <td class="col-indikator">INDIKATOR KETERCAPAIAN CPMK</td>
                    <td class="col-asesmen">BENTUK ASSESMEN</td>
                    <td class="col-materi">MATERI</td>
                    <td class="col-metode">METODE</td>
                    <td class="col-jaringan">LUAR/DALAM JARINGAN</td>
                    <td class="col-bobot" style="display: table-cell !important;">BOBOT</td>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sub_map = [];
                foreach($sub_list as $s) $sub_map[$s['minggu']] = $s;

                for($i = 1; $i <= 16; $i++): 
                    if($i == 8): ?>
                        <tr class="bg-gray text-center">
                            <td class="text-bold">8</td>
                            <td colspan="7" class="text-bold">UTS (Ujian Tengah Semester)</td>
                            <td class="text-bold">30</td>
                        </tr>
                    <?php continue; endif; 

                    if($i == 16): ?>
                        <tr class="bg-gray text-center">
                            <td class="text-bold">16</td>
                            <td colspan="7" class="text-bold">UAS (Ujian Akhir Semester)</td>
                            <td class="text-bold">40</td>
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
                    <td><?php echo $data ? $data['metode'] : 'Ceramah, Diskusi'; ?></td>
                    <td class="text-center"><?php echo $data ? (($data['setting'] == 'Daring' ? 'Daring' : $data['setting']) ?: 'Tatap Muka') : 'Tatap Muka'; ?></td>
                    <td class="text-center" style="display: table-cell !important;"><?php echo $data ? $data['bobot'] : '2'; ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        window.onload = function() {
            // progress bar animation logic here...
            setTimeout(() => {
                const pb = document.getElementById('progress-bar');
                if(pb) pb.style.width = '90%';
            }, 100);

            // Hide scrollbars and fix body for capture
            document.documentElement.style.overflow = 'hidden';
            document.body.style.margin = '0';
            document.body.style.padding = '0';
            document.body.style.background = '#fff';

            const element = document.getElementById('rps-content');
            // Force a predictable width that fits A4 landscape perfectly
            element.style.width = '1040px'; 
            element.style.margin = '0 auto';
            element.style.boxShadow = 'none';

            const opt = {
                margin:       [10, 5, 10, 5], // [top, left, bottom, right] in mm
                filename:     'RPS_<?php echo $mk['kode_mk']; ?>_V<?php echo time(); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: 2, 
                    useCORS: true, 
                    letterRendering: true,
                    scrollY: 0,
                    scrollX: 0
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
                pagebreak:    { mode: ['css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                const pb = document.getElementById('progress-bar');
                if(pb) pb.style.width = '100%';
                
                setTimeout(() => {
                    const overlay = document.getElementById('loading-overlay');
                    if(overlay) {
                        overlay.innerHTML = `
                            <div style="font-size: 54px; margin-bottom: 20px;">✅</div>
                            <div style="font-size: 24px; font-weight: bold; margin-bottom: 10px;">BERHASIL!</div>
                            <div style="font-size: 14px; opacity: 0.9;">File sudah tersimpan di folder Download.</div>
                        `;
                    }
                    setTimeout(() => window.close(), 1500);
                }, 500);
            }).catch(err => {
                console.error('PDF Error:', err);
                const overlay = document.getElementById('loading-overlay');
                if(overlay) overlay.innerHTML = '<div style="color:red">Gagal membuat PDF: ' + err.message + '</div>';
            });
        };
    </script>
</body>
</html>
