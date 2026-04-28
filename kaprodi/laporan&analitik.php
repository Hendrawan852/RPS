<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <h2>Laporan & Analitik Prodi</h2>
    <p>Evaluasi ketercapaian kurikulum dan rekapitulasi RPS dosen.</p>
</div>

<div class="report-grid">
    <div class="card">
        <div class="card-title">Kesesuaian OBE / KKNI</div>
        <div class="obe-chart">
            <div class="obe-circle">
                <span class="value">88%</span>
                <span class="label">Compliance</span>
            </div>
            <div class="obe-details">
                <div class="detail-item">
                    <span>CPL Covered</span>
                    <strong>12 / 14</strong>
                </div>
                <div class="detail-item">
                    <span>CPMK Linked</span>
                    <strong>45 / 50</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Cetak Laporan Berkala</div>
        <form class="laporan-form">
            <div class="form-group">
                <label>Pilih Jenis Rekapitulasi</label>
                <select class="form-control">
                    <option>Rekapitulasi RPS per Dosen</option>
                    <option>Mapping CPL vs CPMK (Matrix)</option>
                    <option>Statistik Capaian Pembelajaran</option>
                </select>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-danger"><i class="fas fa-file-pdf"></i> PDF</button>
                <button type="button" class="btn btn-success"><i class="fas fa-file-excel"></i> Excel</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-title">Progress RPS Dosen (Semester Ini)</div>
    <div class="progress-table">
        <div class="dosen-row">
            <div class="dosen-info">
                 <strong>Dr. Hendrawan</strong>
                 <span>3 MK Diajar</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar"><div class="progress" style="width: 100%;"></div></div>
                <small>100% Selesai</small>
            </div>
        </div>
        <div class="dosen-row">
            <div class="dosen-info">
                 <strong>Siti Aminah, M.Kom.</strong>
                 <span>2 MK Diajar</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar"><div class="progress" style="width: 50%;"></div></div>
                <small>50% Selesai</small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
