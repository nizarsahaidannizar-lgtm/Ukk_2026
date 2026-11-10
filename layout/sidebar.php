<?php
// layout/sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
// Jika role session belum diset, default ke 'admin'
$role = $_SESSION['role'] ?? 'admin'; 

// Fungsi bantuan untuk menandai menu aktif
function is_active($pages, $current_page) {
    $pages_array = (array)$pages;
    return in_array($current_page, $pages_array, true) ? 'active bg-primary text-white' : 'text-white-50';
}

// Deteksi otomatis jika sidebar dipanggil dari folder guru/
$prefix = (strpos($_SERVER['PHP_SELF'], '/guru/') !== false) ? '../' : '';
?>

<!-- SIDEBAR CONTAINER -->
<div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3 text-white sidebar-sticky">
    
    <!-- Brand / Header Logo -->
    <div class="sidebar-brand d-flex align-items-center gap-2 mb-4">
        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
            <i class="fa-solid fa-school text-dark fs-5"></i>
        </div>
        <h5 class="text-white mb-0 fs-6 fw-bold">Sistem Pelanggaran<span class="d-block text-white-50 fs-7" style="font-size: 11px;">Siswa</span></h5>
    </div>

    <hr class="border-secondary my-3">

    <!-- MENU BERDASARKAN ROLE -->
    <ul class="nav nav-pills flex-column mb-auto">
        
        <?php if ($role === 'admin'): ?>
            <!-- ================= MENU ADMIN ================= -->
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>dashboard.php" class="nav-link <?= is_active(['dashboard.php'], $current_page) ?>">
                    <i class="fa-solid fa-gauge me-2 width-fixed"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>guru/kelola_guru.php" class="nav-link <?= is_active(['kelola_guru.php', 'tambah_guru.php', 'edit_guru.php'], $current_page) ?>">
                    <i class="fa-solid fa-chalkboard-user me-2 width-fixed"></i> <span>Kelola Guru</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>kelola_siswa.php" class="nav-link <?= is_active(['kelola_siswa.php', 'tambah_siswa.php', 'edit_siswa.php'], $current_page) ?>">
                    <i class="fa-solid fa-users me-2 width-fixed"></i> <span>Kelola Siswa</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>kelola_kelas.php" class="nav-link <?= is_active(['kelola_kelas.php', 'tambah_kelas.php', 'edit_kelas.php'], $current_page) ?>">
                    <i class="fa-solid fa-door-open me-2 width-fixed"></i> <span>Kelola Kelas</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>kelola_tahun_ajaran.php" class="nav-link <?= is_active(['kelola_tahun_ajaran.php', 'tambah_tahun_ajaran.php', 'edit_tahun_ajaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-calendar-days me-2 width-fixed"></i> <span>Kelola Tahun Ajaran</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>penempatan_siswa.php" class="nav-link <?= is_active(['penempatan_siswa.php'], $current_page) ?>">
                    <i class="fa-solid fa-right-left me-2 width-fixed"></i> <span>Penempatan Siswa</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>kelola_walikelas.php" class="nav-link <?= is_active(['kelola_walikelas.php', 'tambah_walikelas.php', 'edit_walikelas.php'], $current_page) ?>">
                    <i class="fa-solid fa-user-tie me-2 width-fixed"></i> <span>Kelola Wali Kelas</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>kelola_pelanggaran_kategori.php" class="nav-link <?= is_active(['kelola_pelanggaran_kategori.php', 'tambah_pelanggaran_kategori.php', 'edit_pelanggaran_kategori.php'], $current_page) ?>">
                    <i class="fa-solid fa-layer-group me-2 width-fixed"></i> <span>Kelola Kategori Pelanggaran</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>kelola_jenis_pelanggaran.php" class="nav-link <?= is_active(['kelola_jenis_pelanggaran.php', 'tambah_jenis_pelanggaran.php', 'edit_jenis_pelanggaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-list-check me-2 width-fixed"></i> <span>Kelola Jenis Pelanggaran</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>catat_pelanggaran.php" class="nav-link <?= is_active(['catat_pelanggaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-triangle-exclamation me-2 width-fixed"></i> <span>Catat Pelanggaran</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>laporan.php" class="nav-link <?= is_active(['laporan.php', 'cetak_laporan.php'], $current_page) ?>">
                    <i class="fa-solid fa-file-pdf me-2 width-fixed"></i> <span>Laporan / Cetak / Eksport</span>
                </a>
            </li>

        <?php else: ?>
            <!-- ================= MENU GURU / WALI KELAS ================= -->
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>guru/dashboard_guru.php" class="nav-link <?= is_active(['dashboard_guru.php', 'dashboard.php'], $current_page) ?>">
                    <i class="fa-solid fa-gauge me-2 width-fixed"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>guru/catat_pelanggaran.php" class="nav-link <?= is_active(['catat_pelanggaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-triangle-exclamation me-2 width-fixed"></i> <span>Catat Pelanggaran</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>guru/tindakan_guru.php" class="nav-link <?= is_active(['tindakan_guru.php'], $current_page) ?>">
                    <i class="fa-solid fa-hand-holding-hand me-2 width-fixed"></i> <span>Tindakan</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>guru/laporan_guru.php" class="nav-link <?= is_active(['laporan_guru.php', 'laporan.php'], $current_page) ?>">
                    <i class="fa-solid fa-file-pdf me-2 width-fixed"></i> <span>Laporan</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>guru/riwayat_guru.php" class="nav-link <?= is_active(['riwayat_guru.php'], $current_page) ?>">
                    <i class="fa-solid fa-clock-rotate-left me-2 width-fixed"></i> <span>Riwayat</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= $prefix ?>guru/rekap_poin.php" class="nav-link <?= is_active(['rekap_poin.php'], $current_page) ?>">
                    <i class="fa-solid fa-chart-simple me-2 width-fixed"></i> <span>Rekap Poin</span>
                </a>
            </li>
        <?php endif; ?>

    </ul>

    <hr class="border-secondary my-3">

    <!-- MENU FOOTER (ABOUT ME & LOGOUT) -->
    <ul class="nav nav-pills flex-column">
        <li class="nav-item mb-1">
            <a href="<?= $prefix ?>about_me.php" class="nav-link <?= is_active(['about_me.php'], $current_page) ?>">
                <i class="fa-solid fa-user me-2 width-fixed"></i> <span>About Me</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= $prefix ?>logout.php" class="nav-link text-danger" onclick="return confirm('Yakin ingin keluar?');">
                <i class="fa-solid fa-right-from-bracket me-2 width-fixed"></i> <span>Logout</span>
            </a>
        </li>
    </ul>

</div>