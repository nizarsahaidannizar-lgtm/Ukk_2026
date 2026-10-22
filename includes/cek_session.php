<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    header("Location: " . (strpos($_SERVER['PHP_SELF'], '/guru/') !== false ? '../login.php' : 'login.php'));
    exit();
}

function batasi_akses(array $roles_diizinkan) {
    $role_aktif = strtolower(trim($_SESSION['role'] ?? ''));
    $roles_diizinkan = array_map('strtolower', $roles_diizinkan);

    if (!in_array($role_aktif, $roles_diizinkan, true)) {
        $_SESSION['pesan_error'] = 'Akses ditolak. Anda tidak memiliki izin ke halaman ini.';
        $prefix = strpos($_SERVER['PHP_SELF'], '/guru/') !== false ? '../' : '';
        if ($role_aktif === 'admin') {
            header('Location: ' . $prefix . 'dashboard.php');
        } elseif ($role_aktif === 'guru') {
            header('Location: ' . $prefix . 'catat_pelanggaran.php');
        } else {
            header('Location: ' . $prefix . 'login.php');
        }
        exit();
    }
}

$halaman = basename($_SERVER['PHP_SELF']);
$hak_akses_halaman = [
    'dashboard.php' => ['admin'],
    'dashboard_guru.php' => [],
    'kelola_siswa.php' => ['admin'],
    'tambah_siswa.php' => ['admin'],
    'edit_siswa.php' => ['admin'],
    'hapus_siswa.php' => ['admin'],
    'proses_tambah_siswa.php' => ['admin'],
    'proses_edit_siswa.php' => ['admin'],
    'penempatan_siswa.php' => ['admin'],
    'proses_simpan_penempatan.php' => ['admin'],
    'catat_pelanggaran.php' => ['guru'],
    'proses_simpan_pelanggaran.php' => ['guru'],
    'tindakan.php' => ['admin', 'guru'],
    'proses_tindakan.php' => ['admin', 'guru'],
    'laporan.php' => ['admin', 'guru'],
    'cetak_laporan.php' => ['admin'],
    'riwayat.php' => ['admin', 'guru'],
    'rekap_poin.php' => ['admin', 'guru'],
];

batasi_akses($hak_akses_halaman[$halaman] ?? ['admin']);
?>