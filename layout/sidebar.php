
<?php
// layout/sidebar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = strtolower(
    pathinfo(basename($_SERVER['PHP_SELF'] ?? ''), PATHINFO_FILENAME)
);

$base_url = '/Ukk_2026/';

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (function_exists('normalisasi_role')) {
    $role = normalisasi_role($role);
}

$role_guru = ($role === 'guru');
$role_wali = ($role === 'guru_wali_kelas');

// ============================================
// FUNGSI MENU AKTIF
// ============================================

if (!function_exists('menu_aktif')) {

    function menu_aktif($menu, $current_page) {

        $menu = basename($menu);
        $menu = strtolower(pathinfo($menu, PATHINFO_FILENAME));

        $current_page = strtolower(
            pathinfo(basename($current_page), PATHINFO_FILENAME)
        );

        // ======================================
        // KELOMPOK HALAMAN MENU ADMIN
        // ======================================

        $kelompok_admin = [

            'dashboard' => [
                'dashboard'
            ],

            'kelola_guru' => [
                'kelola_guru',
                'tambah_guru',
                'edit_guru',
                'hapus_guru',
                'proses_tambah_guru',
                'proses_edit_guru',
                'proses_hapus_guru'
            ],

            'kelola_siswa' => [
                'kelola_siswa',
                'tambah_siswa',
                'edit_siswa',
                'hapus_siswa',
                'proses_tambah_siswa',
                'proses_edit_siswa',
                'proses_hapus_siswa'
            ],

            'kelola_kelas' => [
                'kelola_kelas',
                'tambah_kelas',
                'edit_kelas',
                'hapus_kelas',
                'proses_tambah_kelas',
                'proses_edit_kelas',
                'proses_hapus_kelas'
            ],

            'kelola_tahun_ajaran' => [
                'kelola_tahun_ajaran',
                'tambah_tahun_ajaran',
                'edit_tahun_ajaran',
                'hapus_tahun_ajaran',
                'proses_tambah_tahun_ajaran',
                'proses_edit_tahun_ajaran',
                'proses_hapus_tahun_ajaran'
            ],

            // ======================================
            // PENEMPATAN SISWA
            // ======================================

            'penempatan_siswa' => [
                'penempatan_siswa',
                'tambah_penempatan_siswa',
                'edit_penempatan_siswa',
                'hapus_penempatan_siswa',

                // NAMA FILE SESUAI PROJECT
                'edit_penempatan',
                'proses_simpan_penempatan',
                'proses_edit_penempatan',
                'proses_hapus_penempatan',

                // NAMA FILE TAMBAHAN
                'proses_penempatan_siswa',
                'proses_tambah_penempatan_siswa',
                'proses_edit_penempatan_siswa'
            ],

            'kelola_walikelas' => [
                'kelola_walikelas',
                'tambah_walikelas',
                'edit_walikelas',
                'hapus_walikelas',
                'proses_tambah_walikelas',
                'proses_edit_walikelas',
                'proses_hapus_walikelas'
            ],

            'kelola_pelanggaran_kategori' => [
                'kelola_pelanggaran_kategori',
                'tambah_pelanggaran_kategori',
                'edit_pelanggaran_kategori',
                'hapus_pelanggaran_kategori',
                'proses_tambah_pelanggaran_kategori',
                'proses_edit_pelanggaran_kategori',
                'proses_hapus_pelanggaran_kategori'
            ],

            'kelola_jenis_pelanggaran' => [
                'kelola_jenis_pelanggaran',
                'tambah_jenis_pelanggaran',
                'edit_jenis_pelanggaran',
                'hapus_jenis_pelanggaran',
                'proses_tambah_jenis_pelanggaran',
                'proses_edit_jenis_pelanggaran',
                'proses_hapus_jenis_pelanggaran'
            ],

            'laporan' => [
                'laporan',
                'cetak_laporan',
                'detail_laporan'
            ],

            'riwayat_guru' => [
                'riwayat_guru',
                'detail_riwayat'
            ]
        ];

        // ======================================
        // KELOMPOK HALAMAN GURU DAN WALI KELAS
        // ======================================

        $kelompok_guru = [

            'dashboard_guru' => [
                'dashboard_guru'
            ],

            'catat_pelanggaran' => [
                'catat_pelanggaran',
                'tambah_pelanggaran',
                'edit_pelanggaran',
                'hapus_pelanggaran',
                'edit_catatan_pelanggaran',
                'tambah_catatan_pelanggaran',
                'hapus_catatan_pelanggaran',
                'proses_simpan_pelanggaran',
                'proses_tambah_pelanggaran',
                'proses_edit_pelanggaran',
                'proses_hapus_pelanggaran',
                'proses_edit_catatan_pelanggaran',
                'proses_hapus_catatan_pelanggaran',
                'ubah_pelanggaran',
                'detail_pelanggaran',
                'detail_catatan_pelanggaran'
            ],

            'tindakan_guru' => [
                'tindakan_guru',
                'tambah_tindakan',
                'edit_tindakan',
                'hapus_tindakan',
                'proses_tindakan',
                'proses_simpan_tindakan',
                'proses_tambah_tindakan',
                'proses_edit_tindakan',
                'proses_hapus_tindakan'
            ],

            'riwayat_guru' => [
                'riwayat_guru',
                'detail_riwayat'
            ],

            'rekap_poin' => [
                'rekap_poin',
                'detail_rekap'
            ],

            'kelas_saya' => [
                'kelas_saya',
                'detail_siswa_kelas'
            ]
        ];

        // ======================================
        // TENTUKAN KELOMPOK BERDASARKAN ROLE
        // ======================================

        $role = strtolower(trim($_SESSION['role'] ?? ''));

        if (function_exists('normalisasi_role')) {
            $role = normalisasi_role($role);
        }

        if ($role === 'admin') {
            $kelompok_menu = $kelompok_admin;
        } else {
            $kelompok_menu = $kelompok_guru;
        }

        // ======================================
        // CEK MENU AKTIF
        // ======================================

        if ($current_page === $menu) {
            return 'active';
        }

        if (
            isset($kelompok_menu[$menu]) &&
            in_array(
                $current_page,
                $kelompok_menu[$menu],
                true
            )
        ) {
            return 'active';
        }

        return '';
    }
}
?>

