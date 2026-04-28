<aside class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-university logo-icon"></i>
        <span>RPS System</span>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> <span>Dashboard</span>
        </a>
        <a href="buat_rps_baru.php" class="<?php echo ($current_page == 'buat_rps_baru') ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i> <span>Buat RPS Baru</span>
        </a>
        <a href="daftar_rps.php" class="<?php echo ($current_page == 'daftar_rps') ? 'active' : ''; ?>">
            <i class="fas fa-list-alt"></i> <span>Daftar RPS Saya</span>
        </a>
        <a href="rps_yang_perlu_direvisi.php" class="<?php echo ($current_page == 'rps_yang_perlu_direvisi') ? 'active' : ''; ?>">
            <i class="fas fa-exclamation-triangle"></i> <span>Perlu Revisi</span>
        </a>
        <a href="data_master.php" class="<?php echo ($current_page == 'data_master') ? 'active' : ''; ?>">
            <i class="fas fa-database"></i> <span>Data Master</span>
        </a>
        <a href="arsip.php" class="<?php echo ($current_page == 'arsip') ? 'active' : ''; ?>">
            <i class="fas fa-archive"></i> <span>Arsip & Referensi</span>
        </a>
        <a href="Profil.php" class="<?php echo ($current_page == 'Profil') ? 'active' : ''; ?>">
            <i class="fas fa-user-cog"></i> <span>Profil</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="../auth/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="toggle-sidebar"><i class="fas fa-bars"></i></div>
        <div class="user-profile">
            <span>Halo, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong> (Dosen)</span>
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 2)); ?></div>
        </div>
    </header>
    <div class="content-wrapper">
