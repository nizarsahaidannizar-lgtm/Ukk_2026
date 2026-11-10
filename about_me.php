<?php
// Jika variabel $page belum diset di halaman utama, beri nilai default
$page = isset($page) ? $page : '';
?>

<!-- SIDEBAR -->
<nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse min-vh-100">
    <div class="position-sticky pt-3">
        <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none px-3">
            <span class="fs-6 fw-bold">Sistem Pelanggaran Siswa</span>
        </a>
        <hr class="text-secondary">
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($page == 'dashboard') ? 'active' : 'text-white-50' ?>" href="dashboard.php">
                    <i class="fa-solid fa-gauge me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($page == 'siswa') ? 'active' : 'text-white-50' ?>" href="siswa.php">
                    <i class="fa-solid fa-users me-2"></i> Data Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($page == 'guru') ? 'active' : 'text-white-50' ?>" href="guru.php">
                    <i class="fa-solid fa-chalkboard-user me-2"></i> Data Guru
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($page == 'kelas') ? 'active' : 'text-white-50' ?>" href="kelas.php">
                    <i class="fa-solid fa-door-open me-2"></i> Data Kelas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($page == 'tahun_ajaran') ? 'active' : 'text-white-50' ?>" href="tahun_ajaran.php">
                    <i class="fa-solid fa-calendar-days me-2"></i> Data Tahun Ajaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($page == 'wali_kelas') ? 'active' : 'text-white-50' ?>" href="wali_kelas.php">
                    <i class="fa-solid fa-user-tie me-2"></i> Data Wali Kelas
                </a>
            </li>
            <!-- Menu Penempatan Siswa -->
            <li class="nav-item">
                <a class="nav-link <?= ($page == 'penempatan_siswa') ? 'active' : 'text-white-50' ?>" href="penempatan_siswa.php">
                    <i class="fa-solid fa-right-left me-2"></i> Penempatan Siswa
                </a>
            </li>
        </ul>

        <hr class="text-secondary">

        <ul class="nav flex-column mb-auto">
            <li class="nav-item">
                <a class="nav-link <?= ($page == 'about_me') ? 'active' : 'text-white-50' ?>" href="about_me.php">
                    <i class="fa-solid fa-user me-2"></i> About Me
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>