<?php
// layout/sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? ''; 
$is_admin_menu_active = static function (array $pages) use ($current_page): string {
    return in_array($current_page, $pages, true) ? 'active' : 'text-white';
};

// Deteksi otomatis jika sidebar dipanggil dari dalam folder guru/
$prefix = (strpos($_SERVER['PHP_SELF'], '/guru/') !== false) ? '../' : '';
?>
<svg class="sidebar-icon-definitions" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <symbol id="sidebar-icon-dashboard" viewBox="0 0 24 24"><path d="M4 13a8 8 0 1 1 16 0M12 13l4-4M5 19h14"/><circle cx="12" cy="13" r="1"/></symbol>
    <symbol id="sidebar-icon-students" viewBox="0 0 24 24"><path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20M10 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM16 4.3a3.5 3.5 0 0 1 0 6.7M17 15h1.5a3.5 3.5 0 0 1 3.5 3.5V20"/></symbol>
    <symbol id="sidebar-icon-teacher" viewBox="0 0 24 24"><path d="M3 5h13v10H3zM6 19l3-4 3 4M19 8v12M16 20h6M19 4v1M18 4h2"/><circle cx="9.5" cy="9" r="2"/></symbol>
    <symbol id="sidebar-icon-class" viewBox="0 0 24 24"><path d="M4 21V4h12v17M16 10h4v11M8 8h4M8 12h4M8 16h4M2 21h20"/></symbol>
    <symbol id="sidebar-icon-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01"/></symbol>
    <symbol id="sidebar-icon-homeroom" viewBox="0 0 24 24"><circle cx="12" cy="7" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2M9 14l3 3 3-3"/></symbol>
    <symbol id="sidebar-icon-placement" viewBox="0 0 24 24"><path d="M7 7h13l-3-3M20 7l-3 3M17 17H4l3 3M4 17l3-3"/></symbol>
    <symbol id="sidebar-icon-warning" viewBox="0 0 24 24"><path d="M10.3 4.3 2.8 18a2 2 0 0 0 1.8 3h14.8a2 2 0 0 0 1.8-3L13.7 4.3a2 2 0 0 0-3.4 0Z"/><path d="M12 9v5M12 17h.01"/></symbol>
    <symbol id="sidebar-icon-report" viewBox="0 0 24 24"><path d="M4 20V4M4 20h17M8 17v-5M12 17V8M16 17v-7M20 17V5"/></symbol>
    <symbol id="sidebar-icon-history" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 2.6-6.4L3 8M3 4v4h4M12 7v5l3 2"/></symbol>
    <symbol id="sidebar-icon-logout" viewBox="0 0 24 24"><path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5M15 16l4-4-4-4M19 12H9"/></symbol>
