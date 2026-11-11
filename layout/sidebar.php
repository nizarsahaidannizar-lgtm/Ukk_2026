
<?php
// layout/sidebar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'admin';

// Menentukan lokasi folder
$prefix = (strpos($_SERVER['PHP_SELF'], '/guru/') !== false)
    ? '../'
    : '';

// Menentukan menu aktif
function is_active($pages, $current_page) {
    $pages_array = (array) $pages;

    if (in_array($current_page, $pages_array, true)) {
        return 'active';
    }

    return '';
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>

/* SIDEBAR UTAMA */
.sidebar-fixed {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 240px !important;
    height: 100vh;
    background-color: #212529 !important;
    padding: 12px 10px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1030;
    box-sizing: border-box;
}

.sidebar-fixed::-webkit-scrollbar {
    width: 4px;
}

.sidebar-fixed::-webkit-scrollbar-thumb {
    background: #495057;
    border-radius: 4px;
}

/* LOGO SIP */
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
    flex-shrink: 0;
}

.sidebar-brand .logo-icon svg {
    width: 55px;
    height: 48px;
    display: block;
}

.sidebar-brand .brand-title {
    color: #ffffff;
    font-size: 23px;
    font-weight: 800;
    line-height: 1.1;
    margin: 2px 0 0;
}

.sidebar-brand .brand-subtitle {
    display: block;
    color: #adb5bd;
    font-size: 9px;
    line-height: 1.3;
    text-align: center;
}

/* MENU SIDEBAR */
.sidebar-menu {
    width: 100%;
}

.sidebar-menu .nav-item {
    width: 100%;
}

.sidebar-menu .nav-link {
    width: 100%;
    box-sizing: border-box;
    padding: 9px 12px;
    font-size: 13px;
    color: #ffffff !important;
    border-radius: 8px;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    white-space: nowrap;
    transition: background-color 0.2s ease;
    background-color: transparent;
}

/* HOVER MENU */
.sidebar-menu .nav-link:hover:not(.active) {
    background-color: rgba(255,255,255,0.10) !important;
    color: #ffffff !important;
}

/* MENU YANG SEDANG AKTIF */
.sidebar-menu .nav-link.active,
.sidebar-menu .nav-link.active:hover {
    background-color: #0d6efd !important;
    color: #ffffff !important;
    font-weight: 600;
}

/* IKON MENU */
.sidebar-menu .nav-link i {
    width: 22px;
    min-width: 22px;
    text-align: center;
    font-size: 13.5px;
    color: #ffffff !important;
}

/* LOGOUT */
.sidebar-menu .nav-link.text-danger {
    color: #dc3545 !important;
    background-color: transparent !important;
}

.sidebar-menu .nav-link.text-danger i {
    color: #dc3545 !important;
}

/* KONTEN UTAMA */
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

