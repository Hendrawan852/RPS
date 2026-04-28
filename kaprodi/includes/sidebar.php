<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
            <span>Kaprodi Panel</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo $active_page == 'dashboard' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?php echo $active_page == 'persetujuan_rps' ? 'active' : ''; ?>">
                <a href="persetujuan_rps.php"><i class="fas fa-check-double"></i> <span>Persetujuan RPS</span></a>
            </li>
            <li class="<?php echo $active_page == 'manajemen' ? 'active' : ''; ?>">
                <a href="manajemen.php"><i class="fas fa-file-invoice"></i> <span>Manajemen RPS</span></a>
            </li>
            <li class="<?php echo $active_page == 'data_master' ? 'active' : ''; ?>">
                <a href="data_master.php"><i class="fas fa-database"></i> <span>Data Master</span></a>
            </li>
            <li class="<?php echo $active_page == 'laporan&analitik' ? 'active' : ''; ?>">
                <a href="laporan&analitik.php"><i class="fas fa-chart-line"></i> <span>Laporan & Analitik</span></a>
            </li>
            <li class="<?php echo $active_page == 'manajemen_dosen' ? 'active' : ''; ?>">
                <a href="manajemen_dosen.php"><i class="fas fa-chalkboard-teacher"></i> <span>Manajemen Dosen</span></a>
            </li>
            <li class="<?php echo ($active_page == 'Profil' || $active_page == 'profil') ? 'active' : ''; ?>">
                <a href="Profil.php"><i class="fas fa-user-gear"></i> <span>Profil</span></a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </div>
</aside>

<main class="main-content">
    <header class="top-bar">
        <div class="toggle-sidebar">
            <i class="fas fa-bars"></i>
        </div>
        <div class="user-info">
            <span>Kaprodi: <strong><?php echo $_SESSION['nama_lengkap']; ?></strong></span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_lengkap']); ?>&background=0f172a&color=fff" alt="User">
        </div>
    </header>
    <div class="content-body">
