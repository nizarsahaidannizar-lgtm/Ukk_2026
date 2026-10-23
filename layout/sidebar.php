<?php
// layout/sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? ''; 

// Deteksi otomatis jika sidebar dipanggil dari dalam folder guru/
$prefix = (strpos($_SERVER['PHP_SELF'], '/guru/') !== false) ? '../' : '';
if ($role === "admin") {
?>
    <!-- SIDEBAR ADMIN (SEMUA FITUR) -->
    <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3 sidebar-sticky">
        <h4 class="text-white mb-4">Sistem Pelanggaran<span class="d-block">Siswa</span></h4>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : 'text-white' ?>">Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_siswa.php" class="nav-link <?= ($current_page == 'kelola_siswa.php') ? 'active' : 'text-white' ?>">Data Siswa</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/kelola_guru.php" class="nav-link <?= ($current_page == 'kelola_guru.php') ? 'active' : 'text-white' ?>">Data Guru</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_kelas.php" class="nav-link <?= ($current_page == 'kelola_kelas.php') ? 'active' : 'text-white' ?>">Data Kelas</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_tahun_ajaran.php" class="nav-link <?= ($current_page == 'kelola_tahun_ajaran.php') ? 'active' : 'text-white' ?>">Data Tahun Ajaran</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_walikelas.php" class="nav-link <?= ($current_page == 'kelola_walikelas.php') ? 'active' : 'text-white' ?>">Data Wali Kelas</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>penempatan_siswa.php" class="nav-link <?= ($current_page == 'penempatan_siswa.php') ? 'active' : 'text-white' ?>">Penempatan Siswa</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>kelola_pelanggaran_kategori.php" class="nav-link <?= ($current_page == 'kelola_pelanggaran_kategori.php') ? 'active' : 'text-white' ?>">Kategori Pelanggaran</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>laporan.php" class="nav-link <?= ($current_page == 'laporan.php') ? 'active' : 'text-white' ?>">Laporan</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>riwayat.php" class="nav-link <?= ($current_page == 'riwayat.php') ? 'active' : 'text-white' ?>">Riwayat</a>
            </li>
            <hr class="text-secondary">
            <li class="nav-item">
                <a href="<?= $prefix ?>logout.php" class="nav-link text-danger" onclick="return confirm('Yakin ingin keluar?');">Logout</a>
            </li>
        </ul>
    </div>

<?php
} elseif ($role === "guru") {
?>
    <!-- SIDEBAR GURU (SESUAI USE CASE) -->
    <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3 sidebar-sticky">
        <h4 class="text-white mb-4">Sistem Pelanggaran<span class="d-block">Siswa</span></h4>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/dashboard_guru.php" class="nav-link <?= ($current_page == 'dashboard_guru.php') ? 'active' : 'text-white' ?>">Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/catat_pelanggaran.php" class="nav-link <?= ($current_page == 'catat_pelanggaran.php') ? 'active' : 'text-white' ?>">Catat Pelanggaran</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/tindakan_guru.php" class="nav-link <?= ($current_page == 'tindakan_guru.php') ? 'active' : 'text-white' ?>">Tindakan</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/laporan_guru.php" class="nav-link <?= ($current_page == 'laporan_guru.php') ? 'active' : 'text-white' ?>">Laporan</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>cetak_laporan.php" class="nav-link <?= ($current_page == 'cetak_laporan.php') ? 'active' : 'text-white' ?>" target="_blank">Cetak Laporan</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/riwayat_guru.php" class="nav-link <?= ($current_page == 'riwayat_guru.php') ? 'active' : 'text-white' ?>">Riwayat</a>
            </li>
            <li class="nav-item mb-2">
                <a href="<?= $prefix ?>guru/rekap_poin.php" class="nav-link <?= ($current_page == 'rekap_poin.php') ? 'active' : 'text-white' ?>">Rekap Poin</a>
            </li>
            <hr class="text-secondary">
            <li class="nav-item">
                <a href="<?= $prefix ?>logout.php" class="nav-link text-danger" onclick="return confirm('Yakin ingin keluar?');">Logout</a>
            </li>
        </ul>
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