<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-university"></i>
            <span>RPS System</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo $active_page == 'dashboard' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?php echo $active_page == 'manajemen_user' ? 'active' : ''; ?>">
                <a href="manajemen_user.php"><i class="fas fa-users"></i> <span>Manajemen User</span></a>
            </li>
            <li class="<?php echo $active_page == 'data_master' ? 'active' : ''; ?>">
                <a href="data_master.php"><i class="fas fa-database"></i> <span>Data Master</span></a>
            </li>
            <li class="<?php echo $active_page == 'manajemen_rps' ? 'active' : ''; ?>">
                <a href="manajemen_rps.php"><i class="fas fa-file-contract"></i> <span>Manajemen RPS</span></a>
            </li>
            <li class="<?php echo $active_page == 'laporan' ? 'active' : ''; ?>">
                <a href="laporan.php"><i class="fas fa-chart-bar"></i> <span>Laporan & Analitik</span></a>
            </li>
            <li class="<?php echo $active_page == 'profil' ? 'active' : ''; ?>">
                <a href="profil.php"><i class="fas fa-user-circle"></i> <span>Profil</span></a>
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
            <span>Halo, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong> (Admin)</span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_lengkap']); ?>&background=4f46e5&color=fff" alt="User">
        </div>
    </header>
    <div class="content-body">
