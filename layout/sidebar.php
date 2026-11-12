
<?php
// layout/sidebar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// INFORMASI LOGIN
// ============================================

$current_page = basename($_SERVER['PHP_SELF'] ?? '');
$role = strtolower(trim($_SESSION['role'] ?? ''));

$base_url = '/Ukk_2026/';

// Normalisasi role wali kelas
$role_wali = in_array($role, [
    'guru_wali_kelas',
    'wali_kelas',
    'walikelas',
    'guru wali kelas'
], true);

$role_guru = ($role === 'guru');

// ============================================
// FUNGSI MENU AKTIF
// ============================================

if (!function_exists('is_active')) {
    function is_active($pages, $current_page) {
        return in_array($current_page, (array)$pages, true)
            ? 'active'
            : '';
    }
}
?>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>

/* SIDEBAR */

.sidebar-fixed {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    bottom: 0 !important;

    width: 240px !important;
    height: 100vh !important;

    background: #212529 !important;

    padding: 12px 10px;

    display: flex;
    flex-direction: column;

    overflow-y: auto;
    overflow-x: hidden;

    z-index: 2147483647 !important;

    box-sizing: border-box;
    pointer-events: auto !important;
    isolation: isolate;
}

.sidebar-fixed::-webkit-scrollbar {
    width: 4px;
}

.sidebar-fixed::-webkit-scrollbar-thumb {
    background: #495057;
    border-radius: 4px;
}

/* LOGO */

.sidebar-brand {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 8px 5px;
    gap: 3px;
}