<!-- SIDEBAR -->
<nav class="sidebar-fixed text-white">

    <!-- LOGO SIP -->
    <div class="sidebar-brand mb-2">

        <div class="logo-icon">

            <svg viewBox="0 0 128 104"
                 xmlns="http://www.w3.org/2000/svg"
                 role="img"
                 aria-label="Logo SIP">

                <path
                    d="M8 33 L64 0 L120 33
                       V88 Q120 98 110 98
                       H78 V75
                       Q78 62 64 62
                       Q50 62 50 75
                       V98 H18
                       Q8 98 8 88 Z"
                    fill="#ffffff"/>

                <rect x="23" y="40" width="13" height="19" rx="4" fill="#212529"/>
                <rect x="23" y="66" width="13" height="19" rx="4" fill="#212529"/>
                <rect x="92" y="40" width="13" height="19" rx="4" fill="#212529"/>
                <rect x="92" y="66" width="13" height="19" rx="4" fill="#212529"/>

                <circle cx="64" cy="33" r="17"
                        fill="#212529"
                        stroke="#ffffff"
                        stroke-width="3"/>

                <path d="M64 24 V33 H71"
                      fill="none"
                      stroke="#ffffff"
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

    <!-- MENU UTAMA -->
    <ul class="nav nav-pills flex-column sidebar-menu mb-auto">

        <?php if ($role === 'admin'): ?>

            <!-- DASHBOARD -->
            <li class="nav-item">
                <a href="<?= $prefix ?>dashboard.php"
                   class="nav-link <?= is_active('dashboard.php', $current_page) ?>">
                    <i class="fa-solid fa-gauge me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- KELOLA GURU -->
            <li class="nav-item">
                <a href="<?= $prefix ?>guru/kelola_guru.php"
                   class="nav-link <?= is_active(['kelola_guru.php','tambah_guru.php','edit_guru.php'], $current_page) ?>">
                    <i class="fa-solid fa-chalkboard-user me-2"></i>
                    <span>Kelola Guru</span>
                </a>
            </li>

            <!-- KELOLA SISWA -->
            <li class="nav-item">
                <a href="<?= $prefix ?>kelola_siswa.php"
                   class="nav-link <?= is_active(['kelola_siswa.php','tambah_siswa.php','edit_siswa.php'], $current_page) ?>">
                    <i class="fa-solid fa-users me-2"></i>
                    <span>Kelola Siswa</span>
                </a>
            </li>

            <!-- KELOLA KELAS -->
            <li class="nav-item">
                <a href="<?= $prefix ?>kelola_kelas.php"
                   class="nav-link <?= is_active(['kelola_kelas.php','tambah_kelas.php','edit_kelas.php'], $current_page) ?>">
                    <i class="fa-solid fa-door-open me-2"></i>
                    <span>Kelola Kelas</span>
                </a>
            </li>

            <!-- TAHUN AJARAN -->
            <li class="nav-item">
                <a href="<?= $prefix ?>kelola_tahun_ajaran.php"
                   class="nav-link <?= is_active(['kelola_tahun_ajaran.php','tambah_tahun_ajaran.php','edit_tahun_ajaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-calendar-days me-2"></i>
                    <span>Kelola Tahun Ajaran</span>
                </a>
            </li>

            <!-- PENEMPATAN SISWA -->
            <li class="nav-item">
                <a href="<?= $prefix ?>penempatan_siswa.php"
                   class="nav-link <?= is_active([
                       'penempatan_siswa.php',
                       'edit_penempatan.php',
                       'edit_penempatan_siswa.php'
                   ], $current_page) ?>">
                    <i class="fa-solid fa-right-left me-2"></i>
                    <span>Penempatan Siswa</span>
                </a>
            </li>

            <!-- WALI KELAS -->
            <li class="nav-item">
                <a href="<?= $prefix ?>kelola_walikelas.php"
                   class="nav-link <?= is_active(['kelola_walikelas.php','tambah_walikelas.php','edit_walikelas.php'], $current_page) ?>">
                    <i class="fa-solid fa-user-tie me-2"></i>
                    <span>Kelola Wali Kelas</span>
                </a>
            </li>

            <!-- KATEGORI PELANGGARAN -->
            <li class="nav-item">
                <a href="<?= $prefix ?>kelola_pelanggaran_kategori.php"
                   class="nav-link <?= is_active(['kelola_pelanggaran_kategori.php','tambah_pelanggaran_kategori.php','edit_pelanggaran_kategori.php'], $current_page) ?>">
                    <i class="fa-solid fa-layer-group me-2"></i>
                    <span>Kategori Pelanggaran</span>
                </a>
            </li>

            <!-- JENIS PELANGGARAN -->
            <li class="nav-item">
                <a href="<?= $prefix ?>kelola_jenis_pelanggaran.php"
                   class="nav-link <?= is_active(['kelola_jenis_pelanggaran.php','tambah_jenis_pelanggaran.php','edit_jenis_pelanggaran.php'], $current_page) ?>">
                    <i class="fa-solid fa-list-check me-2"></i>
                    <span>Jenis Pelanggaran</span>
                </a>
            </li>

            <!-- LAPORAN -->
            <li class="nav-item">
                <a href="<?= $prefix ?>laporan.php"
                   class="nav-link <?= is_active(['laporan.php','cetak_laporan.php'], $current_page) ?>">
                    <i class="fa-solid fa-file-pdf me-2"></i>
                    <span>Laporan</span>
                </a>
            </li>

        <?php else: ?>

            <!-- DASHBOARD GURU -->
            <li class="nav-item">
                <a href="<?= $prefix ?>guru/dashboard_guru.php"
                   class="nav-link <?= is_active(['dashboard_guru.php','dashboard.php'], $current_page) ?>">
                    <i class="fa-solid fa-gauge me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- CATAT PELANGGARAN -->
            <li class="nav-item">
                <a href="<?= $prefix ?>catat_pelanggaran.php"
                   class="nav-link <?= is_active('catat_pelanggaran.php', $current_page) ?>">
                    <i class="fa-solid fa-pen-to-square me-2"></i>
                    <span>Catat Pelanggaran</span>
                </a>
            </li>

            <!-- TINDAKAN -->
            <li class="nav-item">
                <a href="<?= $prefix ?>guru/tindakan_guru.php"
                   class="nav-link <?= is_active(['tindakan.php','tindakan_guru.php'], $current_page) ?>">
                    <i class="fa-solid fa-handshake-angle me-2"></i>
                    <span>Tindakan</span>
                </a>
            </li>

            <!-- LAPORAN GURU -->
            <li class="nav-item">
                <a href="<?= $prefix ?>guru/laporan_guru.php"
                   class="nav-link <?= is_active(['laporan_guru.php','laporan.php','laporan_walas.php'], $current_page) ?>">
                    <i class="fa-solid fa-file-pdf me-2"></i>
                    <span>Laporan</span>
                </a>
            </li>

            <!-- RIWAYAT -->
            <li class="nav-item">
                <a href="<?= $prefix ?>guru/riwayat_guru.php"
                   class="nav-link <?= is_active(['riwayat.php','riwayat_guru.php'], $current_page) ?>">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>
                    <span>Riwayat</span>
                </a>
            </li>

            <!-- REKAP POIN -->
            <li class="nav-item">
                <a href="<?= $prefix ?>guru/rekap_poin.php"
                   class="nav-link <?= is_active('rekap_poin.php', $current_page) ?>">
                    <i class="fa-solid fa-list-check me-2"></i>
                    <span>Rekap Poin</span>
                </a>
            </li>

        <?php endif; ?>

    </ul>

    <hr class="border-secondary my-2">

    <!-- MENU FOOTER -->
    <ul class="nav nav-pills flex-column sidebar-menu mb-1">

        <!-- ABOUT ME -->
        <li class="nav-item">
            <a href="<?= $prefix ?>about_me.php"
               class="nav-link <?= is_active('about_me.php', $current_page) ?>">
                <i class="fa-solid fa-user me-2"></i>
                <span>About Me</span>
            </a>
        </li>

        <!-- LOGOUT -->
        <li class="nav-item">
            <a href="<?= $prefix ?>logout.php"
               class="nav-link text-danger"
               onclick="return confirm('Yakin ingin keluar?');">
                <i class="fa-solid fa-right-from-bracket me-2"></i>
                <span>Logout</span>
            </a>
        </li>

    </ul>

</nav>