<!-- BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
/* SIDEBAR */
.sidebar {
    width: 230px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050;
    background-color: #212529;
    padding: 10px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #6c757d #212529;
}

.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: #6c757d;
    border-radius: 10px;
}

/* BRAND */
.sidebar-brand {
    text-align: center;
    color: white;
    padding: 8px 5px;
    flex-shrink: 0;
}

.sidebar-brand i {
    font-size: 26px;
    margin-bottom: 5px;
}

.sidebar-brand h4 {
    font-size: 20px;
    font-weight: bold;
    margin: 0;
    letter-spacing: 2px;
}

.sidebar-brand small {
    font-size: 10px;
    color: #adb5bd;
}

/* GARIS */
.sidebar hr {
    border-color: #6c757d;
    margin: 6px 0;
}

/* MENU */
.sidebar .nav-link {
    display: flex;
    align-items: center;
    color: #ced4da;
    font-size: 12px;
    padding: 7px 10px;
    margin-bottom: 2px;
    border-radius: 6px;
    white-space: nowrap;
    transition: 0.2s;
}

.sidebar .nav-link i {
    width: 22px;
    min-width: 22px;
    margin-right: 5px;
    text-align: center;
}

.sidebar .nav-link:hover {
    background-color: #343a40;
    color: white;
}

.sidebar .nav-link.active,
.sidebar .nav-link.active:hover {
    background-color: #0d6efd !important;
    color: white !important;
    font-weight: 600;
}

/* MENU BAWAH */
.sidebar-bottom {
    margin-top: auto;
    padding-bottom: 3px;
    flex-shrink: 0;
}

/* KONTEN */
.main-content {
    margin-left: 230px;
    padding: 20px;
    min-width: 0;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }

    .main-content {
        margin-left: 200px;
        padding: 12px;
    }
}
</style>