</svg>
<?php
if ($role === "admin") {
?>
    <!-- SIDEBAR ADMIN (SEMUA FITUR) -->
    <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3 sidebar-sticky sidebar-admin">
        <div class="sidebar-brand d-flex align-items-center gap-2 mb-4">
            <div class="bg-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                <!-- Font Awesome Free school icon; CC BY 4.0, https://fontawesome.com/license/free -->
                <svg class="sidebar-logo" viewBox="0 0 640 512" role="img" aria-label="Logo sekolah">
                    <path fill="currentColor" d="M337.8 5.4C327-1.8 313-1.8 302.2 5.4L166.3 96H48C21.5 96 0 117.5 0 144V464c0 26.5 21.5 48 48 48h208v-96c0-35.3 28.7-64 64-64s64 28.7 64 64v96h208c26.5 0 48-21.5 48-48V144c0-26.5-21.5-48-48-48H473.7L337.8 5.4zM96 192h32c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16H96c-8.8 0-16-7.2-16-16v-64c0-8.8 7.2-16 16-16zm400 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-64zM96 320h32c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16H96c-8.8 0-16-7.2-16-16v-64c0-8.8 7.2-16 16-16zm400 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-64zM232 176a88 88 0 1 1 176 0 88 88 0 1 1-176 0zm88-48c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16h-16v-16c0-8.8-7.2-16-16-16z"/>
                </svg>
            </div>
            <h4 class="sidebar-brand-title text-white mb-0">Sistem Pelanggaran<span class="d-block">Siswa</span></h4>
        </div>
        <ul class="sidebar-menu nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>dashboard.php" class="nav-link <?= $is_admin_menu_active(['dashboard.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-dashboard"/></svg><span>Dashboard</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_siswa.php" class="nav-link <?= $is_admin_menu_active(['kelola_siswa.php', 'tambah_siswa.php', 'edit_siswa.php', 'hapus_siswa.php', 'proses_tambah_siswa.php', 'proses_edit_siswa.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-students"/></svg><span>Data Siswa</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/kelola_guru.php" class="nav-link <?= $is_admin_menu_active(['kelola_guru.php', 'tambah_guru.php', 'edit_guru.php', 'hapus_guru.php', 'proses_tambah_guru.php', 'proses_edit_guru.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-teacher"/></svg><span>Data Guru</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_kelas.php" class="nav-link <?= $is_admin_menu_active(['kelola_kelas.php', 'tambah_kelas.php', 'edit_kelas.php', 'hapus_kelas.php', 'proses_tambah_kelas.php', 'proses_edit_kelas.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-class"/></svg><span>Data Kelas</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_tahun_ajaran.php" class="nav-link <?= $is_admin_menu_active(['kelola_tahun_ajaran.php', 'tambah_tahun_ajaran.php', 'edit_tahun_ajaran.php', 'hapus_tahun_ajaran.php', 'proses_tambah_tahun_ajaran.php', 'proses_edit_tahun_ajaran.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-calendar"/></svg><span>Data Tahun Ajaran</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_walikelas.php" class="nav-link <?= $is_admin_menu_active(['kelola_walikelas.php', 'tambah_walikelas.php', 'edit_walikelas.php', 'hapus_walikelas.php', 'proses_tambah_walikelas.php', 'proses_edit_walikelas.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-homeroom"/></svg><span>Data Wali Kelas</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>penempatan_siswa.php" class="nav-link <?= $is_admin_menu_active(['penempatan_siswa.php', 'proses_simpan_penempatan.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-placement"/></svg><span>Penempatan Siswa</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_pelanggaran_kategori.php" class="nav-link <?= $is_admin_menu_active(['kelola_pelanggaran_kategori.php', 'tambah_pelanggaran_kategori.php', 'edit_pelanggaran_kategori.php', 'hapus_pelanggaran_kategori.php', 'proses_tambah_pelanggaran_kategori.php', 'proses_edit_pelanggaran.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-warning"/></svg><span>Kategori Pelanggaran</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>laporan.php" class="nav-link <?= $is_admin_menu_active(['laporan.php', 'cetak_laporan.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-report"/></svg><span>Laporan</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_jenis_pelanggaran.php" class="nav-link <?= $is_admin_menu_active(['kelola_jenis_pelanggaran.php', 'tambah_jenis_pelanggaran.php', 'proses_tambah_jenis_pelanggaran.php', 'edit_jenis_pelanggaran.php', 'proses_edit_jenis_pelanggaran.php', 'hapus_jenis_pelanggaran.php']) ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-warning"/></svg><span>Jenis Pelanggaran</span></a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="<?= $prefix ?>logout.php" class="nav-link text-danger" onclick="return confirm('Yakin ingin keluar?');"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-logout"/></svg><span>Logout</span></a>
        </div>
    </div>

<?php
} elseif ($role === "guru") {
?>
    <!-- SIDEBAR GURU (SESUAI USE CASE) -->
    <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3 sidebar-sticky sidebar-guru-fixed">
        <div class="sidebar-brand d-flex align-items-center gap-2 mb-4">
            <div class="bg-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                <svg class="sidebar-logo" viewBox="0 0 640 512" role="img" aria-label="Logo sekolah">
                    <path d="M337.8 5.4C327-1.8 313-1.8 302.2 5.4L166.3 96H48C21.5 96 0 117.5 0 144V464c0 26.5 21.5 48 48 48h208v-96c0-35.3 28.7-64 64-64s64 28.7 64 64v96h208c26.5 0 48-21.5 48-48V144c0-26.5-21.5-48-48-48H473.7L337.8 5.4zM96 192h32c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16H96c-8.8 0-16-7.2-16-16v-64c0-8.8 7.2-16 16-16zm400 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-64zM96 320h32c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16H96c-8.8 0-16-7.2-16-16v-64c0-8.8 7.2-16 16-16zm400 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v64c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-64zM232 176a88 88 0 1 1 176 0 88 88 0 1 1-176 0zm88-48c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16h-16v-16c0-8.8-7.2-16-16-16z" fill="currentColor"/>
                </svg>
            </div>
            <h4 class="sidebar-brand-title text-white mb-0">Sistem Pelanggaran<span class="d-block">Siswa</span></h4>
        </div>
        <ul class="sidebar-menu nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/dashboard_guru.php" class="nav-link <?= ($current_page == 'dashboard_guru.php') ? 'active' : 'text-white' ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-dashboard"/></svg><span>Dashboard</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/catat_pelanggaran.php" class="nav-link <?= in_array($current_page, ['catat_pelanggaran.php', 'edit_catatan_pelanggaran.php'], true) ? 'active' : 'text-white' ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-warning"/></svg><span>Catat Pelanggaran</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/tindakan_guru.php" class="nav-link <?= ($current_page == 'tindakan_guru.php') ? 'active' : 'text-white' ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-placement"/></svg><span>Tindakan</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/laporan_guru.php" class="nav-link <?= ($current_page == 'laporan_guru.php') ? 'active' : 'text-white' ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-report"/></svg><span>Laporan</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/riwayat_guru.php" class="nav-link <?= ($current_page == 'riwayat_guru.php') ? 'active' : 'text-white' ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-history"/></svg><span>Riwayat</span></a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/rekap_poin.php" class="nav-link <?= ($current_page == 'rekap_poin.php') ? 'active' : 'text-white' ?>"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-report"/></svg><span>Rekap Poin</span></a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="<?= $prefix ?>logout.php" class="nav-link text-danger" onclick="return confirm('Yakin ingin keluar?');"><svg class="sidebar-menu-icon" aria-hidden="true"><use href="#sidebar-icon-logout"/></svg><span>Logout</span></a>
        </div>
    </div>
<?php
}
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const textLimits = <?= json_encode($aturan_batas_input[$current_page] ?? []) ?>;
    const numberLimits = <?= json_encode($aturan_batas_angka[$current_page] ?? []) ?>;

    Object.entries(textLimits).forEach(([name, rule]) => {
        Array.from(document.getElementsByName(name)).forEach((field) => {
            if ('max' in rule) field.maxLength = rule.max;
            if ('min' in rule) field.minLength = rule.min;
        });
    });

    Object.entries(numberLimits).forEach(([name, rule]) => {
        Array.from(document.getElementsByName(name)).forEach((field) => {
            field.min = rule.min;
            field.max = rule.max;
        });
    });
});
</script>