.sidebar-brand .logo-icon {
    width: 55px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-brand .logo-icon svg {
    width: 55px;
    height: 48px;
}

.sidebar-brand .brand-title {
    color: #fff;
    font-size: 23px;
    font-weight: 800;
    line-height: 1.1;
    margin: 2px 0 0;
}

.sidebar-brand .brand-subtitle {
    color: #adb5bd;
    font-size: 9px;
}

/* MENU */

.sidebar-menu,
.sidebar-menu .nav-item {
    width: 100%;
}

.sidebar-menu .nav-link {
    width: 100%;
    box-sizing: border-box;

    padding: 9px 12px;

    font-size: 13px;
    color: #fff !important;

    border-radius: 8px;
    margin-bottom: 3px;

    display: flex;
    align-items: center;

    white-space: nowrap;

    background: transparent;

    text-decoration: none !important;

    position: relative;
    z-index: 2;

    cursor: pointer !important;
    pointer-events: auto !important;
}

.sidebar-menu .nav-link:hover:not(.active) {
    background-color: rgba(255,255,255,.10) !important;
}

.sidebar-menu .nav-link.active {
    background-color: #0d6efd !important;
    font-weight: 600;
}

.sidebar-menu .nav-link i {
    width: 22px;
    min-width: 22px;
    text-align: center;
    font-size: 13.5px;
    color: #fff !important;
}

.sidebar-menu .nav-link.text-danger,
.sidebar-menu .nav-link.text-danger i {
    color: #dc3545 !important;
}

/* KONTEN */

body {
    overflow-x: hidden;
    margin: 0;
}

.main-content,
main,
.container-fluid.main-container {
    margin-left: 240px !important;
    width: calc(100% - 240px) !important;
    min-height: 100vh;
    padding: 25px 30px;
    box-sizing: border-box;
}

/* RESPONSIVE */

@media (max-width: 768px) {

    .sidebar-fixed {
        width: 200px !important;
    }

    .main-content,
    main,
    .container-fluid.main-container {
        margin-left: 200px !important;
        width: calc(100% - 200px) !important;
        padding: 15px;
    }
}

</style>

<!-- ========================================
     SIDEBAR
======================================== -->

<nav class="sidebar-fixed text-white">

    <!-- LOGO -->

    <div class="sidebar-brand mb-2">

        <div class="logo-icon">

            <svg viewBox="0 0 128 104"
                 xmlns="http://www.w3.org/2000/svg">

                <path d="M8 33 L64 0 L120 33 V88 Q120 98 110 98 H78 V75 Q78 62 64 62 Q50 62 50 75 V98 H18 Q8 98 8 88 Z"
                      fill="#fff"/>

                <rect x="23" y="40" width="13" height="19" rx="4" fill="#212529"/>
                <rect x="23" y="66" width="13" height="19" rx="4" fill="#212529"/>

                <rect x="92" y="40" width="13" height="19" rx="4" fill="#212529"/>
                <rect x="92" y="66" width="13" height="19" rx="4" fill="#212529"/>

                <circle cx="64" cy="33" r="17"
                        fill="#212529"
                        stroke="#fff"
                        stroke-width="3"/>

                <path d="M64 24 V33 H71"
                      fill="none"
                      stroke="#fff"
                      stroke-width="4"
                      stroke-linecap="round"
                      stroke-linejoin="round"/>

            </svg>

        </div>

        <div>
            <h2 class="brand-title">SIP</h2>
            <small class="brand-subtitle">
                Sistem Informasi Pelanggaran
            </small>
        </div>

    </div>

    <hr class="border-secondary my-2">

    <!-- ====================================
         MENU ADMIN
    ===================================== -->

    <?php if ($role === 'admin'): ?>

        <ul class="nav nav-pills flex-column sidebar-menu mb-auto">

            <li class="nav-item">
                <a href="<?= $base_url ?>dashboard.php"
                   class="nav-link <?= is_active('dashboard.php', $current_page) ?>">
                    <i class="fa-solid fa-gauge me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>guru/kelola_guru.php"
                   class="nav-link <?= is_active(['kelola_guru.php','tambah_guru.php','edit_guru.php'], $current_page) ?>">
                    <i class="fa-solid fa-chalkboard-user me-2"></i>
                    <span>Kelola Guru</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>kelola_siswa.php"
                   class="nav-link <?= is_active(['kelola_siswa.php','tambah_siswa.php','edit_siswa.php'], $current_page) ?>">
                    <i class="fa-solid fa-users me-2"></i>
                    <span>Kelola Siswa</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>kelola_kelas.php"
                   class="nav-link <?= is_active(['kelola_kelas.php','tambah_kelas.php','edit_kelas.php'], $current_page) ?>">
                    <i class="fa-solid fa-door-open me-2"></i>
                    <span>Kelola Kelas</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>kelola_tahun_ajaran.php"
                   class="nav-link <?= is_active(['kelola_tahun_ajaran.php','tambah_tahun_ajaran.php','edit_tahun_ajaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-calendar-days me-2"></i>
                    <span>Kelola Tahun Ajaran</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>penempatan_siswa.php"
                   class="nav-link <?= is_active('penempatan_siswa.php', $current_page) ?>">
                    <i class="fa-solid fa-right-left me-2"></i>
                    <span>Penempatan Siswa</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>kelola_walikelas.php"
                   class="nav-link <?= is_active(['kelola_walikelas.php','tambah_walikelas.php','edit_walikelas.php'], $current_page) ?>">
                    <i class="fa-solid fa-user-tie me-2"></i>
                    <span>Kelola Wali Kelas</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>kelola_pelanggaran_kategori.php"
                   class="nav-link <?= is_active(['kelola_pelanggaran_kategori.php','tambah_pelanggaran_kategori.php','edit_pelanggaran_kategori.php'], $current_page) ?>">
                    <i class="fa-solid fa-layer-group me-2"></i>
                    <span>Kategori Pelanggaran</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>kelola_jenis_pelanggaran.php"
                   class="nav-link <?= is_active(['kelola_jenis_pelanggaran.php','tambah_jenis_pelanggaran.php','edit_jenis_pelanggaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-list-check me-2"></i>
                    <span>Jenis Pelanggaran</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>laporan.php"
                   class="nav-link <?= is_active(['laporan.php','cetak_laporan.php'], $current_page) ?>">
                    <i class="fa-solid fa-file-pdf me-2"></i>
                    <span>Laporan</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?= $base_url ?>riwayat.php"
                   class="nav-link <?= is_active('riwayat.php', $current_page) ?>">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>
                    <span>Riwayat</span>
                </a>
            </li>

        </ul>

    <!-- ====================================
         MENU GURU DAN WALI KELAS
    ===================================== -->

    <?php elseif ($role_guru || $role_wali): ?>

        <ul class="nav nav-pills flex-column sidebar-menu mb-auto">

            <!-- DASHBOARD -->

            <li class="nav-item">
                <a href="<?= $base_url ?>guru/dashboard_guru.php"
                   class="nav-link <?= is_active('dashboard_guru.php', $current_page) ?>">
                    <i class="fa-solid fa-gauge me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- CATAT PELANGGARAN -->

            <li class="nav-item">
                <a href="<?= $base_url ?>guru/catat_pelanggaran.php"
                   class="nav-link <?= is_active(['catat_pelanggaran.php','edit_catatan_pelanggaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-pen-to-square me-2"></i>
                    <span>Catat Pelanggaran</span>
                </a>
            </li>

            <!-- TINDAKAN -->

            <li class="nav-item">
                <a href="<?= $base_url ?>guru/tindakan_guru.php"
                   class="nav-link <?= is_active('tindakan_guru.php', $current_page) ?>">
                    <i class="fa-solid fa-handshake-angle me-2"></i>
                    <span>Tindakan</span>
                </a>
            </li>

            <!-- LAPORAN HANYA GURU BIASA -->

            <?php if ($role_guru): ?>

                <li class="nav-item">
                    <a href="<?= $base_url ?>guru/laporan_guru.php"
                       class="nav-link <?= is_active('laporan_guru.php', $current_page) ?>">
                        <i class="fa-solid fa-file-pdf me-2"></i>
                        <span>Laporan</span>
                    </a>
                </li>

            <?php endif; ?>

            <!-- RIWAYAT -->

            <li class="nav-item">
                <a href="<?= $base_url ?>guru/riwayat_guru.php"
                   class="nav-link <?= is_active('riwayat_guru.php', $current_page) ?>">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>
                    <span>Riwayat</span>
                </a>
            </li>

            <!-- REKAP POIN -->

            <li class="nav-item">
                <a href="<?= $base_url ?>guru/rekap_poin.php"
                   class="nav-link <?= is_active('rekap_poin.php', $current_page) ?>">
                    <i class="fa-solid fa-list-check me-2"></i>
                    <span>Rekap Poin</span>
                </a>
            </li>

            <!-- KELAS SAYA KHUSUS WALI KELAS -->

            <?php if ($role_wali): ?>

                <li class="nav-item">
                    <a href="<?= $base_url ?>guru/kelola_kelas_walas.php"
                       class="nav-link <?= is_active('kelola_kelas_walas.php', $current_page) ?>">
                        <i class="fa-solid fa-users-rectangle me-2"></i>
                        <span>Kelas Saya</span>
                    </a>
                </li>

            <?php endif; ?>

        </ul>

    <?php endif; ?>

    <!-- ====================================
         MENU BAWAH
    ===================================== -->

    <hr class="border-secondary my-2">

    <ul class="nav nav-pills flex-column sidebar-menu mb-1">

        <li class="nav-item">
            <a href="<?= $base_url ?>about_me.php"
               class="nav-link <?= is_active('about_me.php', $current_page) ?>">
                <i class="fa-solid fa-user me-2"></i>
                <span>About Me</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= $base_url ?>logout.php"
               class="nav-link text-danger"
               onclick="return confirm('Yakin ingin keluar?');">
                <i class="fa-solid fa-right-from-bracket me-2"></i>
                <span>Logout</span>
            </a>
        </li>

    </ul>

</nav>