<div class="sidebar">

    <!-- BRAND -->
    <div class="sidebar-brand">
        <i class="fa-solid fa-school"></i>
        <h4>SIP</h4>
        <small>Sistem Informasi Pelanggaran</small>
    </div>

    <hr>

    <!-- MENU ADMIN -->
    <?php if ($role === 'admin'): ?>

        <ul class="nav nav-pills flex-column mb-0">

            <li class="nav-item">
                <a href="<?= $base_url ?>dashboard.php"
                   class="nav-link <?= menu_aktif('dashboard.php', $current_page) ?>">
                    <i class="fa-solid fa-gauge"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>guru/kelola_guru.php"
                   class="nav-link <?= menu_aktif('kelola_guru.php', $current_page) ?>">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    Kelola Guru
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>kelola_siswa.php"
                   class="nav-link <?= menu_aktif('kelola_siswa.php', $current_page) ?>">
                    <i class="fa-solid fa-users"></i>
                    Kelola Siswa
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>kelola_kelas.php"
                   class="nav-link <?= menu_aktif('kelola_kelas.php', $current_page) ?>">
                    <i class="fa-solid fa-door-open"></i>
                    Kelola Kelas
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>kelola_tahun_ajaran.php"
                   class="nav-link <?= menu_aktif('kelola_tahun_ajaran.php', $current_page) ?>">
                    <i class="fa-solid fa-calendar-days"></i>
                    Kelola Tahun Ajaran
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>penempatan_siswa.php"
                   class="nav-link <?= menu_aktif('penempatan_siswa.php', $current_page) ?>">
                    <i class="fa-solid fa-right-left"></i>
                    Penempatan Siswa
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>kelola_walikelas.php"
                   class="nav-link <?= menu_aktif('kelola_walikelas.php', $current_page) ?>">
                    <i class="fa-solid fa-user-tie"></i>
                    Kelola Wali Kelas
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>kelola_pelanggaran_kategori.php"
                   class="nav-link <?= menu_aktif('kelola_pelanggaran_kategori.php', $current_page) ?>">
                    <i class="fa-solid fa-layer-group"></i>
                    Kategori Pelanggaran
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>kelola_jenis_pelanggaran.php"
                   class="nav-link <?= menu_aktif('kelola_jenis_pelanggaran.php', $current_page) ?>">
                    <i class="fa-solid fa-list-check"></i>
                    Jenis Pelanggaran
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>laporan.php"
                   class="nav-link <?= menu_aktif('laporan.php', $current_page) ?>">
                    <i class="fa-solid fa-file-pdf"></i>
                    Laporan
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>guru/riwayat_guru.php"
                   class="nav-link <?= menu_aktif('riwayat_guru.php', $current_page) ?>">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Riwayat
                </a>
            </li>

        </ul>

    <!-- MENU GURU DAN WALI KELAS -->
    <?php elseif ($role_guru || $role_wali): ?>

        <ul class="nav nav-pills flex-column mb-0">

            <li>
                <a href="<?= $base_url ?>guru/dashboard_guru.php"
                   class="nav-link <?= menu_aktif('dashboard_guru.php', $current_page) ?>">
                    <i class="fa-solid fa-gauge"></i>
                    Dashboard
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>guru/catat_pelanggaran.php"
                   class="nav-link <?= menu_aktif('catat_pelanggaran.php', $current_page) ?>">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Catat Pelanggaran
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>guru/tindakan_guru.php"
                   class="nav-link <?= menu_aktif('tindakan_guru.php', $current_page) ?>">
                    <i class="fa-solid fa-handshake-angle"></i>
                    Tindakan
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>guru/riwayat_guru.php"
                   class="nav-link <?= menu_aktif('riwayat_guru.php', $current_page) ?>">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Riwayat
                </a>
            </li>

            <li>
                <a href="<?= $base_url ?>guru/rekap_poin.php"
                   class="nav-link <?= menu_aktif('rekap_poin.php', $current_page) ?>">
                    <i class="fa-solid fa-list-check"></i>
                    Rekap Poin
                </a>
            </li>

            <?php if ($role_wali): ?>
                <li>
                    <a href="<?= $base_url ?>guru/kelas_saya.php"
                       class="nav-link <?= menu_aktif('kelas_saya.php', $current_page) ?>">
                        <i class="fa-solid fa-users-rectangle"></i>
                        Kelas Saya
                    </a>
                </li>
            <?php endif; ?>

        </ul>

    <?php endif; ?>

    <!-- MENU BAWAH -->
    <div class="sidebar-bottom">

        <hr>

        <ul class="nav nav-pills flex-column mb-0">

            <?php if (!$role_wali): ?>
                <li>
                    <a href="<?= $base_url ?>about_me.php"
                       class="nav-link <?= menu_aktif('about_me.php', $current_page) ?>">
                        <i class="fa-solid fa-user"></i>
                        About Me
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="<?= $base_url ?>logout.php"
                   class="nav-link text-danger"
                   onclick="return confirm('Yakin ingin keluar?');">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout
                </a>
            </li>

        </ul>

    </div>

</div>
