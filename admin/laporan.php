<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <h2>Laporan & Analitik</h2>
    <p>Lihat rekapitulasi data dan analisis kesesuaian kurikulum RPS.</p>
</div>

<div class="grid-laporan">
    <div class="card">
        <div class="card-title">Capaian RPS per Semester</div>
        <div class="chart-placeholder">
            <i class="fas fa-chart-line fa-3x"></i>
            <p>Grafik Capaian RPS akan muncul di sini</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-title">Generate Laporan</div>
        <form class="report-form">
            <div class="form-group">
                <label>Pilih Jenis Laporan</label>
                <select class="form-control">
                    <option>Rekapitulasi RPS per Dosen</option>
                    <option>Rekapitulasi RPS per Mata Kuliah</option>
                    <option>Analisis Kesesuaian OBE / KKNI</option>
                    <option>Statistik Capaian CPL</option>
                </select>
            </div>
            <div class="form-group">
                <label>Semester</label>
                <select class="form-control">
                    <option>Gasal 2023/2024</option>
                    <option>Genap 2022/2023</option>
                </select>
            </div>
            <div class="report-actions">
                <button type="button" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Export PDF</button>
                <button type="button" class="btn btn-secondary"><i class="fas fa-file-excel"></i> Export Excel</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-title">Analisis OBE (Outcome Based Education)</div>
    <div class="obe-status">
        <div class="obe-item">
            <span>Pemenuhan CPL</span>
            <div class="progress-bar"><div class="progress" style="width: 75%;"></div></div>
            <span>75%</span>
        </div>
        <div class="obe-item">
            <span>Kesesuaian BK (Bahan Kajian)</span>
            <div class="progress-bar"><div class="progress" style="width: 90%;"></div></div>
            <span>90%</span>
        </div>
        <div class="obe-item">
            <span>Integrasi CPMK</span>
            <div class="progress-bar"><div class="progress" style="width: 60%;"></div></div>
            <span>60%</span>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
